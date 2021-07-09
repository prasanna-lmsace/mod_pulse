<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   mod_pulse
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined( 'MOODLE_INTERNAL') || die(' No direct access ');

define( 'MAX_PULSE_NAME_LENGTH', 50);

global $PAGE;

$PAGE->requires->js_call_amd('mod_pulse/completion', 'init');

require_once($CFG->libdir."/completionlib.php");

require_once($CFG->dirroot.'/mod/pulse/locallib.php');

/**
 * Add pulse instance.
 *
 * @param  mixed $pulse
 * @return void
 */
function pulse_add_instance($pulse) {
    global $DB;

    $context = context_module::instance($pulse->coursemodule);

    // $pulse->name = get_pulse_name($pulse->intro, $context);
    $pulse->timemodified = time();

    if (isset($pulse->pulse_content_editor)) {
        $pulse->pulse_content = file_save_draft_area_files($pulse->pulse_content_editor['itemid'],
                                                    $context->id, 'mod_pulse', 'pulse_content', 0,
                                                    array('subdirs' => true), $pulse->pulse_content_editor['text']);
        $pulse->pulse_contentformat = $pulse->pulse_content_editor['format'];
        unset($pulse->pulse_content_editor);
    }
    // Insert the instance in DB.
    $pulseid = $DB->insert_record('pulse', $pulse);

    pulse_extend_add_instance($pulseid, $pulse);

  

    // Retrun new instance id.
    return $pulseid;
}

/**
 * Update pulse instance.
 *
 * @param  mixed $pulse formdata in object.
 * @return bool Update record instance result.
 */
function pulse_update_instance($pulse) {
    global $DB;

    $context = context_module::instance($pulse->coursemodule);

    $pulse->id = $pulse->instance;
    $pulse->timemodified = time();
    if (isset($pulse->pulse_content_editor)) {
        // Save pulse content areafiles.
        $pulse->pulse_content = file_save_draft_area_files($pulse->pulse_content_editor['itemid'],
                                                    $context->id, 'mod_pulse', 'pulse_content', 0,
                                                    array('subdirs' => true), $pulse->pulse_content_editor['text']);
        $pulse->pulse_contentformat = $pulse->pulse_content_editor['format'];
        unset($pulse->pulse_content_editor);
    }
    // Update instance data.
    $updates = $DB->update_record('pulse', $pulse);

    pulse_extend_update_instance($pulse);


    return $updates;
}


/**
 * Delete Pulse instnace
 *
 * @param  mixed $pulseid
 * @return bool
 */
function pulse_delete_instance($pulseid) {
    global $DB;
    if ($DB->record_exists('pulse', ['id' => $pulseid])) {
        if ($DB->delete_records('pulse', ['id' => $pulseid])) {
            return true;
        }
        pulse_extend_delete_instance($pulseid);
    }
    return false;
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function pulse_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_NO_VIEW_LINK:
            return true;

        default:
            return null;
    }
}

/**
 * Pulse form editor element options.
 *
 * @return array
 */
function pulse_get_editor_options() {
    return array('maxfiles' => EDITOR_UNLIMITED_FILES,
                'trusttext' => true);
}

/**
 * Process template text and send pulse to the course users.
 *
 * @param  mixed $pulseid
 * @return void
 */
function mod_pulse_send_pulse($users, $pulse, $course, $context, $addnotify=true, $extend=true) {
    global $DB;
    if (!empty($pulse) && !empty($users)) {
        // Get course module using instanceid.
        // Check pulse enabled.
        if ($pulse->pulse == true) {
            $notifiedusers = [];
            // Collect list of available enrolled students in course module.
            mtrace('Sending pulse to enrolled users in course '.$course->fullname."\n");
            foreach ($users as $key => $student) {
                $userto = $student; // Send to.
                $subject = $pulse->pulse_subject ?: get_string('pulse_subject', 'pulse'); // Message subject.
                // Use intro content as message text, if different pulse disabled.
                $template = $pulse->intro;
                $filearea = 'intro';
                if ($pulse->diff_pulse) {
                    // Email template content.
                    $template = $pulse->pulse_content;
                    $filearea = 'pulse_content';
                }
                // Replace the email text placeholders with data.
                list($subject, $messagehtml) = mod_pulse_update_emailvars($template, $subject, $course, $student, $pulse);
                // Rewrite the plugin file placeholders in the email text.
                $messagehtml = file_rewrite_pluginfile_urls($messagehtml, 'pluginfile.php',
                $context->id, 'mod_pulse', $filearea, 0);
                $messageplain = html_to_text($messagehtml); // Plain text.
                // Send message to user.
                mtrace("Sending pulse to the user ". fullname($userto) ."\n" );
                // echo $messagehtml;
                $messagesend = mod_pulse_messagetouser($userto, $subject, $messageplain, $messagehtml, $pulse, $extend);
                if ($messagesend) {
                    $notifiedusers[] = $userto->id;
                }
            }
            if ($addnotify) {
                mod_pulse_update_notified_users($notifiedusers, $course, $pulse);
            }
        }
    }
}

/**
 * Update the user id in the db notified users list.
 *
 * @param  mixed $users
 * @param  mixed $course
 * @param  mixed $pulse
 * @return void
 */
function mod_pulse_update_notified_users($users, $course, $pulse) {
    global $DB;
    $condition = ['course' => $course->id, 'pulse' => $pulse->id];
    if ($id = $DB->record_exists('pulse_users', $condition)) {
        $notifiedusers = $DB->get_field('pulse_users', 'notified_users', $condition);
        $list = json_decode($notifiedusers);
        if (empty($users)) {
            $users = [];
        }
        $notifiedusers = array_merge($list, $users);
        $notifiedusers = json_encode($notifiedusers);
        $DB->set_field('pulse_users', 'notified_users', $notifiedusers, $condition);
    } else {
        $record = new stdclass();
        $record->course = $course->id;
        $record->pulse = $pulse->id;
        $record->notified_users = json_encode($users);
        $DB->insert_record('pulse_users', $record);
    }
}

/**
 * Replace email template placeholders with dynamic datas.
 *
 * @param  mixed $templatetext
 * @param  mixed $subject
 * @param  mixed $course
 * @param  mixed $user
 * @return void
 */
function mod_pulse_update_emailvars($templatetext, $subject, $course, $user, $mod) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/pulse/lib/vars.php');
    $sender = core_user::get_support_user(); // Support user.
    $amethods = EmailVars::vars(); // List of available placeholders.
    $vars = new EmailVars($user, $course, $sender, $mod);
    foreach ($amethods as $funcname) {
        $replacement = "{" . $funcname . "}";
        // Message text placeholder update.
        if (stripos($templatetext, $replacement) !== false) {
            $val = $vars->$funcname;
            // Placeholder found on the text, then replace with data.
            $templatetext = str_replace($replacement, $val, $templatetext);
        }
        // Replace message subject placeholder.
        if (stripos($subject, $replacement) !== false) {
            $val = $vars->$funcname;
            $subject = str_replace($replacement, $val, $subject);
        }
    }
    return [$subject, $templatetext];
}

/**
 * List of available enrolled users in the course
 *
 * @param  mixed $context module context.
 * @return array $students listof students.
 */
function mod_pulse_get_course_students($students, $cm=[], $notifiedusers=[], $course) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/mod/pulse/locallib.php');

    $modinfo = new pulse_course_modinfo($course, 0);
    // Filter available users.
    if (!empty($cm)) {
        mtrace('Filter users based on their availablity..');
        $section = ($cm->get_section_info());
        $sectioninfo = new \core_availability\info_section($section);
        foreach ($students as $student) {
            $pulse = '';
            // Update the modinfo user id to fix the group availality condition issue.
            $modinfo->changeuserid($student->id);

            $info = new \core_availability\info_module($cm);
            if (!$sectioninfo->is_available($pulse, false, $student->id, $modinfo)
                || !$info->is_available($pulse, false, $student->id, $modinfo) ) {
                unset($students[$student->id]);
            }

        }
    }

    if (!empty($notifiedusers)) {
        mtrace('Filter already notified users...');
        foreach ($notifiedusers as $userid) {
            if (in_array($userid, array_keys($students))) {
                unset($students[$userid]);
            }
        }
    }
    return $students;
}


/**
 * Send message to user using message api.
 *
 * @param  mixed $userto
 * @param  mixed $subject
 * @param  mixed $messageplain
 * @param  mixed $messagehtml
 * @param  mixed $pulse
 * @return bool message status
 */
function mod_pulse_messagetouser($userto, $subject, $messageplain, $messagehtml, $pulse, $extend=true) {
    $eventdata = new \core\message\message();
    $eventdata->name = 'mod_pulse';
    $eventdata->component = 'mod_pulse';
    $eventdata->courseid = $pulse->course;
    $eventdata->modulename = 'pulse';
    $eventdata->userfrom = core_user::get_support_user();
    $eventdata->userto = $userto;
    $eventdata->subject = $subject;
    $eventdata->fullmessage = $messageplain;
    $eventdata->fullmessageformat = FORMAT_HTML;
    $eventdata->fullmessagehtml = $messagehtml;
    $eventdata->smallmessage = $subject;
    if (message_send($eventdata)) {
        // Extend the pro plugin to send the approval mail.
        if ($extend) {
            pulse_extend_student_messagesend($userto, $pulse, $eventdata);
        }
        mtrace( "Pulse send to the user.");
        return true;
    } else {
        mtrace( "Failed - Pulse send to the user.");
        return false;
    }
}

/**
 * Serve the files from the Pulse file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function pulse_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'pulse_content' && $filearea !== 'intro') {
        return false;
    }

    // Item id is 0.
    $itemid = array_shift($args);

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // ...$args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // ...$args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_pulse', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

/**
 * Pulse cron task to send notification for course users.
 *
 * @return void
 */
function mod_pulse_cron_task() {
    global $DB;

    mtrace( 'Fetching notificaion instance list - MOD-Pulse INIT ');

    $rolesql = "SELECT  rc.roleid FROM {role_capabilities} rc
            JOIN {capabilities} cap ON rc.capability = cap.name
            JOIN {context} ctx on rc.contextid = ctx.id
            WHERE rc.capability = :capability ";
    $roles = $DB->get_records_sql($rolesql, ['capability' => 'mod/pulse:notifyuser']);
    $roles = array_column($roles, 'roleid');

    list($roleinsql, $roleinparams) = $DB->get_in_or_equal($roles);

    $sql = "SELECT nt.id as nid, nt.*, '' as pulseend, 
    cm.id as cmid, cm.*, md.id as mid, nou.id as nouid,
    nou.notified_users, ctx.id as contextid, ctx.*, cu.id as courseid, cu.* FROM {pulse} nt
    JOIN {course_modules} cm ON cm.instance = nt.id
    JOIN {modules} md ON md.id = cm.module
    LEFT JOIN {pulse_users} nou ON  nou.pulse = nt.id and nt.course = nou.course
    JOIN {course} cu on cu.id = nt.course
    RIGHT JOIN {context} ctx on ctx.instanceid = cm.id and contextlevel = 70
    WHERE md.name = 'pulse'";

    $records = $DB->get_records_sql($sql, []);
    if (empty($records)) {
        mtrace('No pulse instance are added yet'."\n");
        return true;
    }
    $modinfo = [];
    foreach ($records as $key => $record) {        
        $params = [];
        $record = (array) $record;
        $keys = array_keys($record);
        // Pulse.
        $pulseendpos = array_search('pulseend', $keys);
        $pulse = array_slice($record, 0, $pulseendpos);
        $pulse['id'] = $pulse['nid'];
        mtrace( 'Initiate pulse module - '.$pulse['name'] );
        // Context.
        $ctxpos = array_search('contextid', $keys);
        $ctxendpos = array_search('locked', $keys);
        $context = array_slice($record, $ctxpos, ($ctxendpos - $ctxpos) + 1 );
        $context['id'] = $context['contextid']; unset($context['contextid']);
        // Course module.
        $cmpos = array_search('cmid', $keys);
        $cmendpos = array_search('deletioninprogress', $keys);
        $cm = array_slice($record, $cmpos, ($cmendpos - $cmpos) + 1 );
        $cm['id'] = $cm['cmid']; unset($cm['cmid']);
        // Course records.
        $coursepos = array_search('courseid', $keys);
        $course = array_slice($record, $coursepos);
        $course['id'] = $course['courseid'];
        // Get enrolled users with capability.
        $contextlevel = explode('/', $context['path']);
        list($insql, $inparams) = $DB->get_in_or_equal(array_filter($contextlevel));
        // Enrolled  users list.
        $usersql = "SELECT u.*
                FROM {user} u
                JOIN (SELECT DISTINCT eu1_u.id
                FROM {user} eu1_u
                JOIN {user_enrolments} ej1_ue ON ej1_ue.userid = eu1_u.id
                JOIN {enrol} ej1_e ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = ?)
                JOIN (SELECT DISTINCT userid
                                FROM {role_assignments}
                                WHERE contextid $insql
                                AND roleid $roleinsql
                            ) ra ON ra.userid = eu1_u.id
            WHERE 1 = 1 AND eu1_u.deleted = 0 AND eu1_u.id <> ? AND eu1_u.deleted = 0) je ON je.id = u.id
            WHERE u.deleted = 0 ORDER BY u.lastname, u.firstname, u.id";

        $params[] = $course['id'];
        $params = array_merge($params, array_filter($inparams));
        $params = array_merge($params, array_filter($roleinparams));
        $params[] = 1;
        $students = $DB->get_records_sql($usersql, $params);

        $notifiedusers = json_decode($record['notified_users']);
        $courseid = $pulse['course'];
        if (!in_array($courseid, $modinfo)) {
            $modinfo[$courseid] = get_fast_modinfo($courseid, 0);
        }
        $cm = $modinfo[$course['id']]->get_cm($cm['id']);

        // Filter users from course pariticipants by completion.
        $listofusers = mod_pulse_get_course_students($students, $cm, $notifiedusers, (object) $course );
        if (!empty($listofusers)) {
            mod_pulse_send_pulse($listofusers, (object) $pulse, (object) $course, (object) $context);
        } else {
            mtrace('There is not users to send pulse');
        }
    }

    mtrace('Pulse message sending completed....');
}


/**
 * Add a get_coursemodule_info function in case any pulse type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function pulse_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, intro, introformat, completionavailable, completionself, completionapproval, completionapprovalroles';
    if (!$pulse = $DB->get_record('pulse', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $pulse->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('pulse', $pulse, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionself'] = $pulse->completionself;
        $result->customdata['customcompletionrules']['completionwhenavailable'] = $pulse->completionavailable;
        $result->customdata['customcompletionrules']['completionapproval'] = $pulse->completionapproval;
    }

    // Populate some other values that can be used in calendar or on dashboard.
    if ($pulse->completionapprovalroles ) {
        $result->customdata['completionapprovalroles'] = $pulse->completionapprovalroles;

    }

    return $result;
}



/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_pulse_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionwhenavailable':
                $descriptions[] = get_string('completionwhenavailable', 'pulse');
                break;
            case 'completionself':
                $descriptions[] = get_string('completionself', 'pulse');
                break;
            case 'completionapproval':
                $descriptions[] = get_string('completionrequireapproval', 'pulse');
                break;
            default:
                break;
        }
    }
    return $descriptions;
}


/**
 * Obtains the automatic completion state for this forum based on any conditions
 * in forum settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function pulse_get_completion_state($course, $cm, $userid, $type, $pulse=null, $completion=null, $modinfo=null) {
    global $CFG, $DB;

    if ($pulse == null) {
        $pulse = $DB->get_record('pulse', ['id' => $cm->instance], "*", MUST_EXIST);
    }

    if ($completion == null) {
        $completion = $DB->get_record('pulse_completion', ['userid' => $userid, 'pulseid' => $pulse->id]);
    }
    $status = COMPLETION_INCOMPLETE;
    // Module availablity completion for student.
    if ($pulse->completionavailable) {
        if ($modinfo == null) {
            $modinfo = get_fast_modinfo($course->id, $userid);
        }
        $cm = $modinfo->get_cm($cm->id);
        $info = new \core_availability\info_module($cm);
        $str = '';
        // Get section info for cm.
        // Check section is accessable by user.
        $section = $cm->get_section_info();
        $sectioninfo = new \core_availability\info_section($section);

        if ($sectioninfo->is_available($str, false, $userid, $modinfo) && $info->is_available($str, false, $userid, $modinfo )) {
            $status = COMPLETION_COMPLETE;
        } else {
            return COMPLETION_INCOMPLETE;
        }
    }
    
    // Completion by any selected role user.
    if ($pulse->completionapproval) {
        if (!empty($completion) && $completion->approvalstatus == 1) {
            $status = COMPLETION_COMPLETE;
        } else {
            return COMPLETION_INCOMPLETE;
        }
    }
    // Self completion by own.
    if ($pulse->completionself) {
        if (!empty($completion) && $completion->selfcompletion == 1) {
            $status = COMPLETION_COMPLETE;
        } else {
            return COMPLETION_INCOMPLETE;
        }
    }

    return $status;
}


/**
 * Cron task for completion check for students in all pulse module.
 *
 * @return void
 */
function mod_pulse_completion_crontask() {
    global $DB, $USER;

    mtrace('Pulse activity completion - Pulse Starting');

    mtrace('Fetching pulse instance list - MOD-Pulse INIT');

    $rolesql = "SELECT  rc.roleid FROM {role_capabilities} rc
            JOIN {capabilities} cap ON rc.capability = cap.name
            JOIN {context} ctx on rc.contextid = ctx.id
            WHERE rc.capability = :capability ";
    $roles = $DB->get_records_sql($rolesql, ['capability' => 'mod/pulse:notifyuser']);
    $roles = array_column($roles, 'roleid');

    list($roleinsql, $roleinparams) = $DB->get_in_or_equal($roles);

    $sql = "SELECT nt.id as nid, nt.*, '' as pulseend, cm.id as cmid, cm.*, md.id as mid,
    ctx.id as contextid, ctx.*, cu.id as courseid, cu.*
    FROM {pulse} nt
    JOIN {course_modules} cm ON cm.instance = nt.id
    JOIN {modules} md ON md.id = cm.module
    JOIN {course} cu on cu.id = nt.course
    RIGHT JOIN {context} ctx on ctx.instanceid = cm.id and contextlevel = 70
    WHERE md.name = 'pulse' ";

    $records = $DB->get_records_sql($sql, []);
    if (empty($records)) {
        mtrace('No pulse instance are added yet'."\n");
        return true;
    }
    $modinfo = [];
    foreach ($records as $key => $record) {

        $params = [];
        $record = (array) $record;
        $keys = array_keys($record);
        // Pulse.
        $pulseendpos = array_search('pulseend', $keys);
        $pulse = array_slice($record, 0, $pulseendpos);
        $pulse['id'] = $pulse['nid'];

        mtrace("Check the user module completion - Pulse id: ".$pulse['id']);
        // Context.
        $ctxpos = array_search('contextid', $keys);
        $ctxendpos = array_search('locked', $keys);
        $context = array_slice($record, $ctxpos, ($ctxendpos - $ctxpos) + 1 );
        $context['id'] = $context['contextid']; unset($context['contextid']);
        // Course module.
        $cmpos = array_search('cmid', $keys);
        $cmendpos = array_search('deletioninprogress', $keys);
        $cm = array_slice($record, $cmpos, ($cmendpos - $cmpos) + 1 );
        $cm['id'] = $cm['cmid']; unset($cm['cmid']);
        // Course records.
        $coursepos = array_search('courseid', $keys);
        $course = array_slice($record, $coursepos);
        $course['id'] = $course['courseid'];
        // Get enrolled users with capability.
        $contextlevel = explode('/', $context['path']);
        list($insql, $inparams) = $DB->get_in_or_equal(array_filter($contextlevel));
        // Enrolled  users list.
        $usersql = "SELECT u.*, je.*
                FROM {user} u
                JOIN (
                    SELECT DISTINCT eu1_u.id, pc.id as pcid, pc.userid as userid, pc.pulseid,
                    pc.approvalstatus, pc.selfcompletion, cmc.id as coursemodulecompletionid
                        FROM {user} eu1_u
                        JOIN {user_enrolments} ej1_ue ON ej1_ue.userid = eu1_u.id
                        JOIN {enrol} ej1_e ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = ?)
                        LEFT JOIN {pulse_completion} pc ON pc.userid = eu1_u.id AND pc.pulseid = ?
                        LEFT JOIN {course_modules_completion} cmc ON cmc.userid = eu1_u.id AND cmc.coursemoduleid = ?
                        JOIN (SELECT DISTINCT userid
                                        FROM {role_assignments}
                                        WHERE contextid $insql
                                        AND roleid $roleinsql
                                    ) ra ON ra.userid = eu1_u.id
                    WHERE 1 = 1 AND eu1_u.deleted = 0 AND eu1_u.id <> ? AND eu1_u.deleted = 0
                ) je ON je.id = u.id
            WHERE u.deleted = 0 ORDER BY u.lastname, u.firstname, u.id";

        $params[] = $course['id'];
        $params[] = $cm['instance'];
        $params[] = $cm['id'];
        $params = array_merge($params, array_filter($inparams));
        $params = array_merge($params, array_filter($roleinparams));
        $params[] = 1;
        $students = $DB->get_records_sql($usersql, $params);

        $courseid = $pulse['course'];
        $course = (object) $course;
        $pulse = (object) $pulse;
        if (!in_array($courseid, $modinfo)) {
            $modinfo[$courseid] = new \pulse_course_modinfo($course, 0);
        }
        $cm = $modinfo[$course->id]->get_cm($cm['id']);
        if (!empty($students)) {
            $completion = new completion_info($course);
            $context = context_module::instance($cm->id);
            if ($completion->is_enabled($cm) ) {
                foreach ($students as $key => $user) {
                    $modinfo[$course->id]->changeuserid($user->id);
                    $md = $modinfo[$course->id];
                    $result = pulse_get_completion_state($course, $cm, $user->id, COMPLETION_UNKNOWN, $pulse, $user, $md);
                    $activitycompletion = new \stdclass();
                    $activitycompletion->coursemoduleid = $cm->id;
                    $activitycompletion->userid = $user->id;
                    $activitycompletion->viewed = null;
                    $activitycompletion->overrideby = null;
                    if ($user->coursemodulecompletionid == '') {
                        $activitycompletion->completionstate = $result;
                        $activitycompletion->timemodified = time();
                        $activitycompletion->id = $DB->insert_record('course_modules_completion', $activitycompletion);
                    } else {
                        $activitycompletion->id = $user->coursemodulecompletionid;
                        $activitycompletion->completionstate = $result;
                        $activitycompletion->timemodified = time();
                        $DB->update_record('course_modules_completion', $activitycompletion);
                    }
                    mtrace("Updated course module completion - user ". $user->id);

                    // Trigger an event for course module completion changed.
                    $event = \core\event\course_module_completion_updated::create(array(
                        'objectid' => $activitycompletion->id,
                        'context' => (object) $context,
                        'relateduserid' => $user->id,
                        'other' => array(
                            'relateduserid' => $user->id
                        )
                    ));
                    $event->add_record_snapshot('course_modules_completion', $activitycompletion);
                    $event->trigger();

                }
            }

        } else {
            mtrace('There is not users to update pulse module completion');
        }
    }

    mtrace('Course module completions are updated for all pulse module....');

}

/**
 * Generate approval buttons and self mark completions buttons based on user roles and availability.
 *
 * @param  mixed $args
 * @return void
 */
function mod_pulse_output_fragment_completionbuttons($args) {
    global $CFG, $DB, $USER;

    $modules = json_decode($args['modules']);
    list($insql, $inparams) = $DB->get_in_or_equal($modules);
    $sql = "SELECT cm.*, nf.completionapproval, nf.completionapprovalroles, nf.completionself FROM {course_modules} cm
    JOIN {modules} AS md ON md.id = cm.module
    JOIN {pulse} nf ON nf.id = cm.instance WHERE cm.id $insql AND md.name = 'pulse'";
    $records = $DB->get_records_sql($sql, $inparams);

    $html = [];

    foreach ($modules as $moduleid) {
        if (isset($records[$moduleid])) {
            $data = $records[$moduleid];
            $html[$moduleid] = '';
            $extend = true;
            // Approval button generation for selected roles.
            if ($data->completionapproval == 1) {
                $roles = $data->completionapprovalroles;
                if (pulse_has_approvalrole($roles, $moduleid)) {
                    $approvelink = new moodle_url('/mod/pulse/approve.php', ['cmid' => $moduleid]);
                    $html[$moduleid] .= html_writer::tag('div',
                        html_writer::link($approvelink, get_string('approveuser', 'pulse'),
                        ['class' => 'btn btn-primary pulse-approve-users']),
                        ['class' => 'approve-user-wrapper']
                    );
                }
            }
            // Generate self mark completion buttons for students.
            if (mod_pulse_is_uservisible($moduleid, $USER->id, $data->course)) {
                if ($data->completionself == 1 && pulse_user_isstudent($moduleid)
                    && !pulse_isusercontext($data->completionapprovalroles, $moduleid)) {
                    // Add self mark completed informations.
                    if (!pulse_already_selfcomplete($records[$moduleid]->instance, $USER->id)) {
                        $selfcomplete = new moodle_url('/mod/pulse/approve.php', ['cmid' => $moduleid, 'action' => 'selfcomplete']);
                        $selfmarklink = html_writer::link($selfcomplete, get_string('markcomplete', 'pulse'),
                            ['class' => 'btn btn-primary pulse-approve-users']
                        );
                        $html[$moduleid] .= html_writer::tag('div', $selfmarklink, ['class' => 'pulse-approve-users']);
                    } 
                } 
            } else {
                $extend = false;
            }
            // Extend the pro features if the logged in users has able to view the module.
            if ($extend) {
                $instance = new stdclass();
                $instance->pulse = $data;
                $instance->pulse->id = $data->instance;
                $instance->user = $USER;
                $html[$moduleid] .= pulse_extend_reaction($instance, 'content');
            }
        }
    }

    return json_encode($html);
}

/**
 * Find the course module is visible to current user.
 *
 * @param  mixed $cmid
 * @param  mixed $userid
 * @param  mixed $courseid
 * @return void
 */
function mod_pulse_is_uservisible($cmid, $userid, $courseid) {
    // Filter available users.
    if (!empty($cmid)) {
        $modinfo = get_fast_modinfo($courseid, $userid);
        $cm = $modinfo->get_cm($cmid);
        return $cm->uservisible;
    }
}


/**
 * Check the current users has role to approve the completion for students in current pulse module.
 *
 * @param  mixed $completionapprovalroles
 * @param  mixed $cmid
 * @return void
 */
function pulse_has_approvalrole($completionapprovalroles, $cmid, $usercontext=true, $userid=null) {
    global $USER, $DB; 
    if ($userid == null) {
        $userid = $USER->id;
    }

    $modulecontext = context_module::instance($cmid);
    $approvalroles = json_decode($completionapprovalroles);
    $roles = get_user_roles($modulecontext, $userid);
    $hasrole = false;
    foreach ($roles as $key => $role) {
        if (in_array($role->roleid, $approvalroles)) {
            $hasrole = true;
        }
    }
    // Check if user has role in course context level role to approve.
    if (!$usercontext) {
        return $hasrole;
    }

    // Test user has user context.
    $sql = "SELECT ra.id, ra.userid, ra.contextid, ra.roleid, ra.component, ra.itemid, c.path
            FROM {role_assignments} ra
            JOIN {context} c ON ra.contextid = c.id
            JOIN {role} r ON ra.roleid = r.id
            WHERE ra.userid = ? and c.contextlevel = ?
            ORDER BY contextlevel DESC, contextid ASC, r.sortorder ASC";
    $roleassignments = $DB->get_records_sql($sql, array($userid, CONTEXT_USER));
    if ($roleassignments) {
        foreach ($roleassignments as $role) {
            if (in_array($role->roleid, $approvalroles)) {
                return true;
            }
        }
    }
    return $hasrole;
}


function pulse_isusercontext($completionapprovalroles, $cmid) {
    global $DB, $USER;

    // Test user has user context.
    $sql = "SELECT ra.id, ra.userid, ra.contextid, ra.roleid, ra.component, ra.itemid, c.path
            FROM {role_assignments} ra
            JOIN {context} c ON ra.contextid = c.id
            JOIN {role} r ON ra.roleid = r.id
            WHERE ra.userid = ? and c.contextlevel = ?
            ORDER BY contextlevel DESC, contextid ASC, r.sortorder ASC";
    $roleassignments = $DB->get_records_sql($sql, array($USER->id, CONTEXT_USER));
    if ($roleassignments) {
        return true;
    }
    return false;
}

/**
 * Get mentees assigned students list.
 *
 * @return void
 */
function pulse_user_getmentessuser() {
    global $DB, $USER;

    if ($usercontexts = $DB->get_records_sql("SELECT c.instanceid, c.instanceid
                                            FROM {role_assignments} ra, {context} c, {user} u
                                            WHERE ra.userid = ?
                                                    AND ra.contextid = c.id
                                                    AND c.instanceid = u.id
                                                    AND c.contextlevel = ".CONTEXT_USER, array($USER->id))) {

        $users = [];
        foreach ($usercontexts as $usercontext) {
            $users[] = $usercontext->instanceid;
        }
        return $users;
    }
    return false;
}
/**
 * Check and generate user approved information for module.
 *
 * @param  mixed $pulseid
 * @param  mixed $userid
 * @return void
 */
function pulse_user_approved($pulseid, $userid) {
    global $DB;
    $completion = $DB->get_record('pulse_completion', ['userid' => $userid, 'pulseid' => $pulseid]);
    if (!empty($completion) && $completion->approvalstatus) {
        $date = userdate($completion->approvaltime, get_string('strftimedaydate', 'core_langconfig'));
        $approvaltime = isset($completion->approvalstatus) ? $date : 0;
        $params['date'] = ($approvaltime) ? $approvaltime : '-';
        $approvedby = isset($completion->approveduser) ? \core_user::get_user($completion->approveduser) : '';
        $params['user'] = ($approvedby) ? fullname($approvedby) : '-';

        $approvalstr = get_string('approvedon', 'pulse', $params);
        return $approvalstr;
    }
    return false;
}

/**
 * Check the logged in users is student for pulse.
 *
 * @param  mixed $cmid
 * @return void
 */
function pulse_user_isstudent($cmid) {
    global $USER;
    $modulecontext = context_module::instance($cmid);
    $roles = get_user_roles($modulecontext, $USER->id);
    $hasrole = false;
    foreach ($roles as $key => $role) {
        if ($role->shortname == 'student') {
            $hasrole = true;
            break;
        }
    }
    return $hasrole;
}

/**
 * Find the user already completed the module by self compeltion.
 *
 * @param  mixed $pulseid
 * @param  mixed $userid
 * @return void
 */
function pulse_already_selfcomplete($pulseid, $userid) {
    global $DB;
    $completion = $DB->get_record('pulse_completion', ['userid' => $userid, 'pulseid' => $pulseid]);
    if (!empty($completion) && $completion->selfcompletion) {
        if (isset($completion->selfcompletion) && $completion->selfcompletion != '') {
            $result = userdate($completion->selfcompletiontime, get_string('strftimedaydate', 'core_langconfig'));
        }
    }
    return isset($result) ? $result : false;
}


function pulse_extend_add_instance($pulseid, $pulse) {
    $callbacks = get_plugins_with_function('extend_pulse_add_instance');
    foreach ($callbacks as $type => $plugins) {
        foreach ($plugins as $plugin => $pluginfunction) {
            $pluginfunction($pulseid, $pulse);
        }
    }
}

function pulse_extend_update_instance($pulse) {
    $callbacks = get_plugins_with_function('extend_pulse_update_instance');
    foreach ($callbacks as $type => $plugins) {
        foreach ($plugins as $plugin => $pluginfunction) {
            $pluginfunction($pulse);
        }
    }
}


/** Inject form elements into mod instance form.
 * @param mform $mform the form to inject elements into.
 */
function mod_pulse_extend_form($mform, $instance) {
    $callbacks = get_plugins_with_function('extend_pulse_form');
    foreach ($callbacks as $type => $plugins) {
        foreach ($plugins as $plugin => $pluginfunction) {
            $pluginfunction($mform, $instance);
        }
    }
}

/** Inject form elements into mod instance form.
 * @param mform $mform the form to inject elements into.
 */
function mod_pulse_extend_formdata($mform) {
    $callbacks = get_plugins_with_function('extend_pulse_formdata');
    foreach ($callbacks as $type => $plugins) {
        foreach ($plugins as $plugin => $pluginfunction) {
            $pluginfunction($mform);
        }
    }
}

/** Inject form elements into mod instance form.
 * @param mform $mform the form to inject elements into.
 */
function pulse_extend_postprocessing($data) {
    $callbacks = get_plugins_with_function('extend_pulse_postprocessing');
    foreach ($callbacks as $type => $plugins) {
        foreach ($plugins as $plugin => $pluginfunction) {
            $pluginfunction($data);
        }
    }
}


/** Inject form elements into mod instance form.
 * @param mform $mform the form to inject elements into.
 */
function pulse_extend_preprocessing(&$defaultvalues, $currentinstance, $context) {
    $callbacks = get_plugins_with_function('extend_pulse_preprocessing');
    foreach ($callbacks as $type => $plugins) {
        foreach ($plugins as $plugin => $pluginfunction) {
            $pluginfunction($defaultvalues, $currentinstance, $context);
        }
    }
}

function pulse_extend_reaction($instance, $displaytype='notification') {
    $html = '';
    $callbacks = get_plugins_with_function('extend_pulse_reaction');
    foreach ($callbacks as $type => $plugins) {
        foreach ($plugins as $plugin => $pluginfunction) {
            $html .= $pluginfunction($instance, $displaytype);
        }
    }
    return $html;
}

function pulse_extend_student_messagesend($userid, $instance, $messagedata) {
    $callbacks = get_plugins_with_function('extend_pulse_notification');
    foreach ($callbacks as $type => $plugins) {
        foreach ($plugins as $plugin => $pluginfunction) {
            $pluginfunction($userid, $instance, $messagedata);
        }
    }
}



/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid User id to use for all capability checks, etc. Set to 0 for current user (default).
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_pulse_core_calendar_provide_event_action(calendar_event $event,
                                                     \core_calendar\action_factory $factory,
                                                     int $userid = 0) {
    global $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['pulse'][$event->instance];

    if (!$cm->uservisible) {
        // The module is not visible to the user for any reason.
        return null;
    }

    $context = context_module::instance($cm->id);

    if (!has_capability('mod/pulse:notifyuser', $context, $userid)) {
        return null;
    }

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_url('/mod/pulse/view.php', ['id' => $cm->id]),
        1,
        true
    );
}

/* function pulse_extend_calendar_event($event, $factory, $userid) {
    $callbacks = get_plugins_with_function('extend_pulse_calendar_event');
    foreach ($callbacks as $type => $plugins) {
        foreach ($plugins as $plugin => $pluginfunction) {
           return $pluginfunction($event, $factory, $userid);
        }
    }
} */

/* function pulse_extend_reaction($instance) {
    $params[] = $instance;
    $params[] = true;
    return component_callback('local_pulsepro', 'extend_pulse_reaction', $params);
} */
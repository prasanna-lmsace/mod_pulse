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
 * Pulse instance test cases defined.
 *
 * @package   mod_pulse
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined( 'MOODLE_INTERNAL') || die(' No direct access ');


/**
 * Pulse resource phpunit test cases defined.
 */
class mod_pulse_lib_testcase extends advanced_testcase {

    public $intro = 'Pulse test notification';

    /**
     * Setup testing cases.
     *
     * @return void
     */
    public function setUp(): void {
        global $CFG;

        $this->resetAfterTest();
        // Remove the output display of cron task.
        $CFG->mtrace_wrapper = 'mod_pulse_remove_mtrace_output';
        $this->course = $this->getDataGenerator()->create_course();
        $this->module = $this->getDataGenerator()->create_module('pulse', ['course' => $this->course->id, 'intro' => $this->intro]);
        $this->cm = get_coursemodule_from_instance('pulse', $this->module->id);
    }

    /**
     * Test the pulse student test function to check it identifies the students.
     *
     * @return void
     */
    public function test_is_studentuser() {
        // Student.
        $user = self::getDataGenerator()->create_and_enrol($this->course, 'student');
        $this->setUser($user);
        $result = pulse_user_isstudent($this->cm->id);
        $this->assertTrue($result);

        // Editing teacher.
        $user = self::getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $this->setUser($user);
        $result = pulse_user_isstudent($this->cm->id);
        $this->assertFalse($result);
    }

    /**
     * Test the pulse fetch course senders for send notification.
     *
     * @return void
     */
    public function test_course_sender() {
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'student', ['email' => 'testuser1@test.com', 'username' => 'testuser1']);
        $sender = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher', ['email' => 'sender1@test.com', 'username' => 'sender1']);
        $sender2 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher', ['email' => 'sender2@test.com', 'username' => 'sender2']);
        $contacts = mod_pulse\task\sendinvitation::get_sender($this->course->id);
        $coursecontact = $contacts->coursecontact;
        $this->assertEquals($coursecontact->username, 'sender1');

        // Assign teacher sender2 and user in group.
        $groupid = $this->getDataGenerator()->create_group(array('courseid' => $this->course->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $user, 'groupid' => $groupid));
        $this->getDataGenerator()->create_group_member(array('userid' => $sender2, 'groupid' => $groupid));
        $contacts = mod_pulse\task\sendinvitation::get_sender($this->course->id);
        $sender = \mod_pulse\task\sendinvitation::find_user_sender($contacts, $user);
        $this->assertEquals($sender->username, 'sender2');
    }

    /**
     * Test pulse email placeholder filters function.
     *
     * @return void
     */
    public function test_pulse_update_email_vars() {
        $user = $this->getDataGenerator()->create_user(['email' => 'testuser1@test.com', 'username' => 'testuser1']);
        $sender = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher', ['email' => 'sender1@test.com', 'username' => 'sender1']);
        $templatetext = "Mail to {User_Email} - mail from {Sender_Email} content";
        $subject = '';
        list($subject, $template) = mod_pulse_update_emailvars($templatetext, $subject, $this->course, $user, $this->cm, $sender);
        $actualcontent = "Mail to testuser1@test.com - mail from sender1@test.com content";
        $this->assertEquals($actualcontent, $template);
    }

    /**
     * Test delete pulse instance and check notified users are removed.
     *
     * @return void
     */
    public function test_pulse_delete_instance() {
        global $DB;
        // Enrol two usres in course, then activity must send two messages.
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'student', ['email' => 'testuser2@test.com', 'username' => 'testuser2']);
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'student', ['email' => 'testuser3@test.com', 'username' => 'testuser3']);
        $sender = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher', ['email' => 'sender2@test.com', 'username' => 'sender2']);
        $task = $this->send_message();

        // Check notified users are stored.
        $count = $DB->count_records('pulse_users', ['pulseid' => $this->module->id]);
        $this->assertEquals(2, $count);

        pulse_delete_instance($this->module->id);
        phpunit_util::run_all_adhoc_tasks();
        // After delete instance all user notified data should deleted.
        $count = $DB->count_records('pulse_users', ['pulseid' => $this->module->id]);
        $this->assertEquals(0, $count);

        $completioncount = $DB->count_records('pulse_completion', ['pulseid' => $this->module->id]);
        $this->assertEquals(0, $count);
    }

    /**
     * Send messages.
     *
     * @return void
     */
    public function send_message() {
        $this->preventResetByRollback();
        $slink = $this->redirectMessages();
        // Setup adhoc task to send notifications.
        mod_pulse_cron_task(false);
        // Check adhock task count;
        $tasklist = core\task\manager::get_adhoc_tasks('mod_pulse\task\sendinvitation');
        // ...cron_run_adhoc_tasks(time());.
        // Run all adhoc task to send notification.
        phpunit_util::run_all_adhoc_tasks();
        $messages = $slink->get_messages();
        return ['tasklist' => $tasklist, 'messages' => $messages];
    }

    /**
     * Test pulse sends the message for enrolled users.
     *
     * @return void
     */
    public function test_pulse_sending_messages() {
        global $CFG;
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'student', [
            'email' => 'testuser2@test.com',
            'username' => 'testuser2'
        ]);
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'student', [
            'email' => 'testuser3@test.com',
            'username' => 'testuser3'
        ]);
        $sender = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher', [
            'email' => 'sender2@test.com',
            'username' => 'sender2'
        ]);
        $task = $this->send_message();
        $tasklist = $task['tasklist'];
        $messages = $task['messages'];
        // List of task assinged for mod_pulse in adhoc.
        $this->assertEquals(1, count($tasklist));
        // Notified users count.
        $this->assertEquals(2, count($messages));
        $message = reset($messages);
        // Check the message body is same as intro content.
        $this->assertEquals($this->intro, $message->fullmessage);
    }

    /**
     * test_pulse_different_message
     *
     * @return void
     */
    public function test_pulse_different_message() {
        global $CFG;
        $this->setAdminUser();

        $this->course = $this->getDataGenerator()->create_course();
        $pulsegenerator = $this->getDataGenerator()->get_plugin_generator('mod_pulse');
        $draftitemid = file_get_submitted_draft_itemid('pulse_content');
        $instancemessage = ['itemid' => $draftitemid, 'text' => 'Different content test pulse', 'format' => 1];
        $subject = 'Test pulse - subject content';
        $this->module = $pulsegenerator->create_instance(array(
            'course' => $this->course->id,
            'diff_pulse' => 1,
            'pulse_content_editor' => $instancemessage,
            'pulse_subject' => $subject
        ));
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'student', ['email' => 'testuser2@test.com', 'username' => 'testuser2']);
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'student', ['email' => 'testuser2@test.com', 'username' => 'testuser3']);
        $sender = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher', ['email' => 'sender2@test.com', 'username' => 'sender2']);
        $task = $this->send_message();
        $messages = $task['messages'];
        $message = reset($messages);
        $this->assertEquals($instancemessage['text'], $message->fullmessage);
    }

}

# About Pulse - 2.0

## Background

Pulse revolves around the idea of automating courses. In its current form, this is achieved through an activity that triggers an action based on availability status. Moodle provides various availability conditions that can be deeply integrated with learning content, and additional triggers can be added by creating availability conditions.

While this approach is highly flexible, it may not be immediately intuitive as it combines course content with automation. While both are integral to a course's learning path, not all teachers may find this approach comfortable. Another challenge is that determining availability on a large scale can be resource-intensive and require a robust server.

To address these issues, we aim to revamp the architecture of Pulse for the next version. Our goals include improving scalability, enhancing robustness, and simplifying usability. We discuss our general goals, followed by requirements and implementation notes. We then introduce the concept of automation templates, which can be used to create automation instances in courses.

Finally, we specify the requirements for a report source for Moodle's custom report builder, which will be used for a notification queue.

## Goals

Based on our own experience with Pulse in various projects, as well as input from customers and partners, we aim to achieve the following goals with Pulse 2.0:

**Focused:** We developed Pulse because we consistently encountered specific issues within courses. These issues, while somewhat heterogeneous due to their course-based nature, have made it challenging to immediately grasp the essence of Pulse. Therefore, the next release will refine Pulse into a product primarily designed for course automation, with the initial major focus on notifications.

**Robust:** Despite its high flexibility, Pulse has occasionally proven to be somewhat finicky, particularly concerning the availability status. This fragility is attributable to the diverse array of availability conditions and their susceptibility to changes. In the next release, we intend to enhance Pulse's robustness and make troubleshooting easier.

**Scalable:** Pulse's current resource-intensive demands stem from the complex queries required to determine availability status. Our goal for the next release is to ensure that Pulse functions smoothly on standard Moodle hosting infrastructure, even for mid-sized installations, without resource-related issues.

**Intuitive:** While Pulse's integration as a course activity is advantageous for the learning path, customers have expressed reservations about including an activity in the course that, in many cases, should remain hidden from students. Therefore, the next release of Pulse will empower learning designers and teachers to choose whether or not to display an activity.

**Easier to maintain:** Pulse comes with presets to simplify its use for teachers. However, once a preset has been applied, any subsequent changes to the template do not affect existing Pulse activities, posing significant maintenance challenges, especially on larger sites with potentially hundreds of courses, each containing numerous Pulse activities. In the next release, site administrators will gain the capability to create, update, and globally deploy automations, with the option to override them on a course/activity level.

## Architecture

The new Pulse architecture comprises the following key components:

1. **Automation Templates:**

   These are globally managed by users with the appropriate capabilities.

2. **Automation Instances:**

   These instances are created based on automation templates and are kept in sync with the automation template. They offer the option to override specific settings per instance.

3. **Automation Conditions:**

   Conditions trigger the automation and rely on events and/or completion. They are built in a modular way.

4. **Automation Actions:**

   These represent the outcomes of the automation, determining what actually happens. They are built in a modular way, with the initial scope primarily focusing on notifications.

# Pulse - General settings

**Detailed log**

Display a detailed log for a scheduled task, but only use it for troubleshooting purposes and disable it on a production site.

**Number of schedule count**

This setting allows you to control how many scheduled task notifications are sent during each cron run. By specifying a numerical value, you can regulate the rate at which system administrators receive notifications regarding the completion or status of scheduled tasks.


![Pulse-general-setting](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/fe81d840-4fb0-4c7f-a605-c09d8e7a3853)


# Automation templates

Users with the appropriate permissions create automation templates globally, outside of courses. The template itself doesn't perform any actions; it serves as the foundation for instances.

## Relation between templates and instances

The relationship between templates and instances ensures that settings defined in the template are synchronized with instances based on that template, except when a specific setting in an instance has been overridden. This means that any changes made to a setting in the template will automatically apply to all instances derived from the template.

For each setting within an instance, there is an override toggle available to protect locally made changes. Settings that have been locally modified will not be affected by changes to the same setting in the template.

Within the template, there is information indicating the number of instances where a setting has been locally overridden. Clicking on this number will open a modal with a link to the corresponding automation instance.

# Manage Automation templates lists

Automation templates can function in different ways based on their configuration. They can serve as 'default' templates that are applied to new courses, they can be mandated for every course, or they can be used to create an automation instance from within a course.

![Pulse-automation-template-lists](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/ab734e1e-759f-4d11-928b-8c285eb44f67)


***Create New Template***

The 'Create new template' button that allow you to create custom templates for Automation templates.

***Sort***

It provides users with the ability to arrange and display a list of automation templates in a Alphabetic order by the 'Preferences'.

***Filter***

It enables to filter and display a list of templates based on predefined categories.

***Circle with Icon***

The icon represents the enabled actions in the automation template. The following actions are available: 'Notification,' 'Assignment,' 'Membership,' and 'Skills'.

***Template Title Name***

The title of the automation template should provide a generic explanation of its purpose.

***Pencil icon***

You can edit the template title by clicking on the pencil icon next to it.

***Notification Pills***

The pills provide additional important information about the automation template. In this case, it explains that it's a notification

***Preferences***

This serves as the reference for the template, providing a unique identifier. It will be part of the unique identifier for the automation instance.

***Cog icon***

Click on this icon to edit the template.

***Eye icon***

Click on this icon to toggle the visibility of a template. A template that is not visible will be hidden in courses. Existing automation instances will still be available, but new ones cannot be added anymore.

***Toggle Button***

Use this toggle to enable or disable a template. When a template is disabled, it also disables all automation instances unless they are locally enabled using an override.

***Number of Automation template instances Badge***

How many automation instances are using the template? The number in brackets indicates the number of disabled instances.

# General settings

1. **Title**

   Provide a title for this automation template. This title is for administrative purposes and helps in identifying the template.

2. **Preference**

   Assign a reference to this automation template. This identifier is also for administrative purposes and assists in uniquely identifying the template.

3. **Visibility**

   This option allows you to show or hide the automation template on the Automation Templates list.

   ***Note:*** If hidden, users won't be able to create new instances based on this template, but existing instances will still be available.

4. **Internal Notes**

   Include any internal notes or information related to this automation template that will be visible on this template.

5. **Status**

   This option is for enabling or disabling the template.

   ***Enabled:*** Allows instances of this template to be created. Enabling the template may also prompt the user to decide whether to enable all existing instances based on the template or only the template itself and not its instances.

   ***Disabled:*** Turns off the automation template and its instances. Users can still enable instances individually if needed. Disabling the template may prompt the user to decide whether to disable all existing instances based on the template or only the template itself and not its instances.

6. **Tags**

   Add tags to this template for administrative purposes. Tags can help categorize and organize templates.

7. **Available for tenants**

   Specify for which Moodle Workplace tenants this template should be available. Select one or more tenants to make the template accessible to specific groups.

8. **Available in Course Categories**

   Choose the course categories where this template should be available. Select one or more categories to determine where users can create instances based on this template.

![Pulse-automation-template - Edit settings](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/d4220218-02f2-4069-ace5-bcf05953250c)


### Conditions

1. **Trigger**

   Choose the trigger events that will activate and be visible on the automation instances. You can select one or more of the following trigger options:

   ***Activity Completion:*** This automation will be triggered when an activity within the course is marked as completed. You will need to specify the activity within the automation instance.

   ***Course Completion:*** This automation will be triggered when the entire course is marked as completed, where this instance is used.

   ***Enrolments:*** This automation will be triggered when a user is enrolled in the course where this instance is located.

   ***Session:*** This automation will be triggered when a session is booked within the course. This trigger is only available within the course and should be selected within the automation instance.

   ***Cohort Membership:*** This automation will be triggered if the user is a member of one of the selected cohorts.

2. **Trigger operator**

   Choose the operator that determines how the selected triggers are evaluated:

   ***Any:*** At least one of the selected triggers must occur to activate the automation.

   ***All:*** All of the selected triggers must occur simultaneously to activate the automation.

![Pulse-automation-template - Condition](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/843795af-0972-490b-b078-cf69fc837eb1)


### Notifications

1. **Sender**

   Determines how the selected triggers are evaluated. Choose the sender of the notification from the following options:

   **Course Teacher:** The notification will be sent from the course teacher (the first one assigned if there are several). If the user is not in any group, it falls back to the site support contact. Note that this is determined by capability, not by an actual role.

   **Group Teacher:** The notification will be sent from the non-editing teacher who is a member of the same group as the user (the first one assigned if there are several). If there's no non-editing teacher in the group, it falls back to the course teacher. Note that this is determined by capability, not by an actual role.

   **Tenant Role (Workplace Feature):** The notification will be sent from the user assigned to the specified role in the tenant (the first one assigned if there are several). If there's no user with the selected role, it falls back to the site support contact. Note that this is determined by capability, not by an actual role.

   **Custom:** If this option is selected, an additional setting for 'Sender Email' will become available. Here, you can enter a specific email address to be used as the sender.

   ***Sender email:*** You can enter a specific email address to be used as the sender.

2. **Schedule**

   This scheduling allows you to control when the notification is delivered to its intended recipients. Choose the interval for sending notifications:

   **Once:** Send the notification only one time.

   **Daily:** Send the notification every day at the time selected below.

   **Weekly:** Send the notification every week on the day of the week and time of day selected below.

   **Monthly:** Send the notification every month on the day of the month and time of day selected below.

3. **Delay**

   A notification that is postponed for a specific period before it is sent to the recipient. Choose the delay option for sending notifications.

   **None:** Send notifications immediately upon the condition being met, considering the schedule limitations (e.g., weekday or time of day).

   **Before X Days/Hours:** Send the notification a specified number of days/hours before the condition is met. Note that this is only possible for timed events, e.g., appointment sessions.

   **After X Days/Hours:** Send the notification a specified number of days/hours after the condition is met. This is possible for all conditions.

4. **Limit Number of Notifications**

    This limit is typically imposed to prevent users from receiving an excessive number of notifications, which could be overwhelming or spammy. Enter a number to limit the total number of notifications sent. Enter "0" for no limit. This is only relevant if the schedule is not set to "Once."

5. **Recipients**

   Select one or more roles that have the capability to receive notifications. By default, it's set for all graded roles, including students. Users selected here will be used in the query to determine who gets notifications.

6. **CC**

   Select course context and user context roles that will receive the notification as a CC (Carbon Copy) alongside the main recipient. Course context roles determine users based on their enrollment in the course and membership in a group, while user context roles determine users based on their relationship to the recipient (assigned role in user).

7. **BCC**

   Select course context and user context roles that will receive the notification as a BCC (Blind Carbon Copy) alongside the main recipient. Course context roles determine users based on their enrollment in the course and membership in a group, while user context roles determine users based on their relationship to the recipient (assigned role in user).

8. **Subject**

   Refers to the title or headline that you would provide for an notification to briefly describe the content or purpose of the notification

9. **Header Content**

   The context of email notifications refers to the information and elements displayed at the top of an email message before the main body of the email. This field supports filters and placeholders.

10. **Static Content**

      The context of email notifications refers to the fixed or unchanging elements within the email that do not vary from one email to another. This field supports filters and placeholders.

11. **Footer Content**

      The context of notifications refers to the information and elements placed at the bottom of a notification message.

12. **Preview**

      Click this button to open a modal window that displays the notification, allowing you to select an example user to determine the content of the notification.

![Pulse-automation-template - Notification](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/b46142ed-a4f5-445a-b2ef-98691cb3bcfd)


# Automation instances

Based on the available automation templates within the current course, users with appropriate permissions can create automation instances. To create an automation instance, the user must select the automation template on the "Automation" page within a course and configure the instance accordingly.

For each setting within an automation instance, the value from the template is used. If the user wants to deviate from the template's value, they can locally override it by toggling the switch to "override" and making local changes to the setting.

Any changes made to the automation template will impact all instances where the setting has not been locally overridden. Automation instances inherit the same settings as the underlying automation template, with a few differences and exceptions.

# Manage Automation instances lists

![Pulse-automation-instances](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/3bc322af-a13f-489c-8699-187bce4d1097)

***Select box***

You can choose an automation template from the following list to create an automation instance.

***Add Automation Instances***

The 'Add automation instances' button that allows you to create automation instances in the selected automation template.

***Manage templates***

The 'Manage Templates' button redirects you to the Manage Automation Templates listing page.

***Sort***

It provides users with the ability to arrange and display a list of automation instances in a Alphabets order by the 'Preferences'.

***Circle with Icon***

The icon represents the enabled actions in the automation template instances. The following actions are available: 'Notification,' 'Assignment,' 'Membership,' and 'Skills'.

***Instances Title Name***

The title of the automation instances should provide a generic explanation of its purpose.

***Pencil icon***

You can edit the automation instances title by clicking on the pencil icon next to it.

***Notification Pills***

The pills provide additional important information about the automation instances. In this case, it explains that it's a notification.

***Preferences***

This serves as the reference for the automation instances, providing a unique identifier. It will be part of the unique identifier for the automation instance.

***Cog icon***

Click on this cog icon to edit the automation instances settings.

***Duplicate icon***

Click on this copy icon to duplicate the specific automation instances.

***Calendar icon***

Click on this Calendar icon to view the report page of the automation instances schedule. This report will display the 'Course full name', 'Message type,' 'Subject,' 'Full name,' 'Time created,' 'Scheduled time,' 'Status,' and you can also 'Download table data.'

***Eye icon***

Use this toggle to enable or disable the automation instance locally. This will override the template's status. For example, even if the template is turned off, it can still be enabled here.

***Delete icon***

Clicking on this delete icon will remove the specific automation instances from the automation template.


# General settings

1. **Title**

   Provide a title for this automation template. This title is for administrative purposes and helps in identifying the template.

   *`Toggle button - If you enable the toggle button, the provided value will be applied for the 'title' in the instance; otherwise, the automation templates value of the 'title'  will be applied.`*

2. **Reference**

   Assign a reference to this automation instance. This identifier is also for administrative purposes and helps uniquely identify the instance. The 'reference' setting of this instance will have the prefix of its automation template's 'Reference'.

   *`Toggle button -  If you enable the toggle button, the provided value will be applied for the 'reference' in the instance; otherwise, the automation templates value of the 'reference'  will be applied.`*

3. **Internal Notes**

   Include any internal notes or information related to this automation template that will be visible on this template.

   *`Toggle button -  If you enable the toggle button, the provided value will be applied for the 'Internal Notes' in the instance; otherwise, the automation templates value of the 'Internal Notes'  will be applied.`*

4. **Status**

    This option is for enabling or disabling the template.

     ***Enabled:*** Allows instances of this template to be created and overrides the option, even if the automation template is enabled or disabled.

     ***Disabled:*** Disables the automation instances, regardless of whether the automation template is enabled or disabled.

     *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'status' in the instance; otherwise, the automation templates option of the 'status' will be applied.`*

5. **Tags**

   Add tags to this template for administrative purposes. Tags can help categorize and organize templates.

   *`Toggle button - If you enable the toggle button, the provided value will be applied for the 'tags' in the instance; otherwise, the automation templates value of the 'tags' will be applied.`*

6. **Available for tenants**

   Specify for which Moodle Workplace tenants this template should be available. Select one or more tenants to make the template accessible to specific groups.

   *`Toggle button - If you enable the toggle button, the provided value will be applied for the 'Available for tenants' in the instance; otherwise, the automation templates value of the 'Available for tenants'  will be applied.`*

![Pulse-automation-instances - Edit settings](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/45e26035-f730-4e23-a275-3043b15d7879)


## Conditions

1. **Trigger operator**

   Choose the operator that determines how the selected triggers are evaluated:

   ***Any:*** At least one of the selected triggers must occur to activate the automation.

   ***All:*** All of the selected triggers must occur simultaneously to activate the automation.

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Trigger operator' in the instance; otherwise, the automation templates of the 'Trigger Operator' option will be applied.`*

2. **Activity completion**

   This automation will be triggered when an activity within the course is marked as completed. You will need to specify the activity within the automation instance. The options for activity completion include:

   **Disabled:** Activity completion condition is disabled.

   **All:** Activity completion condition applies to all enrolled users. Enabling this option will make the 'Select Activities' option visible.

   **Upcoming:** Activity completion condition only applies to future enrollments. Enabling this option will make the 'Select Activities' option visible.

   *`'Select Activities: This setting allows you to choose from all available activities within your course that have completion configured. This selection determines which specific activities will trigger the automation when their completion conditions are met.`*

3. **Enrolments**

   This automation will be triggered when a user is enrolled in the course where this instance is located.

   ***Disabled:*** Enrolment condition is disabled.

   ***All:*** Enrolment condition applies to all enrolments.

   ***Upcoming:*** Enrolment condition only applies to future enrolments.

4. **Session Booking**

   This automation will be triggered when a session module is booked within the course. This trigger is only available within the course and should be selected within the automation instance. The options for session triggers include:

   ***Disabled:*** Session trigger is disabled.

   ***All:*** Session trigger applies to all enrolled users. Enabling this option will make the 'Session module' option visible.

   ***Upcoming:*** Session trigger only applies to future enrollments. Enabling this option will make the 'Session module' option visible.

   *`Session module: This setting allows you to choose the session module that will be associated with a session booking condition.`*

5. **Cohort Membership**

   This automation will be triggered when a user belongs to one of the selected cohorts. The options for cohort membership include:

   ***Disabled:*** Cohort membership condition is disabled.

   ***All:*** Cohort membership condition applies to all enrolled users. Enabling this option will make the 'Cohort' option visible.

   ***Upcoming:*** Cohort membership condition only applies to future enrollments. Enabling this option will make the 'Cohort' option visible.

   *`Cohorts: This setting allows you to choose the cohorts. This selection determines which specific cohorts will trigger the automation when the users are assign on the cohorts.`*

6. **Course completion**

   This automation will be triggered when the course is marked as completed, where this instance is used. The options for course completion include:

   **Disabled:** Course completion condition is disabled.

   **All:** Course completion condition applies to all enrolled users.

   **Upcoming:** Course completion condition only applies to future enrollments.

![Pulse-automation-instances - Condition](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/00f33374-2235-495d-93c9-faed24a46aa7)


### Notifications

1. **Sender**

   Determines how the selected triggers are evaluated.

   Choose the sender of the notification from the following options:

   **Course Teacher:** The notification will be sent from the course teacher (the first one assigned if there are several). If the user is not in any group, it falls back to the site support contact. Note that this is determined by capability, not by an actual role.

   **Group Teacher:** The notification will be sent from the non-editing teacher who is a member of the same group as the user (the first one assigned if there are several). If there's no non-editing teacher in the group, it falls back to the course teacher. Note that this is determined by capability, not by an actual role.

   **Tenant Role (Workplace Feature):** The notification will be sent from the user assigned to the specified role in the tenant (the first one assigned if there are several). If there's no user with the selected role, it falls back to the site support contact. Note that this is determined by capability, not by an actual role.

   **Custom:** If this option is selected, an additional setting for 'Sender Email' will become available. Here, you can enter a specific email address to be used as the sender.

   ***Sender email:*** You can enter a specific email address to be used as the sender.

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'sender' option in the instance; otherwise, the automation templates of the 'sender' option will be applied.`*

2. **Schedule**

   This scheduling allows you to control when the notification is delivered to its intended recipients.

   Choose the interval for sending notifications:

   **Once:** Send the notification only one time.

   **Daily:** Send the notification every day at the time selected below.

   **Weekly:** Send the notification every week on the day of the week and time of day selected below.

   **Monthly:** Send the notification every month on the day of the month and time of day selected below.

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Schedule' in the instance; otherwise, the automation templates option of the 'Schedule' will be applied.`*


3. **Delay**

   A notification that is postponed for a specific period before it is sent to the recipient.

   Choose the delay option for sending notifications.

   **None:** Send notifications immediately upon the condition being met, considering the schedule limitations (e.g., weekday or time of day).

   **Before X Days/Hours:** Send the notification a specified number of days/hours before the condition is met. Note that this is only possible for timed events, e.g., appointment sessions.

   **After X Days/Hours:** Send the notification a specified number of days/hours after the condition is met. This is possible for all conditions.

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Delay' in the instance; otherwise, the automation templates option of the 'Delay' will be applied.`*

4. **Limit Number of Notifications**

   This limit is typically imposed to prevent users from receiving an excessive number of notifications, which could be overwhelming or spammy. Enter a number to limit the total number of notifications sent. Enter "0" for no limit. This is only relevant if the schedule is not set to "Once."

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Limit Number of Notifications' in the instance; otherwise, the automation templates option of the 'Limit Number of Notifications' will be applied.`*

5. **Recipients**

   Select one or more roles that have the capability to receive notifications. By default, it's set for all graded roles, including students. Users selected here will be used in the query to determine who gets notifications.

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Recipients' in the instance; otherwise, the automation templates option of the 'Recipients' will be applied.`*

6. **CC**

   Select course context and user context roles that will receive the notification as a CC (Carbon Copy) to the main recipient. Course context roles determine users by enrolment in the course and membership of a group, while user context roles determine users by their relation to the recipient (assigned role in user).

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'CC' in the instance; otherwise, the automation templates option of the 'CC' will be applied.`*

7. **BCC**

   Select course context and user context roles that will receive the notification as a BCC (Blind Carbon Copy) to the main recipient. Course context roles determine users by enrolment in the course and membership of a group, while user context roles determine users by their relation to the recipient (assigned role in user).

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'BCC' in the instance; otherwise, the automation templates option of the 'BCC' will be applied.`*

8. **Subject**

   Refers to the title or headline that you would provide for an notification to briefly describe the content or purpose of the notification

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Subject' in the instance; otherwise, the automation templates option of the 'Subject' will be applied.`*

9. **Header Content**

   The context of email notifications refers to the information and elements displayed at the top of an email message before the main body of the email. This field supports filters and placeholders.

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Header Content' in the instance; otherwise, the automation templates option of the 'Header Content' will be applied.`*

10. **Static Content**

      The context of email notifications refers to the fixed or unchanging elements within the email that do not vary from one email to another. This field supports filters and placeholders.

      *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Static Content' in the instance; otherwise, the automation templates option of the 'Static Content' will be applied.`*

11. **Dynamic Content**

      Select an activity within the course to add content below the static content. This is only available in the automation instance within the course.

      *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Dynamic Content' in the instance; otherwise, the automation templates option of the 'Dynamic Content' will be applied.`*

      Choose the option on the Dynamic content:

      **None:** This option will disable the Dynamic content of the notification on the automation instances.

      ***Page:*** When you select the 'Page' activity option in Dynamic content, the 'Content Type' and 'Content Length' options will become visible.

      ***Book:*** When you select the 'Book' activity option in Dynamic content, the 'Content Type' and 'Content Length' and 'Chapters' options will become visible.

12. **Content Type**

      Refers to the format of the content being used that helps to describe the type of data or information contained within a resource. Please note that this feature supports specific mod types, such as Page and Book.

      Choose the type of content to be added below the Dynamic content:

      **Description**: If this option is selected, the description of the chosen activity will be included in the body of the notification.

      **Content**: If this option is chosen, the content of the selected activity will be included in the notification body.

14. **Content Length**

      Refers to the size or extent of a piece of content.

      Choose the content length to include in the notification

      **Teaser**: If chosen, only the first paragraph will be used, followed by a 'Read More' link.

      **Full, Linked**: If 'Full, Linked' is selected, the entire content shall be used with a link to the content provided after it.

      **Full, Not Linked**: If 'Full, Not Linked' is selected, the entire content shall be used without a link to the content afterward.

15. ***Chapters***

      Refer to the divisions or sections within a book that help organize and structure the content.

      Select which chapters of the chosen activity will be included in the notification body. To view the chapter content, select the specific chapter using the 'Chapters' option and the content using the 'Content' option for the 'Book' activity.

15. **Footer Content**

      The context of notifications refers to the information and elements placed at the bottom of a notification message. This field supports filters and placeholders.

      *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Footer Content' in the instance; otherwise, the automation templates option of the 'Footer Content' will be applied.`*

16. **Preview**

      Click this button to open a modal window that displays the notification, allowing you to select an example user to determine the content of the notification.

![Pulse-automation-instances - Notification](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/e16ca2ac-c191-49cb-8c90-3089e1f852e3)



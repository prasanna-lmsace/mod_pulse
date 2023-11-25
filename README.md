# About Pulse - 2.0

Pulse streamlines and automates processes within the system based on predefined conditions or events. When these conditions are met, such as a student completing an assignment or an important announcement being made, Pulse takes immediate action by sending notifications to the relevant users. This functionality not only simplifies and expedites communication but also enhances user engagement and overall efficiency within the system. Pulse's ability to automate notifications can greatly improve the user experience and reduce the manual effort required for managing and disseminating important information.


# Architecture

The new Pulse architecture comprises the following key components:

1. **Automation Templates:**

   These are globally managed by automation managers with the appropriate capabilities.

2. **Automation Instances:**

   These instances are generated in accordance with automation templates and are maintained in synchronization with their respective templates. They provide the flexibility to override specific settings on a per-instance basis.

3. **Automation Conditions:**

   Conditions serve as triggers for automation and depend on events or task completions. They are constructed in a modular fashion for enhanced flexibility.

4. **Automation Actions:**

   These actions embody the results of the automation process, defining the actual events that occur. They are constructed in a modular fashion, with the initial priority centered primarily on notifications.


# Pulse - General settings

**Number of schedule count**

This configuration option enables precise control over the quantity of scheduled task notifications dispatched during each cron execution. By specifying a numerical value, you can effectively manage the frequency at which system administrators receive notifications regarding the completion or status of scheduled tasks.

![Pulse-general-setting](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/fe81d840-4fb0-4c7f-a605-c09d8e7a3853)


# Relation between templates and instances

The relationship between templates and instances ensures that settings defined in the template are synchronized with instances based on that template, except when a specific setting in an instance has been overridden. This means that any changes made to a setting in the template will automatically apply to all instances derived from the template.

For each setting within an instance, there is an override toggle available to protect locally made changes. Settings that have been locally modified will not be affected by changes to the same setting in the template.

Within the template, there is information indicating the number of instances where a setting has been locally overridden. Clicking on this number will open a modal with a link to the corresponding automation instance.

# Automation templates

Users with the appropriate permissions create automation templates globally, independent of specific courses. The template itself doesn't perform any actions; it serves as the foundation for creating the instances.

# Manage Automation templates lists

The 'Manage Automation Templates' provides comprehensive control over your automation templates. It empowers you to create new templates, efficiently organize existing ones through sorting and course category-based filtering. This presents a list of templates, each accompanied by additional options such as visibility, status, and editing for individual templates.

![Pulse-automation-template-lists](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/ab734e1e-759f-4d11-928b-8c285eb44f67)


***Create New Template***

The 'Create new template' button that allow you to create custom templates for Automation templates.

***Sort***

It provides with the ability to arrange and display a list of automation templates in a alphabetic order by the 'Reference' parameter.

***Filter***

It provides the capability to filter and display a list of templates based on course categories.

***Template icon***

The 'Bell' icon represents the actions enabled for notifications in the automation template. The available actions include 'Notification,' 'Assignment,' 'Membership,' and 'Skills.'

***Template Title***

The title of the automation template should provide a generic explanation of its purpose.

***Edit Title***

To modify the template title, simply click on the pencil icon located next to the template title.

***Notification Pills***

The pills provide additional important information about the automation template. In this case, it explains that it's a notification.

***Reference***

This serves as the reference for the template, providing a unique identifier. It will be part of the unique identifier for the automation instance.

***Edit Settings***

To make changes to the template, click on the 'Cog' icon.

***Visibility***

To adjust the visibility of a template, simply click on the 'Eye' icon. A template set to 'Not Visible' will be concealed within courses. Existing automation instances will remain accessible, but new instances cannot be created

***Status***

Use this toggle to enable or disable a template. When a template is disabled, it also disables all automation instances unless they are locally enabled using an override. Enabling or disabling the template triggers the display of a modal window, allowing you to update the status of either the template alone or both the template and its instances.

***Number of Automation template instances Badge***

The count of automation instances currently utilizing the template is displayed, with the number in brackets indicating the quantity of disabled instances.



## General settings

1. **Title**

   Provide a title for this automation template. This title is intended for administrative purposes and helps in identifying the template.

2. **Reference**

   Assign a reference to this automation template. This identifier is also for administrative purposes and assists in uniquely identifying the template.

3. **Visibility**

   This option allows you to show or hide the automation template on the Automation Templates list.

   ***Note:*** If hidden, users won't be able to create new instances based on this template, but existing instances will still be available.

4. **Internal Notes**

   Including any internal notes or information relevant to this automation template. These details will be visible within the template for reference.

5. **Status**

   This option is for enabling or disabling the template.

   ***Enabled:*** Allows instances of this template to be created. Enabling the template may also prompt the user to decide whether to enable all existing instances based on the template or only the template itself and not its instances.

   ***Disabled:*** Turns off the automation template and its instances. Users can still enable instances individually if needed. Disabling the template may prompt the user to decide whether to disable all existing instances based on the template or only the template itself and not its instances.

6. **Tags**

   Adding tags can help categorize and organize templates for administrative purposes.

7. **Available for tenants**

   Specify for which Moodle Workplace tenants this template should be available. Select one or more tenants to make the template accessible to specific groups.

8. **Available in Course Categories**

   Select the course categories to be available for the specific template. By selecting one or more categories, you can define where users have the option to create instances based on this template. In the event that no categories are selected, the template will be accessible across all categories.

![Pulse-automation-template - Edit settings](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/d4220218-02f2-4069-ace5-bcf05953250c)


## Conditions

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


## Notifications

1. **Sender**

   Determines how the selected triggers are evaluated.

   Choose the sender of the notification from the following options:

   **Course Teacher:** The notification will be dispatched from the course teacher, specifically the first one assigned if there are multiple course teachers. In the event that the user is not part of any group, the notification will default to the site support contact.

   *`Note that this is determined by capability, not by an actual role.`*

   **Group Teacher:** The notification will be dispatched by the non-editing teacher who belongs to the same group as the user, specifically the first non-editing teacher assigned if there are multiple in the group. If no non-editing teacher is present in the group, the notification will default to the course teacher.

   *`Note that this is determined by capability, not by an actual role.`*

   **Tenant Role (Workplace Feature):** The notification will be initiated by the user designated to the specified role within the tenant, with preference given to the first one assigned if there are multiple users in that role. In the absence of any user with the selected role, the notification will revert to the site support contact.

   *`Note that this is determined by capability, not by an actual role.`*

   **Custom:** If this option is enabled, an additional configuration for 'Sender Email' will be accessible. In this field, you have the option to specify a precise email address for use as the sender.

   ***Sender email:*** You can enter a specific email address to be used as the sender.

2. **Schedule**

   This scheduling allows you to control when the notification is delivered to its intended recipients.

   Choose the interval for sending notifications:

   **Once:** Send the notification only one time.

   **Daily:** Send the notification every day at the time selected below.

   **Weekly:** Send the notification every week on the day of the week and time of day selected below.

   **Monthly:** Send the notification every month on the day of the month and time of day selected below.

3. **Delay**

   A notification that is postponed for a specific period before it is sent to the recipient.

   Choose the delay option for sending notifications.

   **None:** Send notifications immediately upon the condition being met.

   **Before:** The notification to be dispatched a specified number of days or hours before the condition is met. It's important to note that this feature is exclusively applicable to timed events, such as appointment sessions.

   **After:** The notification to be dispatched a specific number of days or hours after the condition has been met. This functionality is available for all types of conditions..

4. **Delay duraion**

   The duration time for the delay in sending the notification. This duration should be specified in either days or hours, depending on the chosen delay option.

5. **Limit Number of Notifications**

   This limit is typically imposed to prevent users from receiving an excessive number of notifications, which could be overwhelming or spammy. Enter a number to limit the total number of notifications sent. Enter "0" for no limit. This is only relevant if the schedule is not set to "Once."

6. **Recipients**

   The selected roles with the capability to receive notifications will be used for determining the recipients of notifications.

7. **CC**

   The selected roles with the capability to receive notifications will be used as a CC (Carbon Copy) to the main recipient.

8. **BCC**

   The selected roles with the capability to receive notifications will be used as a BCC (Blind Carbon Copy) to the main recipient.

9. **Subject**

   Refers to the title that you would provide for an notification to briefly describe the content or purpose of the notification.

10. **Header Content**

      The context of email notifications encompasses the information and elements that are presented at the outset of an email message, preceding the main body of the email. This field is equipped to accommodate filters and placeholders

11. **Static Content**

      Static content is positioned in the second segment of the notification content. This static content also offers support for filters and placeholders.

12. **Footer Content**

      The context of notifications refers to the information and elements placed at the bottom of a notification message.

13. **Preview**

      This option displays the notification, enabling you to choose an example user for evaluating the notification's content within a modal window accessed by clicking the button.

![Pulse-automation-template - Notification](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/b46142ed-a4f5-445a-b2ef-98691cb3bcfd)


# Automation instances

Users with the requisite permissions can generate automation instances by selecting an existing template. Within each automation instance, the initial values for settings are inherited from the template. However, should a user desire to deviate from the template's values, they have the option to locally override them by activating the 'override' toggle and implementing local adjustments to the settings.

# Manage Automation instances lists

Automation instances can be generated within pre-existing automation templates, offering the ability to effectively oversee instance lists with sorting options. This enables comprehensive control over each instance, with additional features including editing, duplication, viewing report, visibility, and instance deletion.

![Pulse-automation-instances](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/3bc322af-a13f-489c-8699-187bce4d1097)

***Select Template***

You can select an automation template from the following list in the drop-down box to create an automation instance.

***Add Automation Instances***

The 'Add automation instances' button that allow you to create custom 'instance' for automation template.

***Manage templates***

The 'Manage Templates' button redirects you to the Manage Automation Templates listing page to manage it.

***Sort***

It provides with the ability to arrange and display a list of automation instances in a Alphabets order by the 'Reference'.

***Instance icon***

The 'Bell' icon represents the actions enabled for notifications in the automation template instance. The available actions include 'Notification,' 'Assignment,' 'Membership,' and 'Skills.'

***Instances Title***

The title of the automation template instance should provide a generic explanation of its purpose.

***Edit Title***

To modify the template title, simply click on the pencil icon located next to the instance title.

***Notification Pills***

The pills provide additional important information about the automation instance. In this case, it explains that it's a notification.

***Reference***

This serves as the reference for the instance, providing a unique identifier. It will be part of the unique identifier for the automation instance.

***Edit settings***

To make changes to the instance, click on the 'Cog' icon.

***Duplicate Instance***

To duplicate specific automation instances, click on this 'Copy' icon.

***Instances Report***

To access the report page of the automation instance schedule, click on the 'Calendar' icon. This report will provide details including 'Course full name,' 'Message type,' 'Subject,' 'Full name,' 'Time created,' 'Scheduled time,' 'Status,' and the option to 'Download table data.

***Visibility***

To adjust the visibility of a template, simply click on the 'Eye' icon. A template set to 'Not Visible' will be concealed within courses. Existing automation instances will remain accessible, but new instances cannot be created

***Delete Instances***

To remove specific automation instances from the automation template, simply click on the 'Bin' icon.


## General settings

1. **Title**

   Provide a title for this automation template instance. This title is for administrative purposes and helps in identifying the template.

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

   Adding tags can help categorize and organize templates for administrative purposes.

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

   ***Select Activities:*** This setting allows you to choose from all available activities within your course that have completion configured. This selection determines which specific activities will trigger the automation when their completion conditions are met.

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Activity completion' in the instance; otherwise, the automation templates of the 'Activity completion' option will be applied.`*

3. **Enrolments**

   This automation will be triggered when a user is enrolled in the course where this instance is located.

   ***Disabled:*** Enrolment condition is disabled.

   ***All:*** Enrolment condition applies to all enrolments.

   ***Upcoming:*** Enrolment condition only applies to future enrolments.

4. **Session Booking**

   This automation will activate when a session module is scheduled within the course. This trigger is exclusive to the course and should be chosen when configuring the automation instance. The options for session triggers include:

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


## Notifications

1. **Sender**

   Determines how the selected triggers are evaluated.

   Choose the sender of the notification from the following options:

   **Course Teacher:** The notification will be dispatched from the course teacher, specifically the first one assigned if there are multiple course teachers. In the event that the user is not part of any group, the notification will default to the site support contact.

   *`Note that this is determined by capability, not by an actual role.`*

   **Group Teacher:** The notification will be dispatched by the non-editing teacher who belongs to the same group as the user, specifically the first non-editing teacher assigned if there are multiple in the group. If no non-editing teacher is present in the group, the notification will default to the course teacher.

   *`Note that this is determined by capability, not by an actual role.`*

   **Tenant Role (Workplace Feature):** The notification will be initiated by the user designated to the specified role within the tenant, with preference given to the first one assigned if there are multiple users in that role. In the absence of any user with the selected role, the notification will revert to the site support contact.

   *`Note that this is determined by capability, not by an actual role.`*

   **Custom:** If this option is enabled, an additional configuration for 'Sender Email' will be accessible. In this field, you have the option to specify a precise email address for use as the sender.

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

   **None:** Send notifications immediately upon the condition being met.

   **Before:** The notification to be dispatched a specified number of days or hours before the condition is met. It's important to note that this feature is exclusively applicable to timed events, such as appointment sessions.

   **After:** The notification to be dispatched a specific number of days or hours after the condition has been met. This functionality is available for all types of conditions..

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Delay' in the instance; otherwise, the automation templates option of the 'Delay' will be applied.`*

4. **Delay duraion**

   The duration time for the delay in sending the notification. This duration should be specified in either days or hours, depending on the chosen delay option.

5. **Suppress module**

   The "Suppress Module" functions by proactively preventing the dispatch of notifications once one or more selected activities have been successfully completed.

6. **Suppress Operator**

   The selection of the "suppression operator" is pivotal in determining the precise influence of these activities on the overall notification process.

7. **Limit Number of Notifications**

   This limit is typically imposed to prevent users from receiving an excessive number of notifications, which could be overwhelming or spammy. Enter a number to limit the total number of notifications sent. Enter "0" for no limit. This is only relevant if the schedule is not set to "Once."

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Limit Number of Notifications' in the instance; otherwise, the automation templates option of the 'Limit Number of Notifications' will be applied.`*

8. **Recipients**

   The selected roles with the capability to receive notifications will be used for determining the recipients of notifications.

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Recipients' in the instance; otherwise, the automation templates option of the 'Recipients' will be applied.`*

9. **CC**

   The selected roles with the capability to receive notifications will be used as a CC (Carbon Copy) to the main recipient.

   *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'CC' in the instance; otherwise, the automation templates option of the 'CC' will be applied.`*

10. **BCC**

      The selected roles with the capability to receive notifications will be used as a BCC (Blind Carbon Copy) to the main recipient.

      *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'BCC' in the instance; otherwise, the automation templates option of the 'BCC' will be applied.`*

11. **Subject**

      Refers to the title that you would provide for an notification to briefly describe the content or purpose of the notification.

      *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Subject' in the instance; otherwise, the automation templates option of the 'Subject' will be applied.`*

12. **Header Content**

      The context of email notifications encompasses the information and elements that are presented at the outset of an email message, preceding the main body of the email. This field is equipped to accommodate filters and placeholders

      *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Header Content' in the instance; otherwise, the automation templates option of the 'Header Content' will be applied.`*

13. **Static Content**

      Static content is positioned in the second segment of the notification content. This static content also offers support for filters and placeholders.

      *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Static Content' in the instance; otherwise, the automation templates option of the 'Static Content' will be applied.`*

14. **Dynamic Content**

      Select an activity within the course to add content below the static content. This is only available in the automation instance within the course.

      *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Dynamic Content' in the instance; otherwise, the automation templates option of the 'Dynamic Content' will be applied.`*

      Choose the option on the Dynamic content:

      **None:** This option will disable the Dynamic content of the notification on the automation instances.

      ***Page:*** When you select the 'Page' activity option in Dynamic content, the 'Content Type' and 'Content Length' options will become visible.

      ***Book:*** When you select the 'Book' activity option in Dynamic content, the 'Content Type' and 'Content Length' and 'Chapters' options will become visible.

15. **Content Type**

      Refers to the format of the content being used that helps to describe the type of data or information contained within a resource. Please note that this feature supports specific mod types, such as Page and Book.

      Choose the type of content to be added below the Dynamic content:

      **Description**: If this option is selected, the description of the chosen activity will be included in the body of the notification.

      **Content**: If this option is chosen, the content of the selected activity will be included in the notification body.

16. **Content Length**

      Refers to the size or extent of a piece of content.

      Choose the content length to include in the notification

      **Teaser**: If chosen, only the first paragraph will be used, followed by a 'Read More' link.

      **Full, Linked**: If 'Full, Linked' is selected, the entire content shall be used with a link to the content provided after it.

      **Full, Not Linked**: If 'Full, Not Linked' is selected, the entire content shall be used without a link to the content afterward.

17. ***Chapters***

      Refer to the divisions or sections within a book that help organize and structure the content.

      Select which chapters of the chosen activity will be included in the notification body. To view the chapter content, select the specific chapter using the 'Chapters' option and the content using the 'Content' option for the 'Book' activity.

18. **Footer Content**

      The context of notifications refers to the information and elements placed at the bottom of a notification message.

      *`Toggle button - If you enable the toggle button, the provided option will be applied for the 'Footer Content' in the instance; otherwise, the automation templates option of the 'Footer Content' will be applied.`*

19. **Preview**

      This option displays the notification, enabling you to choose an example user for evaluating the notification's content within a modal window accessed by clicking the button.

![Pulse-automation-instances - Notification](https://github.com/bdecentgmbh/moodle-mod_pulse/assets/57126778/e16ca2ac-c191-49cb-8c90-3089e1f852e3)

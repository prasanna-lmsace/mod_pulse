@mod @mod_pulse @mod_pulse_automation @mod_pulse_automation_template @pulseactions @pulseaction_notification_template
Feature: Configuring the pulseaction_notification plugin on the "Automation template" page, applying different configurations to the notification
  In order to use the features
  As admin
  I need to be able to configure the pulse automation template

  Background:
    Given the following "categories" exist:
        | name  | category | idnumber |
        | Cat 1 | 0        | CAT1     |
    And the following "course" exist:
        | fullname    | shortname | category |
        | Course 1    | C1        | 0        |
    And the following "users" exist:
        | username | firstname | lastname | email |
        | student1 | student | User 1 | student1@test.com |
    And the following "course enrolments" exist:
        | user     | course | role |
        | student1 | C1     | student |

  @javascript
  Scenario: Create notification template and instance
    Given I log in as "admin"
    And I navigate to automation templates
    And I create pulse notification template "WELCOME MESSAGE" "WELCOMEMESSAGE_" to these values:
      | Sender     | Course teacher   |
      | Recipients | Student          |
      | Subject     | Welcome to {Site_FullName} |
      | Header content | Hi {User_firstname} {User_lastname}, <br> Welcome to learning portal of {Site_FullName} |
      | Footer content | Copyright @ 2023 {Site_FullName} |
    Then I should see "Automation templates"
    And I should see "WELCOME MESSAGE" in the "pulse_automation_template" "table"
    And I navigate to course "Course 1" automation instances
    And I create pulse notification instance "WELCOME MESSAGE" "COURSE_1" to these values:
    | Recipients | Student          |
    And I should see "WELCOMEMESSAGE_COURSE_1" in the "pulse_automation_template" "table"
    And I click on ".action-report" "css_element" in the "WELCOMEMESSAGE_COURSE_1" "table_row"
    And I switch to a second window
    And the following should exist in the "reportbuilder-table" table:
    | Full name       | Subject                              | Status |
    | student User 1  | Welcome to Acceptance test site      | Queued |

  @javascript
  Scenario: Override notification template
    Given I log in as "admin"
    And I navigate to automation templates
    And I create pulse notification template "WELCOME MESSAGE" "WELCOMEMESSAGE_" to these values:
      | Sender     | Course teacher   |
      | Recipients | Student          |
      | Subject     | Welcome to {Site_FullName} |
      | Header content | Hi {User_firstname} {User_lastname}, <br> Welcome to learning portal of {Site_FullName} |
      | Footer content | Copyright @ 2023 {Site_FullName} |
    Then I should see "Automation templates"
    And I should see "WELCOME MESSAGE" in the "pulse_automation_template" "table"
    And I navigate to course "Course 1" automation instances
    And I create pulse notification instance "WELCOME MESSAGE" "COURSE_1" to these values:
      | override[subject] | 1                  |
      | Subject           | Welcome to learning portal {Site_FullName}  |
    And I should see "WELCOMEMESSAGE_COURSE_1" in the "pulse_automation_template" "table"
    And I click on ".action-report" "css_element" in the "WELCOMEMESSAGE_COURSE_1" "table_row"
    And I switch to a second window
    And the following should exist in the "reportbuilder-table" table:
    | Full name       | Subject                              | Status |
    | student User 1  | Welcome to learning portal Acceptance test site | Queued |

  # @javascript
  # Scenario: Create notification template and instance
  #   Given I log in as "admin"
  #   And I navigate to automation templates
  #   And I create pulse notification template "WELCOME MESSAGE" "WELCOMEMESSAGE_" to these values:
  #     | Sender     | Course teacher   |
  #     | Recipients | Student          |
  #     | Subject     | Welcome to {Site_FullName} |
  #     | Header content | Hi {User_firstname} {User_lastname}, <br> Welcome to learning portal of {Site_FullName} |
  #     | Footer content | Copyright @ 2023 {Site_FullName} |
  #   Then I should see "Automation templates"
  #   And I should see "WELCOME MESSAGE" in the "pulse_automation_template" "table"
  #   And I navigate to course "Course 1" automation instances
  #   And I create pulse notification instance "WELCOME MESSAGE" "COURSE_1" to these values:
  #     | override[subject] | 1                  |
  #     | Subject           | Welcome to learning portal {Site_FullName}  |
  #   And I should see "WELCOMEMESSAGE_COURSE_1" in the "pulse_automation_template" "table"

    # And I click on "Create new template" "button"
    # Then I should see "Notification" in the "#automation-tabs .nav-item .nav-link[href='#pulse-action-notification']" "css_element"

    # And I set the following fields to these values:
    #   | Title      | WELCOME MESSAGE  |
    #   | Reference  | WELCOMEMESSAGE_   |
    # And I click on "Notification" "link" in the "#automation-tabs .nav-item" "css_element"
    # And I set the following fields to these values:
    #   | Sender     | Course teacher   |
    #   | Recipients | Student          |
    #   | Subject     | Welcome to {Site_FullName} |
    #   | Header content | Hi {User_firstname} {User_lastname}, <br> Welcome to learning portal of {Site_FullName} |
    #   | Footer content | Copyright @ 2023 {Site_FullName} |
    # Then I press "Save changes"
    # Then I should see "Automation templates"
    # And I should see "WELCOME MESSAGE" in the "pulse_automation_template" "table"
    # And I navigate to course "Course 1" automation instances
    # Then I click on "Add automation instance" "button"
    # And I click on "Notification" "link" in the "#automation-tabs .nav-item" "css_element"
    # And I set the following fields to these values:
    #   | insreference      | COURSE_1           |
    # Then I press "Save changes"
    # And I should see "WELCOMEMESSAGE_COURSE_1" in the "pulse_automation_template" "table"

  # @javascript
  # Scenario: Verify instance override in template
  #   Given I log in as "admin"
  #   And I navigate to automation templates
  #   And I click on "Create new template" "button"
  #   Then I should see "Notification" in the "#automation-tabs .nav-item .nav-link[href='#pulse-action-notification']" "css_element"
  #   And I set the following fields to these values:
  #     | Title      | WELCOME MESSAGE  |
  #     | Reference  | WELCOMEMESSAGE_   |
  #   And I click on "Notification" "link" in the "#automation-tabs .nav-item" "css_element"
  #   And I set the following fields to these values:
  #     | Sender     | Course teacher   |
  #     | Recipients | Student          |
  #     | Subject     | Welcome to {Site_FullName} |
  #     | Header content | Hi {User_firstname} {User_lastname}, <br> Welcome to learning portal of {Site_FullName} |
  #     | Footer content | Copyright @ 2023 {Site_FullName} |
  #   Then I press "Save changes"
  #   Then I should see "Automation templates"
  #   And I should see "WELCOME MESSAGE" in the "pulse_automation_template" "table"
  #   And I navigate to course "Course 1" automation instances
  #   Then I click on "Add automation instance" "button"
  #   And I click on "Notification" "link" in the "#automation-tabs .nav-item" "css_element"
  #   And I set the following fields to these values:
  #     | insreference      | COURSE_1           |
  #   And I click on "Notification" "link" in the "#automation-tabs .nav-item" "css_element"
  #     | override[subject] | 1                  |
  #     | Subject           | Welcome to learning portal {Site_FullName}  |
  #   Then I press "Save changes"
  #   And I should see "WELCOMEMESSAGE_COURSE_1" in the "pulse_automation_template" "table"

@core @core_completion
Feature: Students will be marked as completed if they have achieved a passing grade.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | Frist | teacher1@example.com |
      | student1 | Student | First | student1@example.com |
      | student2 | Student | Second | student2@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I navigate to "Edit settings" in current page administration
    And I set the following fields to these values:
      | Enable completion tracking | Yes |
    And I press "Save and display"
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name | Test assignment name |
      | Description | Submit your online text |
      | Completion tracking | Show activity as complete when conditions are met |
      | completionpassgrade | 1 |
      | gradepass           | 50                                                |
      | assignsubmission_onlinetext_enabled | 1 |
      | assignsubmission_onlinetext_wordlimit_enabled | 1 |
      | assignsubmission_onlinetext_wordlimit | 500 |
      | assignsubmission_file_enabled | 0 |
    And "Student First" user has not completed "Test assignment name" activity
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test assignment name"
    When I press "Add submission"
    And I set the following fields to these values:
      | Online text | This is more than 10 words. 1 2 3 4 5 6 7 8 9 10. |
    And I press "Save changes"
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test assignment name"
    When I press "Add submission"
    And I set the following fields to these values:
      | Online text | This is more than 10 words. 1 2 3 4 5 6 7 8 9 10. |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Passing grade completion
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test assignment name"
    And I navigate to "View all submissions" in current page administration
    And I click on "Grade" "link" in the "Student First" "table_row"
    And I set the following fields to these values:
      | Grade | 21 |
    And I press "Save changes"
    And I press "Ok"
    And I follow "View all submissions"
    And I click on "Grade" "link" in the "Student Second" "table_row"
    And I set the following fields to these values:
      | Grade | 50 |
    And I press "Save changes"
    And I press "Ok"
    And I follow "View all submissions"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then the "Test assignment name" "assign" activity with "auto" completion should be marked as not complete
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    Then the "Test assignment name" "assign" activity with "auto" completion should be marked as complete
@core @core_completion
Feature: Students will be marked as completed if they have achieved a passing grade.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1 | 0 | 1 |
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
    And the following "activities" exist:
      | activity | course | idnumber | name | intro | assignsubmission_onlinetext_enabled | assignsubmission_file_enabled | completion | completionpassgrade | gradepass |
      | assign | C1 | a1 | Test assignment name | Submit your online text | 1 | 0 | 2 | 1 | 50 |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And "Student First" user has not completed "Test assignment name" activity
    And I log out

  @javascript
  Scenario: Passing grade completion
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on
    And I give the grade "21" to the user "Student First" for the grade item "Test assignment name"
    And I give the grade "50" to the user "Student Second" for the grade item "Test assignment name"
    And I press "Save changes"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then the "Test assignment name" "assign" activity with "auto" completion should be marked as not complete
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And the "Test assignment name" "assign" activity with "auto" completion should be marked as complete
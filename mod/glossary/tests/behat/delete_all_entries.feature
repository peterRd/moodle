@mod @mod_glossary @blah
Feature: A teacher can choose whether to empty glossary.
  A student should not be able to view this option.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    Given I add a "Glossary" to section "1" and I fill the form with:
      | Name | Test glossary name |
      | Description | Test glossary entries require approval |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test glossary name"
    When I add a glossary entry with the following data:
      | Concept | Just a test concept |
      | Definition | Concept definition |
      | Keyword(s) | Black |
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test glossary name"
    When I add a glossary entry with the following data:
      | Concept | Just another test concept |
      | Definition | Another concept definition |
      | Keyword(s) | White |
    And I log out

  Scenario: Check visibility of empty glossary on a student
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test glossary name"
    Then I should not see "Empty glossary"

  Scenario: Empty glossary when logged in as a teacher
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test glossary name"
    And I press "Empty glossary"
    And I press "Continue"
    Then I should see "No entries found in this section"

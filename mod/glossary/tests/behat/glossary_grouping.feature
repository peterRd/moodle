@mod @mod_glossary @core_tag @javascript
Feature: Glossary entries allow for grouping
  New glossary entries can be assigned to a group if enabled

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 1 | student2@example.com |
      | student3 | Student | 1 | student3@example.com |
      | student4 | Student | 1 | student4@example.com |
      | student5 | Student | 1 | student5@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | teacher |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C1 | student |
      | student4 | C1 | student |
      | student5 | C1 | student |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group A | C1 | G1 |
      | Group B | C1 | G2 |
      | Group C | C1 | G3 |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student2 | G1 |
      | student3 | G2 |
      | student4 | G2 |
      | student2 | G3 |
      | student3 | G3 |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Glossary" to section "1" and I fill the form with:
      | Name | Test glossary |
      | Description | A glossary about dreams! |
      | Group mode  | Separate groups          |
    And I follow "Test glossary"
    And I press "Add a new entry"
    And I set the following fields to these values:
      | Concept | Dummy first entry |
      | Definition | Dream is the start of a journey |
      | Group      | All participants                |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test glossary"
    And I press "Add a new entry"
    And I set the following fields to these values:
      | Concept | Dummy second entry |
      | Definition | Dream is the start of a journey |
    And I press "Save changes"
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And I follow "Test glossary"
    And I press "Add a new entry"
    And I set the following fields to these values:
      | Concept | Dummy third entry |
      | Definition | Dream is the start of a journey |
    And I press "Save changes"
    And I log out
    And I log in as "student5"
    And I am on "Course 1" course homepage
    And I follow "Test glossary"
    And I press "Add a new entry"
    And I set the following fields to these values:
      | Concept | Dummy fourth entry |
      | Definition | Dream is the start of a journey |
    And I press "Save changes"
    And I log out

  Scenario: Glossary 'Separate groups' viewability as a teacher
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test glossary"
    Then I should see "Separate groups" in the ".groupselector" "css_element"
    And I select "All participants" from the "Separate groups" singleselect
    Then I should see "Dummy first entry"
    And  I should see "Dummy second entry"
    And I should see "Dummy third entry"
    And I should see "Dummy fourth entry"
    And I select "Group A" from the "Separate groups" singleselect
    Then I should see "Dummy first entry"
    And  I should see "Dummy second entry"
    And I should see "Dummy fourth entry"
    And I select "Group B" from the "Separate groups" singleselect
    Then I should see "Dummy first entry"
    And I should see "Dummy third entry"
    And I should see "Dummy fourth entry"
    And I select "Group C" from the "Separate groups" singleselect
    Then I should see "Dummy first entry"
    And I should see "Dummy fourth entry"

  Scenario: Glossary 'Separate groups' viewability as a student
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test glossary"
    Then I should see "Separate groups: Group A"
    Then I should see "Dummy first entry"
    And  I should see "Dummy second entry"
    And I should see "Dummy fourth entry"

  Scenario: Glossary 'Separate groups' viewability as a student who belongs to multiple groups
    Given I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test glossary"
    Then I should see "Separate groups" in the ".groupselector" "css_element"
    And I select "Group A" from the "Separate groups" singleselect
    Then I should see "Dummy first entry"
    And  I should see "Dummy second entry"
    And I should see "Dummy fourth entry"
    And I select "Group C" from the "Separate groups" singleselect
    Then I should see "Dummy first entry"
    And I should see "Dummy fourth entry"

  Scenario: Glossary 'Visible groups' viewability as a teacher
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I open "Test glossary" actions menu
    And I choose "Edit settings" in the open action menu
    And I set the following fields to these values:
      | Group | Visible groups |
    And I press "Save and display"
    Then I should see "Visible groups" in the ".groupselector" "css_element"
    And I select "All participants" from the "Visible groups" singleselect
    Then I should see "Dummy first entry"
    And  I should see "Dummy second entry"
    And I should see "Dummy third entry"
    And I should see "Dummy fourth entry"
    And I select "Group A" from the "Visible groups" singleselect
    Then I should see "Dummy first entry"
    And  I should see "Dummy second entry"
    And I should see "Dummy fourth entry"
    And I select "Group B" from the "Visible groups" singleselect
    Then I should see "Dummy first entry"
    And I should see "Dummy third entry"
    And I should see "Dummy fourth entry"
    And I select "Group C" from the "Visible groups" singleselect
    Then I should see "Dummy first entry"
    And I should see "Dummy fourth entry"

  Scenario: Glossary 'Visible groups' viewability as a student
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I open "Test glossary" actions menu
    And I choose "Edit settings" in the open action menu
    And I set the following fields to these values:
      | Group | Visible groups |
    And I press "Save and display"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test glossary"
    Then I should see "Visible groups" in the ".groupselector" "css_element"
    And I select "All participants" from the "Visible groups" singleselect
    Then I should see "Dummy first entry"
    And  I should see "Dummy second entry"
    And I should see "Dummy third entry"
    And I should see "Dummy fourth entry"
    And I select "Group A" from the "Visible groups" singleselect
    Then I should see "Dummy first entry"
    And  I should see "Dummy second entry"
    And I should see "Dummy fourth entry"
    And I select "Group B" from the "Visible groups" singleselect
    Then I should see "Dummy first entry"
    And I should see "Dummy third entry"
    And I should see "Dummy fourth entry"
    And I select "Group C" from the "Visible groups" singleselect
    Then I should see "Dummy first entry"
    And I should see "Dummy fourth entry"

  Scenario: Glossary 'No groups' viewability as a teacher
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I open "Test glossary" actions menu
    And I choose "Edit settings" in the open action menu
    And I set the following fields to these values:
      | Group | No groups |
    And I press "Save and display"
    Then I should see "Dummy first entry"
    And I should see "Dummy second entry"
    And I should see "Dummy third entry"
    And I should see "Dummy fourth entry"

  Scenario: Glossary 'No groups' viewability as a student
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I open "Test glossary" actions menu
    And I choose "Edit settings" in the open action menu
    And I set the following fields to these values:
      | Group | No groups |
    And I press "Save and display"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test glossary"
    Then I should see "Dummy first entry"
    And  I should see "Dummy second entry"
    And I should see "Dummy third entry"
    And I should see "Dummy fourth entry"

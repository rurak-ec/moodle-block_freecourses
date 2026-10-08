@block @block_freecourses @javascript
Feature: Free courses block on the Dashboard
  In order to discover courses I can join for free
  As a student
  I need the Free courses block to list openly self-enrollable courses with search and category filtering

  Background:
    Given the following "categories" exist:
      | name        | idnumber |
      | Science     | sci      |
      | Mathematics | mat      |
    And the following "courses" exist:
      | fullname           | shortname | category |
      | Alpha free course  | ALPHA     | sci      |
      | Beta free course   | BETA      | mat      |
      | Gamma paid course  | GAMMA     | sci      |
    And the following "users" exist:
      | username | firstname | lastname |
      | student1 | Sam       | Student  |
    And I log in as "admin"
    And I add "Self enrolment" enrolment method in "Alpha free course" with:
      | Custom instance name | Alpha self |
    And I add "Self enrolment" enrolment method in "Beta free course" with:
      | Custom instance name | Beta self |
    And I log out

  Scenario: The block lists openly self-enrollable courses and excludes the rest
    Given I log in as "student1"
    And I turn editing mode on
    When I add the "Free courses" block
    Then I should see "Alpha free course" in the "Free courses" "block"
    And I should see "Beta free course" in the "Free courses" "block"
    And I should not see "Gamma paid course" in the "Free courses" "block"

  Scenario: The category filter restricts the listed courses
    Given I log in as "student1"
    And I turn editing mode on
    And I add the "Free courses" block
    When I click on "Filter courses by category" "button" in the "Free courses" "block"
    And I click on "Mathematics" "link" in the "Free courses" "block"
    Then I should see "Beta free course" in the "Free courses" "block"
    And I should not see "Alpha free course" in the "Free courses" "block"

  Scenario: Enrolling from the block lands the user inside the course
    Given I log in as "student1"
    And I turn editing mode on
    And I add the "Free courses" block
    When I click on "Enrol" "button" in the "//div[@data-region='course-content'][contains(normalize-space(.), 'Alpha free course')]" "xpath_element"
    Then the url should match "/course/view\.php\?id=[0-9]+"
    And I should see "You are enrolled in the course."
    And I should see "Alpha free course" in the "page-header" "region"

  Scenario: The search box filters the listed courses
    Given I log in as "student1"
    And I turn editing mode on
    And I add the "Free courses" block
    When I set the field "Search courses" to "Alpha"
    Then I should see "Alpha free course" in the "Free courses" "block"
    And I should not see "Beta free course" in the "Free courses" "block"

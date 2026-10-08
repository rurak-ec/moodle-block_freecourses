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

namespace block_freecourses\local;

/**
 * Unit tests for the Free courses one-click enrolment helper.
 *
 * @package    block_freecourses
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_freecourses\local\enrolment
 */
final class enrolment_test extends \advanced_testcase {
    /**
     * Make sure the 'self' enrolment plugin is enabled site-wide.
     */
    private function enable_self_enrolment(): void {
        global $CFG;

        $enabled = array_filter(explode(',', $CFG->enrol_plugins_enabled));
        if (!in_array('self', $enabled, true)) {
            $enabled[] = 'self';
            set_config('enrol_plugins_enabled', implode(',', $enabled));
        }
    }

    /**
     * Add a self-enrolment instance to a course with exact field values.
     *
     * @param \stdClass $course The course.
     * @param array $enrolfields Overrides for the self-enrolment instance.
     * @return int The new instance id.
     */
    private function add_self_instance(\stdClass $course, array $enrolfields = []): int {
        global $DB;

        $fields = array_merge([
            'status' => ENROL_INSTANCE_ENABLED,
            'customint6' => 1, // Allow new enrolments.
            'password' => '',
            'customint1' => 0,
            'customint3' => 0,
            'customint5' => 0,
            'enrolstartdate' => 0,
            'enrolenddate' => 0,
        ], $enrolfields);

        $instanceid = enrol_get_plugin('self')->add_instance($course, $fields);
        // The add_instance() call may normalise some defaults; force our exact values back.
        $fields['id'] = $instanceid;
        $DB->update_record('enrol', (object) $fields);

        return $instanceid;
    }

    /**
     * Count the user's enrolments (through any instance) in a course.
     *
     * @param int $userid
     * @param int $courseid
     * @return int
     */
    private function count_user_enrolments(int $userid, int $courseid): int {
        global $DB;

        return $DB->count_records_sql(
            'SELECT COUNT(1)
               FROM {user_enrolments} ue
               JOIN {enrol} e ON e.id = ue.enrolid
              WHERE ue.userid = :userid AND e.courseid = :courseid',
            ['userid' => $userid, 'courseid' => $courseid]
        );
    }

    /**
     * A freely self-enrollable course is joined in one call.
     */
    public function test_open_course_enrols_user(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();
        $this->redirectMessages();

        $course = $this->getDataGenerator()->create_course();
        $this->add_self_instance($course);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertTrue(enrolment::enrol_current_user($course));
        $this->assertTrue(is_enrolled(\context_course::instance($course->id), $user, '', true));
    }

    /**
     * Courses with an enrolment key are never joined through the block.
     */
    public function test_course_with_key_is_not_enrolled(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();
        $this->redirectMessages();

        $course = $this->getDataGenerator()->create_course();
        $this->add_self_instance($course, ['password' => 'secret']);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertFalse(enrolment::enrol_current_user($course));
        $this->assertSame(0, $this->count_user_enrolments($user->id, $course->id));
    }

    /**
     * Hidden courses are never joined through the block.
     */
    public function test_hidden_course_is_not_enrolled(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();
        $this->redirectMessages();

        $course = $this->getDataGenerator()->create_course(['visible' => 0]);
        $this->add_self_instance($course);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertFalse(enrolment::enrol_current_user($course));
        $this->assertSame(0, $this->count_user_enrolments($user->id, $course->id));
    }

    /**
     * Repeating the request (e.g. a double click) does not create a second enrolment.
     */
    public function test_already_enrolled_user_is_not_enrolled_twice(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();
        $this->redirectMessages();

        $course = $this->getDataGenerator()->create_course();
        $this->add_self_instance($course);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertTrue(enrolment::enrol_current_user($course));
        $this->assertTrue(enrolment::enrol_current_user($course));
        $this->assertSame(1, $this->count_user_enrolments($user->id, $course->id));
    }

    /**
     * With several open self-enrolment instances the user is enrolled through exactly one.
     */
    public function test_only_one_instance_is_used(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();
        $this->redirectMessages();

        $course = $this->getDataGenerator()->create_course();
        $this->add_self_instance($course);
        $this->add_self_instance($course);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertTrue(enrolment::enrol_current_user($course));
        $this->assertSame(1, $this->count_user_enrolments($user->id, $course->id));
    }
}

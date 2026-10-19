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

namespace block_freecourses;

/**
 * Unit tests for the Free courses block class.
 *
 * @package    block_freecourses
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_freecourses
 */
final class block_test extends \advanced_testcase {
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
     * Create a course with an enabled, fully open self-enrolment instance.
     *
     * @return \stdClass The created course.
     */
    private function create_free_course(): \stdClass {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $fields = [
            'status' => ENROL_INSTANCE_ENABLED,
            'customint6' => 1, // Allow new enrolments.
            'password' => '',
            'customint1' => 0,
            'customint3' => 0,
            'customint5' => 0,
            'enrolstartdate' => 0,
            'enrolenddate' => 0,
        ];
        $instanceid = enrol_get_plugin('self')->add_instance($course, $fields);
        // The add_instance() call may normalise some defaults; force our exact values back.
        $fields['id'] = $instanceid;
        $DB->update_record('enrol', (object) $fields);

        return $course;
    }

    /**
     * Create a block instance on the Dashboard as seen by the given user.
     *
     * @param \stdClass $user The viewing user.
     * @return \block_base
     */
    private function create_block_as(\stdClass $user): \block_base {
        global $PAGE;

        $this->setUser($user);
        $PAGE->set_url(new \moodle_url('/my/index.php'));
        $record = $this->getDataGenerator()->create_block('freecourses');

        return block_instance_by_id($record->id);
    }

    /**
     * With no free courses at all, the block has no content so Moodle hides it, title included.
     */
    public function test_block_is_empty_without_free_courses(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();

        $block = $this->create_block_as($this->getDataGenerator()->create_user());

        $this->assertTrue($block->is_empty());
        $this->assertSame('', $block->get_content()->text);
    }

    /**
     * A user who is already enrolled in every free course sees no block.
     */
    public function test_block_is_empty_when_user_is_enrolled_in_every_free_course(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();

        $course = $this->create_free_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $block = $this->create_block_as($user);

        $this->assertTrue($block->is_empty());
    }

    /**
     * A user with a free course left to join sees the block with its card.
     */
    public function test_block_has_content_with_a_free_course(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();

        $course = $this->create_free_course();

        $block = $this->create_block_as($this->getDataGenerator()->create_user());

        $this->assertFalse($block->is_empty());
        $this->assertStringContainsString(format_string($course->fullname), $block->get_content()->text);
    }
}

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

namespace block_freecourses\output;

/**
 * Unit tests for the Free courses block renderable.
 *
 * @package    block_freecourses
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_freecourses\output\main
 */
final class main_test extends \advanced_testcase {
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
     * Create a course with an enabled self-enrolment instance.
     *
     * @param array $coursefields Extra fields for the course (e.g. category).
     * @param array $enrolfields Overrides for the self-enrolment instance.
     * @return \stdClass The created course.
     */
    private function create_course_with_self_enrol(array $coursefields = [], array $enrolfields = []): \stdClass {
        global $DB;

        $course = $this->getDataGenerator()->create_course($coursefields);
        $selfplugin = enrol_get_plugin('self');

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

        $instanceid = $selfplugin->add_instance($course, $fields);
        // The add_instance() call may normalise some defaults; force our exact values back.
        $fields['id'] = $instanceid;
        $DB->update_record('enrol', (object) $fields);

        return $course;
    }

    /**
     * Export the block template context as the given user.
     *
     * @param \stdClass $user The viewing user.
     * @return array
     */
    private function export_as(\stdClass $user): array {
        global $PAGE;

        $this->setUser($user);
        $PAGE->set_url(new \moodle_url('/my/index.php'));
        $renderer = $PAGE->get_renderer('block_freecourses');
        $main = new main();
        return $main->export_for_template($renderer);
    }

    /**
     * Return the course ids present in an exported context.
     *
     * @param array $context Exported template context.
     * @return int[]
     */
    private function course_ids(array $context): array {
        return array_map(static fn($card) => (int) $card['id'], $context['courses']);
    }

    /**
     * A freely self-enrollable course is listed for a non-enrolled user.
     */
    public function test_free_course_is_listed(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();

        $course = $this->create_course_with_self_enrol();
        $user = $this->getDataGenerator()->create_user();

        $context = $this->export_as($user);

        $this->assertTrue($context['hascourses']);
        $this->assertContains((int) $course->id, $this->course_ids($context));
    }

    /**
     * Courses that are not freely open are excluded.
     *
     * @dataProvider barrier_provider
     * @param array $enrolfields The self-enrolment instance fields that introduce a barrier.
     */
    public function test_barriers_exclude_course(array $enrolfields): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();

        $course = $this->create_course_with_self_enrol([], $enrolfields);
        $user = $this->getDataGenerator()->create_user();

        $context = $this->export_as($user);

        $this->assertNotContains((int) $course->id, $this->course_ids($context));
    }

    /**
     * Data provider of self-enrolment barriers that must hide a course.
     *
     * @return array<string, array{0: array}>
     */
    public static function barrier_provider(): array {
        return [
            'enrolment key' => [['password' => 'secret']],
            'group key' => [['customint1' => 1]],
            'cohort restriction' => [['customint5' => 99]],
            'capacity limit' => [['customint3' => 5]],
            'new enrolments disabled' => [['customint6' => 0]],
            'instance disabled' => [['status' => ENROL_INSTANCE_DISABLED]],
        ];
    }

    /**
     * A course the user is already enrolled in is not listed.
     */
    public function test_enrolled_course_is_excluded(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();

        $course = $this->create_course_with_self_enrol();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $context = $this->export_as($user);

        $this->assertNotContains((int) $course->id, $this->course_ids($context));
    }

    /**
     * The category filter restricts the listed courses.
     */
    public function test_category_filter(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();

        $cat1 = $this->getDataGenerator()->create_category();
        $cat2 = $this->getDataGenerator()->create_category();
        $course1 = $this->create_course_with_self_enrol(['category' => $cat1->id]);
        $course2 = $this->create_course_with_self_enrol(['category' => $cat2->id]);
        $user = $this->getDataGenerator()->create_user();

        // Without a filter, both courses are listed.
        $all = $this->export_as($user);
        $this->assertContains((int) $course1->id, $this->course_ids($all));
        $this->assertContains((int) $course2->id, $this->course_ids($all));
        $this->assertTrue($all['showcategoryfilter']);

        // Filtering by the first category leaves only its course.
        $_GET['freecoursescategory'] = $cat1->id;
        $filtered = $this->export_as($user);
        unset($_GET['freecoursescategory']);

        $this->assertContains((int) $course1->id, $this->course_ids($filtered));
        $this->assertNotContains((int) $course2->id, $this->course_ids($filtered));
    }

    /**
     * The expensive candidate scan is cached site-wide after the first render.
     */
    public function test_candidate_list_is_cached(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();

        $this->create_course_with_self_enrol();
        $user = $this->getDataGenerator()->create_user();

        $cache = \cache::make('block_freecourses', 'candidates');
        $this->assertFalse($cache->get('all'));

        $this->export_as($user);

        $this->assertIsArray($cache->get('all'));
    }

    /**
     * With no free courses the block reports an empty state.
     */
    public function test_empty_state(): void {
        $this->resetAfterTest();
        $this->enable_self_enrolment();

        $user = $this->getDataGenerator()->create_user();
        $context = $this->export_as($user);

        $this->assertFalse($context['hascourses']);
        $this->assertEmpty($context['courses']);
    }
}

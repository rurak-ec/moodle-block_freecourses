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

/**
 * Renderable for block_freecourses.
 *
 * @package    block_freecourses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_freecourses\output;

defined('MOODLE_INTERNAL') || die();

use core_text;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Main block renderable.
 */
class main implements renderable, templatable {

    /**
     * Export template context.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $courses = $this->get_free_courses();

        return [
            'uniqid' => uniqid(),
            'hascourses' => !empty($courses),
            'courses' => $courses,
        ];
    }

    /**
     * Load courses that are open for free self-enrolment.
     *
     * A free course here means:
     * - visible course
     * - enabled self-enrol instance
     * - no enrolment key and no group key
     * - no cohort/date/capacity barriers
     * - currently self-enrollable according to Moodle self enrol plugin rules
     *
     * @return array<int, array<string, mixed>>
     */
    private function get_free_courses(): array {
        global $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        if (!enrol_is_enabled('self')) {
            return [];
        }

        $selfplugin = enrol_get_plugin('self');
        if (!$selfplugin) {
            return [];
        }

        // Use core API that already applies the standard course visibility checks.
        $courses = get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.visible');
        if (!$courses) {
            return [];
        }

        $cards = [];
        foreach ($courses as $course) {
            if ((int)$course->id === SITEID || (int)$course->visible !== 1) {
                continue;
            }

            if (!$this->get_open_self_enrol_instance($course, $selfplugin)) {
                continue;
            }

            $context = \context_course::instance($course->id);
            $fullname = format_string($course->fullname, true, ['context' => $context]);
            $cards[] = [
                'fullname' => $fullname,
                'searchname' => core_text::strtolower($fullname),
                'enrolurl' => (new moodle_url('/enrol/index.php', [
                    'id' => $course->id,
                    'action' => 'enrol',
                    'sesskey' => sesskey(),
                ]))->out(false),
            ];
        }

        return $cards;
    }

    /**
     * Find an enabled self-enrol instance that is fully open.
     *
     * @param stdClass $course
     * @param \enrol_plugin $selfplugin
     * @return stdClass|null
     */
    private function get_open_self_enrol_instance(stdClass $course, \enrol_plugin $selfplugin): ?stdClass {
        $instances = enrol_get_instances($course->id, true);
        foreach ($instances as $instance) {
            if ($instance->enrol !== 'self') {
                continue;
            }

            if ((int)$instance->status !== ENROL_INSTANCE_ENABLED) {
                continue;
            }

            if (!$this->is_open_self_enrol_instance($instance)) {
                continue;
            }

            if ($selfplugin->can_self_enrol($instance) !== true) {
                continue;
            }

            return $instance;
        }

        return null;
    }

    /**
     * Validate self-enrol instance conditions for free access.
     *
     * @param stdClass $instance
     * @return bool
     */
    private function is_open_self_enrol_instance(stdClass $instance): bool {
        if (!empty($instance->password)) {
            return false;
        }

        if (!empty($instance->customint1)) {
            return false;
        }

        if (!empty($instance->customint5)) {
            return false;
        }

        if (!empty($instance->enrolstartdate) || !empty($instance->enrolenddate)) {
            return false;
        }

        if (!empty($instance->customint3)) {
            return false;
        }

        if (empty($instance->customint6)) {
            return false;
        }

        return true;
    }
}

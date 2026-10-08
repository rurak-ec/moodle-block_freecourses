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
 * Free self-enrolment detection and one-click enrolment for block_freecourses.
 *
 * @package    block_freecourses
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_freecourses\local;

use stdClass;

/**
 * Shared helper used by the block (to list free courses) and by enrol.php (to enrol into one).
 */
class enrolment {
    /**
     * Find the first enabled self-enrol instance of a course that is fully open (no barriers).
     *
     * @param stdClass $course Course record (needs at least id and visible).
     * @return stdClass|null The open enrol instance, or null if the course is not freely self-enrollable.
     */
    public static function get_open_instance(stdClass $course): ?stdClass {
        global $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        if ((int)$course->id === SITEID || (int)$course->visible !== 1) {
            return null;
        }

        if (!enrol_is_enabled('self')) {
            return null;
        }

        $instances = enrol_get_instances($course->id, true);
        foreach ($instances as $instance) {
            if ($instance->enrol !== 'self') {
                continue;
            }

            if ((int)$instance->status !== ENROL_INSTANCE_ENABLED) {
                continue;
            }

            if (self::is_open_instance($instance)) {
                return $instance;
            }
        }

        return null;
    }

    /**
     * Validate self-enrol instance conditions for free, open access.
     *
     * @param stdClass $instance
     * @return bool
     */
    public static function is_open_instance(stdClass $instance): bool {
        // An enrolment key (password) is required: not freely open.
        if (!empty($instance->password)) {
            return false;
        }

        // A group enrolment key is required.
        if (!empty($instance->customint1)) {
            return false;
        }

        // Restricted to members of a cohort.
        if (!empty($instance->customint5)) {
            return false;
        }

        // Restricted to a date window.
        if (!empty($instance->enrolstartdate) || !empty($instance->enrolenddate)) {
            return false;
        }

        // Limited to a maximum number of enrolled users.
        if (!empty($instance->customint3)) {
            return false;
        }

        // New self-enrolments are not allowed on this instance.
        if (empty($instance->customint6)) {
            return false;
        }

        return true;
    }

    /**
     * Enrol the current user into a free course through its open self-enrol instance.
     *
     * Everything is re-checked against the database (not the block's cached candidate list), and the
     * enrolment goes through the self-enrol plugin's own API, so notifications, events and welcome
     * messages are exactly those of the core enrolment page. Only one instance is ever used.
     *
     * @param stdClass $course Course record.
     * @return bool True if the user is actively enrolled in the course afterwards.
     */
    public static function enrol_current_user(stdClass $course): bool {
        global $USER;

        $context = \context_course::instance($course->id);
        if (is_enrolled($context, $USER, '', true)) {
            return true;
        }

        if (!\core_course_category::can_view_course_info($course)) {
            return false;
        }

        $instance = self::get_open_instance($course);
        if (!$instance) {
            return false;
        }

        $selfplugin = enrol_get_plugin('self');
        if (!$selfplugin || $selfplugin->can_self_enrol($instance) !== true) {
            return false;
        }

        $selfplugin->enrol_self($instance, (object)[]);

        return is_enrolled($context, $USER, '', true);
    }
}

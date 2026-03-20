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

use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Main block renderable.
 */
class main implements renderable, templatable {
    /** @var string category filter query parameter name */
    private const CATEGORYPARAM = 'freecoursescategory';

    /**
     * Export template context.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $selectedcategoryid = optional_param(self::CATEGORYPARAM, 0, PARAM_INT);
        $coursecontext = $this->get_free_courses($output, $selectedcategoryid);

        return [
            'uniqid' => uniqid(),
            'hascourses' => !empty($coursecontext['courses']),
            'courses' => $coursecontext['courses'],
            'showcategoryfilter' => !empty($coursecontext['categoryoptions']),
            'selectedcategoryname' => $coursecontext['selectedcategoryname'],
            'allcategoriesurl' => $this->build_category_filter_url(0)->out(false),
            'allcategoriesactive' => empty($coursecontext['selectedcategoryid']),
            'hascategoryoptions' => !empty($coursecontext['categoryoptions']),
            'categoryoptions' => $coursecontext['categoryoptions'],
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
     * @param renderer_base $output
     * @param int $selectedcategoryid
     * @return array<string, mixed>
     */
    private function get_free_courses(renderer_base $output, int $selectedcategoryid): array {
        global $CFG, $USER;

        require_once($CFG->libdir . '/enrollib.php');

        if (!enrol_is_enabled('self')) {
            return $this->get_empty_course_context();
        }

        $selfplugin = enrol_get_plugin('self');
        if (!$selfplugin) {
            return $this->get_empty_course_context();
        }

        // Use core API that already applies standard Moodle course visibility checks.
        $courses = get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.shortname, c.category, c.visible');
        if (!$courses) {
            return $this->get_empty_course_context();
        }

        $cards = [];
        $categories = [];
        foreach ($courses as $course) {
            if ((int)$course->id === SITEID || (int)$course->visible !== 1) {
                continue;
            }

            if (!$this->get_open_self_enrol_instance($course, $selfplugin)) {
                continue;
            }

            $context = \context_course::instance($course->id);
            if (is_enrolled($context, $USER, "", true)) {
                continue;
            }

            $fullname = format_string($course->fullname, true, ['context' => $context]);
            $categoryid = (int)$course->category;
            $coursecategory = $this->get_course_category_name($categoryid);
            $courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
            if (!$courseimage) {
                $courseimage = $output->get_generated_image_for_id($course->id);
            }

            if (!empty($coursecategory) && !array_key_exists($categoryid, $categories)) {
                $categories[$categoryid] = $coursecategory;
            }

            $cards[] = [
                'id' => (int)$course->id,
                'uniqid' => uniqid(),
                'fullname' => $fullname,
                'categoryid' => $categoryid,
                'viewurl' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
                'enrolurl' => (new moodle_url('/enrol/index.php', [
                    'id' => $course->id,
                    'action' => 'enrol',
                    'sesskey' => sesskey(),
                ]))->out(false),
                'courseimage' => $courseimage,
                'coursecategory' => $coursecategory,
                'showcoursecategory' => !empty($coursecategory),
                'visible' => true,
            ];
        }

        \core_collator::asort($categories, \core_collator::SORT_NATURAL);

        if (!array_key_exists($selectedcategoryid, $categories)) {
            $selectedcategoryid = 0;
        }

        if (!empty($selectedcategoryid)) {
            $cards = array_values(array_filter($cards, static function(array $course) use ($selectedcategoryid): bool {
                return (int)$course['categoryid'] === $selectedcategoryid;
            }));
        }

        $categoryoptions = [];
        foreach ($categories as $categoryid => $categoryname) {
            $categoryoptions[] = [
                'id' => (int)$categoryid,
                'name' => $categoryname,
                'url' => $this->build_category_filter_url((int)$categoryid)->out(false),
                'active' => ((int)$categoryid === $selectedcategoryid),
            ];
        }

        return [
            'courses' => $cards,
            'categoryoptions' => $categoryoptions,
            'selectedcategoryid' => $selectedcategoryid,
            'selectedcategoryname' => $selectedcategoryid
                ? $categories[$selectedcategoryid]
                : get_string('allcategories', 'block_freecourses'),
        ];
    }

    /**
     * Return empty template context for courses and filters.
     *
     * @return array<string, mixed>
     */
    private function get_empty_course_context(): array {
        return [
            'courses' => [],
            'categoryoptions' => [],
            'selectedcategoryid' => 0,
            'selectedcategoryname' => get_string('allcategories', 'block_freecourses'),
        ];
    }

    /**
     * Build filter URL preserving current page params.
     *
     * @param int $categoryid
     * @return moodle_url
     */
    private function build_category_filter_url(int $categoryid): moodle_url {
        global $PAGE;

        $url = new moodle_url($PAGE->url);
        $url->remove_params(self::CATEGORYPARAM);
        if ($categoryid > 0) {
            $url->param(self::CATEGORYPARAM, $categoryid);
        }
        return $url;
    }

    /**
     * Get formatted course category name.
     *
     * @param int $categoryid
     * @return string
     */
    private function get_course_category_name(int $categoryid): string {
        if (empty($categoryid)) {
            return '';
        }

        try {
            $category = \core_course_category::get($categoryid, MUST_EXIST, true);
            return format_string($category->name, true, ['context' => $category->get_context()]);
        } catch (\Throwable $e) {
            return '';
        }
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

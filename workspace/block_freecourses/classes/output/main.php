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
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_freecourses\output;

use block_freecourses\local\enrolment;
use cache;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Main block renderable.
 */
class main implements renderable, templatable {
    /** @var string Category filter query parameter name. */
    private const CATEGORYPARAM = 'freecoursescategory';

    /** @var string Unique id shared between the rendered template and the AMD module. */
    private string $uniqid;

    /** @var bool Whether this renderable has courses to display. */
    private bool $hascourses = false;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->uniqid = uniqid();
    }

    /**
     * Get the unique id for this block instance.
     *
     * @return string
     */
    public function get_uniqid(): string {
        return $this->uniqid;
    }

    /**
     * Check if this block instance has free courses to display.
     *
     * @return bool
     */
    public function has_courses(): bool {
        return $this->hascourses;
    }

    /**
     * Export template context.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $selectedcategoryid = optional_param(self::CATEGORYPARAM, 0, PARAM_INT);
        $coursecontext = $this->get_free_courses($output, $selectedcategoryid);
        $this->hascourses = !empty($coursecontext['courses']);

        return array_merge([
            'uniqid' => $this->uniqid,
            'sesskey' => sesskey(),
            'hascourses' => $this->hascourses,
            'courses' => $coursecontext['courses'],
            'showcategoryfilter' => !empty($coursecontext['categoryoptions']),
            'selectedcategoryname' => $coursecontext['selectedcategoryname'],
            'allcategoriesurl' => $this->build_category_filter_url(0)->out(false),
            'allcategoriesactive' => empty($coursecontext['selectedcategoryid']),
            'hascategoryoptions' => !empty($coursecontext['categoryoptions']),
            'categoryoptions' => $coursecontext['categoryoptions'],
        ], self::get_bootstrap_markup());
    }

    /**
     * Markup that differs between Bootstrap 4 (Moodle 4.5) and Bootstrap 5 (Moodle 5.0+).
     *
     * Moodle 4.5 only bridges the BS5 spacing/float/text utilities, while Moodle 5.x deprecates the
     * BS4 names, so these three must follow the running Moodle branch.
     *
     * @return array<string, string>
     */
    public static function get_bootstrap_markup(): array {
        global $CFG;

        $bs5 = (int)$CFG->branch >= 500;
        return [
            'srclass' => $bs5 ? 'visually-hidden' : 'sr-only',
            'dropdowntoggleattr' => $bs5 ? 'data-bs-toggle' : 'data-toggle',
            'dropdownmenuendclass' => $bs5 ? 'dropdown-menu-end' : 'dropdown-menu-right',
        ];
    }

    /**
     * Build the per-user view of free courses from the cached candidate list.
     *
     * The expensive, user-independent scan (which courses are open for free
     * self-enrolment) is cached site-wide; here we only apply the per-user
     * filters (already enrolled / can self enrol) and build the display data.
     *
     * @param renderer_base $output
     * @param int $selectedcategoryid
     * @return array<string, mixed>
     */
    private function get_free_courses(renderer_base $output, int $selectedcategoryid): array {
        global $USER;

        $candidates = $this->get_candidate_courses();
        if (!$candidates) {
            return $this->get_empty_course_context();
        }

        $selfplugin = enrol_get_plugin('self');
        if (!$selfplugin) {
            return $this->get_empty_course_context();
        }

        $cards = [];
        $categories = [];
        $categorynamecache = [];
        foreach ($candidates as $candidate) {
            $context = \context_course::instance($candidate->id, IGNORE_MISSING);
            if (!$context) {
                continue;
            }

            // Per-user filters: skip courses the user is already in or cannot self enrol into.
            if (is_enrolled($context, $USER, '', true)) {
                continue;
            }
            if ($selfplugin->can_self_enrol($candidate->enrol) !== true) {
                continue;
            }

            $fullname = format_string($candidate->fullname, true, ['context' => $context]);
            $categoryid = (int)$candidate->category;

            // Memoize category formatting to avoid redundant category fetches across courses.
            if (!isset($categorynamecache[$categoryid])) {
                $categorynamecache[$categoryid] = $this->get_course_category_name($categoryid);
            }
            $coursecategory = $categorynamecache[$categoryid];

            // Use pre-computed course image if cached, or fallback to generated theme image.
            $courseimage = !empty($candidate->courseimage)
                ? $candidate->courseimage
                : $output->get_generated_image_for_id($candidate->id);

            if (!empty($coursecategory) && !array_key_exists($categoryid, $categories)) {
                $categories[$categoryid] = $coursecategory;
            }

            $cards[] = [
                'id' => (int)$candidate->id,
                'uniqid' => $this->uniqid . '-c' . $candidate->id,
                'fullname' => $fullname,
                'categoryid' => $categoryid,
                'viewurl' => (new moodle_url('/course/view.php', ['id' => $candidate->id]))->out(false),
                // Posted (with sesskey) to our own endpoint, which enrols and then lands the user in the course.
                'enrolurl' => (new moodle_url('/blocks/freecourses/enrol.php', ['id' => $candidate->id]))->out(false),
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
            $cards = array_values(array_filter($cards, static function (array $course) use ($selectedcategoryid): bool {
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
     * Return the cached, user-independent list of free-self-enrolment candidate courses.
     *
     * @return array<int, stdClass> Candidate records (id, fullname, shortname, category, enrol instance).
     */
    private function get_candidate_courses(): array {
        $cache = cache::make('block_freecourses', 'candidates');
        $candidates = $cache->get('all');
        if (is_array($candidates)) {
            return $candidates;
        }

        $candidates = $this->build_candidate_courses();
        $cache->set('all', $candidates);
        return $candidates;
    }

    /**
     * Scan the site for courses open for free self-enrolment (user-independent).
     *
     * Uses a single indexed SQL JOIN query instead of N+1 individual instance queries.
     *
     * @return array<int, stdClass>
     */
    private function build_candidate_courses(): array {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        if (!enrol_is_enabled('self')) {
            return [];
        }

        // Single indexed query joining course and enrol tables.
        // Replaces get_courses() + N calls to enrol_get_instances().
        $sql = "SELECT c.id, c.fullname, c.shortname, c.category, c.visible,
                       e.id AS enrolid, e.enrol, e.status AS enrolstatus, e.password,
                       e.customint1, e.customint2, e.customint3, e.customint4, e.customint5, e.customint6,
                       e.enrolstartdate, e.enrolenddate, e.sortorder AS enrolsortorder
                  FROM {course} c
                  JOIN {enrol} e ON e.courseid = c.id
                 WHERE c.id != :siteid
                   AND c.visible = 1
                   AND e.enrol = 'self'
                   AND e.status = :enrolstatus
                 ORDER BY c.sortorder ASC, e.sortorder ASC";

        $records = $DB->get_records_sql($sql, [
            'siteid' => SITEID,
            'enrolstatus' => ENROL_INSTANCE_ENABLED,
        ]);

        if (!$records) {
            return [];
        }

        $candidates = [];
        $seen = [];
        foreach ($records as $rec) {
            if (isset($seen[$rec->id])) {
                continue;
            }

            $instance = (object)[
                'id' => (int)$rec->enrolid,
                'courseid' => (int)$rec->id,
                'enrol' => $rec->enrol,
                'status' => (int)$rec->enrolstatus,
                'password' => $rec->password,
                'customint1' => $rec->customint1,
                'customint2' => $rec->customint2,
                'customint3' => $rec->customint3,
                'customint4' => $rec->customint4,
                'customint5' => $rec->customint5,
                'customint6' => $rec->customint6,
                'enrolstartdate' => $rec->enrolstartdate,
                'enrolenddate' => $rec->enrolenddate,
            ];

            if (!enrolment::is_open_instance($instance)) {
                continue;
            }

            $seen[$rec->id] = true;

            // Pre-resolve course image once so it is saved in MUC and shared across users.
            $courseobj = (object)[
                'id' => (int)$rec->id,
                'fullname' => $rec->fullname,
                'shortname' => $rec->shortname,
                'category' => (int)$rec->category,
            ];
            $courseimage = \core_course\external\course_summary_exporter::get_course_image($courseobj) ?: '';

            $candidates[] = (object)[
                'id' => (int)$rec->id,
                'fullname' => $rec->fullname,
                'shortname' => $rec->shortname,
                'category' => (int)$rec->category,
                'courseimage' => $courseimage,
                'enrol' => $instance,
            ];
        }

        return $candidates;
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
}

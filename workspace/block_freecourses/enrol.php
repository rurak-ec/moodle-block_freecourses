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
 * One-click enrolment from the Free courses block, landing the user in the course.
 *
 * The core enrolment page is not used directly because it behaves differently across the supported
 * versions (Moodle 4.5 ignores action=enrol and asks for a second click) and, after enrolling, it
 * redirects to $SESSION->wantsurl, which OAuth2 logins leave pointing at the Dashboard.
 *
 * @package    block_freecourses
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$courseid = required_param('id', PARAM_INT);

$course = get_course($courseid);

$PAGE->set_url(new moodle_url('/blocks/freecourses/enrol.php', ['id' => $course->id]));
$PAGE->set_context(context_system::instance());

require_login(null, false);

// The core enrolment page explains why a course cannot be joined (or asks for a key), so it is the fallback.
$coreenrolurl = new moodle_url('/enrol/index.php', ['id' => $course->id]);

if (!confirm_sesskey()) {
    redirect($coreenrolurl);
}

if (\block_freecourses\local\enrolment::enrol_current_user($course)) {
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}

redirect($coreenrolurl);

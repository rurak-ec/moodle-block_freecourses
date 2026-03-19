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
 * Renderer for block_freecourses.
 *
 * @package    block_freecourses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_freecourses\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Plugin renderer.
 */
class renderer extends plugin_renderer_base {

    /**
     * Render main content.
     *
     * @param main $main
     * @return string
     */
    public function render_main(main $main): string {
        return $this->render_from_template('block_freecourses/main', $main->export_for_template($this));
    }
}

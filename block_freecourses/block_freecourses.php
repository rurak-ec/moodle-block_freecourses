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
 * Main class for the Free courses block.
 *
 * @package    block_freecourses
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Free courses block class.
 */
class block_freecourses extends block_base {
    /**
     * Initialise block title.
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_freecourses');
    }

    /**
     * Build block content.
     *
     * @return stdClass
     */
    public function get_content(): stdClass {
        if (isset($this->content)) {
            return $this->content;
        }

        $renderable = new \block_freecourses\output\main();
        $renderer = $this->page->get_renderer('block_freecourses');

        $this->content = new stdClass();
        $this->content->text = $renderer->render($renderable);
        $this->content->footer = '';

        // Deliver the search/filter behaviour as an AMD module (no inline JavaScript).
        $this->page->requires->js_call_amd('block_freecourses/search', 'init', [$renderable->get_uniqid()]);

        return $this->content;
    }

    /**
     * Limit block to dashboard.
     *
     * @return array
     */
    public function applicable_formats(): array {
        return ['my' => true];
    }

    /**
     * This block has no global settings page.
     *
     * @return bool
     */
    public function has_config(): bool {
        return false;
    }
}

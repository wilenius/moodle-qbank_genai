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
 * Plugin entrypoint for navigation.
 *
 * @package    qbank_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace qbank_genai;

/**
 * Class navigation.
 *
 * @package    qbank_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation extends \core_question\local\bank\navigation_node_base {

    public function get_navigation_title(): string {
        return get_string('aiquestions', 'qbank_genai');
    }

    public function get_navigation_key(): string {
        return 'generatequestion';
    }

    public function get_navigation_url(): \moodle_url {
        return new \moodle_url('/question/bank/genai/story.php');
    }

}

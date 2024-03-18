<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SpellCheckingInspection */
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * View MBTI teams
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $CFG, $PAGE, $OUTPUT, $ALL_COGNITIVE_MODES;

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/helpers/build_helper_functions.php');
require_once(__DIR__ . '/classes/form/build_teams_form.php');
require_once($CFG->libdir.'/tablelib.php');

class mbti_team_table extends flexible_table {

    function __construct($uniqueid) {

    }
    public function define_columns($mform) {
        $columns = array();
        $columns[] = 'id';
        $columns[] = 'name';
        $columns[] = 'description';
        return $columns;
    }

    public function define_headers($mform) {
        $headers = array();
        $headers['id'] = get_string('id', 'your_plugin');
        $headers['name'] = get_string('name', 'your_plugin');
        $headers['description'] = get_string('description', 'your_plugin');
        return $headers;
    }

    protected function define_data($mform) {
        $data = array();
        // Get data from a database or other source
        $data = $this->get_data();
        return $data;
    }

    private function get_data() {
        global $DB;
        $sql = "SELECT * FROM your_table";
        $data = $DB->get_records_sql($sql);
        return $data;
    }
}

$groups = groups_get_all_groups($courseid);



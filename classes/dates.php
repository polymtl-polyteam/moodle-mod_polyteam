<?php
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
 * Dates definition to work with
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_polyteam;

use core\activity_dates;

class dates extends activity_dates {

    /**
     * Returns a list of important dates in mod_polyteam
     *
     * @return array
     */
    protected function get_dates(): array {
        global $DB;
        $moduleinstance = $DB->get_record('polyteam', array('id' => $this->cm->instance), '*', MUST_EXIST);

        $timeopen = $moduleinstance->timeopen ?? null;
        $timeclose = $moduleinstance->timeclose ?? null;
        $now = time();
        $dates = [];

        if ($timeopen) {
            $openlabelid = $timeopen > $now ? 'activitydate:opens' : 'activitydate:opened';
            $dates[] = [
                'label' => get_string($openlabelid, 'course'),
                'timestamp' => (int) $timeopen,
            ];
        }

        if ($timeclose) {
            $closelabelid = $timeclose > $now ? 'activitydate:closes' : 'activitydate:closed';
            $dates[] = [
                'label' => get_string($closelabelid, 'course'),
                'timestamp' => (int) $timeclose,
            ];
        }

        return $dates;
    }
}
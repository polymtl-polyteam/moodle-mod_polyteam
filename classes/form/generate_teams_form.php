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
 * the mod_polyteam generate team form.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_polyteam\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

// TODO : Internationalization
// TODO : Refactor random matching to enum ?

/**
 * the mod_polyteam generate team form class.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generate_teams_form extends \moodleform
{

    /**
     * Add elements to form.
     */
    public function definition()
    {
        global $OUTPUT;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id', $this->_customdata['id']); // Course module id.
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'generateteamsheader', 'Generate teams');
        $mform->setExpanded('generateteamsheader');

        $buttons = array();
        $buttons[] =& $mform->createElement('radio', 'matchingstrategy', '', 'Random match', 'randommatching');
        $buttons[] =& $mform->createElement('radio', 'matchingstrategy', '', 'Fast matching', 'fastmatching');
        $buttons[] =& $mform->createElement('radio', 'matchingstrategy', '', 'Maximize the number of perfect teams', 'simulatedannealingsum');
        $buttons[] =& $mform->createElement('radio', 'matchingstrategy', '', 'Minimize area under cognitive curve', 'simulatedannealingsse');
        $buttons[] =& $mform->createElement('radio', 'matchingstrategy', '', 'Minimize cognitive differences between teams', 'simulatedannealingstd');
        $mform->addGroup($buttons, 'matchingstrategy', 'Matching strategy', ['<br>'], false);
        $mform->setDefault('matchingstrategy', $this->_customdata['matchingstrategy']);
        $mform->addHelpButton('matchingstrategy', 'matchingstrategy', 'mod_polyteam');

        $teamssizeoptions = array();
        for ($i = 2; $i <= 25; $i++) {
            $teamssizeoptions[$i] = $i;
        }
        $select = $mform->addElement('select', 'nstudentsperteam', 'Teams size', $teamssizeoptions);
        $select->setSelected($this->_customdata['nstudentsperteam']);
        $mform->addHelpButton('nstudentsperteam', 'nstudentsperteam', 'mod_polyteam');

        $allgroupingssoptions = ["all" => "All students"];
        foreach ($this->get_or_default('allgroupings', []) as $grouping) {
            $allgroupingssoptions[$grouping->id] = $grouping->name;
        }
        $select = $mform->addElement('select', 'grouping', 'Grouping', $allgroupingssoptions);
        $select->setSelected($this->_customdata['grouping']);
        $mform->addHelpButton('grouping', 'grouping', 'mod_polyteam');

        $submitbutton = $mform->createElement('submit', 'submitbutton', 'Generate teams');
        $mform->addGroup([$submitbutton], 'generateteams', '', array(' '), false);
        $mform->addHelpButton('generateteams', 'generateteams', 'mod_polyteam');

        $teamshavebeengenerated = $this->get_or_default('teamshavebeengenerated', false);
        if ($teamshavebeengenerated) {
            $this->_form->addElement('header', 'createteamsheader', 'Create teams');
            $this->_form->setExpanded('createteamsheader');

            // The chart get rendered in this div
            $mform->addElement('html', '<div id="polyteamgeneratedteams"></div>');

            $teamshavebeencreated = $this->get_or_default('teamshavebeencreated', false);
            if ($teamshavebeencreated) {
                $mform->addElement('html', '<div class="alert alert-success">Teams have already been created for the following configuration.</div>');
            } else {
                $submitbutton = $mform->createElement('submit', 'submitbutton', 'Create teams');
                $mform->addGroup([$submitbutton], 'createteams', '', array(' '), false);
                $mform->addHelpButton('createteams', 'createteams', 'mod_polyteam');
            }
        }
    }

    public function get_or_default($param, $default)
    {
        if (array_key_exists($param, $this->_customdata)) {
            return $this->_customdata[$param];
        }
        return $default;
    }

}

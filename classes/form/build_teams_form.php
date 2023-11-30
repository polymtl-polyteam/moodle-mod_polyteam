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
 * the mod_polyteam build team form.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_polyteam\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/../../helpers/build_constants.php');

use grouping_id;
use matching_strategy;

// TODO : Internationalization
// TODO : Refactor random matching to enum ?

/**
 * the mod_polyteam build team form class.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class build_teams_form extends \moodleform {

    /**
     * Add elements to form.
     */
    public function definition() {
        global $OUTPUT;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id', $this->_customdata['id']); // Course module id.
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'generateteamsheader', get_string('generateteams', 'mod_polyteam'));
        $mform->setExpanded('generateteamsheader');

        $buttons = array();
        $strategies = [matching_strategy::RandomMatching,
                matching_strategy::FastMatching,
                matching_strategy::SimulatedAnnealingSum,
                matching_strategy::SimulatedAnnealingSse,
                matching_strategy::SimulatedAnnealingStd];
        foreach ($strategies as $strategy) {
            $buttons[] =& $mform->createElement('radio', 'matchingstrategy', '', get_string($strategy, 'mod_polyteam'), $strategy);
        }
        $mform->addGroup($buttons, 'matchingstrategy', get_string('matchingstrategy', 'mod_polyteam'), ['<br>'], false);
        $mform->setDefault('matchingstrategy', $this->_customdata['matchingstrategy']);
        $mform->addHelpButton('matchingstrategy', 'matchingstrategy', 'mod_polyteam');

        $teamssizeoptions = array();
        for ($i = 2; $i <= 25; $i++) {
            $teamssizeoptions[$i] = $i;
        }
        $select = $mform->addElement('select', 'nstudentsperteam', get_string('teamssize', 'mod_polyteam'), $teamssizeoptions);
        $select->setSelected($this->_customdata['nstudentsperteam']);
        $mform->addHelpButton('nstudentsperteam', 'nstudentsperteam', 'mod_polyteam');

        $allgroupingssoptions = [grouping_id::All => get_string('allstudents', 'mod_polyteam')];
        foreach ($this->get_or_default('allgroupings', []) as $grouping) {
            $allgroupingssoptions[$grouping->id] = $grouping->name;
        }
        $select = $mform->addElement('select', 'grouping', 'Grouping', $allgroupingssoptions);
        $select->setSelected($this->_customdata['grouping']);
        $mform->addHelpButton('grouping', 'grouping', 'mod_polyteam');

        $submitbutton = $mform->createElement('submit', 'submitbutton', get_string('generateteams', 'mod_polyteam'));
        $mform->addGroup([$submitbutton], 'generateteams', '', array(' '), false);
        $mform->addHelpButton('generateteams', 'generateteams', 'mod_polyteam');

        $teamshavebeengenerated = $this->get_or_default('teamshavebeengenerated', false);
        if ($teamshavebeengenerated) {
            $this->_form->addElement('header', 'createteamsheader', get_string('createteams', 'mod_polyteam'));
            $this->_form->setExpanded('createteamsheader');

            //$mform->addElement('text', 'teamscognitivemodes', get_string('teamscognitivemodes', 'mod_polyteam'));
            //$mform->addHelpButton('teamscognitivemodes', 'teamscognitivemodes', 'mod_polyteam');
            $icon = $OUTPUT->help_icon('teamscognitivemodes', 'mod_polyteam');
            $mform->addElement('html', '<h5 class="text-center">' . get_string('teamscognitivemodes', 'mod_polyteam') . $icon . '</h5>');
            //$mform->addElement('static', 'teamscognitivemodes', '', get_string('teamscognitivemodes', 'mod_polyteam') . $icon);
            // The chart get rendered in this div. Do not remove nor change ID
            $mform->addElement('html', '<div id="polyteamgeneratedteams"></div>');

            $teamshavebeencreated = $this->get_or_default('teamshavebeencreated', false);
            if ($teamshavebeencreated) {
                $mform->addElement('html',
                        '<div class="alert alert-success">' . get_string('teamsalreadygenerated', 'mod_polyteam') . '</div>');
            } else {
                $submitbutton = $mform->createElement('submit', 'submitbutton', get_string('createteams', 'mod_polyteam'));
                $mform->addGroup([$submitbutton], 'createteams', '', array(' '), false);
                $mform->addHelpButton('createteams', 'createteams', 'mod_polyteam');
            }
        }
    }

    public function get_or_default($param, $default) {
        if (array_key_exists($param, $this->_customdata)) {
            return $this->_customdata[$param];
        }
        return $default;
    }

}

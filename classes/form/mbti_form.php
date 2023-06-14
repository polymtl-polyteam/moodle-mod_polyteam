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
 * the mod_polyteam MBTI questionaire form.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_polyteam\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * the mod_polyteam MBTI questionnaire form class.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mbti_form extends \moodleform {
    /**
     * Add elements to form.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'eiheader', get_string('eiheader', 'mod_polyteam'));
        $mform->setExpanded('eiheader');

        $this::mbti_question($mform, 'ei1', get_string('youaremore', 'mod_polyteam'), get_string('sociable', 'mod_polyteam'), get_string('reserved', 'mod_polyteam'));
        $this::mbti_question($mform, 'ei2', get_string('youaremore', 'mod_polyteam'), get_string('expressive', 'mod_polyteam'), get_string('contained', 'mod_polyteam'));
        $this::mbti_question($mform, 'ei3', get_string('youprefer', 'mod_polyteam'), get_string('groups', 'mod_polyteam'), get_string('individuals', 'mod_polyteam'));
        $this::mbti_question($mform, 'ei4', get_string('youlearnbetterby', 'mod_polyteam'), get_string('listening', 'mod_polyteam'), get_string('reading', 'mod_polyteam'));
        $this::mbti_question($mform, 'ei5', get_string('youaremore', 'mod_polyteam'), get_string('talkative', 'mod_polyteam'), get_string('quiet', 'mod_polyteam'));

        $mform->addElement('header', 'jpheader', get_string('jpheader', 'mod_polyteam'));
        $mform->setExpanded('jpheader');

        $this::mbti_question($mform, 'jp1', get_string('youaremore', 'mod_polyteam'), get_string('systematic', 'mod_polyteam'), get_string('casual', 'mod_polyteam'));
        $this::mbti_question($mform, 'jp2', get_string('youpreferactivities', 'mod_polyteam'), get_string('planned', 'mod_polyteam'), get_string('openended', 'mod_polyteam'));
        $this::mbti_question($mform, 'jp3', get_string('youworkbetter', 'mod_polyteam'), get_string('withoutpressure', 'mod_polyteam'), get_string('withpressure', 'mod_polyteam'));
        $this::mbti_question($mform, 'jp4', get_string('youprefer', 'mod_polyteam'), get_string('routine', 'mod_polyteam'), get_string('variety', 'mod_polyteam'));
        $this::mbti_question($mform, 'jp5', get_string('youaremore', 'mod_polyteam'), get_string('methodical', 'mod_polyteam'), get_string('improvisational', 'mod_polyteam'));

        $mform->addElement('header', 'snheader', get_string('snheader', 'mod_polyteam'));
        $mform->setExpanded('snheader');

        $this::mbti_question($mform, 'sn1', get_string('youpreferthe', 'mod_polyteam'), get_string('concrete', 'mod_polyteam'), get_string('abstract', 'mod_polyteam'));
        $this::mbti_question($mform, 'sn2', get_string('youprefer', 'mod_polyteam'), get_string('factfinding', 'mod_polyteam'), get_string('speculating', 'mod_polyteam'));
        $this::mbti_question($mform, 'sn3', get_string('youaremore', 'mod_polyteam'), get_string('practical', 'mod_polyteam'), get_string('conceptual', 'mod_polyteam'));
        $this::mbti_question($mform, 'sn4', get_string('youaremore', 'mod_polyteam'), get_string('handson', 'mod_polyteam'), get_string('theoretical', 'mod_polyteam'));
        $this::mbti_question($mform, 'sn5', get_string('youpreferthe', 'mod_polyteam'), get_string('traditional', 'mod_polyteam'), get_string('novel', 'mod_polyteam'));

        $mform->addElement('header', 'tfheader', get_string('tfheader', 'mod_polyteam'));
        $mform->setExpanded('tfheader');

        $this::mbti_question($mform, 'tf1', get_string('youprefer', 'mod_polyteam'), get_string('logic', 'mod_polyteam'), get_string('empathy', 'mod_polyteam'));
        $this::mbti_question($mform, 'tf2', get_string('youaremore', 'mod_polyteam'), get_string('truthful', 'mod_polyteam'), get_string('tactful', 'mod_polyteam'));
        $this::mbti_question($mform, 'tf3', get_string('youseeyourself', 'mod_polyteam'), get_string('questioning', 'mod_polyteam'), get_string('accomodating', 'mod_polyteam'));
        $this::mbti_question($mform, 'tf4', get_string('youaremore', 'mod_polyteam'), get_string('skeptical', 'mod_polyteam'), get_string('tolerant', 'mod_polyteam'));
        $this::mbti_question($mform, 'tf5', get_string('youthinkjudges', 'mod_polyteam'), get_string('impartial', 'mod_polyteam'), get_string('merciful', 'mod_polyteam'));

        $this->add_action_buttons();
    }

    private function mbti_question($form, $num, $intro, $opt1, $opt2) {
        $buttons = array();
        $buttons[] =& $form->createElement('advcheckbox', $num.substr($num, 0, 1), $opt1);
        $buttons[] =& $form->createElement('advcheckbox', $num.substr($num, 1, 1), $opt2);
        $form->addGroup($buttons, $num.'buttons', $intro, array(' '), false);
    }
}

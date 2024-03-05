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

        $mform->addElement('hidden', 'id', $this->_customdata['id']); // Course module id.
        $mform->setType('id', PARAM_INT);

        // The edit flag is used to display the form in read-only.
        $mform->addElement('hidden', 'edit', $this->_customdata['edit']);
        $mform->setType('edit', PARAM_INT);
        // Ugly workaround because disabledIf only test conditions on form elements.

        $mform->addElement('header', 'eiheader', get_string('eiheader', 'mod_polyteam'));
        $mform->setExpanded('eiheader');

        $this::mbti_question($mform, 'ei1', get_string('youaremore', 'mod_polyteam'),
            get_string('sociable', 'mod_polyteam'), get_string('reserved', 'mod_polyteam'));
        $mform->disabledIf('ei1buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'ei2', get_string('youaremore', 'mod_polyteam'),
            get_string('expressive', 'mod_polyteam'), get_string('contained', 'mod_polyteam'));
        $mform->disabledIf('ei2buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'ei3', get_string('youprefer', 'mod_polyteam'),
            get_string('groups', 'mod_polyteam'), get_string('individuals', 'mod_polyteam'));
        $mform->disabledIf('ei3buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'ei4', get_string('youlearnbetterby', 'mod_polyteam'),
            get_string('listening', 'mod_polyteam'), get_string('reading', 'mod_polyteam'));
        $mform->disabledIf('ei4buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'ei5', get_string('youaremore', 'mod_polyteam'),
            get_string('talkative', 'mod_polyteam'), get_string('quiet', 'mod_polyteam'));
        $mform->disabledIf('ei5buttons', 'edit', 'neq', 1);

        $mform->addElement('header', 'jpheader', get_string('jpheader', 'mod_polyteam'));
        $mform->setExpanded('jpheader');

        $this::mbti_question($mform, 'jp1', get_string('youaremore', 'mod_polyteam'),
            get_string('systematic', 'mod_polyteam'), get_string('casual', 'mod_polyteam'));
        $mform->disabledIf('jp1buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'jp2', get_string('youpreferactivities', 'mod_polyteam'),
            get_string('planned', 'mod_polyteam'), get_string('openended', 'mod_polyteam'));
        $mform->disabledIf('jp2buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'jp3', get_string('youworkbetter', 'mod_polyteam'),
            get_string('withoutpressure', 'mod_polyteam'), get_string('withpressure', 'mod_polyteam'));
        $mform->disabledIf('jp3buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'jp4', get_string('youprefer', 'mod_polyteam'),
            get_string('routine', 'mod_polyteam'), get_string('variety', 'mod_polyteam'));
        $mform->disabledIf('jp4buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'jp5', get_string('youaremore', 'mod_polyteam'),
            get_string('methodical', 'mod_polyteam'), get_string('improvisational', 'mod_polyteam'));
        $mform->disabledIf('jp5buttons', 'edit', 'neq', 1);

        $mform->addElement('header', 'snheader', get_string('snheader', 'mod_polyteam'));
        $mform->setExpanded('snheader');

        $this::mbti_question($mform, 'sn1', get_string('youpreferthe', 'mod_polyteam'),
            get_string('concrete', 'mod_polyteam'), get_string('abstract', 'mod_polyteam'));
        $mform->disabledIf('sn1buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'sn2', get_string('youprefer', 'mod_polyteam'),
            get_string('factfinding', 'mod_polyteam'), get_string('speculating', 'mod_polyteam'));
        $mform->disabledIf('sn2buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'sn3', get_string('youaremore', 'mod_polyteam'),
            get_string('practical', 'mod_polyteam'), get_string('conceptual', 'mod_polyteam'));
        $mform->disabledIf('sn3buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'sn4', get_string('youaremore', 'mod_polyteam'),
            get_string('handson', 'mod_polyteam'), get_string('theoretical', 'mod_polyteam'));
        $mform->disabledIf('sn4buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'sn5', get_string('youpreferthe', 'mod_polyteam'),
            get_string('traditional', 'mod_polyteam'), get_string('novel', 'mod_polyteam'));
        $mform->disabledIf('sn5buttons', 'edit', 'neq', 1);

        $mform->addElement('header', 'tfheader', get_string('tfheader', 'mod_polyteam'));
        $mform->setExpanded('tfheader');

        $this::mbti_question($mform, 'tf1', get_string('youprefer', 'mod_polyteam'),
            get_string('logic', 'mod_polyteam'), get_string('empathy', 'mod_polyteam'));
        $mform->disabledIf('tf1buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'tf2', get_string('youaremore', 'mod_polyteam'),
            get_string('truthful', 'mod_polyteam'), get_string('tactful', 'mod_polyteam'));
        $mform->disabledIf('tf2buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'tf3', get_string('youseeyourself', 'mod_polyteam'),
            get_string('questioning', 'mod_polyteam'), get_string('accomodating', 'mod_polyteam'));
        $mform->disabledIf('tf3buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'tf4', get_string('youaremore', 'mod_polyteam'),
            get_string('skeptical', 'mod_polyteam'), get_string('tolerant', 'mod_polyteam'));
        $mform->disabledIf('tf4buttons', 'edit', 'neq', 1);
        $this::mbti_question($mform, 'tf5', get_string('youthinkjudges', 'mod_polyteam'),
            get_string('impartial', 'mod_polyteam'), get_string('merciful', 'mod_polyteam'));
        $mform->disabledIf('tf5buttons', 'edit', 'neq', 1);

        if ($this->_customdata['prev']) {
            // Pre-filling questionnaire with previous answers.
            foreach ($this->_customdata['prev'] as $key => $val) {
                if ($key != 'id' && $key != 'moduleid' && $key != 'userid' && $key != 'timemodified') {
                    $mform->setDefault($key, $val);
                }
            }
        }

        // Action buttons are not added as regular fields in the form so we have to use this structure, rather than a disabledIf.
        if ($this->_customdata['edit'] == 1) {
            $this->add_action_buttons();
        }
    }

    /**
     * Structure for MBTI questions : one generic question followed by two opposite options.
     *
     * @param object $form the form to add the question to
     * @param string $quid question identifier
     * @param string $intro question statement
     * @param string $opt1 first answer
     * @param string $opt2 second answer
     */
    private function mbti_question($form, $quid, $intro, $opt1, $opt2) {
        $buttons = array();
        $buttons[] =& $form->createElement('advcheckbox', $quid.substr($quid, 0, 1), $opt1);
        $buttons[] =& $form->createElement('advcheckbox', $quid.substr($quid, 1, 1), $opt2);
        $form->addGroup($buttons, $quid.'buttons', $intro, array(' '), false);
    }
}

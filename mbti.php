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
 * MBTI questionnaire page.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

$cm = get_coursemodule_from_id('polyteam', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('polyteam', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

require_capability('mod/polyteam:answerquestionnaire', $modulecontext);

$PAGE->set_url('/mod/polyteam/mbti.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$mform = new \mod_polyteam\form\mbti_form(null, array('id' => $cm->id));
// Passing $cm->id as hidden params to the form to be able to run page config (lines 29-44) after submitting or canceling form.

$mainurl = new moodle_url('/mod/polyteam/view.php', array('id' => $cm->id));

if ($mform->is_cancelled()) {
    redirect($mainurl);
} else if ($d = $mform->get_data()) {
    // Tendency towards opposite types.
    $ei = $d->ei1e + $d->ei2e + $d->ei3e + $d->ei4e + $d->ei5e - ($d->ei1i + $d->ei2i + $d->ei3i + $d->ei4i + $d->ei5i);
    $jp = $d->jp1j + $d->jp2j + $d->jp3j + $d->jp4j + $d->jp5j - ($d->jp1p + $d->jp2p + $d->jp3p + $d->jp4p + $d->jp5p);
    $sn = $d->sn1s + $d->sn2s + $d->sn3s + $d->sn4s + $d->sn5s - ($d->sn1n + $d->sn2n + $d->sn3n + $d->sn4n + $d->sn5n);
    $tf = $d->tf1t + $d->tf2t + $d->tf3t + $d->tf4t + $d->tf5t - ($d->tf1f + $d->tf2f + $d->tf3f + $d->tf4f + $d->tf5f);

    $record = new stdClass;
    $record->userid = $USER->id;
    $record->moduleid = $cm->id;
    $record->timemodified = time();
    // Computations by Doug Wilde (2008).
    $record->es = $ei + $jp + 2 * $sn; // IN = - ES.
    $record->en = $ei + $jp - 2 * $sn; // IS = - EN.
    $record->et = $ei + $jp + 2 * $tf; // IF = - ET.
    $record->ef = $ei + $jp - 2 * $tf; // IT = - EF.

    $DB->insert_record('polyteam_mbti', $record);

    redirect($mainurl);
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('mbtiquest', 'mod_polyteam'));
    echo get_string('checkzerooneortwo', 'mod_polyteam');

    $mform->display();

    echo $OUTPUT->footer();
}

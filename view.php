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
 * Prints an instance of mod_polyteam.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$p = optional_param('p', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('polyteam', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('polyteam', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('polyteam', array('id' => $p), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('polyteam', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_polyteam\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('polyteam', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/polyteam/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

// Mark as viewed.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

echo $OUTPUT->header();

if ($CFG->branch < 400) {
    if (class_exists('\core_completion\cm_completion_details') && class_exists('\core\activity_dates')) {
        // Show the activity dates and completion details.
        $modinfo = get_fast_modinfo($course);
        $cminfo = $modinfo->get_cm($cm->id);
        $completiondetails = \core_completion\cm_completion_details::get_instance($cminfo, $USER->id);
        $activitydates = \core\activity_dates::get_dates_for_module($cminfo, $USER->id);
        echo $OUTPUT->activity_information($cminfo, $completiondetails, $activitydates);
    }
}

$backtocourseurl = new moodle_url('/course/view.php', array('id' => $cm->course));

$editquestionnaire = optional_param('e', 0, PARAM_INT);
// Any arbitrary data assigned to e in the URL would result a PARAM_BOOL to be true, so we use a PARAM_INT instead.

if ($moduleinstance->timeopen and time() < $moduleinstance->timeopen) { // First condition to check if timeopen != 0 (default).
    echo html_writer::tag('p', get_string('notopenyet', 'mod_polyteam', userdate($moduleinstance->timeopen)));
    echo html_writer::link(
        $backtocourseurl,
        get_string('backtocourse', 'mod_polyteam'),
        array('class' => 'btn btn-secondary')
    );
    // Replace by notice(get_string(...), $backtocourseurl) ? In notice, the redirect button has a 'Continue' label though.
} else { 
    // Date is either ok or too late.
    if ($moduleinstance->timeclose and time() > $moduleinstance->timeclose) {
        echo html_writer::tag('p', get_string('nowclosed', 'mod_polyteam', userdate($moduleinstance->timeclose)));
        echo html_writer::link(
            $backtocourseurl,
            get_string('backtocourse', 'mod_polyteam'),
            array('class' => 'btn btn-secondary')
        );
        // Forcing editquestionnaire=0 so that users cannot submit the questionnaire after expiration even with URL forgery.
        $editquestionnaire = 0;
    } else { // Date is ok.
        // Setting the interface whether the user is allowed to answer or not.
        if (has_capability('mod/polyteam:answerquestionnaire', $modulecontext)) {
            if ($editquestionnaire == 0) { 
                // Interface elements to reload the page in edit mode.

                $editurl = new moodle_url('/mod/polyteam/view.php', array('id' => $cm->id, 'e' => 1));
                if (!$answer = $DB->get_record('polyteam_mbti_ans', array('moduleid' => $cm->id, 'userid' => $USER->id))) {
                    echo html_writer::tag('p', get_string('notansweredyet', 'mod_polyteam'));
                    echo html_writer::link(
                        $editurl,
                        get_string('fillinquestionnaire', 'mod_polyteam'),
                        array('class' => 'btn btn-secondary')
                    );
                } else {
                    echo html_writer::tag('p',
                        get_string('alreadyanswered', 'mod_polyteam', userdate($answer->timemodified))
                    );
                    echo html_writer::link(
                        $editurl,
                        get_string('editanswer', 'mod_polyteam'),
                        array('class' => 'btn btn-secondary')
                    );
                }
            } else {
                // Interface in edit mode.
                echo $OUTPUT->heading(get_string('mbtiquest', 'mod_polyteam'));
                echo get_string('checkzerooneortwo', 'mod_polyteam');
            }
        } else {
            echo html_writer::tag('p', get_string('cantanswer', 'mod_polyteam'));
            echo html_writer::link(
                $backtocourseurl,
                get_string('backtocourse', 'mod_polyteam'),
                array('class' => 'btn btn-secondary')
            );
            $editquestionnaire = 0;
        }
    }

    // Displaying and processing form.

    if ($recordans = $DB->get_record('polyteam_mbti_ans', array('moduleid' => $cm->id, 'userid' => $USER->id))){
        $mform = new \mod_polyteam\form\mbti_form(null, array('id' => $cm->id, 'edit' => $editquestionnaire, 'prev' => $recordans));
    } else {
        $mform = new \mod_polyteam\form\mbti_form(null, array('id' => $cm->id, 'edit' => $editquestionnaire, 'prev' => array()));
        $recordans = new stdClass;
        $recordans->userid = $USER->id;
        $recordans->moduleid = $cm->id;
    }

    if ($mform->is_cancelled()) {
        redirect($backtocourseurl);
    } else if ($d = $mform->get_data()) {
        // Tendency towards opposite types.
        $ei = $d->ei1e + $d->ei2e + $d->ei3e + $d->ei4e + $d->ei5e - ($d->ei1i + $d->ei2i + $d->ei3i + $d->ei4i + $d->ei5i);
        $jp = $d->jp1j + $d->jp2j + $d->jp3j + $d->jp4j + $d->jp5j - ($d->jp1p + $d->jp2p + $d->jp3p + $d->jp4p + $d->jp5p);
        $sn = $d->sn1s + $d->sn2s + $d->sn3s + $d->sn4s + $d->sn5s - ($d->sn1n + $d->sn2n + $d->sn3n + $d->sn4n + $d->sn5n);
        $tf = $d->tf1t + $d->tf2t + $d->tf3t + $d->tf4t + $d->tf5t - ($d->tf1f + $d->tf2f + $d->tf3f + $d->tf4f + $d->tf5f);

        if (!$recordpers = $DB->get_record('polyteam_mbti_pers', array('moduleid' => $cm->id, 'userid' => $USER->id))) {
            $recordpers = new stdClass;
            $recordpers->userid = $USER->id;
            $recordpers->moduleid = $cm->id;
        }

        $t = time(); // To put the same time in both records.
        $recordpers->timemodified = $t;
        $recordans->timemodified = $t;

        // Dumping answers in mbti_ans.
        foreach (get_object_vars($d) as $key => $val) {
            if ($key != 'id' && $key != 'edit' && strlen($key) < 5) {
                $recordans->$key = $d->$key;
            }
        }

        if ($DB->record_exists('polyteam_mbti_ans', array('moduleid' => $cm->id, 'userid' => $USER->id))) {
            $DB->update_record('polyteam_mbti_ans', $recordans);
        } else {
            $DB->insert_record('polyteam_mbti_ans', $recordans);
        }

        // Computations by Doug Wilde (2008).
        $recordpers->es = $ei + $jp + 2 * $sn; // IN = - ES.
        $recordpers->en = $ei + $jp - 2 * $sn; // IS = - EN.
        $recordpers->et = $ei + $jp + 2 * $tf; // IF = - ET.
        $recordpers->ef = $ei + $jp - 2 * $tf; // IT = - EF.

        if ($DB->record_exists('polyteam_mbti_pers', array('moduleid' => $cm->id, 'userid' => $USER->id))) {
            $DB->update_record('polyteam_mbti_pers', $recordpers);
        } else {
            $DB->insert_record('polyteam_mbti_pers', $recordpers);
        }

        redirect($PAGE->url);
    } else {
        $mform->display();
    }
}

echo $OUTPUT->footer();

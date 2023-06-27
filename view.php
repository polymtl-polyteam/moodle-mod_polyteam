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

echo $OUTPUT->header();

if (has_capability('mod/polyteam:answerquestionnaire', $modulecontext)) {
    if (!$answer = $DB->get_record('polyteam_mbti', array('moduleid' => $cm->id, 'userid' => $USER->id))) {
        echo html_writer::tag('p', get_string('notansweredyet', 'mod_polyteam'));
        echo html_writer::link(
            new moodle_url(
                '/mod/polyteam/mbti.php',
                array('id' => $cm->id)
            ),
            get_string('fillinquestionnaire', 'mod_polyteam'),
            array('class' => 'btn btn-secondary') // To format it as a bootstrap button.
        );
    } else {
        echo html_writer::tag('p',
            get_string('alreadyanswered', 'mod_polyteam', userdate($answer->timemodified))
        );
        echo html_writer::link(
            new moodle_url(
                '/mod/polyteam/mbti.php',
                array('id' => $cm->id)
            ),
            get_string('fillinquestionnaire', 'mod_polyteam'),
            array('class' => 'btn btn-secondary') // To format it as a bootstrap button.
        );
    }
}

echo $OUTPUT->footer();

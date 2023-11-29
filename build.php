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
global $DB, $PAGE, $OUTPUT;

/**
 * Build teams based on questionnaire answers.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/helpers/build_helper_functions.php');


// Course module id.
$id = optional_param('id', 0, PARAM_INT);


$cm = get_coursemodule_from_id('polyteam', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('polyteam', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

require_capability('mod/polyteam:viewanswers', $modulecontext);

// Event ?

$PAGE->set_url('/mod/polyteam/build.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

$allmbtianswerscount = $DB->get_record_sql(
    'SELECT COUNT(*) AS rowcount FROM {polyteam_mbti} WHERE moduleid = ?',
    [$cm->id]
)->rowcount;
$nstudents = count_enrolled_users($modulecontext, 'mod/assign:submit');
$allgroupings = groups_get_all_groupings($course->id);

echo html_writer::div(
    html_writer::tag('p', 'Response rate for the questionnaire: ' . $allmbtianswerscount . ' out of ' . $nstudents),
    "text-center"
);
$mainurl = (new moodle_url('/mod/polyteam/build.php', array('id' => $cm->id)))->out();

$formcustomdatacache = $DB->get_record('polyteam_build_cache', ['moduleid' => $cm->id]);

if ($formcustomdatacache) {
    $cachedata = json_decode($formcustomdatacache->data);
    $nstudentsperteam = $cachedata->nstudentsperteam;
    $matchingstrategy = $cachedata->matchingstrategy;
    $grouping = $cachedata->grouping;
    $errorwhilegeneratingteams = $cachedata->errorwhilegeneratingteams;
    $teamshavebeengenerated = $cachedata->teamshavebeengenerated;
    $generatedteams = $cachedata->generatedteams;
    $teamshavebeencreated = $cachedata->teamshavebeencreated;
    $errorwhilecreatingteams = $cachedata->errorwhilecreatingteams;
} else {
    $nstudentsperteam = 4;
    $matchingstrategy = 'randommatching';
    $grouping = 'all';
    $errorwhilegeneratingteams = '';
    $teamshavebeengenerated = false;
    $generatedteams = json_encode([]);
    $teamshavebeencreated = false;
    $errorwhilecreatingteams = '';
}

$mform = new \mod_polyteam\form\generate_teams_form(
    $mainurl,
    array(
        'id' => $cm->id,
        'nstudentsperteam' => $nstudentsperteam,
        'matchingstrategy' => $matchingstrategy,
        'grouping' => $grouping,
        'allgroupings' => $allgroupings,
        'teamshavebeengenerated' => $teamshavebeengenerated,
        'generatedteams' => $generatedteams,
        'teamshavebeencreated' => $teamshavebeencreated
    )
);

if ($fromform = $mform->get_data()) {
    $submittedbutton = $fromform->submitbutton;
    if ($submittedbutton == 'Generate teams') {
        $nstudentsperteam = $fromform->nstudentsperteam;
        $matchingstrategy = $fromform->matchingstrategy;
        $grouping = $fromform->grouping;
        list($teamshavebeengenerated, $errorwhilegeneratingteams, $generatedteams) = generate_teams(
            $course, $cm, $nstudentsperteam, $matchingstrategy, $grouping
        );
        $teamshavebeencreated = false;
    } else if ($submittedbutton == 'Create teams') {
        list($teamshavebeencreated, $errorwhilecreatingteams) = create_teams(
            $course, $grouping, json_decode($generatedteams)
        );
    }
    $record = new stdClass;
    $record->moduleid = $cm->id;
    $record->data = json_encode([
        'nstudentsperteam' => $nstudentsperteam,
        'matchingstrategy' => $matchingstrategy,
        'grouping' => $grouping,
        'errorwhilegeneratingteams' => $errorwhilegeneratingteams,
        'teamshavebeengenerated' => $teamshavebeengenerated,
        'generatedteams' => $generatedteams,
        'teamshavebeencreated' => $teamshavebeencreated,
        'errorwhilecreatingteams' => $errorwhilecreatingteams
    ]);
    if ($formcustomdatacache) {
        $record->id = $formcustomdatacache->id;
        $DB->update_record('polyteam_build_cache', $record);
    } else {
        $DB->insert_record('polyteam_build_cache', $record);
    }
    $mform = new \mod_polyteam\form\generate_teams_form(
        $mainurl,
        array(
            'id' => $cm->id,
            'nstudentsperteam' => $nstudentsperteam,
            'matchingstrategy' => $matchingstrategy,
            'grouping' => $grouping,
            'allgroupings' => $allgroupings,
            'teamshavebeengenerated' => $teamshavebeengenerated,
            'generatedteams' => $generatedteams,
            'teamshavebeencreated' => $teamshavebeencreated
        )
    );
}

if ($errorwhilegeneratingteams != "") {
    echo html_writer::div($errorwhilegeneratingteams, 'alert alert-danger');
}

if ($errorwhilecreatingteams != "") {
    echo html_writer::div($errorwhilecreatingteams, 'alert alert-danger');
}

echo html_writer::div('', 'hidden', [
    "id" => "generatedteams",
    "data-generatedteams" => $generatedteams
]);

$mform->display();
if ($teamshavebeengenerated) {
    $PAGE->requires->js_call_amd('mod_polyteam/polyteam', 'displayTeams');
}

echo $OUTPUT->footer();

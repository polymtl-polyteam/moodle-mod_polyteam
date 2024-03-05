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
 * Build teams based on questionnaire answers.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/helpers/build_helper_functions.php');
require_once(__DIR__ . '/classes/form/build_teams_form.php');

global $DB, $PAGE, $OUTPUT;
global $ALL_COGNITIVE_MODES;

// Course module.
$cm_id = optional_param('id', 0, PARAM_INT);
$cm = get_coursemodule_from_id('polyteam', $cm_id, 0, false, MUST_EXIST);

// Course.
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$cminstance = $DB->get_record('polyteam', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

// TODO: Change capability to a more appropriate one.
require_capability('mod/polyteam:viewanswers', $modulecontext);

// TODO: Event ?
$PAGE->set_url('/mod/polyteam/build.php', array('id' => $cm->id));
$PAGE->set_title(format_string($cminstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

// Display the form response rate.
$allmbtianswerscount = $DB->count_records('polyteam_mbti_ans', ['moduleid' => $cm->id]);
$nstudents = count_enrolled_users($modulecontext, 'mod/assign:submit');
$responseratestr = get_string('mbtiresponserate', 'mod_polyteam', ['count' => $allmbtianswerscount, 'total' => $nstudents]);
echo html_writer::div(html_writer::tag('p', $responseratestr), "text-center");

// The page has two forms. A first one to generate teams and a second one to create them.
// We save form date in polyteam_build_cache table.
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

$allgroupings = groups_get_all_groupings($course->id);
$mainurl = (new moodle_url('/mod/polyteam/build.php', array('id' => $cm->id)))->out();
$mform = new \mod_polyteam\form\build_teams_form(
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
    if ($submittedbutton == get_string('generateteams', 'mod_polyteam')) {
        $nstudentsperteam = $fromform->nstudentsperteam;
        $matchingstrategy = $fromform->matchingstrategy;
        $grouping = $fromform->grouping;
        list($teamshavebeengenerated, $errorwhilegeneratingteams, $generatedteams) = generate_teams(
                $course, $cm, $nstudentsperteam, $matchingstrategy, $grouping
        );
        $teamshavebeencreated = false;
    } else if ($submittedbutton == get_string('createteams', 'mod_polyteam')) {
        list($teamshavebeencreated, $errorwhilecreatingteams) = create_teams(
                $course, $grouping, array($generatedteams)
        );
    }
    $record = new stdClass();
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
    $mform = new \mod_polyteam\form\build_teams_form(
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
    $errorstr = get_string($errorwhilegeneratingteams, 'mod_polyteam');
    echo html_writer::div($errorstr, 'alert alert-danger');
}

if ($errorwhilecreatingteams != "") {
    $errorstr = get_string($errorwhilecreatingteams, 'mod_polyteam');
    echo html_writer::div($errorstr, 'alert alert-danger');
}

// We transmit data into the DOM which is then utilized by JavaScript to generate the cognitive chart.
echo html_writer::div('', 'hidden', [
        'id' => 'generatedteams',
        'data-generatedteams' => $generatedteams,
        'data-allcognitivemodes' => json_encode($ALL_COGNITIVE_MODES),
        'data-strings' => json_encode([
                'ideal' => get_string('ideal', 'mod_polyteam'),
                'teams' => get_string('teams', 'mod_polyteam'),
                'cognitivemodesproportions' => get_string('cognitivemodesproportions', 'mod_polyteam'),
                'standarddeviation' => get_string('standarddeviation', 'mod_polyteam'),
                matching_strategy::RANDOMMATCHING => get_string(matching_strategy::RANDOMMATCHING, 'mod_polyteam'),
                matching_strategy::RANDOMMATCHINGWITHNOCOGNITIVEMODE => get_string(
                        matching_strategy::RANDOMMATCHINGWITHNOCOGNITIVEMODE, 'mod_polyteam'),
                matching_strategy::FASTMATCHING => get_string(matching_strategy::FASTMATCHING, 'mod_polyteam'),
                matching_strategy::SIMULATEDANNEALINGSUM => get_string(matching_strategy::SIMULATEDANNEALINGSUM, 'mod_polyteam'),
                matching_strategy::SIMULATEDANNEALINGSSE => get_string(matching_strategy::SIMULATEDANNEALINGSSE, 'mod_polyteam'),
                matching_strategy::SIMULATEDANNEALINGSTD => get_string(matching_strategy::SIMULATEDANNEALINGSTD, 'mod_polyteam'),
                cognitive_mode::ES => get_string(cognitive_mode::ES, 'mod_polyteam'),
                cognitive_mode::IS => get_string(cognitive_mode::IS, 'mod_polyteam'),
                cognitive_mode::EN => get_string(cognitive_mode::EN, 'mod_polyteam'),
                cognitive_mode::IN => get_string(cognitive_mode::IN, 'mod_polyteam'),
                cognitive_mode::ET => get_string(cognitive_mode::ET, 'mod_polyteam'),
                cognitive_mode::IT => get_string(cognitive_mode::IT, 'mod_polyteam'),
                cognitive_mode::EF => get_string(cognitive_mode::EF, 'mod_polyteam'),
                cognitive_mode::IF => get_string(cognitive_mode::IF, 'mod_polyteam'),
                'nocognitivemodedata' => get_string('nocognitivemodedata', 'mod_polyteam'),
        ])
]);

$mform->display();
if ($teamshavebeengenerated) {
    $PAGE->requires->js_call_amd('mod_polyteam/polyteam', 'displayTeams');
}

echo $OUTPUT->footer();

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
 * Plugin strings are defined here.
 *
 * @package     mod_polyteam
 * @category    string
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../helpers/build_constants.php');

$string['pluginname'] = 'PolyTeam';

$string['modulename'] = 'PolyTeam';
$string['modulenameplural'] = 'PolyTeam modules'; // Used in instance list view.

// Config.
$string['pluginadministration'] = 'PolyTeam administration'; // Mostly here because this string is required. Never used though.
$string['modulename_help'] =
        'PolyTeam is a tool to assign students to Moodle groups based on their answers to a personnality questionnaire such as Myers-Briggs Type Indicator.';
// TODO : expand modulename_help as we add new features.
$string['polyteamname'] = 'Name';

// Main interface.
$string['mbtiquest'] = 'MBTI questionnaire';
$string['buildteams'] = 'Build teams';
$string['viewmbtiteams'] = 'View MBTI teams';
$string['mbtiteams'] = 'MBTI teams';
$string['notopenyet'] = 'This activity is not open until {$a}.';
$string['nowclosed'] = 'This activity ended on {$a} and is no longer available.';
$string['backtocourse'] = 'Back to course page';
$string['notansweredyet'] = 'You do not have filled the questionnaire yet.';
$string['alreadyanswered'] = 'You have filled the questionnaire on {$a}.';
$string['fillinquestionnaire'] = "Fill in questionnaire";
$string['editanswer'] = 'Edit answer';

// MBTI questionnaire.
$string['checkzerooneortwo'] = 'Check zero, one or two answers for each question.';
$string['youaremore'] = 'You are more';
$string['youprefer'] = 'You prefer';
$string['youlearnbetterby'] = 'You learn better by';
$string['youpreferactivities'] = 'You prefer activities';
$string['youworkbetter'] = 'You work better';
$string['youpreferthe'] = 'You prefer the';
$string['youseeyourself'] = 'You see yourself as more';
$string['youthinkjudges'] = 'You think judges should be';
$string['eiheader'] = 'Energy Direction: Outward or Inward';
$string['sociable'] = 'sociable';
$string['reserved'] = 'reserved';
$string['expressive'] = 'expressive';
$string['contained'] = 'contained';
$string['groups'] = 'groups';
$string['individuals'] = 'individuals';
$string['listening'] = 'listening';
$string['reading'] = 'reading';
$string['talkative'] = 'talkative';
$string['quiet'] = 'quiet';
$string['jpheader'] = 'Orientation: Structured or Flexible';
$string['systematic'] = 'systematic';
$string['casual'] = 'casual';
$string['planned'] = 'planned';
$string['openended'] = 'open-ended';
$string['withpressure'] = 'with pressure';
$string['withoutpressure'] = 'without pressure';
$string['routine'] = 'routine';
$string['variety'] = 'variety';
$string['methodical'] = 'methodical';
$string['improvisational'] = 'improvisational';
$string['snheader'] = 'Information Collection: Facts or Possibilities';
$string['abstract'] = 'abstract';
$string['concrete'] = 'concrete';
$string['factfinding'] = 'fact-finding';
$string['speculating'] = 'speculating';
$string['practical'] = 'practical';
$string['conceptual'] = 'conceptual';
$string['handson'] = 'hands-on';
$string['theoretical'] = 'theoretical';
$string['traditional'] = 'traditional';
$string['novel'] = 'novel';
$string['tfheader'] = 'Decision-Making Process: Objects or People';
$string['logic'] = 'logic';
$string['empathy'] = 'empathy';
$string['truthful'] = 'truthful';
$string['tactful'] = 'tactful';
$string['questioning'] = 'questioning';
$string['accomodating'] = 'accomodating';
$string['skeptical'] = 'skeptical';
$string['tolerant'] = 'tolerant';
$string['impartial'] = 'impartial';
$string['merciful'] = 'merciful';

// Capabilities.

// Build page
$string['mbtiresponserate'] = 'Response rate for the MBTI questionnaire: {$a->count} out of {$a->total}';
$string['generateteams'] = 'Generate teams';
$string['createteams'] = 'Create teams';
$string['errornotenoughstudents'] = 'Not enough students to generate teams';
$string['errorunknownalgo'] = 'Unknown matching algorithm';
$string['errorunabletocreategroup'] = 'Enable to create one or more group';
$string['errorenabletoaddstudenttogroup'] = 'Enable to add one or more student to a group';
$string['errorenableassigngrouping'] = 'Enable to assign one or more grouping to a group';
$string[matching_strategy::RandomMatching] = 'Random match';
$string[matching_strategy::RandomMatchingWithNoCognitiveMode] = 'Random match without cognitive modes';
$string[matching_strategy::FastMatching] = 'Fast matching';
$string[matching_strategy::SimulatedAnnealingSum] = 'Maximize the number of perfect teams';
$string[matching_strategy::SimulatedAnnealingSse] = 'Minimize area under cognitive curve';
$string[matching_strategy::SimulatedAnnealingStd] = 'Minimize cognitive differences between teams';
$string['teamssize'] = 'Teams size';
$string['allstudents'] = 'All students';
$string['teamsalreadygenerated'] = 'Teams have already been created for the following configuration.';
$string['ideal'] = 'Ideal';
$string['teams'] = 'Teams';
$string['cognitivemodesproportions'] = 'Cognitive mode proportions (%)';
$string['standarddeviation'] = 'Standard deviation (markers)';
$string[cognitive_mode::ES] = "ES";
$string[cognitive_mode::IS] = "IS";
$string[cognitive_mode::EN] = "EN";
$string[cognitive_mode::IN] = "IN";
$string[cognitive_mode::ET] = "ET";
$string[cognitive_mode::IT] = "IT";
$string[cognitive_mode::EF] = "EF";
$string[cognitive_mode::IF] = "IF";
$string[cognitive_mode::IS . 'def'] = "Experimentation";
$string[cognitive_mode::EN . 'def'] = "Ideation";
$string[cognitive_mode::IN . 'def'] = "Knowledge";
$string[cognitive_mode::ET . 'def'] = "Imagination";
$string[cognitive_mode::IT . 'def'] = "Organization";
$string[cognitive_mode::EF . 'def'] = "Community";
$string[cognitive_mode::IF . 'def'] = "Analysis";
$string['nocognitivemodedata'] = 'No cognitive modes data';
$string['matchingstrategy'] = 'Matching strategy';
$string['matchingstrategy_help'] =
        'The ***Matching strategy*** refers to the algorithm utilized for team composition. The ***Random match*** approach pairs students arbitrarily, disregarding their cognitive profiles. The ***Fast matching*** algorithm is a straightforward and swift method that sequentially places students in a team where their inclusion would minimize the team’s cognitive variance. The ***Maximize the number of perfect teams*** strategy employs a heuristic algorithm geared towards creating the maximum number of ‘perfect’ teams, defined as teams with a cognitive variance of zero. The ***Minimize area under cognitive curve*** strategy uses a heuristic algorithm designed to reduce the area under all teams’ cognitive variance trends as much as possible. Lastly, the ***Minimize cognitive differences between teams*** strategy uses a heuristic algorithm aimed at equalizing the cognitive variance among all teams.';
$string['nstudentsperteam'] = 'Teams size';
$string['nstudentsperteam_help'] =
        'This is the maximum number of students per team. All teams will adhere to this size, with potentially two exceptions. There may be one smaller team composed of students who responded, and another smaller one made up of those who did not respond.';
$string['grouping'] = 'Grouping';
$string['grouping_help'] =
        'The Moodle grouping for which you wish to generate teams should be selected. Created teams will be labelled as MBTI_&lt;grouping_name&gt;_&lt;team_number&gt;. If you wish to create teams for all groupings, please select the <b><i>All students</i></b> option.';
$string['generateteams'] = 'Generate teams';
$string['generateteams_help'] =
        'The ***Generate teams*** action button will formulate prospective teams based on the selected matching strategy, designated team size, and defined grouping. This action takes into account the responses from students who have completed the MBTI form and matches them together using the selected strategy. Students who have not replied to the MBTI form, however, will be matched randomly. Each time you use this action, the suggested teams can change, providing you with various team configurations. This action should be used as many times as needed until a suitable configuration is achieved. Please note that these are only potential team compositions and they are not created within Moodle at this stage. The proposed teams will be displayed below after each generation. Once you have a satisfactory team configuration, you can officially create and establish these generated teams within Moodle using the ***Create teams*** action button.';
$string['createteams'] = 'Create teams';
$string['createteams_help'] =
        'The <b><i>Create teams</b></i> action button facilitates the creation of the suggested teams within Moodle. When this action is initiated, any existing teams associated with the selected grouping will be removed. They will then be superseded by the new teams generated from the most recent configuration. <u><b>Please note that this action finalizes the team setup, replacing old teams with the newly proposed ones</b></u>.';
$string['teamscognitivemodes'] = 'Teams cognitive modes';
$string['teamscognitivemodes_help'] = 'The goal of this chart is to show generated teams cognitive modes. Ideal teams have a cognitive variance of 0. A team with a cognitive variance of 0 is a team which have the same number of student for each cognitive mode. Cognitive modes are ES (Experimentation), IS (Ideation), EN (Knowledge), IN (Imagination), ET (Organization), IT (Community), EF (Analysis) and IF (Evaluation)';

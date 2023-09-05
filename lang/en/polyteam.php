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

$string['pluginname'] = 'PolyTeam';

$string['modulename'] = 'PolyTeam';
$string['modulenameplural'] = 'PolyTeam modules'; // Used in instance list view.

// Config.
$string['pluginadministration'] = 'PolyTeam administration'; // Mostly here because this string is required. Never used though.
$string['modulename_help'] = 'PolyTeam is a tool to assign students to Moodle groups based on their answers to a personnality questionnaire such as Myers-Briggs Type Indicator.';
// TODO : expand modulename_help as we add new features.
$string['polyteamname'] = 'Name';

// Main interface.
$string['mbtiquest'] = 'MBTI questionnaire';
$string['buildteams'] = 'Build teams';
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

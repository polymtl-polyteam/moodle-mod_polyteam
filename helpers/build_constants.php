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

// TODO : Change descriptions
/**
 * Library of interface functions and constants.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class matching_strategy
{
    const Unknown = 'unknown';
    const RandomMatching = 'randommatching';
    const RandomMatchingWithNoCognitiveMode = 'randommatchingwithnocognitivemode';
    const FastMatching = 'fastmatching';
    const SimulatedAnnealingSum = 'simulatedannealingsum';
    const SimulatedAnnealingSse = 'simulatedannealingsse';
    const SimulatedAnnealingStd = 'simulatedannealingstd';
}

class grouping_id {
    const All = 'all';
}

class cognitive_mode {
    const ES = "cognitivemodees"; # Experimentation
    const IS = "cognitivemodeis"; # Ideation
    const EN = "cognitivemodeen"; # Knowledge
    const IN = "cognitivemodein"; # Imagination
    const ET = "cognitivemodeet"; # Organization
    const IT = "cognitivemodeit"; # Community
    const EF = "cognitivemodeef"; # Analysis
    const IF = "cognitivemodeif"; # Evaluation
}

$ALL_COGNITIVE_MODES = [
        cognitive_mode::ES,
        cognitive_mode::IS,
        cognitive_mode::EN,
        cognitive_mode::IN,
        cognitive_mode::ET,
        cognitive_mode::IT,
        cognitive_mode::EF,
        cognitive_mode::IF
];

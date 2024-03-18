<?php
global $ALL_COGNITIVE_MODES;
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SpellCheckingInspection */
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
 * View MBTI teams
 * Inpired from group/overview.php (Moodle Groups Overview View)
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/helpers/build_helper_functions.php');
require_once(__DIR__ . '/classes/form/build_teams_form.php');

global $DB, $CFG, $PAGE, $OUTPUT;
define('OVERVIEW_NO_GROUP', -1); // The fake group for users not in a group.
define('OVERVIEW_GROUPING_GROUP_NO_GROUPING', -1); // The fake grouping for groups that have no grouping.
define('OVERVIEW_GROUPING_NO_GROUP', -2); // The fake grouping for users with no group.

// Course module
$coursemoduleid = optional_param('id', 0, PARAM_INT);
$coursemodule = get_coursemodule_from_id('polyteam', $coursemoduleid, 0, false, MUST_EXIST);
$courseid = $coursemodule->course;
$course = $DB->get_record('course', array('id' => $courseid));

require_login($courseid, true, $coursemodule);
$modulecontext = context_module::instance($coursemoduleid);
$coursecontext = context_course::instance($courseid);
// TODO: Change capability to a more appropriate one
require_capability('mod/polyteam:viewanswers', $modulecontext);
require_capability('moodle/course:managegroups', $modulecontext);

$groupid = optional_param('group', 0, PARAM_INT);
$groupingid = optional_param('grouping', 0, PARAM_INT);
$dataformat = optional_param('dataformat', '', PARAM_ALPHA);

$rooturl = $CFG->wwwroot . '/mod/polyteam/teams.php?id=' . $coursemoduleid;

// TODO: Event ?
$PAGE->set_url('/mod/polyteam/teams.php', array(
        'id' => $coursemoduleid,
        'group' => $groupid,
        'grouping' => $groupingid
));
$PAGE->set_title(format_string($coursemodule->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$strgroups = get_string('groups');
$strparticipants = get_string('participants');
$stroverview = get_string('overview', 'group');
$strgrouping = get_string('grouping', 'group');
$strgroup = get_string('group', 'group');
$strnotingrouping = get_string('notingrouping', 'group');
$strfiltergroups = get_string('filtergroups', 'group');
$strnogroups = get_string('nogroups', 'group');
$strdescription = get_string('description');
$strnotingroup = get_string('notingrouplist', 'group');
$strnogroup = get_string('nogroup', 'group');
$strnogrouping = get_string('nogrouping', 'group');
$strmbtiteams = get_string('mbtiteams', 'mod_polyteam');

// This can show all users and all groups in a course.
// This is lots of data so allow this script more resources.
raise_memory_limit(MEMORY_EXTRA);

// Get all groupings and sort them by formatted name.
$groupings = $DB->get_records('groupings', array('courseid' => $courseid), 'name');
foreach ($groupings as $gid => $grouping) {
    $groupings[$gid]->formattedname = format_string($grouping->name, true, array('context' => $coursecontext));
}
core_collator::asort_objects_by_property($groupings, 'formattedname');
$members = array();
foreach ($groupings as $grouping) {
    $members[$grouping->id] = array();
}
// Groups not in a grouping.
$members[OVERVIEW_GROUPING_GROUP_NO_GROUPING] = array();

// Get all MBTI groups
$sql = "SELECT * FROM {groups} WHERE courseid = {$courseid} AND name LIKE 'MBTI_%' ORDER BY name";
$groups = $DB->get_records_sql($sql);

$params = array('courseid' => $courseid);
if ($groupid) {
    $groupwhere = "AND g.id = :groupid";
    $params['groupid'] = $groupid;
} else {
    $groupwhere = "";
}

if ($groupingid) {
    if ($groupingid < 0) { // No grouping filter.
        $groupingwhere = "AND gg.groupingid IS NULL";
    } else {
        $groupingwhere = "AND gg.groupingid = :groupingid";
        $params['groupingid'] = $groupingid;
    }
} else {
    $groupingwhere = "";
}

list($sort, $sortparams) = users_order_by_sql('u');

$userfieldsapi = \core_user\fields::for_identity($coursecontext)->with_userpic();
[
        'selects' => $userfieldsselects,
        'joins' => $userfieldsjoin,
        'params' => $userfieldsparams
] = (array) $userfieldsapi->get_sql('u', true);
$extrafields = $userfieldsapi->get_required_fields([\core_user\fields::PURPOSE_IDENTITY]);
$allnames = 'u.id ' . $userfieldsselects;

//$sql = "SELECT g.id AS groupid, gg.groupingid, u.id AS userid, $allnames, u.idnumber, u.username
//          FROM {groups} g
//               LEFT JOIN {groupings_groups} gg ON g.id = gg.groupid
//               LEFT JOIN {groups_members} gm ON g.id = gm.groupid
//               LEFT JOIN {user} u ON gm.userid = u.id
//               $userfieldsjoin
//         WHERE
//               g.courseid = :courseid $groupwhere $groupingwhere
//      ORDER BY g.name, $sort";

$sql = "SELECT g.id AS groupid, gg.groupingid, u.id AS userid, $allnames, u.idnumber, u.username
          FROM {groups} g
               LEFT JOIN {groupings_groups} gg ON g.id = gg.groupid
               LEFT JOIN {groups_members} gm ON g.id = gm.groupid
               LEFT JOIN {user} u ON gm.userid = u.id
               $userfieldsjoin
         WHERE
               g.courseid = :courseid $groupwhere $groupingwhere AND
               g.name LIKE 'MBTI_%'
      ORDER BY g.name, $sort";

$rs = $DB->get_recordset_sql($sql, array_merge($params, $sortparams, $userfieldsparams));
foreach ($rs as $row) {
    $user = username_load_fields_from_object((object) [], $row, null,
            array_merge(['id' => 'userid', 'username', 'idnumber'], $extrafields));

    if (!$row->groupingid) {
        $row->groupingid = OVERVIEW_GROUPING_GROUP_NO_GROUPING;
    }
    if (!array_key_exists($row->groupid, $members[$row->groupingid])) {
        $members[$row->groupingid][$row->groupid] = array();
    }
    if (!empty($user->id)) {
        $members[$row->groupingid][$row->groupid][] = $user;
    }
}
$rs->close();

// Add 'no groupings' / 'no groups' selectors.
$groupings[OVERVIEW_GROUPING_GROUP_NO_GROUPING] = (object) array(
        'id' => OVERVIEW_GROUPING_GROUP_NO_GROUPING,
        'formattedname' => $strnogrouping,
);
$groups[OVERVIEW_NO_GROUP] = (object) array(
        'id' => OVERVIEW_NO_GROUP,
        'courseid' => $courseid,
        'idnumber' => '',
        'name' => $strnogroup,
        'description' => '',
        'descriptionformat' => FORMAT_HTML,
        'enrolmentkey' => '',
        'picture' => 0,
        'timecreated' => 0,
        'timemodified' => 0,
);

// Add users who are not in a group.
if ($groupid <= 0 && $groupingid <= 0) {
    list($esql, $params) = get_enrolled_sql($coursecontext, null, 0, true);
    $sql = "SELECT u.id, $allnames, u.idnumber, u.username
              FROM {user} u
              JOIN ($esql) e ON e.id = u.id
              LEFT JOIN (
                  SELECT gm.userid, g.name
                    FROM {groups_members} gm
                    JOIN {groups} g ON g.id = gm.groupid
                   WHERE g.courseid = :courseid
                   ) grouped ON grouped.userid = u.id
                  $userfieldsjoin
             WHERE grouped.name NOT LIKE 'MBTI_%'
             ORDER BY $sort";
    $params['courseid'] = $courseid;

    $nogroupusers = $DB->get_records_sql($sql, array_merge($params, $userfieldsparams));

    if ($nogroupusers) {
        $members[OVERVIEW_GROUPING_NO_GROUP][OVERVIEW_NO_GROUP] = $nogroupusers;
    }
}

// Export groups if requested.
if ($dataformat !== '') {
    $columnnames = array(
            'grouping' => $strgrouping,
            'group' => $strgroup,
            'firstname' => get_string('firstname'),
            'lastname' => get_string('lastname'),
    );
    $extrafields = \core_user\fields::get_identity_fields($coursecontext, false);
    foreach ($extrafields as $field) {
        $columnnames[$field] = \core_user\fields::get_display_name($field);
    }
    $alldata = array();
    // Generate file name.
    $shortname = format_string($course->shortname, true, array('context' => $coursecontext)) . "_groups";
    $i = 0;
    foreach ($members as $gpgid => $groupdata) {
        if ($groupingid and $groupingid != $gpgid) {
            if ($groupingid > 0 || $gpgid > 0) {
                // Still show 'not in group' when 'no grouping' selected.
                continue; // Do not export.
            }
        }
        if ($gpgid < 0) {
            // Display 'not in group' for grouping id == OVERVIEW_GROUPING_NO_GROUP.
            if ($gpgid == OVERVIEW_GROUPING_NO_GROUP) {
                $groupingname = $strnotingroup;
            } else {
                $groupingname = $strnotingrouping;
            }
        } else {
            $groupingname = $groupings[$gpgid]->formattedname;
        }
        if (empty($groupdata)) {
            $alldata[$i] = array_fill_keys(array_keys($columnnames), '');
            $alldata[$i]['grouping'] = $groupingname;
            $i++;
        }
        foreach ($groupdata as $gpid => $users) {
            if ($groupid and $groupid != $gpid) {
                continue;
            }
            if (empty($users)) {
                $alldata[$i] = array_fill_keys(array_keys($columnnames), '');
                $alldata[$i]['grouping'] = $groupingname;
                $alldata[$i]['group'] = $groups[$gpid]->name;
                $i++;
            }
            foreach ($users as $option => $user) {
                $alldata[$i]['grouping'] = $groupingname;
                $alldata[$i]['group'] = $groups[$gpid]->name;
                $alldata[$i]['firstname'] = $user->firstname;
                $alldata[$i]['lastname'] = $user->lastname;
                foreach ($extrafields as $field) {
                    $alldata[$i][$field] = $user->$field;
                }
                $i++;
            }
        }
    }

    \core\dataformat::download_data(
            $shortname,
            $dataformat,
            $columnnames,
            $alldata,
            function($record, $supportshtml) use ($extrafields) {
                if ($supportshtml) {
                    foreach ($extrafields as $extrafield) {
                        $record[$extrafield] = s($record[$extrafield]);
                    }
                }
                return $record;
            });
    die;
}

// Main page content.
//navigation_node::override_active_url(new moodle_url('/group/index.php', array('id' => $courseid)));
//$PAGE->navbar->add(get_string('overview', 'group'));

/// Print header
$PAGE->set_title($strmbtiteams);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($strmbtiteams, true, array('context' => $coursecontext)), 3);

echo $strfiltergroups;

$options = array();
$options[0] = get_string('all');
foreach ($groupings as $grouping) {
    $options[$grouping->id] = strip_tags($grouping->formattedname);
}
$popupurl = new moodle_url($rooturl . '&group=' . $groupid);
$select = new single_select($popupurl, 'grouping', $options, $groupingid, array());
$select->label = $strgrouping;
$select->formid = 'selectgrouping';
echo ' ' . $OUTPUT->render($select);

$options = array();
$options[0] = get_string('all');
foreach ($groups as $group) {
    $options[$group->id] = strip_tags(format_string($group->name));
}
$popupurl = new moodle_url($rooturl . '&grouping=' . $groupingid);
$select = new single_select($popupurl, 'group', $options, $groupid, array());
$select->label = $strgroup;
$select->formid = 'selectgroup';
echo ' ' . $OUTPUT->render($select);

/// Print table
$printed = false;
foreach ($members as $gpgid => $groupdata) {
    if ($groupingid and $groupingid != $gpgid) {
        if ($groupingid > 0 || $gpgid > 0) { // Still show 'not in group' when 'no grouping' selected.
            continue; // Do not show.
        }
    }
    $table = new html_table();
    $table->head = array(
            'Group (Size)',
        // TODO : Internationalisation
            'Group members',
            'Cognitive modes',
            'Cognitive modes proportions',
    );
    //$table->size = array('20%', '70%', '10%');
    $table->align = array('left', 'left', 'left', 'left');
    $table->colclasses = array('align-middle', 'align-middle', 'align-middle', 'align-middle');
    //$table->width = '90%';
    $table->data = array();
    foreach ($groupdata as $gpid => $users) {
        if ($groupid and $groupid != $gpid) {
            continue;
        }
        $line = array();
        $name = print_group_picture($groups[$gpid], $course->id, false, true, false) . format_string($groups[$gpid]->name);
        $description = file_rewrite_pluginfile_urls($groups[$gpid]->description, 'pluginfile.php', $coursecontext->id, 'group',
                'description', $gpid);
        $options = new stdClass;
        $options->noclean = true;
        $options->overflowdiv = true;
        $viewfullnames = has_capability('moodle/site:viewfullnames', $coursecontext);

        $nusers = count($users);
        foreach (array_values($users) as $i => $user) {
            $displayname = fullname($user, $viewfullnames);
            if ($extrafields) {
                $extrafieldsdisplay = [];
                foreach ($extrafields as $field) {
                    $extrafieldsdisplay[] = s($user->{$field});
                }
                $displayname .= ' (' . implode(', ', $extrafieldsdisplay) . ')';
            }

            $fullname = html_writer::link(new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $course->id]),
                    $displayname);

            $row = new html_table_row();
            if ($i === 0) {
                $cell = new html_table_cell($name . " ({$nusers})");
                $cell->rowspan = $nusers;
                $row->cells[] = $cell;
                $row->cells[] = $fullname;
                $row->cells[] = "ES, IS";
                $cell = new html_table_cell("TODO");
                $cell->rowspan = $nusers;
                $row->cells[] = $cell;
            } else {
                $row->cells[] = $fullname;
                $row->cells[] = "ES, IS";
            }
            $table->data[] = $row;
        }
    }
    if ($groupid and empty($table->data)) {
        continue;
    }
    if ($gpgid < 0) {
        // Display 'not in group' for grouping id == OVERVIEW_GROUPING_NO_GROUP.
        if ($gpgid == OVERVIEW_GROUPING_NO_GROUP) {
            echo $OUTPUT->heading($strnotingroup, 4);
        } else {
            echo $OUTPUT->heading($strnotingrouping, 4);
        }
    } else {
        echo $OUTPUT->heading($groupings[$gpgid]->formattedname, 4);
        $description =
                file_rewrite_pluginfile_urls($groupings[$gpgid]->description, 'pluginfile.php', $coursecontext->id, 'grouping',
                        'description', $gpgid);
        $options = new stdClass;
        $options->overflowdiv = true;
        echo $OUTPUT->box(format_text($description, $groupings[$gpgid]->descriptionformat, $options),
                'generalbox boxwidthnarrow boxaligncenter');
    }
    echo html_writer::table($table);
    $printed = true;
}

// Add buttons for exporting groups/groupings.
echo $OUTPUT->download_dataformat_selector(get_string('exportgroupsgroupings', 'group'), 'teams.php', 'dataformat', [
        'id' => $coursemoduleid,
        'group' => $groupid,
        'grouping' => $groupingid,
]);

echo $OUTPUT->footer();

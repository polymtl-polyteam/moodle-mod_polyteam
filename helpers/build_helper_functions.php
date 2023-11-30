<?php /** @noinspection SpellCheckingInspection */
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
 * Interface functions used by the build page.
 *
 * @package     mod_polyteam
 * @copyright   2023 GIGL <...@polymtl.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/build_constants.php');

function get_new_cognitive_mode_counter(): array {
    global $ALL_COGNITIVE_MODES;
    return array_fill_keys($ALL_COGNITIVE_MODES, 0);
}

function standard_deviation(array $data): float {
    $n = count($data);

    if ($n <= 1) {
        return 0; // Standard deviation is undefined for a single value or an empty array
    }

    $mean = array_sum($data) / $n;
    $squareddeviations = array_map(function($x) use ($mean) {
        return pow($x - $mean, 2);
    }, $data);

    $variance = array_sum($squareddeviations) / ($n - 1);
    return sqrt($variance);
}

function get_variance_from_cognitive_modes_counter(array $counter): float {
    global $ALL_COGNITIVE_MODES;
    $countertotal = array_sum($counter);
    if ($countertotal == 0) {
        return 0;
    }
    $countervaluefactor = 100.0 / $countertotal;

    $modevalues = array_map(function(string $mode) use ($counter, $countervaluefactor) {
        return $counter[$mode] * $countervaluefactor;
    }, $ALL_COGNITIVE_MODES);

    return standard_deviation($modevalues);
}

class cognitive_mode_metrics {
    private int $es;
    private int $en;
    private int $is;
    private int $in;
    private int $et;
    private int $ef;
    private int $it;
    private int $if;

    public function __construct($es, $en, $is, $in, $et, $ef, $it, $if) {
        $this->es = $es;
        $this->en = $en;
        $this->is = $is;
        $this->in = $in;
        $this->et = $et;
        $this->ef = $ef;
        $this->it = $it;
        $this->if = $if;
    }

    public function get_modes(): array {
        $map = [
                cognitive_mode::ES => $this->es,
                cognitive_mode::IS => $this->is,
                cognitive_mode::EN => $this->en,
                cognitive_mode::IN => $this->in,
                cognitive_mode::ET => $this->et,
                cognitive_mode::IT => $this->it,
                cognitive_mode::EF => $this->ef,
                cognitive_mode::IF => $this->if,
        ];
        $modes = [];
        foreach ($map as $cognitivemode => $value) {
            if ($value > 0) {
                $modes[] = $cognitivemode;
            }
        }
        return $modes;
    }

}

class form_metrics {
    private int $ei;
    private int $jp;
    private int $sn;
    private int $tf;

    /**
     * @param int $ei Extrovert/Introvert - Energy Direction: Outward or Inward
     * @param int $jp Structured(Judgement)/Flexible (Perception) - Orientation: Flexible or Structured
     * @param int $sn Sensing/Intuition - Information Collection Process: Facts or Possibilities
     * @param int $tf Thinking/Feeling - Decision-Making Process: Objects or People
     */
    public function __construct(int $ei, int $jp, int $sn, int $tf) {
        $this->ei = $ei;
        $this->jp = $jp;
        $this->sn = $sn;
        $this->tf = $tf;
    }

    public function get_cognitive_mode_metrics(): cognitive_mode_metrics {
        $es = $this->ei - $this->jp + 2 * $this->sn;
        $en = $this->ei - $this->jp - 2 * $this->sn;
        $et = $this->ei + $this->jp + 2 * $this->tf;
        $ef = $this->ei + $this->jp - 2 * $this->tf;

        return new cognitive_mode_metrics($es, $en, -$en, -$es, $et, $ef, -$ef, -$et);
    }
}

class student implements JsonSerializable {
    // TODO: Only save required attributes from the user
    private object $user;
    private array $modes;

    public function __construct(object $user, form_metrics $formmetrics) {
        $this->user = $user;
        $this->modes = [];
        $cognitivemodemetrics = $formmetrics->get_cognitive_mode_metrics();
        foreach ($cognitivemodemetrics->get_modes() as $mode) {
            $this->modes[] = $mode;
        }
    }

    public function get_user(): object {
        return $this->user;
    }

    public function get_cognitive_modes_names(): array {
        return $this->modes;
    }

    public function has_cognitive_mode(string $modename): bool {
        return in_array($modename, $this->modes);
    }

    public function jsonSerialize(): array {
        return [
                "user" => $this->user,
                "modes" => $this->modes
        ];
    }
}

class team implements JsonSerializable {

    private array $students = [];

    private array $cognitivemodescounter = [];
    private float $cognitivevariance;

    private string $matchingstrategy;

    public function __construct($students = [], $matching_strategy = matching_strategy::Unknown) {
        $this->students = $students;
        $this->cognitivemodescounter = get_new_cognitive_mode_counter();
        foreach ($this->students as $student) {
            foreach ($student->get_cognitive_modes_names() as $mode) {
                $this->cognitivemodescounter[$mode]++;
            }
        }
        $this->cognitivevariance = get_variance_from_cognitive_modes_counter($this->cognitivemodescounter);

        $this->matchingstrategy = $matching_strategy;
    }

    public function add_student(student $student): void {
        $this->students[] = $student;
        foreach ($student->get_cognitive_modes_names() as $mode) {
            $this->cognitivemodescounter[$mode]++;
        }
        $this->cognitivevariance = get_variance_from_cognitive_modes_counter($this->cognitivemodescounter);
    }

    public function get_all_students(): array {
        return $this->students;
    }

    public function get_cognitive_modes_counter(): array {
        return $this->cognitivemodescounter;
    }

    public function get_cognitive_variance(): float {
        return $this->cognitivevariance;
    }

    public function get_n_students(): float {
        return count($this->students);
    }

    public function set_matching_strategy(string $matchingstrategy) {
        $this->matchingstrategy = $matchingstrategy;
    }

    public function jsonSerialize(): array {
        return [
                "students" => $this->students,
                "cognitive_modes_counter" => $this->cognitivemodescounter,
                "cognitive_variance" => $this->cognitivevariance,
                "matching_strategy" => $this->matchingstrategy
        ];
    }
}

function get_random_float(): float {
    return rand() / getrandmax();
}

function generate_one_fake_form_metrics(
        float $extroversionratio,
        float $judgingratio,
        float $sensingratio,
        float $thinkingratio): form_metrics {
    $ei = rand(0, 5);
    $jp = rand(0, 5);
    $sn = rand(0, 5);
    $tf = rand(0, 5);
    if (get_random_float() > $extroversionratio) {
        $ei = -$ei;
    }
    if (get_random_float() > $judgingratio) {
        $jp = -$jp;
    }
    if (get_random_float() > $sensingratio) {
        $sn = -$sn;
    }
    if (get_random_float() > $thinkingratio) {
        $tf = -$tf;
    }
    return new form_metrics($ei, $jp, $sn, $tf);
}

function generate_random_teams(array $students, int $team_size): array {
    $studentsshuffled = array_slice($students, 0);
    shuffle($studentsshuffled);

    $numteams = ceil(count($studentsshuffled) / $team_size);
    $generatedteams = [];

    for ($i = 0; $i < $numteams; $i++) {
        $team_students = array_slice($studentsshuffled, $i * $team_size, $team_size);
        $generatedteams[] = new team($team_students);
    }

    return $generatedteams;
}

function remove_from_array(array $arr, array $toremove): array {
    $result = [];
    foreach ($arr as $item) {
        if (!in_array($item, $toremove)) {
            $result[] = $item;
        }
    }
    return $result;
}

function generate_greedily_teams(array $students, int $teamsize): array {
    $nonassignedstudents = array_slice($students, 0);
    $numteams = ceil(count($nonassignedstudents) / $teamsize);
    $generatedteams = [];
    for ($i = 0; $i < $numteams; $i++) {
        $generatedteams[] = new team();
    }

    $currentteamindex = 0;
    while (!empty($nonassignedstudents)) {
        $currentteam = $generatedteams[$currentteamindex];
        $bestcandidate = $nonassignedstudents[0];
        $bestvariance = (new team(array_merge([$bestcandidate], $currentteam->get_all_students())))
                ->get_cognitive_variance();

        for ($i = 1; $i < count($nonassignedstudents); $i++) {
            $candidate = $nonassignedstudents[$i];
            $variance = (new team(array_merge([$candidate], $currentteam->get_all_students())))
                    ->get_cognitive_variance();
            if ($variance < $bestvariance) {
                $bestcandidate = $candidate;
                $bestvariance = $variance;
            }
        }

        $nonassignedstudents = remove_from_array($nonassignedstudents, [$bestcandidate]);
        $currentteam->add_student($bestcandidate);

        $currentteamindex = ($currentteamindex + 1) % $numteams;
    }

    return $generatedteams;
}

function sum_cost($teams): float {
    return array_sum(array_map(
            function($team) {
                return $team->get_cognitive_variance();
            },
            $teams
    ));
}

function sse_cost($teams): float {
    return array_sum(array_map(
            function($team) {
                return pow($team->get_cognitive_variance(), 2);
            },
            $teams
    ));
}

function std_cost(array $teams): float {
    return standard_deviation(array_map(
            function($team) {
                return $team->get_cognitive_variance();
            },
            $teams
    ));
}

function get_neighbor_solution(array $teams, callable $costfn): array {
    $bestneighbor = null;
    $bestcost = INF;

    $tworandomkeys = array_rand($teams, 2);
    $t1 = $teams[$tworandomkeys[0]];
    $t2 = $teams[$tworandomkeys[1]];
    $t1students = $t1->get_all_students();
    $t2students = $t2->get_all_students();
    $allstudentsswaps = [];

    foreach ($t1students as $s1) {
        foreach ($t2students as $s2) {
            $allstudentsswaps[] = [$s1, $s2];
        }
    }

    $allteamsexceptpicked = remove_from_array($teams, [$t1, $t2]);

    foreach ($allstudentsswaps as list($s1, $s2)) {
        $t1studentsnew = array_merge(remove_from_array($t1students, [$s1]), [$s2]);
        $t2studentsnew = array_merge(remove_from_array($t2students, [$s2]), [$s1]);
        $neighbor = array_merge(
                $allteamsexceptpicked,
                [new team($t1studentsnew), new team($t2studentsnew)]
        );
        $cost = $costfn($neighbor);
        if ($cost < $bestcost) {
            $bestneighbor = $neighbor;
            $bestcost = $cost;
        }
    }

    if (count($t1students) !== count($t2students)) {
        if (count($t1students) < count($t2students)) {
            list($t1students, $t2students) = [$t2students, $t1students];
        }
        foreach ($t1students as $s1) {
            $t1studentsnew = remove_from_array($t1students, [$s1]);
            $t2studentsnew = array_merge($t2students, [$s1]);
            $neighbor = array_merge(
                    $allteamsexceptpicked,
                    [new team($t1studentsnew), new team($t2studentsnew)]
            );
            $cost = $costfn($neighbor);
            if ($cost < $bestcost) {
                $bestneighbor = $neighbor;
                $bestcost = $cost;
            }
        }
    }

    return [$bestneighbor, $bestcost];
}

function generate_teams_with_simulated_annealing(
        array $students,
        int $teamsize,
        callable $costfn
): array {
    $initialtemperature = 1000.0;
    $stoppingtemperature = 0.05;
    $coolingrate = 0.95;

    $currenttemp = $initialtemperature;

    // Initialize the current solution with the initial state
    $currentsolution = generate_greedily_teams($students, $teamsize);
    if (count($currentsolution) <= 1) {
        return $currentsolution;
    }
    $currentcost = $costfn($currentsolution);
    $bestsolution = $currentsolution;
    $bestcost = $currentcost;

    $nteams = ceil(count($students) / $teamsize);
    $maximalnumberofneighbour = $nteams * ($nteams - 1) / 2; # Total number of groups of 2
    $nneighbortogenerate = min(50, $maximalnumberofneighbour);
    while ($currenttemp > $stoppingtemperature) {
        $neighborsolsandcosts = [];
        for ($i = 0; $i < $nneighbortogenerate; $i++) {
            list($neighborsol, $neighborsolcost) = get_neighbor_solution($currentsolution, $costfn);
            $neighborsolsandcosts[] = [$neighborsol, $neighborsolcost];
        }

        usort($neighborsolsandcosts, function($a, $b) {
            return $a[1] <=> $b[1];
        });

        list($neighborsol, $neighborsolcost) = reset($neighborsolsandcosts);

        // Check if neighbor is best so far
        $costdiff = $currentcost - $neighborsolcost;

        // Accept the new solution if it's better or with a probability of e^(-cost/temp)
        $neighborsolaccepted = ($costdiff > 0) || (get_random_float() < exp(-$costdiff / $currenttemp));
        if ($neighborsolaccepted) {
            $currentsolution = $neighborsol;
            $currentcost = $neighborsolcost;

            if ($currentcost < $bestcost) {
                $bestsolution = $currentsolution;
                $bestcost = $currentcost;
            }
        }

        // Decrement the temperature
        $currenttemp *= $coolingrate;
    }

    return $bestsolution;
}

function generate_teams($course, $coursemodule, int $nstudentsperteam, string $strategy, string $groupingid): array {
    $ctx = context_course::instance($course->id);
    if ($groupingid == grouping_id::All) {
        // TODO: Change capability to users that can answer the questionnaire
        $users = get_enrolled_users($ctx, 'mod/assign:submit');
    } else {
        $users = groups_get_grouping_members($groupingid, 'DISTINCT u.id');
    }
    if (count($users) == 0) {
        return [false, 'errornotenoughstudents', []];
    } else if (count($users) <= $nstudentsperteam) {
        $students = array_map(function($user) {
            return new student($user, new form_metrics(0, 0, 0, 0));
        }, $users);
        return [true, '', new team($students, $strategy)];
    }

    // We match students that have replied using the choosen matching strategy
    list($usersswhoreplied, $userswhohaventreplied) =
            seperate_users_who_havent_replied($coursemodule, $users);

    if (count($usersswhoreplied) == 0) {
        $teams = [];
    } else if ($strategy == matching_strategy::RandomMatching) {
        $teams = generate_random_teams($usersswhoreplied, $nstudentsperteam);
    } else if ($strategy == matching_strategy::FastMatching) {
        $teams = generate_greedily_teams($usersswhoreplied, $nstudentsperteam, "sse_cost");
    } else if ($strategy == matching_strategy::SimulatedAnnealingSum) {
        $teams = generate_teams_with_simulated_annealing($usersswhoreplied, $nstudentsperteam, "sum_cost");
    } else if ($strategy == matching_strategy::SimulatedAnnealingSse) {
        $teams = generate_teams_with_simulated_annealing($usersswhoreplied, $nstudentsperteam, "sse_cost");
    } else if ($strategy == matching_strategy::SimulatedAnnealingStd) {
        $teams = generate_teams_with_simulated_annealing($usersswhoreplied, $nstudentsperteam, "std_cost");
    } else {
        return [false, 'errorunknownalgo', []]; // TODO: Internationalization
    }

    usort($teams, function($a, $b) {
        return $a->get_cognitive_variance() <=> $b->get_cognitive_variance();
    });

    foreach ($teams as $team) {
        $team->set_matching_strategy($strategy);
    }

    // We match randomly students that have not responded
    $randomteams = generate_random_teams($userswhohaventreplied, $nstudentsperteam);
    foreach ($randomteams as $team) {
        $team->set_matching_strategy(matching_strategy::RandomMatchingWithNoCognitiveMode);
    }

    $teams = array_merge($teams, $randomteams);

    return [true, '', json_encode($teams)];
}

function seperate_users_who_havent_replied($coursemodule, $users): array {
    global $DB;
    $allmbtianswers = $DB->get_records('polyteam_mbti', array('moduleid' => $coursemodule->id));

    $usersswhoreplied = [];
    $userswhohaventreplied = [];
    foreach ($users as $user) {
        $studentreplied = false;
        foreach ($allmbtianswers as $mbtianswer) {
            if ($user->id === $mbtianswer->userid) {
                $studentreplied = true;
                break;
            }
        }
        if ($studentreplied) {
            // TODO: Agree on $mbtianswer fields
            // $fm = new FormMetrics(
            //     intval($mbtianswer->ei),
            //     intval($mbtianswer->jp),
            //     intval($mbtianswer->sn),
            //     intval($mbtianswer->tf)
            // );
            $fm = generate_one_fake_form_metrics(0.5, 0.5, 0.5, 0.5);
            $usersswhoreplied[] = new student($user, $fm);
        } else {
            // $fm = generate_one_fake_form_metrics(0.5, 0.5, 0.5, 0.5),
            $fm = new form_metrics(0, 0, 0, 0);
            $userswhohaventreplied[] = new student($user, $fm);
        }
    }

    return [$usersswhoreplied, $userswhohaventreplied];
}

function create_teams($course, string $groupingid, array $generatedteams): array {
    $group_name_prefix = "MBTI_";
    $grouping = null;
    if ($groupingid != grouping_id::All) {
        $grouping = groups_get_grouping($groupingid);
        $group_name_prefix = $group_name_prefix . $grouping->name . "_";
    }

    // Remove all groups with the prefix before creating new ones
    $allgroups = groups_get_all_groups($course->id);
    foreach ($allgroups as $group) {
        if (substr($group->name, 0, strlen($group_name_prefix)) == $group_name_prefix) {
            groups_delete_group($group);
        }
    }

    foreach ($generatedteams as $i => $generatedteam) {
        $groupid = groups_create_group(
                (object) array(
                        'name' => $group_name_prefix . sprintf("%02d", $i + 1),
                        'courseid' => $course->id
                )
        );
        if (!$groupid) {
            return [false, 'errorunabletocreategroup'];
        }
        foreach ($generatedteam->students as $student) {
            if (!groups_add_member($groupid, $student->user->id)) {
                return [false, 'errorenabletoaddstudenttogroup'];
            }
        }
        if ($grouping && !groups_assign_grouping($groupingid, $groupid)) {
            return [false, 'errorenableassigngrouping'];
        }
    }

    return [true, ''];
}

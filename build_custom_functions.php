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

//require(__DIR__ . '/../../config.php');

class CognitiveMode
{
    const ES = "ES"; # Experimentation
    const IS = "IS"; # Ideation
    const EN = "EN"; # Knowledge
    const IN = "IN"; # Imagination
    const ET = "ET"; # Organization
    const IT = "IT"; # Community
    const EF = "EF"; # Analysis
    const IF = "IF"; # Evaluation
}

$ALL_COGNITIVE_MODES = [
    CognitiveMode::ES,
    CognitiveMode::IS,
    CognitiveMode::EN,
    CognitiveMode::IN,
    CognitiveMode::ET,
    CognitiveMode::IT,
    CognitiveMode::EF,
    CognitiveMode::IF
];

function get_new_cognitive_mode_counter(): array
{
    global $ALL_COGNITIVE_MODES;
    return array_fill_keys($ALL_COGNITIVE_MODES, 0);
}

function standard_deviation(array $data): float
{
    $n = count($data);

    if ($n <= 1) {
        return 0; // Standard deviation is undefined for a single value or an empty array
    }

    $mean = array_sum($data) / $n;
    $squared_deviations = array_map(function ($x) use ($mean) {
        return pow($x - $mean, 2);
    }, $data);

    $variance = array_sum($squared_deviations) / ($n - 1);
    return sqrt($variance);
}

function get_variance_from_cognitive_modes_counter(array $counter): float
{
    global $ALL_COGNITIVE_MODES;
    $counter_total = array_sum($counter);
    if ($counter_total == 0) {
        return 0;
    }
    $counter_value_factor = 100.0 / $counter_total;

    $mode_values = array_map(function (string $mode) use ($counter, $counter_value_factor) {
        return $counter[$mode] * $counter_value_factor;
    }, $ALL_COGNITIVE_MODES);

    return standard_deviation($mode_values);
}

class CognitiveModeMetrics
{
    private int $ES;
    private int $EN;
    private int $IS;
    private int $IN;
    private int $ET;
    private int $EF;
    private int $IT;
    private int $IF;

    /**
     * @param $ES
     * @param $EN
     * @param $IS
     * @param $IN
     * @param $ET
     * @param $EF
     * @param $IT
     * @param $IF
     */
    public function __construct($ES, $EN, $IS, $IN, $ET, $EF, $IT, $IF)
    {
        $this->ES = $ES;
        $this->EN = $EN;
        $this->IS = $IS;
        $this->IN = $IN;
        $this->ET = $ET;
        $this->EF = $EF;
        $this->IT = $IT;
        $this->IF = $IF;
    }

    public function get_modes(): array
    {
        $map = [
            CognitiveMode::ES => $this->ES,
            CognitiveMode::IS => $this->IS,
            CognitiveMode::EN => $this->EN,
            CognitiveMode::IN => $this->IN,
            CognitiveMode::ET => $this->ET,
            CognitiveMode::IT => $this->IT,
            CognitiveMode::EF => $this->EF,
            CognitiveMode::IF => $this->IF,
        ];
        $modes = [];
        foreach ($map as $cognitiveMode => $value) {
            if ($value > 0) {
                $modes[] = $cognitiveMode;
            }
        }
        return $modes;
    }

}

class FormMetrics
{
    private int $EI;
    private int $JP;
    private int $SN;
    private int $TF;

    /**
     * @param int $EI Extrovert/Introvert - Energy Direction: Outward or Inward
     * @param int $JP Structured(Judgement)/Flexible (Perception) - Orientation: Flexible or Structured
     * @param int $SN Sensing/Intuition - Information Collection Process: Facts or Possibilities
     * @param int $TF Thinking/Feeling - Decision-Making Process: Objects or People
     */
    public function __construct(int $EI, int $JP, int $SN, int $TF)
    {
        $this->EI = $EI;
        $this->JP = $JP;
        $this->SN = $SN;
        $this->TF = $TF;
    }


    public function get_cognitive_mode_metrics(): CognitiveModeMetrics
    {
        $es = $this->EI - $this->JP + 2 * $this->SN;
        $en = $this->EI - $this->JP - 2 * $this->SN;
        $et = $this->EI + $this->JP + 2 * $this->TF;
        $ef = $this->EI + $this->JP - 2 * $this->TF;

        return new CognitiveModeMetrics($es, $en, -$en, -$es, $et, $ef, -$ef, -$et);
    }
}

class Student implements JsonSerializable
{
    private object $user; // TODO: Only save required attributes from the user
    private array $modes;

    public function __construct(object $user, FormMetrics $form_metrics)
    {
        $this->user = $user;
        $this->modes = [];
        $cognitive_mode_metrics = $form_metrics->get_cognitive_mode_metrics();
        foreach ($cognitive_mode_metrics->get_modes() as $mode) {
            $this->modes[] = $mode;
        }
    }

    public function get_user(): object
    {
        return $this->user;
    }

    public function get_cognitive_modes_names(): array
    {
        return $this->modes;
    }

    public function has_cognitive_mode(string $mode_name): bool
    {
        return in_array($mode_name, $this->modes);
    }

    public function jsonSerialize(): array
    {
        return [
            "user" => $this->user,
            "modes" => $this->modes
        ];
    }
}

class Team implements JsonSerializable
{
    private array $students = [];
    private array $cognitive_modes_counter = [];
    private float $cognitive_variance;

    public function __construct($students = [])
    {
        $this->students = $students;
        $this->cognitive_modes_counter = get_new_cognitive_mode_counter();
        foreach ($this->students as $student) {
            foreach ($student->get_cognitive_modes_names() as $mode) {
                $this->cognitive_modes_counter[$mode]++;
            }
        }
        $this->cognitive_variance = get_variance_from_cognitive_modes_counter($this->cognitive_modes_counter);
    }

    public function add_student(Student $student): void
    {
        $this->students[] = $student;
        foreach ($student->get_cognitive_modes_names() as $mode) {
            $this->cognitive_modes_counter[$mode]++;
        }
        $this->cognitive_variance = get_variance_from_cognitive_modes_counter($this->cognitive_modes_counter);
    }

    public function get_all_students(): array
    {
        return $this->students;
    }

    public function get_cognitive_modes_counter(): array
    {
        return $this->cognitive_modes_counter;
    }

    public function get_cognitive_variance(): float
    {
        return $this->cognitive_variance;
    }

    public function get_n_students(): float
    {
        return count($this->students);
    }

    public function jsonSerialize(): array
    {
        return [
            "students" => $this->students,
            "cognitive_modes_counter" => $this->cognitive_modes_counter,
            "cognitive_variance" => $this->cognitive_variance
        ];
    }
}

function getRandomFloat(): float
{
    return rand() / getrandmax();
}

function generate_fake_students(
    int   $n_students,
    float $extroversion_ratio,
    float $judging_ratio,
    float $sensing_ratio,
    float $thinking_ratio
): array
{
    $fake_students = [];

    for ($i = 1; $i <= $n_students; $i++) {
        $form_metrics = generate_one_fake_form_metrics(
            $extroversion_ratio,
            $judging_ratio,
            $sensing_ratio,
            $thinking_ratio
        );
        $user = new stdClass();
        $user->id = $i;
        $fake_students[] = new Student($user, $form_metrics);
    }

    return $fake_students;
}

/**
 * @param float $extroversion_ratio
 * @param float $judging_ratio
 * @param float $sensing_ratio
 * @param float $thinking_ratio
 * @return FormMetrics
 */
function generate_one_fake_form_metrics(
    float $extroversion_ratio,
    float $judging_ratio,
    float $sensing_ratio,
    float $thinking_ratio): FormMetrics
{
    $ei = rand(0, 5);
    $jp = rand(0, 5);
    $sn = rand(0, 5);
    $tf = rand(0, 5);
    if (getRandomFloat() > $extroversion_ratio) {
        $ei = -$ei;
    }
    if (getRandomFloat() > $judging_ratio) {
        $jp = -$jp;
    }
    if (getRandomFloat() > $sensing_ratio) {
        $sn = -$sn;
    }
    if (getRandomFloat() > $thinking_ratio) {
        $tf = -$tf;
    }
    return new FormMetrics($ei, $jp, $sn, $tf);
}

function generate_random_teams(array $students, int $team_size): array
{
    $students_shuffled = array_slice($students, 0);
    shuffle($students_shuffled);

    $num_teams = ceil(count($students_shuffled) / $team_size);
    $generated_teams = [];

    for ($i = 0; $i < $num_teams; $i++) {
        $team_students = array_slice($students_shuffled, $i * $team_size, $team_size);
        $generated_teams[] = new Team($team_students);
    }

    return $generated_teams;
}

function remove_from_array(array $arr, array $toRemove): array
{
    $result = [];
    foreach ($arr as $item) {
        if (!in_array($item, $toRemove)) {
            $result[] = $item;
        }
    }
    return $result;
}

function generate_greedily_teams(array $students, int $team_size): array
{
    $non_assigned_students = array_slice($students, 0);
    $num_teams = ceil(count($non_assigned_students) / $team_size);
    $generated_teams = [];
    for ($i = 0; $i < $num_teams; $i++) {
        $generated_teams[] = new Team();
    }

    $current_team_index = 0;
    while (!empty($non_assigned_students)) {
        $current_team = $generated_teams[$current_team_index];
        $best_candidate = $non_assigned_students[0];
        $best_variance = (new Team(array_merge([$best_candidate], $current_team->get_all_students())))
            ->get_cognitive_variance();

        for ($i = 1; $i < count($non_assigned_students); $i++) {
            $candidate = $non_assigned_students[$i];
            $variance = (new Team(array_merge([$candidate], $current_team->get_all_students())))
                ->get_cognitive_variance();
            if ($variance < $best_variance) {
                $best_candidate = $candidate;
                $best_variance = $variance;
            }
        }

        $non_assigned_students = remove_from_array($non_assigned_students, [$best_candidate]);
        $current_team->add_student($best_candidate);

        $current_team_index = ($current_team_index + 1) % $num_teams;
    }

    return $generated_teams;
}

function sum_cost($teams): float
{
    return array_sum(array_map(
        function ($team) {
            return $team->get_cognitive_variance();
        },
        $teams
    ));
}

function sse_cost($teams): float
{
    return array_sum(array_map(
        function ($team) {
            return pow($team->get_cognitive_variance(), 2);
        },
        $teams
    ));
}

function std_cost(array $teams): float
{
    return standard_deviation(array_map(
        function ($team) {
            return $team->get_cognitive_variance();
        },
        $teams
    ));
}

function get_neighbor_solution(array $teams, callable $cost_fn): array
{
    $best_neighbor = null;
    $best_cost = INF;

    $two_random_keys = array_rand($teams, 2);
    $t1 = $teams[$two_random_keys[0]];
    $t2 = $teams[$two_random_keys[1]];
    $t1_students = $t1->get_all_students();
    $t2_students = $t2->get_all_students();
    $all_students_swaps = [];

    foreach ($t1_students as $s1) {
        foreach ($t2_students as $s2) {
            $all_students_swaps[] = [$s1, $s2];
        }
    }

    $all_teams_except_picked = remove_from_array($teams, [$t1, $t2]);

    foreach ($all_students_swaps as list($s1, $s2)) {
        $t1_students_new = array_merge(remove_from_array($t1_students, [$s1]), [$s2]);
        $t2_students_new = array_merge(remove_from_array($t2_students, [$s2]), [$s1]);
        $neighbor = array_merge(
            $all_teams_except_picked,
            [new Team($t1_students_new), new Team($t2_students_new)]
        );
        $cost = $cost_fn($neighbor);
        if ($cost < $best_cost) {
            $best_neighbor = $neighbor;
            $best_cost = $cost;
        }
    }

    if (count($t1_students) !== count($t2_students)) {
        if (count($t1_students) < count($t2_students)) {
            list($t1_students, $t2_students) = [$t2_students, $t1_students];
        }
        foreach ($t1_students as $s1) {
            $t1_students_new = remove_from_array($t1_students, [$s1]);
            $t2_students_new = array_merge($t2_students, [$s1]);
            $neighbor = array_merge(
                $all_teams_except_picked,
                [new Team($t1_students_new), new Team($t2_students_new)]
            );
            $cost = $cost_fn($neighbor);
            if ($cost < $best_cost) {
                $best_neighbor = $neighbor;
                $best_cost = $cost;
            }
        }
    }

    return [$best_neighbor, $best_cost];
}

function generate_teams_with_simulated_annealing(
    array    $students,
    int      $team_size,
    callable $cost_fn
): array
{
    $initial_temperature = 1000.0;
    $stopping_temperature = 0.05;
    $cooling_rate = 0.95;

    $current_temp = $initial_temperature;

    // Initialize the current solution with the initial state
    $current_solution = generate_greedily_teams($students, $team_size);
    if (count($current_solution) <= 1) {
        return $current_solution;
    }
    $current_sse = $cost_fn($current_solution);
    $best_solution = $current_solution;
    $best_sse = $current_sse;

    $n_teams = ceil(count($students) / $team_size);
    $maximal_number_of_neighbour_sol = $n_teams * ($n_teams - 1) / 2; # Total number of groups of 2
    $n_neighbor_to_generate = min(50, $maximal_number_of_neighbour_sol);
    while ($current_temp > $stopping_temperature) {
        $neighbor_sols_and_costs = [];
        for ($i = 0; $i < $n_neighbor_to_generate; $i++) {
            list($neighbor_sol, $neighbor_sol_sse) = get_neighbor_solution($current_solution, $cost_fn);
            $neighbor_sols_and_costs[] = [$neighbor_sol, $neighbor_sol_sse];
        }

        usort($neighbor_sols_and_costs, function ($a, $b) {
            return $a[1] <=> $b[1];
        });

        list($neighbor_sol, $neighbor_sol_sse) = reset($neighbor_sols_and_costs);

        // Check if neighbor is best so far
        $sse_diff = $current_sse - $neighbor_sol_sse;

        // Accept the new solution if it's better or with a probability of e^(-cost/temp)
        $neighbor_sol_accepted = ($sse_diff > 0) || (getRandomFloat() < exp(-$sse_diff / $current_temp));
        if ($neighbor_sol_accepted) {
            $current_solution = $neighbor_sol;
            $current_sse = $neighbor_sol_sse;

            if ($current_sse < $best_sse) {
                $best_solution = $current_solution;
                $best_sse = $current_sse;
            }
        }

        // Decrement the temperature
        $current_temp *= $cooling_rate;
    }

    return $best_solution;
}

function test_random_student_generation(): void
{
    srand(0); // Make results reproducible

    $students = generate_fake_students(64, 0.46, 0.73, 0.87, 0.78); // Adjust ratios as needed
    # $students = generate_fake_students(64, 0.5, 0.5, 0.5, 0.5); // Adjust ratios as needed
    $cognitive_modes_counter = get_new_cognitive_mode_counter();

    foreach ($students as $student) {
        echo "id: " . $student->get_id() . ", modes: " . implode(', ', $student->get_cognitive_modes_names()) . '<br>';
        $student_modes = $student->get_cognitive_modes_names();

        foreach ($student_modes as $mode) {
            $cognitive_modes_counter[$mode]++;
        }
    }

    print_r($cognitive_modes_counter);

}

function test_random_team_generation(): void
{
    srand(0); // Make results reproducible

    $students = generate_fake_students(64, 0.46, 0.73, 0.87, 0.78); // Adjust ratios as needed
    # $students = generate_fake_students(64, 0.5, 0.5, 0.5, 0.5); // Adjust ratios as needed

    // Generate random teams and sort them based on cognitive variance
    $teams = generate_random_teams($students, 4);
    usort($teams, function ($a, $b) {
        return $a->get_cognitive_variance() <=> $b->get_cognitive_variance();
    });

    // Print std of every team
    echo "Variances:" . '<br>';
    foreach ($teams as $team) {
        echo "" . $team->get_cognitive_variance() . '<br>';
    }
}

function test_greedy_team_generation(): void
{
    srand(0); // Make results reproducible

    $students = generate_fake_students(64, 0.46, 0.73, 0.87, 0.78); // Adjust ratios as needed
    # $students = generate_fake_students(64, 0.5, 0.5, 0.5, 0.5); // Adjust ratios as needed

    // Generate random teams and sort them based on cognitive variance
    $teams = generate_greedily_teams($students, 4);
    usort($teams, function ($a, $b) {
        return $a->get_cognitive_variance() <=> $b->get_cognitive_variance();
    });

    // Print std of every team
    echo "Variances:" . '<br>';
    foreach ($teams as $team) {
        echo "" . $team->get_cognitive_variance() . '<br>';
    }
}

function test_simulated_annealing_team_generation(): void
{
    $cost_functions = [
        "sum_cost",
        "sse_cost",
        "std_cost"
    ];

    foreach ($cost_functions as $cost_fn) {
        echo '<br>' . "Cost function: " . $cost_fn . '<br>' . '<br>';

        srand(0); // Make results reproducible

        // Generate random teams and sort them based on cognitive variance
        $students = generate_fake_students(64, 0.46, 0.73, 0.87, 0.78); // Adjust ratios as needed
        # $students = generate_fake_students(64, 0.5, 0.5, 0.5, 0.5); // Adjust ratios as needed

        $teams = generate_teams_with_simulated_annealing($students, 4, $cost_fn);
        usort($teams, function ($a, $b) {
            return $a->get_cognitive_variance() <=> $b->get_cognitive_variance();
        });

        // Print std of every team
        echo "Variances:" . '<br>';
        foreach ($teams as $team) {
            echo "" . $team->get_cognitive_variance() . '<br>';
        }
    }
}

function generate_teams($course, $coursemodule, int $nstudentsperteam, string $strategy, string $groupingid): array
{
    global $DB;
    $ctx = context_course::instance($course->id);
    if ($groupingid == "all") {
        // TODO: Change capability to users that can answer the questionnaire
        $users = get_enrolled_users($ctx, 'mod/assign:submit');
    } else {
        $users = groups_get_grouping_members($groupingid, "DISTINCT u.id");
    }
    if (count($users) == 0) {
        return [false, "Not enough students to generate teams", []]; // TODO: Internationalization
    } elseif (count($users) <= $nstudentsperteam) {
        $students = array_map(function ($user) {
            return new Student($user, new FormMetrics(0, 0, 0, 0));
        }, $users);
        return [True, "", new Team($students)];
    }

    list($usersswhoreplied, $userswhohaventreplied) =
        seperateuserswhohaventreplied($coursemodule, $users);

    if (count($usersswhoreplied) == 0) {
        $teams = [];
    } else {
        if ($strategy == "randommatching") {
            $teams = generate_random_teams($usersswhoreplied, $nstudentsperteam);
        } elseif ($strategy == "fastmatching") {
            $teams = generate_greedily_teams($usersswhoreplied, $nstudentsperteam, "sse_cost");
        } elseif ($strategy == "simulatedannealingsum") {
            $teams = generate_teams_with_simulated_annealing($usersswhoreplied, $nstudentsperteam, "sum_cost");
        } elseif ($strategy == "simulatedannealingsse") {
            $teams = generate_teams_with_simulated_annealing($usersswhoreplied, $nstudentsperteam, "sse_cost");
        } elseif ($strategy == "simulatedannealingstd") {
            $teams = generate_teams_with_simulated_annealing($usersswhoreplied, $nstudentsperteam, "std_cost");
        } else {
            return [false, "Unknown matching algorithm", []]; // TODO: Internationalization
        }
    }

    usort($teams, function ($a, $b) {
        return $a->get_cognitive_variance() <=> $b->get_cognitive_variance();
    });

    // We create random teams with students that haven't replied
    $teams = array_merge(
        $teams,
        generate_random_teams($userswhohaventreplied, $nstudentsperteam)
    );

    return [true, '', json_encode($teams)];
}

function seperateuserswhohaventreplied($coursemodule, $users): array
{
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
            $usersswhoreplied[] = new Student($user, $fm);
        } else {
            // $fm = generate_one_fake_form_metrics(0.5, 0.5, 0.5, 0.5),
            $fm = new FormMetrics(0, 0, 0, 0);
            $userswhohaventreplied[] = new Student($user, $fm);
        }
    }

    return [$usersswhoreplied, $userswhohaventreplied];
}

/**
 * @throws coding_exception
 * @throws moodle_exception
 */
function create_teams($course, string $groupingid, array $generatedteams): array
{
    $group_name_prefix = "MBTI_";
    $grouping = null;
    if ($groupingid != "all") {
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
            (object)array(
                'name' => $group_name_prefix . sprintf("%02d", $i + 1),
                'courseid' => $course->id
            )
        );
        if (!$groupid) {
            return [false, "Enable to create one or more group"];
        }
        foreach ($generatedteam->students as $student) {
            if (!groups_add_member($groupid, $student->user->id)) {
                return [false, "Enable to add one or more student to a group"];
            }
        }
        if ($grouping && !groups_assign_grouping($groupingid, $groupid)) {
            return [false, "Enable to assign one or more grouping to a group"];
        }
    }

    return [true, ''];
}

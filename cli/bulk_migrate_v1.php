<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Turn it in tool two bulk migrate
 *
 * @package    mod_turnitintooltwo
 * @subpackage cli
 * @copyright  2025 Jay Oswald <jayoswald@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once("{$CFG->libdir}/clilib.php");
include_once(__DIR__ . "/../classes/task/turnitintool_v1_migration.php");

list($options, $unrecognized) = cli_get_params([
    'help' => false,
    'count' => 10,
], []);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'mod_turnitintooltwo', $unrecognized));
}

$help = <<<EOT
Bulk migrate all turnitintool v1 to v2

Options:
 --help      Show This
 --count     Set how many items to loop through, 0 for unlimited, default of 10 to test a few
EOT;

if ($options['help']) {
    echo $help;
    exit(0);
}

global $DB;

$query = "SELECT id, course FROM {turnitintool}";
$assignments = $DB->get_records_sql($query);
$max = $options['count'];
$count = 0;
foreach($assignments as $assign){
    $task = new \mod_turnitintooltwo\task\turnitintool_v1_migration();
    $task->set_custom_data([
        'turnitintoolid' => (int) $assign->id,
        'courseid' => (int) $assign->course,
    ]);
    \core\task\manager::queue_adhoc_task($task, true);
    
    if($max !== 0 && $count++ > $max) break;
}
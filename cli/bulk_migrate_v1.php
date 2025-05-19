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
include_once(__DIR__ . "/../classes/v1migration/v1migration.php");

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
    echo json_encode($assign) . PHP_EOL;
    $turnitintool = $DB->get_record("turnitintool", array("id" => $assign->id));
    $v1_migration = new v1migration($assign->course, $turnitintool);
    try{
        $turnitintooltwoid = $v1_migration->migrate();
        echo $turnitintooltwoid . PHP_EOL;
    } catch(Exception $e){
        $errorresponse = array(
            'error' => get_string('migrationtoolerror', 'turnitintooltwo'),
            'message' => $e->getMessage()
        );
        $errorresponse = array_merge($errorresponse, array(
            'exception' => $e,
            'trace' => $e->getTrace()
        ));
        echo json_encode($errorresponse) . PHP_EOL;
    }
    if($max !== 0 && $count++ >= $max) break;
}

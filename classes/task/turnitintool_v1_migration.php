<?php

namespace mod_turnitintooltwo\task;
include_once(__DIR__ . "/../v1migration/v1migration.php");

class turnitintool_v1_migration extends \core\task\adhoc_task {
    public static function instance(int $turnitintoolid, int $courseid): self{
        $task = new self();
        $task->set_custom_data((object)[
            'turnitintoolid' => $turnitintoolid,
            'courseid' => $courseid
        ]);
        return $task;
    }

    public function execute(){
        global $DB;
        $data = $this->get_custom_data();
        $turnitintool = $DB->get_record("turnitintool", array("id" => $data->turnitintoolid));

        $v1_migration = new \v1migration($data->courseid, $turnitintool);
    try{
        $v1_migration->migrate();
    } catch(\Exception $e){
        $errorresponse = array(
            'error' => get_string('migrationtoolerror', 'turnitintooltwo'),
            'message' => $e->getMessage()
        );
        $errorresponse = array_merge($errorresponse, array(
            'exception' => $e,
            'trace' => $e->getTrace()
        ));
        mtrace(json_encode($errorresponse));
    }
    }
}
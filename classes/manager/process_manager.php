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
 * Manager for Cleanup Course Processes
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

use tool_cleanupcourses\entity\process;
use tool_cleanupcourses\entity\trigger_subplugin;

defined('MOODLE_INTERNAL') || die();

class process_manager {

    /**
     * Creates a process for the course which is at the respective step the trigger is followed by.
     * @param int $courseid id of the course
     * @param trigger_subplugin $trigger
     */
    public static function create_process($courseid, $trigger) {
        global $DB;
        if ($trigger->followedby !== null) {
            $record = new \stdClass();
            $record->courseid = $courseid;
            $record->stepid = $trigger->followedby;
            $DB->insert_record('tool_cleanupcourses_process', $record);
        }
    }

    /**
     * Creates a process for the course which is at the respective step the trigger is followed by.
     * @return process[]
     */
    public static function get_processes() {
        global $DB;
        $records = $DB->get_records('tool_cleanupcourses_process');
        $processes = array();
        foreach ($records as $record) {
            $processes []= process::from_record($record);
        }
        return $processes;
    }

    /**
     * Proceeds the process to the next step.
     * @param process $process
     */
    public static function proceed_process(&$process) {
        global $DB;
        $step = step_manager::get_step_instance($process->stepid);
        if ($step->followedby !== null) {
            $process->stepid = $step->followedby;
            $DB->update_record('tool_cleanupcourses_process', $process);
        } else {
            if (get_course($process->courseid)) {
                debugging('Course should no longer exist!!!!');
            }
            $DB->delete_records('tool_cleanupcourses_process', $process);
        }
    }

}
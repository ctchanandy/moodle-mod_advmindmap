<?php
// This file is part of Mindmap module for Moodle - http://moodle.org/
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
 * Unlock advmindmap
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Andy Chan <ctchan.andy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);

if (! $cm = $DB->get_record("course_modules", array("id"=>$id))) {
    print_error('invalidcoursemodule');
}
if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf', 'advmindmap');
}
if (! $advmindmap = $DB->get_record("advmindmap", array("id"=>$cm->instance))) {
    print_error('invalidid', 'advmindmap');
}
if (! $advmindmap_instance = $DB->get_record("advmindmap_instances", array("id"=>$instanceid))) {
    print_error('errorinvalidadvmindmap', 'advmindmap');
}

require_login($course->id);

//add_to_log($course->id, "advmindmap", "unlock", "unlock.php?id=$id&instanceid=$instanceid", $advmindmap->id, $cm->id);

// constructing URL for event
$viewuser = 0;
$viewgroup = 0;
$viewdummy = 0;
$groupmode = groups_get_activity_groupmode($cm, $course);
if ($groupmode) {
    $viewgroup = $id;
} else if (!$groupmode && !$advmindmap->numdummygroups) {
    $viewuser = $id;
} else if (!$advmindmap->numdummygroups > 0) {
    $viewdummy = $id;
}

$event = \mod_advmindmap\event\mindmap_unlocked::create(array(
    'objectid' => $cm->id,
    'courseid' => $course->id,
    'context' => context_module::instance($cm->id),
    'other' => array("viewuser"=>$viewuser, "viewgroup"=>$viewgroup, "viewdummy"=>$viewdummy)
));
$event->add_record_snapshot('advmindmap_instances', $advmindmap_instances);
$event->trigger();

if (advmindmap_clear_access_record($advmindmap_instance)) {
    $coursepage = $CFG->wwwroot.'/course/view.php?id='.$course->id;
    header('Location: '.$coursepage);
} else {
    print_error('errorcannotunlockadvmindmap', 'advmindmap');
}
?>
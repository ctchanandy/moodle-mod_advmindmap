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
 * Saving advmindmap nodes
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Andy Chan <ctchan.andy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

$id = optional_param('id', 0, PARAM_INT); // advmindmap instance ID
$xml = optional_param('mindmap', '', PARAM_RAW); 

if ($id) {
    if (! $advmindmap_instance = $DB->get_record("advmindmap_instances", array("id"=>$id))) {
        print_error('errorinvalidadvmindmap', 'advmindmap');
    }
    if (! $advmindmap = $DB->get_record("advmindmap", array("id"=>$advmindmap_instance->advno))) {
        print_error('invalidid', 'advmindmap');
    }
    if (! $course = $DB->get_record("course", array("id"=>$advmindmap->course))) {
        print_error('coursemisconf', 'advmindmap');
    }
    if (! $cm = get_coursemodule_from_instance("advmindmap", $advmindmap->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($advmindmap->course);

//add_to_log($course->id, "advmindmap", "update instance", "save.php?id=$id", $advmindmap->id, $cm->id);

// constructing URL for event
$viewuser = 0;
$viewgroup = 0;
$viewdummy = 0;
$groupmode = groups_get_activity_groupmode($cm, $course);
if ($groupmode) {
    $viewgroup = $id;
} else if (!$groupmode && !$advmindmap->numdummygroups) {
    $viewuser = $id;
    if ($viewuser == $USER->id) $viewuser = 0;
} else if (!$advmindmap->numdummygroups > 0) {
    $viewdummy = $id;
}

$event = \mod_advmindmap\event\mindmap_updated::create(array(
    'objectid' => $cm->id,
    'courseid' => $course->id,
    'context' => context_module::instance($cm->id),
    'other' => array("viewuser"=>$viewuser, "viewgroup"=>$viewgroup, "viewdummy"=>$viewdummy)
));
$event->add_record_snapshot('advmindmap_instances', $advmindmap_instances);
$event->trigger();

if($xml && $advmindmap->editable == '1') {
    if(get_magic_quotes_gpc()) {
        $xml = stripslashes($xml);
    }
    
    $new = new stdClass();
    $new->editable = '1';
    $new->id = $advmindmap_instance->id;
    $new->xmldata = $xml;
    
    if (!advmindmap_update_user_instance($new)) {
        echo "update failed";
    }
} else {
    echo "fail";
}
?>
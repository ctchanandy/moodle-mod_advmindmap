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
 * advmindmap index page
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Andy Chan <ctchan.andy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);   // id: course id

if (! $course = $DB->get_record("course", array("id"=>$id))) {
    print_error('invalidcourseid');
}

require_course_login($course);

// Legacy logging function call
//add_to_log($course->id, "advmindmap", "view all", "index.php?id=$course->id", "");

$params = array(
    'context' => context_course::instance($id)
);
$event = \mod_advmindmap\event\course_module_instances_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

/// Get all required strings
$strsectionname = get_string('sectionname', 'format_'.$course->format);
$stradvmindmaps = get_string("modulenameplural", "advmindmap");
$stradvmindmap  = get_string("modulename", "advmindmap");

/// Print the header
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add($stradvmindmaps);
$PAGE->set_title($stradvmindmaps);
echo $OUTPUT->header();

/// Get all the appropriate data
if (! $advmindmaps = get_all_instances_in_course('advmindmap', $course)) {
    notice(get_string('thereareno', 'moodle', $stradvmindmaps), "../../course/view.php?id=$course->id");
    die();
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

/// Print the list of instances
$timenow  = time();
$strname  = get_string('name');

$table = new html_table();

if ($usesections) {
    $table->head  = array ($strsectionname, $strname);
    $table->align = array ('center', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left');
}

$currentsection = '';
foreach ($advmindmaps as $advmindmap) {
    if (!$advmindmap->visible) {
        //Show dimmed if the mod is hidden
        $link = html_writer::link('view.php?id='.$advmindmap->coursemodule, format_string($advmindmap->name,true), array('class'=>'dimmed'));
    } else {
        //Show normal if the mod is visible
        $link = html_writer::link('view.php?id='.$advmindmap->coursemodule, format_string($advmindmap->name,true));
    }
    $printsection = '';
    if ($advmindmap->section !== $currentsection) {
        if ($advmindmap->section) {
            $printsection = get_section_name($course, $sections[$advmindmap->section]);
        }
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $advmindmap->section;
    }
    if ($usesections) {
        $table->data[] = array ($printsection, $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo html_writer::table($table);

/// Finish the page
echo $OUTPUT->footer($course);
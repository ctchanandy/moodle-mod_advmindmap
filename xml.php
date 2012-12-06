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
 * advmindmap XML parsing
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Andy Chan <ctchan.andy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or

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
}

require_login($course->id);

echo $advmindmap_instance->xmldata;

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
 * @package mod
 * @subpackage advmindmap
 * @author Andy Chan <ctchan.andy@gmail.com>
 * @copyright 2012 Andy Chan <ctchan.andy@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one advmindmap activity
 */
class restore_advmindmap_activity_structure_step extends restore_activity_structure_step {
 
    protected function define_structure() {
 
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');
 
        $paths[] = new restore_path_element('advmindmap', '/activity/advmindmap');
        
        if ($userinfo) {
            $paths[] = new restore_path_element('advmindmap_instance', '/activity/advmindmap/instances/advmindmap_instance');
        }
        
        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }
 
    protected function process_advmindmap($data) {
        global $DB;
 
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        // insert the advmindmap record
        $newitemid = $DB->insert_record('advmindmap', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }
    
    protected function process_advmindmap_instance($data) {
        global $DB;
 
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->advno = $this->get_new_parentid('advmindmap');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $data->useraccessed = $this->get_mappingid('user', $data->useraccessed);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timeaccessed = $this->apply_date_offset($data->timeaccessed);
 
        $newitemid = $DB->insert_record('advmindmap_instances', $data);
        $this->set_mapping('advmindmap_instance', $oldid, $newitemid, true); // files by this itemname
    }
    
    protected function after_execute() {
        // Add advmindmap related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_advmindmap', 'intro', null);
        
        $this->add_related_files('mod_advmindmap', 'intro', 'advmindmap_instance');
    }
}
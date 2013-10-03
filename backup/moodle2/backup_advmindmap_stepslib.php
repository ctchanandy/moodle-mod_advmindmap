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
 * Define all the backup steps that will be used by the backup_advmindmap_activity_task
 */
 class backup_advmindmap_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
 
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated
        $advmindmap = new backup_nested_element('advmindmap', array('id'), array(
                            'name', 'intro', 'introformat', 'userid', 'editable', 
                            'numdummygroups', 'xmldata', 'timecreated', 'timemodified'));
        
        
        $instances = new backup_nested_element('instances');
        
        $instance = new backup_nested_element('instance', array('id'), array(
                                     'advno', 'name', 'intro', 'introformat', 'userid', 'groupid',
                                     'editable', 'xmldata', 'timecreated', 'timemodified', 'useraccessed', 'timeaccessed'));
        
        
        // Build the tree
        $advmindmap->add_child($instances);
        $instances->add_child($instance);
        
        // Define sources
        $advmindmap->set_source_table('advmindmap', array('id' => backup::VAR_ACTIVITYID));
        
        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $instance->set_source_table('advmindmap_instances', array('advno' => backup::VAR_PARENTID));
        }
        
        // Define id annotations
        $advmindmap->annotate_ids('user', 'userid');
        
        $instance->annotate_ids('user', 'userid');
        $instance->annotate_ids('group', 'groupid');
        $instance->annotate_ids('user', 'useraccessed');
        
        // Define file annotations
        $advmindmap->annotate_files('mod_advmindmap', 'intro', null); // This file area hasn't itemid
        
        $instance->annotate_files('mod_advmindmap', 'intro', 'id');
        
        // Return the root element (advmindmap), wrapped into standard activity structure
        return $this->prepare_activity_structure($advmindmap);
    }
}
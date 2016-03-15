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
 * advmindmap instance add/edit form
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Andy Chan <ctchan.andy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_advmindmap_mod_form extends moodleform_mod {
	
    function definition() {
        
        global $CFG;
        $mform = $this->_form;
        
        $mform->addElement('header', 'general', get_string('general', 'form'));
        
        /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('advmindmapname', 'advmindmap'), array('size'=>'64'));
		if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        
        $mform->addElement('checkbox', 'editable', get_string('editable', 'advmindmap'));
        $mform->setDefault('editable', 1);
        
		// add intro section
        $this->standard_intro_elements(get_string('advmindmapintro', 'advmindmap'));
        
        // add dummy groups settings
        $dummyoptions = array();
        for ($i=0; $i<=40; $i++) {
            $dummyoptions[$i] = $i;
        }
        $mform->addElement('select', 'numdummygroups', get_string('numdummygroups', 'advmindmap'), $dummyoptions);
        
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        
        // add standard buttons, common to all modules
        $this->add_action_buttons();
	}
    
    function data_preprocessing(&$default_values){
        $mform =& $this->_form;
        if (isset($default_values['numdummygroups'])) {
            if ($default_values['numdummygroups'] > 0) $mform->setDefault('numdummygroupsenabled', 1);
        }
    }
    
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        if (isset($data['groupmode']) && isset($data['numdummygroups'])) {
            if ($data['groupmode'] != 0 && $data['numdummygroups'] > 0) {
                $errors['groupmode'] = get_string('invalidgroupmodefordummygroups', 'advmindmap');
            }
        }
        
        return $errors;
    }
}

?>
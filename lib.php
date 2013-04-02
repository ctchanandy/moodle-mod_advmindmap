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
 * Mindmap core interaction API
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Andy Chan <ctchan.andy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted newmodule record
 **/
function advmindmap_add_instance($advmindmap) {
	global $DB, $USER;
    
	// advmindmap
    $advmindmap->userid = $USER->id;
    if (isset($advmindmap->editable)) {
        $advmindmap->editable = '1';
    } else {
        $advmindmap->editable = '0';
    }
    // Default mindmap: one "Moodle" node in the center
    $advmindmap->xmldata = '
<MindMap>		
 <MM>
   <Node x_Coord="400" y_Coord="270">
     <Text>Moodle</Text>
     <Format Underlined="0" Italic="0" Bold="0">
       <Font>Trebuchet MS</Font>
       <FontSize>14</FontSize>
       <FontColor>ffffff</FontColor>
       <BackgrColor>ff0000</BackgrColor>
     </Format>
   </Node>
 </MM>
</MindMap>';
    
    $advmindmap->timecreated = time();
    
    $advmindmap->id = $DB->insert_record("advmindmap", $advmindmap);
    
    if ($advmindmap->numdummygroups > 0) {
        for ($i=0; $i<$advmindmap->numdummygroups; $i++) {
            $dummy_mindmap_instance = advmindmap_set_new_instance($advmindmap);
            $dummy_mindmap_instance->name = get_string('group').' '.($i+1);
            $dummy_mindmap_instance->id = $DB->insert_record("advmindmap_instances", $dummy_mindmap_instance);
        }
    }
    
    return $advmindmap->id;
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function advmindmap_update_instance($advmindmap) {
    global $DB;
    $advmindmap->timemodified = time();
    $advmindmap->id = $advmindmap->instance;
    
    if(isset($advmindmap->editable)) {
        $advmindmap->editable = '1';
    } else {
        $advmindmap->editable = '0';
    }
    
    if ($advmindmap->numdummygroups == 0) {
        // delete all previous created mindmap instances
        $DB->delete_records("advmindmap_instances", array("advno"=>$advmindmap->id, "userid"=>"0", "groupid"=>"0"));
    } else {
        $params = array($advmindmap->id, 0, 0);
        if ($alldummyinstances = $DB->get_records_select("advmindmap_instances", "advno = ? AND userid = ? AND groupid = ?", $params, "id ASC")) {
            if (count($alldummyinstances) > $advmindmap->numdummygroups) {
                // set lower number of dummies, so delete existing
                $numinstancestodelete = count($alldummyinstances) - $advmindmap->numdummygroups;
                while ($numinstancestodelete > 0) {
                    $deleteinstance = array_pop($alldummyinstances);
                    $DB->delete_records("advmindmap_instances", array("id"=>$deleteinstance->id));
                    $numinstancestodelete--;
                }
            } else if ($advmindmap->numdummygroups > count($alldummyinstances)) {
                // set more number of dummies, add new
                $numinstancestoadd = $advmindmap->numdummygroups - count($alldummyinstances);
                for ($i=0; $i<$numinstancestoadd; $i++) {
                    $dummy_mindmap_instance = advmindmap_set_new_instance($advmindmap);
                    $dummy_mindmap_instance->name = get_string('group').' '.(count($alldummyinstances)+$i+1);
                    $dummy_mindmap_instance->id = $DB->insert_record("advmindmap_instances", $dummy_mindmap_instance);
                }
            }
        } else {
            if ($advmindmap->numdummygroups > 0) {
                for ($i=0; $i<$advmindmap->numdummygroups; $i++) {
                    $dummy_mindmap_instance = advmindmap_set_new_instance($advmindmap);
                    $dummy_mindmap_instance->name = get_string('group').' '.($i+1);
                    $dummy_mindmap_instance->id = $DB->insert_record("advmindmap_instances", $dummy_mindmap_instance);
                }
            }
        }
    }
    
    $advmindmap->timemodified = time();
    
    return $DB->update_record("advmindmap", $advmindmap);
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function advmindmap_delete_instance($id) {
    global $DB;
    
    if (! $advmindmap = $DB->get_record("advmindmap", array("id"=>$id))) {
        return false;
    }
    
    $result = true;
    
    if (! $DB->delete_records("advmindmap_instances", array("advno"=>$advmindmap->id))) {
        $result = false;
    }
    
    if (! $DB->delete_records("advmindmap", array("id"=>$advmindmap->id))) {
        $result = false;
    }
    
    return $result;
}

// Update the per-user instance
function advmindmap_update_user_instance($advmindmap_instance) {
    global $DB, $USER;
    
    $advmindmap_instance->timemodified = time();
    
    $advmindmap_instance->useraccessed = $USER->id;
    $advmindmap_instance->timeaccessed = time();
    
    if(isset($advmindmap_instance->editable)) {
        $advmindmap_instance->editable = '1';
    } else {
        $advmindmap_instance->editable = '0';
    }
    
    return $DB->update_record("advmindmap_instances", $advmindmap_instance);
}

/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function advmindmap_user_outline($course, $user, $mod, $advmindmap) {
    global $DB;
    $params = array($user->id, 'advmindmap', 'view', $advmindmap->id);
    if ($logs = $DB->get_records_select("log", "userid = ? AND module = ? AND action = ? AND info = ?", $params, "time ASC")) {
        
        $numviews = count($logs);
        $lastlog = array_pop($logs);
        
        $result = new object();
        $result->info = get_string("numviews", "", $numviews);
        $result->time = $lastlog->time;
        
        return $result;
    }
    
    return null;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function advmindmap_user_complete($course, $user, $mod, $newmodule) {
    global $CFG, $DB;
    $params = array($user->id, 'advmindmap', 'view', $newmodule->id);
    if ($logs = $DB->get_records_select("log", "userid = ? AND module = ? AND action = ? AND info = ?", $params, "time ASC")) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);
        
        $strmostrecently = get_string("mostrecently");
        $strnumviews = get_string("numviews", "", $numviews);
        
        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);
    } else {
        print_string("neverseen", "resource");
    }

    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in newmodule activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function advmindmap_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function advmindmap_cron () {
    global $CFG;

    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $newmoduleid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function advmindmap_grades($newmoduleid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of newmodule. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $newmoduleid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function advmindmap_get_participants($newmoduleid) {
    return false;
}

/**
 * This function returns if a scale is being used by one newmodule
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $newmoduleid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function advmindmap_scale_used ($newmoduleid,$scaleid) {
    $return = false;

    //$rec = get_record("newmodule","id","$newmoduleid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

/**
 * Checks if scale is being used by any instance of newmodule.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any newmodule
 */
function advmindmap_scale_used_anywhere($scaleid) {
    global $DB;
    /*
    if ($scaleid and $DB->record_exists('advmindmap', array('grade'=>-$scaleid))) {
        return true;
    } else {
        return false;
    }
    */
    return false;
}

/**
 * Create a new advmindmap instance with default values copy from advmindmap object
 *
 * @return object
 */
function advmindmap_set_new_instance($advmindmap) {
    
    $instance = new stdClass();
    $instance->advno = $advmindmap->id;
    $instance->introformat = $advmindmap->introformat;
    if (isset($advmindmap->editable)) {
        $instance->editable = '1';
    } else {
        $instance->editable = '0';
    }
    $instance->userid = 0;
    $instance->timecreated = time();
    $instance->timemodified = 0;
    $instance->timeaccessed = 0;
    $instance->useraccessed = 0;
    
    $instance->xmldata = '<MindMap>		
     <MM>
       <Node x_Coord="400" y_Coord="270">
         <Text>Moodle</Text>
         <Format Underlined="0" Italic="0" Bold="0">
           <Font>Trebuchet MS</Font>
           <FontSize>14</FontSize>
           <FontColor>ffffff</FontColor>
           <BackgrColor>ff0000</BackgrColor>
         </Format>
       </Node>
     </MM>
    </MindMap>';
    return $instance;
}

function advmindmap_add_access_record($instance) {
    global $DB, $USER;
    
    $instance->useraccessed = $USER->id;
    $instance->timeaccessed = time();
    
    return $DB->update_record("advmindmap_instances", $instance);
}

function advmindmap_clear_access_record($instance) {
    global $DB;
    
    $instance->useraccessed = 0;
    $instance->timeaccessed = 0;
    
    return $DB->update_record("advmindmap_instances", $instance);
}

function advmindmap_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_ADVANCED_GRADING:        return false;

        default: return null;
    }
}

?>
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
 * advmindmap view page
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Andy Chan <ctchan.andy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a = optional_param('a', 0, PARAM_INT);  // newmodule ID

$viewuser = optional_param('viewuser', 0, PARAM_INT);  // view other 
$viewgroup = optional_param('viewgroup', 0, PARAM_INT);  // view other 
$viewdummy = optional_param('viewdummy', 0, PARAM_INT);  // view dummy group 
$group = optional_param('group', 0, PARAM_INT);  // group id

if ($id) {
    if (! $cm = $DB->get_record("course_modules", array("id"=>$id))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
        print_error('coursemisconf', 'advmindmap');
    }
    if (! $advmindmap = $DB->get_record("advmindmap", array("id"=>$cm->instance))) {
        print_error('invalidid', 'advmindmap');
    }
} else {
    if (! $advmindmap = $DB->get_record("advmindmap", array("id"=>$a))) {
        print_error('invalidid', 'advmindmap');
    }
    if (! $course = $DB->get_record("course", array("id"=>$advmindmap->course))) {
        print_error('coursemisconf', 'advmindmap');
    }
    if (! $cm = get_coursemodule_from_instance("advmindmap", $advmindmap->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

//add_to_log($course->id, "advmindmap", "view", "view.php?id=".$cm->id, $advmindmap->id, $cm->id);

$eventdata = array();
$eventdata['objectid'] = $advmindmap->id;
$eventdata['context'] = $context;
$eventdata['courseid'] = $course->id;
$eventdata['other'] = array("content" => "advmindmapactivityview", "group"=>$group, "viewuser"=>$viewuser, "viewgroup"=>$viewgroup, "viewdummy"=>$viewdummy);

$event = \mod_advmindmap\event\course_module_viewed::create($eventdata);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('advmindmap', $advmindmap);
$event->add_record_snapshot('course_modules', $cm);
$event->trigger();

$url = new moodle_url('/mod/advmindmap/view.php');
$url->param('id', $id);
$url->param('a', $a);
$url->param('viewuser', $viewuser);
$url->param('viewgroup', $viewgroup);
$url->param('group', $group);

$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->set_context($context);

// check course module group mode and get groups
$groupmode = groups_get_activity_groupmode($cm, $course);
if ($groupmode) {
    // get all group mind maps
    $params = array($advmindmap->id, 0);
    $allgroupmindmaps = $DB->get_records_select("advmindmap_instances", "advno = ? AND groupid <> ?", $params);
    if ($allowgroups = groups_get_activity_allowed_groups($cm)) {
        // Gets a list of groups that the user is allowed to access
        $allowgids = array_keys($allowgroups);
        $usergroups = array();
        // Check if user is member of any group
        foreach ($allowgids as $gid) {
            if (groups_is_member($gid, $USER->id)) {
                $usergroups[] = $gid;
            }
        }
        // Group mind maps that this user is a member
        $caneditmindmaps = array();
        foreach ($allgroupmindmaps as $agm) {
            if (in_array($agm->groupid, $usergroups)) {
                $caneditmindmaps[$agm->id] = $agm;
            }
        }
        
        // if user is not in any group and cannot view other people mind map = they cannot view anything
        if (empty($usergroups) && !has_capability('mod/advmindmap:viewother', $context)) {
            print_error('errornotingroup', 'advmindmap');
        }
    } else {
        // no group this user can view
        print_error('errornotingroup', 'advmindmap');
    }
}

// Initially user cannot see anything before permission checking
$canedit = false;

// Preparation: check if any mindmaps for current user, if not, add one for them
if (!has_capability('mod/advmindmap:givecomment', $context)) { // not teacher
    if ($groupmode) { // group mind map
        // A user may in multiple groups
        $params = array();
        $query_params = array('advno'=>$advmindmap->id);
        if (!empty($usergroups)) {
        	list($in_sql, $in_params) = $DB->get_in_or_equal($usergroups, SQL_PARAMS_NAMED);
        } else {
            print_error('errornotingroup', 'advmindmap');
        }
        $params = array_merge($in_params, $query_params);
        $advmindmap_instances = $DB->get_records_select("advmindmap_instances", "advno = :advno AND groupid $in_sql", $params);
        if (!$advmindmap_instances) {
            // create an mind map instance for each group the user is in
            foreach ($usergroups as $ug) {
                $advmindmap_instance = advmindmap_set_new_instance($advmindmap);
                $advmindmap_instance->groupid = $ug;
                $advmindmap_instance->id = $DB->insert_record("advmindmap_instances", $advmindmap_instance);
            }
            // mindmap created, let's reload
            header('Location: view.php?id='.$id);
        }
        // if user want to see other group, check the permission
        if ($viewgroup) {
            if (!has_capability('mod/advmindmap:viewother', $context)) {
                foreach ($advmindmap_instances as $ami) {
                    if ($ami->id == $viewgroup) {
                        $matchgroup = true;
                        break;
                    }
                }
                if (!$matchgroup || $groupmode == 1) print_error('errorcannotviewgroupmindmap', 'advmindmap');
            }
        }
    } else if (!$advmindmap->numdummygroups) { // individual mind map, view own mindmap
        $advmindmap_instance = $DB->get_record("advmindmap_instances", array("advno"=>$cm->instance, "userid"=>$USER->id));
        if (!$advmindmap_instance) {
            $advmindmap_instance = advmindmap_set_new_instance($advmindmap);
            $advmindmap_instance->userid = $USER->id;
            $advmindmap_instance->id = $DB->insert_record("advmindmap_instances", $advmindmap_instance);
            // mindmap created, let's reload
            header('Location: view.php?id='.$id);
        }
        if($viewuser) {
            if (!has_capability('mod/advmindmap:viewother', $context)) {
                print_error('errorcannotviewusermindmap', 'advmindmap');
            }
            $advmindmap_instance = $DB->get_record("advmindmap_instances", array("advno"=>$cm->instance, "id"=>$viewuser));
        }
    }
} else { // teacher or overrided permission
    if ($groupmode && $viewgroup) { // group mind map, view other mind map
        if (!$advmindmap_instance = $DB->get_record("advmindmap_instances", array("id"=>$viewgroup))) {
            print_error("The selected group have no mindmaps");
        }
    } else if (!$groupmode && !$advmindmap->numdummygroups && $viewuser) {// individual mind map, view other mindmap
        if (!$advmindmap_instance = $DB->get_record("advmindmap_instances", array("id"=>$viewuser))) {
            print_error("The selected user have no mindmaps");
        }
    } else if (!$advmindmap->numdummygroups) { // individual mind map, view own mindmap
        if (!$advmindmap_instance = $DB->get_record("advmindmap_instances", array("advno"=>$cm->instance, "userid"=>$USER->id))) {
            $advmindmap_instance = advmindmap_set_new_instance($advmindmap);
            $advmindmap_instance->userid = $USER->id;
            $advmindmap_instance->id = $DB->insert_record("advmindmap_instances", $advmindmap_instance);
            // mindmap created, let's reload
            header('Location: view.php?id='.$id);
        }
    }
}

if ($advmindmap->numdummygroups > 0 && $viewdummy) {
    $advmindmap_instance = $DB->get_record("advmindmap_instances", array("id"=>$viewdummy));
}
// end adding map

/// Print the page header
$stradvmindmaps = get_string("modulenameplural", "advmindmap");
$stradvmindmap  = get_string("modulename", "advmindmap");

$courseshortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));
$title = $courseshortname . ': ' . format_string($advmindmap->name);

$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

echo $OUTPUT->box_start();
if ($advmindmap->editable == '0') {
    echo html_writer::tag('p', get_string('uneditable', 'advmindmap'), array('style', 'color:red;font-weight:bold;'));
}

// Check if user have permission to edit mindmap, display a message if viewing others' mindmap
if ($groupmode) {
    if (!has_capability('mod/advmindmap:givecomment', $context)) {
        if (!$viewgroup) {
            $advmindmap_instance = current($caneditmindmaps);
        } else {
            $advmindmap_instance = $allgroupmindmaps[$viewgroup];
        }
        echo "<p>".get_string('viewingauthor', 'advmindmap', '<strong>'.groups_get_group_name($advmindmap_instance->groupid).'</strong>');
        if (in_array($advmindmap_instance->groupid, $usergroups)) {
            echo "&nbsp;&nbsp;".get_string('ismember', 'advmindmap').'</p>';
            $canedit = true;
        }
    } else {
        if ($viewgroup) {
            $advmindmap_instance = $allgroupmindmaps[$viewgroup];
            echo "<p>".get_string('viewingauthor', 'advmindmap', '<strong>'.groups_get_group_name($advmindmap_instance->groupid).'</strong>');
            echo "&nbsp;(<a href='view.php?id=$id'>".get_string('viewown', 'advmindmap')."</a>)";
        } else {
            $canedit = true;
        }
    }
} else {
    if ($advmindmap->numdummygroups > 0) {
        if ($viewdummy) {
            echo "<p>".get_string('viewingauthor', 'advmindmap', '<strong>'.$advmindmap_instance->name.'</strong>')."</p>";
            $canedit = true;
        }
    } else {
        if ($advmindmap_instance->userid != $USER->id) {
            // viewing other user mindmap
            if ($author = $DB->get_record('user', array('id'=>$advmindmap_instance->userid))) {
                echo "<p>".get_string('viewingauthor', 'advmindmap', '<strong>'.fullname($author).'</strong>');
                echo "&nbsp;(<a href='view.php?id=$id'>".get_string('viewown', 'advmindmap')."</a>)";
                echo "</p>";
            } else {
                print_error('errorinvalidauthor', 'advmindmap');
            }
        } else {
            $canedit = true;
        }
    }
}

if (!empty($advmindmap->intro)) {
    // Issue #7: suggestion by rabser
    echo $OUTPUT->box(format_module_intro('advmindmap', $advmindmap, $cm->id), 'generalbox', 'intro');
}

if (isset($advmindmap_instance)) {
    echo "<p>".get_string('lastupdated', 'advmindmap').date("Y-n-j H:i", $advmindmap_instance->timemodified?$advmindmap_instance->timemodified:$advmindmap_instance->timecreated)."</p>";
}

// Locking: user can still occupy the mindmap forever if they reload the page
$islocked = false;
if ($groupmode && $canedit && !has_capability('mod/advmindmap:givecomment', $context)) {
    $locktime = 3600; // 1 hour
    $now = time();
    $waitingtime = 3600- ($now - $advmindmap_instance->timeaccessed);
    $wmin = floor($waitingtime/60);
    $wsec = $waitingtime%60;
    if ($advmindmap_instance->useraccessed && ($advmindmap_instance->useraccessed != $USER->id)) {
        if ($now - $advmindmap_instance->timeaccessed < 3600) {
            $islocked = true;
            $canedit = false;
            $editinguser = $DB->get_record('user', array('id'=>$advmindmap_instance->useraccessed));
            echo "<p style='color:red;'><img src='". $PAGE->theme->name ."/pix/i/lock.gif'/> ".get_string('editingbyuser', 'advmindmap', fullname($editinguser))."</p>";
            echo "<p style='color:red;'>".get_string('unlocktime', 'advmindmap')."<span id='countdown'></span></p>";
        }
    } else {
        advmindmap_add_access_record($advmindmap_instance);
        echo "<p><img src='".$PAGE->theme->name."/pix/i/lock.gif'/> ".get_string('lockedbyyou', 'advmindmap');
        echo "<span style='font-weight:bold; font-size:2.0em;'>&rarr;<span> <input type='button' value='".get_string('unlockbutton', 'advmindmap')."' onclick='unlockmindmap()' /></p>";
    }
}
    
echo $OUTPUT->box_end();

// Construct a unique link for copying
$uniquelink = $CFG->wwwroot."/mod/advmindmap/view.php?id=$id";
if ($viewuser) $uniquelink .= "&viewuser=$viewuser";
else if ($viewgroup) $uniquelink .= "&viewgroup=$viewgroup";
else if ($viewdummy) $uniquelink .= "&viewdummy=$viewdummy";
else if (isset($advmindmap_instance)) {
    if ($groupmode) $uniquelink .= "&viewgroup=".$advmindmap_instance->id;
    else $uniquelink .= "&viewuser=".$advmindmap_instance->id;
}
?>
<div id="flashcontent"></div>
<?php if (isset($advmindmap_instance)) { // Display link for copying and help icon ?>
<div style="width:85%; margin:5px auto; font-size:0.8em;">
    <?php echo get_string('uniquelink', 'advmindmap'); ?>: <input type="text" id="uniquelink" onclick="selectAll('uniquelink');" style="width:500px;" value="<?php echo $uniquelink; ?>" /> 
    <?php echo get_string('copylink', 'advmindmap'); ?>
    <div style="float:right;">
    <?php
        echo '<span class="helplink">';
        echo $OUTPUT->help_icon('detail', 'advmindmap');
        echo '</span>';
    ?>
    </div>
</div>
<?php } ?>
<script type="text/javascript" src="./swfobject.js"></script>
<script type="text/javascript">
    // <![CDATA[
    function selectAll(id) {
        document.getElementById(id).focus();
        document.getElementById(id).select();
    }
    
    function mm_save(str) {
        alert(decodeURI(str));
    }
    
    var so = new SWFObject("./viewer.swf", "viewer", 800, 600, "9", "#FFFFFF");
    <?php if(! $viewuser) { ?>
    so.addVariable("load_url", "./xml.php?id=<?php echo $advmindmap_instance->id;?>");		
    <?php } else { ?>
    so.addVariable("load_url", "./xml.php?id=<?php echo $viewuser;?>");		
    <?php } ?>			
    
    <?php if ($canedit && $advmindmap->editable == '1') {?> // now we check the global setting by teacher instead of the instance
    so.addVariable('save_url', "./save.php?id=<?php echo $advmindmap_instance->id;?>");
    so.addVariable('editable', "true");
    <?php } else { ?>
    so.addVariable('editable', "false");
    <?php } ?>
    so.addVariable("lang", "en");
    <?php if(isset($advmindmap_instance)){ ?>
    so.write("flashcontent");
    <?php } ?>
    <?php if ($islocked) { ?>
    var interval;
    var minutes = <?php echo $wmin; ?>;
    var seconds = <?php echo $wsec; ?>;
    window.onload = function() {
        countdown('countdown');
    }
    <?php } ?>
    function countdown(element) {
        interval = setInterval(function() {
            var el = document.getElementById(element);
            if(seconds == 0) {
                if(minutes == 0) {
                    el.innerHTML = "00m 00s";
                    clearInterval(interval);
                    location.reload(true);
                    return;
                } else {
                    minutes--;
                    seconds = 60;
                }
            }
            if(minutes > 0) {
                var minute_text = minutes + (minutes > 1 ? ' m' : ' m');
            } else {
                var minute_text = '';
            }
            var second_text = seconds > 1 ? 's' : 's';
            el.innerHTML = minute_text + ' ' + seconds + ' ' + second_text;
            seconds--;
        }, 1000);
    }
    
    function unlockmindmap() {
        if (confirm('<?php echo get_string('unlockconfirm', 'advmindmap') ?>')) {
            location.href = 'unlock.php?id=<?php echo $id ?>&instanceid=<?php echo $advmindmap_instance->id ?>';
        }
    }
    // ]]>
</script>
<?php
// Only show a table listing other mindmaps if user have the permission
if (has_capability('mod/advmindmap:viewother', $context)) {
    $table = new html_table();
    if ($groupmode == 1 || $groupmode == 2) { // group mind map
        if (!$advmindmaps = $DB->get_records_sql("SELECT groupid, id, timecreated, timemodified FROM {advmindmap_instances} WHERE advno = ?", array($advmindmap_instance->advno))) {
            notice("No advmindmap was created yet.", "../../course/view.php?id=$course->id");
            echo $OUTPUT->footer();
            die;
        }
        $strname = get_string("groupname", "advmindmap");
        $strmembers = get_string("groupmembers", "advmindmap");
        $strdate  = get_string("lastupdated", "advmindmap");
        
        $table->head  = array($strname, $strmembers, $strdate);
        $table->align = array("left", "left", "left");
        
        foreach ($allowgroups as $ag) {
            // Only display link and last updated date if a mindmap was init,
            // else only display a name without link
            
            // group member list
            $members = groups_get_members($ag->id);
            if (empty($members)) {
                $memberlist = get_string('notavailable', 'advmindmap');
            } else {
                $firstmember = array_shift($members);
                $memberlist = fullname($firstmember);
                if (count($members) > 0) {
                    foreach ($members as $m) {
                        $memberlist .= ", ".fullname($m);
                    }
                }
            }
            
            if (in_array($ag->id, array_keys($advmindmaps))) {
                if ($advmindmap_instance->id == $advmindmaps[$ag->id]->id) {
                    $link = $ag->name." ".get_string('viewing', 'advmindmap');
                } else {
                    $link = "<a href=\"view.php?id=$cm->id&viewgroup=".$advmindmaps[$ag->id]->id."\">".$ag->name."</a>";
                }
                $lastupdated = userdate($advmindmaps[$ag->id]->timemodified?$advmindmaps[$ag->id]->timemodified:$advmindmaps[$ag->id]->timecreated);
                $table->data[] = array($link, $memberlist, $lastupdated);
            } else {
                $table->data[] = array($ag->name, $memberlist, get_string('notavailable', 'advmindmap'));
            }
        }
        echo "<br />";
        echo html_writer::table($table);
    } else if ($groupmode == 0) { // individual mind map or dummy group mind map
        if ($advmindmap->numdummygroups > 0) {
            $params = array($advmindmap->id, 0, 0);
            $dummymindmaps = $DB->get_records_select('advmindmap_instances', 'advno = ? AND userid = ? AND groupid = ?', $params, 'id ASC');
            if ($dummymindmaps) {
                $strgroup = get_string("group");
                $strdate  = get_string("lastupdated", "advmindmap");
                $table->head  = array($strgroup, $strdate);
                $table->align = array("left", "left");
                
                foreach ($dummymindmaps as $dm) {
                    $link = html_writer::link("view.php?id=".$cm->id."&viewdummy=".$dm->id, $dm->name);
                    $lastupdated = userdate($dm->timemodified?$dm->timemodified:$dm->timecreated);
                    $table->data[] = array($link, $lastupdated);
                }
                echo "<br />";
                echo html_writer::table($table);
            }
        } else {
            if (!$advmindmaps = $DB->get_records_sql("SELECT userid, groupid, id, timecreated, timemodified FROM {advmindmap_instances} WHERE advno = ?", array($advmindmap_instance->advno))) {
                notice("No advmindmap was created yet.", "../../course/view.php?id=$course->id");
                echo $OUTPUT->footer();
                die;
            }
            // Issue #6: suggestion by rabser
            // Get all students in the course able to view mindmaps
            $context = context_course::instance($course->id);
            $students = get_enrolled_users($context);
            if (count($students) > 0) {
                $students = array_keys($students);
            } else {
                notice(get_string('errornostudentincourse', 'advmindmap'));
                echo $OUTPUT->footer();
                die;
            }
            
            // DIRTY HACK: create a temp course object just to generate this menu
            $hack_course = $course;
            $hack_course->groupmode = VISIBLEGROUPS;
            if ($visible_groups = groups_print_course_menu($hack_course, "view.php?id=$id&viewuser=$viewuser")) {
                echo $OUTPUT->box_start();
                groups_print_course_menu($hack_course, "view.php?id=$id&viewuser=$viewuser");
                echo $OUTPUT->box_end();
            }
            
            $strname = get_string("name");
            $strclass = get_string("class", "advmindmap");
            $strclassno = get_string("classno", "advmindmap");
            $strdate  = get_string("lastupdated", "advmindmap");
            
            $table->head  = array ($strname, $strclass, $strclassno, $strdate);
            $table->align = array ("left", "left", "left", "left");
            
            if ($group) {
                if ($group_members = groups_get_members($group, 'u.id')) {
                    $group_members = array_keys($group_members);
                    $students = array_intersect($students, $group_members);
                } else {
                    print_error('errornouseringroup', "advmindmap");
                    echo $OUTPUT->footer();
                    exit;
                }
            }
            
            foreach ($students as $studentid) {
                $user = $DB->get_record("user", array("id"=>$studentid));
                $params = array($studentid, 'class');
                if (!$class = $DB->get_field_sql("SELECT data FROM {user_info_data} WHERE userid = ? AND fieldid = (SELECT id FROM {user_info_field} WHERE shortname = ?)", $params)) {
                    $class = '---';
                }
                $params = array($studentid, 'classno');
                if (!$classno = $DB->get_field_sql("SELECT data FROM {user_info_data} WHERE userid = ? AND fieldid = (SELECT id FROM {user_info_field} WHERE shortname = ?)", $params)) {
                    $classno = '---';
                }
                
                // Only display link and last updated date if a mindmap was init,
                // else only display a name without link
                if (in_array($studentid, array_keys($advmindmaps))) {
                    $link = "<a href=\"view.php?id=$cm->id&group=$group&viewuser=".$advmindmaps[$studentid]->id."\">".fullname($user)."</a>";
                    $lastupdated = userdate($advmindmaps[$studentid]->timemodified?$advmindmaps[$studentid]->timemodified:$advmindmaps[$studentid]->timecreated);
                    $table->data[] = array($link, $class, $classno, $lastupdated);
                } else {
                    $table->data[] = array(fullname($user), $class, $classno, get_string('notavailable', 'advmindmap'));
                }
            }
            echo "<br />";
            echo html_writer::table($table);
        }
    }
}

echo $OUTPUT->footer($course);
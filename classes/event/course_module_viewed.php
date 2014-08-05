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
 * This file contains an event for when a advmindmap activity is viewed.
 *
 * @package    mod_advmindmap
 * @copyright  2014 Andy Chan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_advmindmap\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Event for when a advmindmap activity is viewed.
 *
 * @package    mod_advmindmap
 * @copyright  2014 Andy Chan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_viewed extends \core\event\course_module_viewed {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'advmindmap';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $param = '';
        if ($this->other['group']) $param .= 'group id='.$this->other['group'].', ';
        if ($this->other['viewuser']) $param .= 'viewuser='.$this->other['viewuser'].', ';
        if ($this->other['viewgroup']) $param .= 'viewgroup='.$this->other['viewgroup'].', ';
        if ($this->other['viewdummy']) $param .= 'viewdummy='.$this->other['viewdummy'];
        if (substr($param, strlen($param)-2) == ', ') $param = substr($param, 0, strlen($param)-2);
        if (!empty($param)) $param = ' ('.$param.')';
        return 'User with id ' . $this->userid . ' viewed advmindmap activity with instance id ' . $this->objectid. $param;
    }

    /**
     * replace add_to_log() statement.
     *
     * @return array of parameters to be passed to legacy add_to_log() function.
     */
    protected function get_legacy_logdata() {
        $param = array();
        $param['id'] = $this->contextinstanceid;
        if ($this->other['group']) $param['group'] = $this->other['group'];
        if ($this->other['viewuser']) $param['viewuser'] = $this->other['viewuser'];
        if ($this->other['viewgroup']) $param['viewgroup'] = $this->other['viewgroup'];
        if ($this->other['viewdummy']) $param['viewdummy'] = $this->other['viewdummy'];
        $url = new \moodle_url('view.php', $param);
        return array($this->courseid, 'advmindmap', 'view', $url->out(), $this->objectid, $this->contextinstanceid);
    }
}

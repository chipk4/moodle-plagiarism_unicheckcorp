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
 * unicheck_abstract_event.class.php
 *
 * @package     plagiarism_unicheck
 * @subpackage  plagiarism
 * @author      Vadim Titov <v.titov@p1k.co.uk>
 * @copyright   UKU Group, LTD, https://www.unicheck.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace plagiarism_unicheck\classes\event;

use core\event\base;
use plagiarism_unicheck\classes\unicheck_api;
use plagiarism_unicheck\classes\unicheck_assign;
use plagiarism_unicheck\classes\unicheck_core;
use plagiarism_unicheck\classes\unicheck_plagiarism_entity;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class unicheck_abstract_event
 *
 * @package plagiarism_unicheck\classes\event
 */
abstract class unicheck_abstract_event {
    /** @var */
    protected static $instance;
    /** @var array */
    protected $tasks = array();
    /** @var unicheck_core */
    protected $core;

    /**
     * @return static
     */
    public static function instance() {
        $class = get_called_class();

        if (!isset(static::$instance[$class])) {
            static::$instance[$class] = new static;
        }

        return static::$instance[$class];
    }

    /**
     * @param base $event
     *
     * @return bool
     */
    public static function is_submition_draft(base $event) {
        global $CFG;

        if ($event->objecttable != 'assign_submission') {
            return false;
        }

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $submission = unicheck_assign::get_user_submission_by_cmid($event->contextinstanceid);
        if (!$submission) {
            return true;
        }

        return ($submission->status !== 'submitted');
    }

    /**
     *
     */
    protected function after_handle_event() {
        if (empty($this->tasks)) {
            // Skip this file check cause assign is draft.
            return;
        }

        foreach ($this->tasks as $plagiarismentity) {
            if ($plagiarismentity instanceof unicheck_plagiarism_entity) {
                $internalfile = $plagiarismentity->get_internal_file();
                if (isset($internalfile->external_file_id) && !isset($internalfile->check_id)) {
                    $checkresp = unicheck_api::instance()->run_check($internalfile);
                    $plagiarismentity->handle_check_response($checkresp);
                }
            }
        }
    }

    /**
     * @param $plagiarismentity
     */
    protected function add_after_handle_task($plagiarismentity) {
        array_push($this->tasks, $plagiarismentity);
    }

    /**
     * @param unicheck_core $core
     * @param base          $event
     */
    abstract public function handle_event(unicheck_core $core, base $event);
}
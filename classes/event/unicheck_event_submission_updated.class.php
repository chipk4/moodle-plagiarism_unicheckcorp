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
 * unicheck_event_file_submited.class.php
 *
 * @package     plagiarism_unicheck
 * @subpackage  plagiarism
 * @author      Aleksandr Kostylev <a.kostylev@p1k.co.uk>
 * @copyright   UKU Group, LTD, https://www.unicheck.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_unicheck\classes\event;

use core\event\base;
use plagiarism_unicheck\classes\unicheck_assign;
use plagiarism_unicheck\classes\unicheck_core;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class unicheck_event_submission_updated
 *
 * @package   plagiarism_unicheck\classes\event
 * @namespace plagiarism_unicheck\classes\event
 *
 */
class unicheck_event_submission_updated extends unicheck_abstract_event {
    const DRAFT_STATUS = 'draft';

    /**
     * @param unicheck_core $core
     * @param base          $event
     *
     * @return bool
     */
    public function handle_event(unicheck_core $core, base $event) {

        global $DB;
        if (!isset($event->other['newstatus'])) {
            return false;
        }
        $newstatus = $event->other['newstatus'];
        $core->userid = $event->relateduserid;
        if ($newstatus == self::DRAFT_STATUS) {
            $unfiles = \plagiarism_unicheck::get_area_files($event->contextid, UNICHECK_DEFAULT_FILES_AREA, $event->objectid);
            $assignfiles = unicheck_assign::get_area_files($event->contextid, $event->objectid);

            $files = array_merge($unfiles, $assignfiles);

            $ids = array();
            foreach ($files as $file) {
                $plagiarismentity = $core->get_plagiarism_entity($file);
                $internalfile = $plagiarismentity->get_internal_file();
                $ids[] = $internalfile->id;
            }

            $allrecordssql = implode(',', $ids);
            $DB->delete_records_select(UNICHECK_FILES_TABLE, "id IN ($allrecordssql) OR parent_id IN ($allrecordssql)");
        }

        return true;
    }
}
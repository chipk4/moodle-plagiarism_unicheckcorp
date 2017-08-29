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
 * unicheck_event_assessable_submited.class.php
 *
 * @package     plagiarism_unicheck
 * @subpackage  plagiarism
 * @author      Vadim Titov <v.titov@p1k.co.uk>
 * @copyright   UKU Group, LTD, https://www.unicheck.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_unicheck\classes\event;

use core\event\base;
use plagiarism_unicheck;
use plagiarism_unicheck\classes\entities\unicheck_archive;
use plagiarism_unicheck\classes\helpers\unicheck_check_helper;
use plagiarism_unicheck\classes\unicheck_assign;
use plagiarism_unicheck\classes\unicheck_core;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once(dirname(__FILE__) . '/../../locallib.php');

/**
 * Class unicheck_event_file_submited
 *
 * @package plagiarism_unicheck\classes\event
 */
class unicheck_event_assessable_submited extends unicheck_abstract_event {
    /**
     * @param unicheck_core $core
     * @param base          $event
     */
    public function handle_event(unicheck_core $core, base $event) {

        $this->core = $core;

        $submission = unicheck_assign::get_user_submission_by_cmid($event->contextinstanceid);
        $submissionid = (!empty($submission->id) ? $submission->id : false);

        $files = plagiarism_unicheck::get_area_files($event->contextid, UNICHECK_DEFAULT_FILES_AREA, $submissionid);
        $assignfiles = unicheck_assign::get_area_files($event->contextid, $submissionid);

        $files = array_merge($files, $assignfiles);
        if (!empty($files)) {
            foreach ($files as $file) {
                $this->handle_file_plagiarism($file);
            }
        }
    }

    /**
     * @param \stored_file $file
     *
     * @return bool
     */
    private function handle_file_plagiarism(\stored_file $file) {
        if (\plagiarism_unicheck::is_archive($file)) {
            $archive = new unicheck_archive($file, $this->core);
            $archive->run_checks();

            return true;
        }

        $plagiarismentity = $this->core->get_plagiarism_entity($file);

        return unicheck_check_helper::upload_and_run_detection($plagiarismentity);
    }
}
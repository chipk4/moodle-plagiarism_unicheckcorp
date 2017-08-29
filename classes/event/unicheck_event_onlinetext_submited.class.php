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
 * unicheck_event_onlinetext_submited.class.php
 *
 * @package     plagiarism_unicheck
 * @subpackage  plagiarism
 * @author      Vadim Titov <v.titov@p1k.co.uk>
 * @copyright   UKU Group, LTD, https://www.unicheck.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_unicheck\classes\event;

use core\event\base;
use plagiarism_unicheck\classes\unicheck_core;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class unicheck_event_onlinetext_submited
 *
 * @package plagiarism_unicheck\classes\event
 */
class unicheck_event_onlinetext_submited extends unicheck_abstract_event {
    /**
     * @param unicheck_core $core
     * @param base          $event
     */
    public function handle_event(unicheck_core $core, base $event) {
        if (empty($event->other['content'])) {
            return;
        }

        $file = $core->create_file_from_content($event);

        if (self::is_submition_draft($event)) {
            return;
        }

        if ($file) {
            $plagiarismentity = $core->get_plagiarism_entity($file);
            $plagiarismentity->upload_file_on_server();
            $this->add_after_handle_task($plagiarismentity);
        }

        $this->after_handle_event();
    }
}
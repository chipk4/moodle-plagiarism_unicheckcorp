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
 * callback_accepted.php
 *
 * @package     plagiarism_unicheck
 * @subpackage  plagiarism
 * @author      Aleksandr Kostylev <a.kostylev@p1k.co.uk>
 * @copyright   2018 UKU Group, LTD, https://www.unicheck.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_unicheck\event;

use core\event\base;
use plagiarism_unicheck;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once(dirname(__FILE__) . '/../../locallib.php');

/**
 * Class callback_accepted
 *
 * @package     plagiarism_unicheck
 * @subpackage  plagiarism
 *
 * @author      Aleksandr Kostylev <a.kostylev@p1k.co.uk>
 * @copyright   UKU Group, LTD, https://www.unicheck.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since       Moodle 3.3
 */
class callback_accepted extends base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }

    /**
     * Return the event name.
     *
     * @return string
     */
    public static function get_name() {
        return plagiarism_unicheck::trans('event:callback_accepted');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $body = isset($this->other['body']) ? json_encode($this->other['body']) : '-';
        $token = isset($this->other['token']) ? $this->other['token'] : '-';
        $message = "Token: '$token'<br>";
        $message .= "$body";

        return $message;
    }

    /**
     * Creates the event object.
     *
     * @param string $body
     * @param string $token
     * @return base
     */
    public static function create_log_message($body, $token) {
        return self::create([
                'other' => [
                    'body'  => $body,
                    'token' => $token
                ]
            ]
        );
    }
}
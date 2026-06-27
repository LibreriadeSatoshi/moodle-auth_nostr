<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Nostr authentication plugin class.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

class auth_plugin_nostr extends auth_plugin_base {

    public function __construct() {
        $this->authtype = 'nostr';
        $this->config   = get_config('auth_nostr');
    }

    /**
     * Nostr users authenticate via a custom challenge-response endpoint,
     * not username + password.
     */
    public function user_login($username, $password) {
        return false;
    }

    public function is_internal() {
        return false;
    }

    public function prevent_local_passwords() {
        return true;
    }

    public function can_signup() {
        return false;
    }

    /**
     * Inject the "Log in with Nostr" button on the standard login page.
     */
    public function loginpage_hook() {
        global $PAGE;

        $loginurl = (new moodle_url('/auth/nostr/login.php'))->out(false);
        $relay    = get_config('auth_nostr', 'relay') ?: 'wss://relay.damus.io';

        $PAGE->requires->js_call_amd('auth_nostr/nostr_login', 'init', [$loginurl, $relay]);
    }

    public function get_userinfo($username) {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username, 'auth' => 'nostr'], '*', IGNORE_MISSING);
        if (!$user) {
            return false;
        }

        return [
            'username'  => $user->username,
            'email'     => $user->email,
            'firstname' => $user->firstname,
            'lastname'  => $user->lastname,
        ];
    }
}

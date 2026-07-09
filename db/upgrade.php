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
 * Upgrade steps for the Nostr authentication plugin.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion the version we are upgrading from.
 * @return bool
 */
function xmldb_auth_nostr_upgrade($oldversion) {
    if ($oldversion < 2026070901) {
        // The showlog setting was added later; persist its default for
        // existing installs so the admin checkbox matches the actual
        // behaviour (progress log shown by default).
        if (get_config('auth_nostr', 'showlog') === false) {
            set_config('showlog', 1, 'auth_nostr');
        }
        upgrade_plugin_savepoint(true, 2026070901, 'auth', 'nostr');
    }

    return true;
}

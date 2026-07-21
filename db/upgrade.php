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

    if ($oldversion < 2026072100) {
        global $DB;

        // Early releases created the nostrpubkey profile field with param1
        // and param2 swapped: param2 (the real max length in Moodle text
        // profile fields) was 30, so Moodle silently truncated npubs
        // (63 chars) on save. Fix the field definition in place.
        $field = $DB->get_record('user_info_field', ['shortname' => 'nostrpubkey']);
        if ($field && (int) $field->param2 < 70) {
            $field->param1 = 63; // Display size of the input.
            $field->param2 = 70; // Max length: npub = 63 chars, hex pubkey = 64.
            $DB->update_record('user_info_field', $field);
        }

        // Repair already-truncated values. For accounts created by this
        // plugin the username is the full npub, so copy it back into the
        // profile field. Manually-created accounts are left untouched.
        if ($field) {
            $sql = "SELECT d.id, u.username
                      FROM {user_info_data} d
                      JOIN {user} u ON u.id = d.userid
                     WHERE d.fieldid = :fieldid
                       AND u.auth = 'nostr'
                       AND " . $DB->sql_like('u.username', ':npubprefix') . "
                       AND " . $DB->sql_length('d.data') . " < 63";
            $truncated = $DB->get_records_sql($sql, [
                'fieldid'    => $field->id,
                'npubprefix' => 'npub1%',
            ]);
            foreach ($truncated as $record) {
                $DB->set_field('user_info_data', 'data', $record->username, ['id' => $record->id]);
            }
        }

        upgrade_plugin_savepoint(true, 2026072100, 'auth', 'nostr');
    }

    return true;
}

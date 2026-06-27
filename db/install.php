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
 * Post-install hook for auth_nostr.
 *
 * Creates a "Nostr Public Key" custom profile field so users (and admins)
 * can link an existing Moodle account to a Nostr public key.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Creates the Nostr identity custom profile field on install.
 */
function xmldb_auth_nostr_install() {
    global $DB, $CFG;

    // Only create the field if it does not already exist.
    if ($DB->record_exists('user_info_field', ['shortname' => 'nostrpubkey'])) {
        return;
    }

    // Find or create a "Nostr" profile category.
    $category = $DB->get_record('user_info_category', ['name' => 'Nostr']);
    if (!$category) {
        $cat        = new stdClass();
        $cat->name  = 'Nostr';
        $cat->sortorder = ($DB->get_field_sql('SELECT MAX(sortorder) FROM {user_info_category}') ?? 0) + 1;
        $cat->id    = $DB->insert_record('user_info_category', $cat);
        $category   = $cat;
    }

    $field               = new stdClass();
    $field->shortname    = 'nostrpubkey';
    $field->name         = 'Nostr Identity';
    $field->datatype     = 'text';
    $field->description  = 'Your Nostr public key (npub1… or hex). Fill this in to link your Moodle account to your Nostr identity.';
    $field->descriptionformat = FORMAT_HTML;
    $field->categoryid   = $category->id;
    $field->sortorder    = 1;
    $field->required     = 0;
    $field->locked       = 0;
    $field->visible      = 2; // PROFILE_VISIBLE_ALL
    $field->forceunique  = 1;
    $field->signup       = 0;
    $field->defaultdata  = '';
    $field->defaultdataformat = FORMAT_PLAIN;
    $field->param1       = 70; // max length (npub = 63 chars)
    $field->param2       = 63; // display size
    $field->param3       = '';
    $field->param4       = '';
    $field->param5       = '';

    $DB->insert_record('user_info_field', $field);
}

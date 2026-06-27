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
 * English language strings for the Nostr authentication plugin.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']              = 'Nostr authentication';
$string['auth_nostrname']          = 'Nostr';
$string['auth_nostrdescription']   = 'Authenticate using a Nostr keypair via a browser extension (NIP-07).';

$string['login_with_nostr']        = 'Log in with Nostr';
$string['or']                      = 'or';
$string['status_looking']          = 'Looking for Nostr extension…';
$string['status_pubkey']           = 'Requesting public key…';
$string['status_profile']          = 'Fetching your Nostr profile…';
$string['status_challenge']        = 'Requesting login challenge…';
$string['status_signing']          = 'Signing login request…';
$string['status_verifying']        = 'Verifying with server…';
$string['status_success']          = 'Logged in! Redirecting…';

$string['error_no_extension']      = 'No Nostr extension found. Please install Alby or nos2x.';
$string['error_extension_denied']  = 'Extension denied access.';
$string['error_signing_cancelled'] = 'Signing was cancelled.';
$string['error_challenge']         = 'Failed to get login challenge. Please reload and try again.';
$string['error_network']           = 'Network error. Please try again.';
$string['error_login_failed']      = 'Login failed. Please try again.';

$string['relay']                   = 'Nostr relay';
$string['relay_desc']              = 'WebSocket relay URL used to fetch Nostr profile metadata (kind 0). Used only to populate the display name on first login.';
$string['autocreate']              = 'Auto-create accounts';
$string['autocreate_desc']         = 'Automatically create a Moodle account for any valid Nostr public key on first login.';

$string['privacy:metadata']        = 'The Nostr authentication plugin does not store any personal data beyond what Moodle core stores in the standard user account.';

$string['gmprequired']             = 'The GMP PHP extension is required by the Nostr authentication plugin (auth_nostr) to verify Schnorr signatures.';

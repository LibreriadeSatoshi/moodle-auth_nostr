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
 * Tests for the Nostr event helpers.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \auth_nostr\nostr_event
 */

namespace auth_nostr;

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Unit tests for {@see nostr_event}.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(nostr_event::class)]
final class nostr_event_test extends \basic_testcase {
    /**
     * Hex pubkey → npub conversion matches the NIP-19 reference example.
     */
    public function test_pubkey_to_npub(): void {
        $hex = '3bf0c63fcb93463407af97a5e5ee64fa883d107ef9e558472c4eb9aaaefa459d';
        $this->assertSame(
            'npub180cvv07tjdrrgpa0j7j7tmnyl2yr6yr7l8j4s3evf6u64th6gkwsyjh6w6',
            nostr_event::pubkey_to_npub($hex)
        );
    }

    /**
     * The shortened npub keeps the prefix and the last 4 characters.
     */
    public function test_pubkey_to_npub_short(): void {
        $hex = '3bf0c63fcb93463407af97a5e5ee64fa883d107ef9e558472c4eb9aaaefa459d';
        $this->assertSame('npub180cvv07t…h6w6', nostr_event::pubkey_to_npub_short($hex));
    }

    /**
     * The event id is the SHA256 of the NIP-01 serialization.
     */
    public function test_compute_id(): void {
        $event = [
            'pubkey'     => str_repeat('ab', 32),
            'created_at' => 1700000000,
            'kind'       => 27235,
            'tags'       => [['challenge', 'abc']],
            'content'    => '',
        ];
        $expected = hash('sha256', json_encode([
            0,
            str_repeat('ab', 32),
            1700000000,
            27235,
            [['challenge', 'abc']],
            '',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->assertSame($expected, nostr_event::compute_id($event));

        // Any change to a signed field must change the id.
        $event['content'] = 'tampered';
        $this->assertNotSame($expected, nostr_event::compute_id($event));
    }

    /**
     * Tag extraction returns the first matching value, or null.
     */
    public function test_get_tag(): void {
        $event = [
            'tags' => [
                ['u', 'https://example.com/auth/nostr/login.php'],
                ['method', 'POST'],
                ['challenge', 'deadbeef'],
            ],
        ];
        $this->assertSame('POST', nostr_event::get_tag($event, 'method'));
        $this->assertSame('deadbeef', nostr_event::get_tag($event, 'challenge'));
        $this->assertNull(nostr_event::get_tag($event, 'missing'));
    }

    /**
     * An event missing a required field fails verification.
     */
    public function test_verify_requires_all_fields(): void {
        $event = [
            'id'         => str_repeat('00', 32),
            'pubkey'     => str_repeat('ab', 32),
            'created_at' => 1700000000,
            'kind'       => 27235,
            'tags'       => [],
            'content'    => '',
            // No 'sig' field.
        ];
        $this->assertFalse(nostr_event::verify($event));
    }
}

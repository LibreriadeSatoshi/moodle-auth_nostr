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
 * Tests for the per-IP rate limiter.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \auth_nostr\rate_limiter
 */

namespace auth_nostr;

/**
 * Unit tests for {@see rate_limiter}.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class rate_limiter_test extends \advanced_testcase {
    /**
     * Set a fixed client IP for the limiter's getremoteaddr() lookup.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $_SERVER['REMOTE_ADDR'] = '192.0.2.1';
    }

    /**
     * Requests up to the limit pass, the next one is rejected.
     */
    public function test_fixed_window_limit(): void {
        $this->assertTrue(rate_limiter::allow('phpunit_window', 2));
        $this->assertTrue(rate_limiter::allow('phpunit_window', 2));
        $this->assertFalse(rate_limiter::allow('phpunit_window', 2));
    }

    /**
     * A limit of 0 disables the brake entirely.
     */
    public function test_zero_limit_disables(): void {
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue(rate_limiter::allow('phpunit_disabled', 0));
        }
    }

    /**
     * Different buckets do not share counters.
     */
    public function test_buckets_are_independent(): void {
        $this->assertTrue(rate_limiter::allow('phpunit_bucket_a', 1));
        $this->assertTrue(rate_limiter::allow('phpunit_bucket_b', 1));
        $this->assertFalse(rate_limiter::allow('phpunit_bucket_a', 1));
    }
}

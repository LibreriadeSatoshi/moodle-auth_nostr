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
 * Per-IP rate limiter for the public Nostr login endpoint.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_nostr;

/**
 * Simple fixed-window per-IP rate limiter backed by a Moodle cache.
 *
 * Moodle does not ship a rate limiter for anonymous endpoints; this uses the
 * plugin's 'ratelimit' application cache (see db/caches.php). With the
 * default file cache store the counters are shared by all PHP processes of a
 * single server, but NOT across multiple frontends — on multi-node setups map
 * the 'auth_nostr/ratelimit' cache definition to a shared store (Redis,
 * memcached) via Site administration → Plugins → Caching → Configuration.
 */
class rate_limiter {
    /** @var int Default window length in seconds. */
    const WINDOW = 60;

    /**
     * Register a hit for the current client IP and decide whether it is
     * within the limit.
     *
     * @param string $bucket Logical bucket ('challenge', 'verify', …).
     * @param int $limit Maximum hits per window and IP; <= 0 disables the limit.
     * @param int $window Window length in seconds.
     * @return bool True if the request is within the limit.
     */
    public static function allow(string $bucket, int $limit, int $window = self::WINDOW): bool {
        if ($limit <= 0) {
            return true;
        }

        $cache = \cache::make('auth_nostr', 'ratelimit');
        $ip    = getremoteaddr();
        $key   = $bucket . '_' . hash('sha256', $ip === false ? 'unknown' : $ip);
        $now   = time();

        $entry = $cache->get($key);
        if (!is_array($entry) || ($entry['start'] + $window) <= $now) {
            $entry = ['start' => $now, 'count' => 0];
        }
        $entry['count']++;
        $cache->set($key, $entry);

        return $entry['count'] <= $limit;
    }
}

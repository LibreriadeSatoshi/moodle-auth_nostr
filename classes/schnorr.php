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

namespace auth_nostr;

/**
 * BIP340 Schnorr signature verification over secp256k1.
 *
 * Pure PHP implementation using the GMP extension.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class schnorr {
    /** Field prime p of secp256k1, hex encoded. */
    private const P_HEX  = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F';

    /** Curve order n of secp256k1, hex encoded. */
    private const N_HEX  = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141';

    /** X coordinate of the generator point G, hex encoded. */
    private const GX_HEX = '79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798';

    /** Y coordinate of the generator point G, hex encoded. */
    private const GY_HEX = '483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8';

    /** @var \GMP The field prime p. */
    private static $p = null;

    /** @var \GMP The curve order n. */
    private static $n = null;

    /** @var array{x:\GMP,y:\GMP} The generator point G. */
    private static $g = null;

    /**
     * Lazily initialise the curve parameters.
     */
    private static function init(): void {
        if (self::$p !== null) {
            return;
        }
        self::$p = gmp_init(self::P_HEX, 16);
        self::$n = gmp_init(self::N_HEX, 16);
        self::$g = [
            'x' => gmp_init(self::GX_HEX, 16),
            'y' => gmp_init(self::GY_HEX, 16),
        ];
    }

    /**
     * Modular reduction — always returns a value in [0, m-1].
     *
     * @param \GMP $a Value to reduce modulo the field prime.
     * @return \GMP
     */
    private static function fe(\GMP $a): \GMP {
        $r = gmp_mod($a, self::$p);
        if (gmp_cmp($r, gmp_init(0)) < 0) {
            $r = gmp_add($r, self::$p);
        }
        return $r;
    }

    /**
     * Point addition on secp256k1.
     *
     * Returns null to represent the point at infinity.
     *
     * @param array{x:\GMP,y:\GMP}|null $pointp First point.
     * @param array{x:\GMP,y:\GMP}|null $pointq Second point.
     * @return array{x:\GMP,y:\GMP}|null
     */
    private static function point_add(?array $pointp, ?array $pointq): ?array {
        if ($pointp === null) {
            return $pointq;
        }
        if ($pointq === null) {
            return $pointp;
        }

        if (gmp_cmp($pointp['x'], $pointq['x']) === 0) {
            // P == -Q → point at infinity.
            if (gmp_cmp($pointp['y'], $pointq['y']) !== 0) {
                return null;
            }
            // P == Q → point doubling.
            $num = self::fe(gmp_mul(gmp_mul(gmp_init(3), $pointp['x']), $pointp['x']));
            $den = self::fe(gmp_mul(gmp_init(2), $pointp['y']));
            $lam = self::fe(gmp_mul($num, gmp_invert($den, self::$p)));
        } else {
            $num = self::fe(gmp_sub($pointq['y'], $pointp['y']));
            $den = self::fe(gmp_sub($pointq['x'], $pointp['x']));
            $lam = self::fe(gmp_mul($num, gmp_invert($den, self::$p)));
        }

        $x3 = self::fe(gmp_sub(gmp_sub(gmp_mul($lam, $lam), $pointp['x']), $pointq['x']));
        $y3 = self::fe(gmp_sub(gmp_mul($lam, gmp_sub($pointp['x'], $x3)), $pointp['y']));

        return ['x' => $x3, 'y' => $y3];
    }

    /**
     * Scalar multiplication: scalar * point.
     *
     * LSB-first double-and-add.
     *
     * @param \GMP $scalar Scalar to multiply by.
     * @param array{x:\GMP,y:\GMP} $point Point to multiply.
     * @return array{x:\GMP,y:\GMP}|null
     */
    private static function point_mul(\GMP $scalar, array $point): ?array {
        $result = null;
        $addend = $point;
        $bits   = gmp_strval($scalar, 2);

        for ($i = strlen($bits) - 1; $i >= 0; $i--) {
            if ($bits[$i] === '1') {
                $result = self::point_add($result, $addend);
            }
            $addend = self::point_add($addend, $addend);
        }

        return $result;
    }

    /**
     * Given an x-coordinate, compute the corresponding point with even y.
     *
     * Returns null if x is not on the curve.
     *
     * @param \GMP $x X coordinate to lift.
     * @return array{x:\GMP,y:\GMP}|null
     */
    private static function lift_x(\GMP $x): ?array {
        $p = self::$p;

        if (gmp_cmp($x, $p) >= 0) {
            return null;
        }

        // Compute y² = x³ + 7 (mod p).
        $ysq = self::fe(gmp_add(gmp_powm($x, gmp_init(3), $p), gmp_init(7)));

        // Compute y = ysq^((p+1)/4) mod p — valid because p ≡ 3 (mod 4).
        $exp = gmp_div(gmp_add($p, gmp_init(1)), gmp_init(4));
        $y   = gmp_powm($ysq, $exp, $p);

        // Verify the square root is correct.
        if (gmp_cmp(self::fe(gmp_mul($y, $y)), $ysq) !== 0) {
            return null;
        }

        // Enforce even y.
        if (gmp_cmp(gmp_mod($y, gmp_init(2)), gmp_init(0)) !== 0) {
            $y = gmp_sub($p, $y);
        }

        return ['x' => $x, 'y' => $y];
    }

    /**
     * BIP340 tagged hash: SHA256(SHA256(tag) ‖ SHA256(tag) ‖ msg).
     *
     * @param string $tag Tag string.
     * @param string $msg Message bytes.
     * @return string Raw 32-byte hash.
     */
    private static function tagged_hash(string $tag, string $msg): string {
        $taghash = hash('sha256', $tag, true);
        return hash('sha256', $taghash . $taghash . $msg, true);
    }

    /**
     * Verify a BIP340 Schnorr signature.
     *
     * @param string $pubkeyhex 32-byte x-coordinate of public key (64 hex chars).
     * @param string $msghex    32-byte message / event id (64 hex chars).
     * @param string $sighex    64-byte signature (128 hex chars).
     * @return bool True when the signature is valid.
     */
    public static function verify(string $pubkeyhex, string $msghex, string $sighex): bool {
        if (!extension_loaded('gmp')) {
            throw new \RuntimeException('The GMP PHP extension is required for auth_nostr.');
        }

        self::init();

        if (strlen($pubkeyhex) !== 64 || strlen($msghex) !== 64 || strlen($sighex) !== 128) {
            return false;
        }

        $pubkeybytes = hex2bin($pubkeyhex);
        $msgbytes    = hex2bin($msghex);
        $sigbytes    = hex2bin($sighex);

        if ($pubkeybytes === false || $msgbytes === false || $sigbytes === false) {
            return false;
        }

        $pointp = self::lift_x(gmp_init($pubkeyhex, 16));
        if ($pointp === null) {
            return false;
        }

        $r = gmp_init(bin2hex(substr($sigbytes, 0, 32)), 16);
        $s = gmp_init(bin2hex(substr($sigbytes, 32, 32)), 16);

        if (gmp_cmp($r, self::$p) >= 0 || gmp_cmp($s, self::$n) >= 0) {
            return false;
        }

        // Compute e = int(tagged_hash("BIP0340/challenge", bytes(r) ‖ pubkey ‖ msg)) mod n.
        $einput = substr($sigbytes, 0, 32) . $pubkeybytes . $msgbytes;
        $ehash  = self::tagged_hash('BIP0340/challenge', $einput);
        $e      = gmp_mod(gmp_init(bin2hex($ehash), 16), self::$n);

        // Compute R = s·G + (n − e)·P.
        $nege   = gmp_sub(self::$n, $e);
        $pointr = self::point_add(
            self::point_mul($s, self::$g),
            self::point_mul($nege, $pointp)
        );

        if ($pointr === null) {
            return false;
        }

        // R must have even y and R.x must equal r.
        if (gmp_cmp(gmp_mod($pointr['y'], gmp_init(2)), gmp_init(0)) !== 0) {
            return false;
        }

        return gmp_cmp($pointr['x'], $r) === 0;
    }
}

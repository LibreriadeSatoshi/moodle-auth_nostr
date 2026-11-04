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
 * Tests for the BIP340 Schnorr verifier.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \auth_nostr\schnorr
 */

namespace auth_nostr;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for {@see schnorr}.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(schnorr::class)]
final class schnorr_test extends \basic_testcase {
    /**
     * Official BIP340 test vectors (indices 0-14, the ones with a 32-byte
     * message, which is the only shape a Nostr event id can have).
     *
     * @param string $pubkeyhex 32-byte x-only public key, hex.
     * @param string $msghex    32-byte message, hex.
     * @param string $sighex    64-byte signature, hex.
     * @param bool   $expected  Expected verification result.
     */
    #[DataProvider('bip340_vectors_provider')]
    public function test_bip340_vectors(string $pubkeyhex, string $msghex, string $sighex, bool $expected): void {
        $this->assertSame($expected, schnorr::verify($pubkeyhex, $msghex, $sighex));
    }

    /**
     * BIP340 test vectors from https://github.com/bitcoin/bips (bip-0340/test-vectors.csv).
     *
     * @return array[] Rows of [public key hex, message hex, signature hex, expected result].
     */
    public static function bip340_vectors_provider(): array {
        return [
            'vector 0' => [
                'f9308a019258c31049344f85f89d5229b531c845836f99b08601f113bce036f9',
                '0000000000000000000000000000000000000000000000000000000000000000',
                'e907831f80848d1069a5371b402410364bdf1c5f8307b0084c55f1ce2dca821525f66a4a85ea8b71e482a74f' .
                    '382d2ce5ebeee8fdb2172f477df4900d310536c0',
                true,
            ],
            'vector 1' => [
                'dff1d77f2a671c5f36183726db2341be58feae1da2deced843240f7b502ba659',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                '6896bd60eeae296db48a229ff71dfe071bde413e6d43f917dc8dcf8c78de33418906d11ac976abccb20b091292' .
                    'bff4ea897efcb639ea871cfa95f6de339e4b0a',
                true,
            ],
            'vector 2' => [
                'dd308afec5777e13121fa72b9cc1b7cc0139715309b086c960e18fd969774eb8',
                '7e2d58d8b3bcdf1abadec7829054f90dda9805aab56c77333024b9d0a508b75c',
                '5831aaeed7b44bb74e5eab94ba9d4294c49bcf2a60728d8b4c200f50dd313c1bab745879a5ad954a72c45a91c3' .
                    'a51d3c7adea98d82f8481e0e1e03674a6f3fb7',
                true,
            ],
            'vector 3: test fails if msg is reduced modulo p or n' => [
                '25d1dff95105f5253c4022f628a996ad3a0d95fbf21d468a1b33f8c160d8f517',
                'ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff',
                '7eb0509757e246f19449885651611cb965ecc1a187dd51b64fda1edc9637d5ec97582b9cb13db3933705b32ba98' .
                    '2af5af25fd78881ebb32771fc5922efc66ea3',
                true,
            ],
            'vector 4' => [
                'd69c3509bb99e412e68b0fe8544e72837dfa30746d8be2aa65975f29d22dc7b9',
                '4df3c3f68fcc83b27e9d42c90431a72499f17875c81a599b566c9889b9696703',
                '00000000000000000000003b78ce563f89a0ed9414f5aa28ad0d96d6795f9c6376afb1548af603b3eb45c9f820' .
                    '7dee1060cb71c04e80f593060b07d28308d7f4',
                true,
            ],
            'vector 5: public key not on the curve' => [
                'eefdea4cdb677750a420fee807eacf21eb9898ae79b9768766e4faa04a2d4a34',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                '6cff5c3ba86c69ea4b7376f31a9bcb4f74c1976089b2d9963da2e5543e17776969e89b4c5564d00349106b849' .
                    '7785dd7d1d713a8ae82b32fa79d5f7fc407d39b',
                false,
            ],
            'vector 6: has_even_y(R) is false' => [
                'dff1d77f2a671c5f36183726db2341be58feae1da2deced843240f7b502ba659',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                'fff97bd5755eeea420453a14355235d382f6472f8568a18b2f057a14602975563cc27944640ac607cd107ae1092' .
                    '3d9ef7a73c643e166be5ebeafa34b1ac553e2',
                false,
            ],
            'vector 7: negated message' => [
                'dff1d77f2a671c5f36183726db2341be58feae1da2deced843240f7b502ba659',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                '1fa62e331edbc21c394792d2ab1100a7b432b013df3f6ff4f99fcb33e0e1515f28890b3edb6e7189b630448b515' .
                    'ce4f8622a954cfe545735aaea5134fccdb2bd',
                false,
            ],
            'vector 8: negated s value' => [
                'dff1d77f2a671c5f36183726db2341be58feae1da2deced843240f7b502ba659',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                '6cff5c3ba86c69ea4b7376f31a9bcb4f74c1976089b2d9963da2e5543e177769961764b3aa9b2ffcb6ef947b688' .
                    '7a226e8d7c93e00c5ed0c1834ff0d0c2e6da6',
                false,
            ],
            'vector 9: sG - eP is infinite (even-y-as-false, x-as-0 variant)' => [
                'dff1d77f2a671c5f36183726db2341be58feae1da2deced843240f7b502ba659',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                '0000000000000000000000000000000000000000000000000000000000000000123dda8328af9c23a94c1feecfd' .
                    '123ba4fb73476f0d594dcb65c6425bd186051',
                false,
            ],
            'vector 10: sG - eP is infinite (even-y-as-false, x-as-1 variant)' => [
                'dff1d77f2a671c5f36183726db2341be58feae1da2deced843240f7b502ba659',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                '00000000000000000000000000000000000000000000000000000000000000017615fbaf5ae28864013c099742d' .
                    'eadb4dba87f11ac6754f93780d5a1837cf197',
                false,
            ],
            'vector 11: sig[0:32] is not an X coordinate on the curve' => [
                'dff1d77f2a671c5f36183726db2341be58feae1da2deced843240f7b502ba659',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                '4a298dacae57395a15d0795ddbfd1dcb564da82b0f269bc70a74f8220429ba1d69e89b4c5564d00349106b84977' .
                    '85dd7d1d713a8ae82b32fa79d5f7fc407d39b',
                false,
            ],
            'vector 12: sig[0:32] is equal to field size' => [
                'dff1d77f2a671c5f36183726db2341be58feae1da2deced843240f7b502ba659',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                'fffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2f69e89b4c5564d00349106b84977' .
                    '85dd7d1d713a8ae82b32fa79d5f7fc407d39b',
                false,
            ],
            'vector 13: sig[32:64] is equal to curve order' => [
                'dff1d77f2a671c5f36183726db2341be58feae1da2deced843240f7b502ba659',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                '6cff5c3ba86c69ea4b7376f31a9bcb4f74c1976089b2d9963da2e5543e177769fffffffffffffffffffffffffff' .
                    'fffffffffebaaedce6af48a03bbfd25e8cd0364141',
                false,
            ],
            'vector 14: public key exceeds the field size' => [
                'fffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc30',
                '243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89',
                '6cff5c3ba86c69ea4b7376f31a9bcb4f74c1976089b2d9963da2e5543e17776969e89b4c5564d00349106b84977' .
                    '85dd7d1d713a8ae82b32fa79d5f7fc407d39b',
                false,
            ],
        ];
    }

    /**
     * Messages that are not exactly 32 bytes are rejected outright (Nostr
     * event ids are always 32-byte hex, cf. BIP340 vectors 15-18).
     */
    public function test_rejects_non_32_byte_message(): void {
        $pubkey = 'f9308a019258c31049344f85f89d5229b531c845836f99b08601f113bce036f9';
        $sig    = 'e907831f80848d1069a5371b402410364bdf1c5f8307b0084c55f1ce2dca821525f66a4a85ea8b71e482a74f' .
            '382d2ce5ebeee8fdb2172f477df4900d310536c0';
        $this->assertFalse(schnorr::verify($pubkey, '0102', $sig));
    }

    /**
     * Malformed hex input is rejected instead of raising errors.
     */
    public function test_rejects_malformed_hex(): void {
        $pubkey = str_repeat('zz', 32);
        $msg    = str_repeat('00', 32);
        $sig    = str_repeat('00', 64);
        $this->assertFalse(schnorr::verify($pubkey, $msg, $sig));
    }
}

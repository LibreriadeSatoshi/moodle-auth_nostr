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
 * Adds a "Log in with Nostr" button to the Moodle login page.
 *
 * Implements NIP-07 (browser extension) + NIP-98 challenge-response.
 *
 * @module     auth_nostr/nostr_login
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/str'], function(Str) {
    'use strict';

    // Language strings, populated by loadStrings() before the UI is built.
    var STR = {};

    var STRING_KEYS = [
        'login_with_nostr', 'or',
        'status_looking', 'status_pubkey', 'status_profile', 'status_challenge',
        'status_signing', 'status_verifying', 'status_success',
        'error_no_extension', 'error_extension_denied', 'error_signing_cancelled',
        'error_challenge', 'error_network', 'error_login_failed'
    ];

    // Fetch all plugin strings once and cache them in STR.
    var loadStrings = function() {
        var requests = STRING_KEYS.map(function(key) {
            return {key: key, component: 'auth_nostr'};
        });
        return Str.get_strings(requests).then(function(values) {
            STRING_KEYS.forEach(function(key, i) {
                STR[key] = values[i];
            });
            return STR;
        });
    };

    // Poll for window.nostr for up to `timeout` ms (NIP-07 may load async).
    var waitForExtension = function(timeout) {
        return new Promise(function(resolve) {
            var start = Date.now();
            var poll = function() {
                if (window.nostr) {
                    resolve(window.nostr);
                } else if (Date.now() - start < timeout) {
                    setTimeout(poll, 100);
                } else {
                    resolve(null);
                }
            };
            poll();
        });
    };

    // Fetch kind-0 (profile metadata) from a relay via WebSocket.
    // Resolves with the parsed content object, or null on timeout/error.
    var fetchKind0 = function(relay, pubkey, timeout) {
        return new Promise(function(resolve) {
            var timer = setTimeout(function() { resolve(null); }, timeout);
            try {
                var ws = new WebSocket(relay);
                ws.onopen = function() {
                    ws.send(JSON.stringify([
                        'REQ', 'auth-meta',
                        {kinds: [0], authors: [pubkey], limit: 1}
                    ]));
                };
                ws.onmessage = function(e) {
                    try {
                        var msg = JSON.parse(e.data);
                        if (msg[0] === 'EVENT' && msg[2] && msg[2].kind === 0) {
                            clearTimeout(timer);
                            ws.close();
                            resolve(JSON.parse(msg[2].content));
                        } else if (msg[0] === 'EOSE') {
                            clearTimeout(timer);
                            ws.close();
                            resolve(null);
                        }
                    } catch (_) {
                        clearTimeout(timer);
                        resolve(null);
                    }
                };
                ws.onerror = function() { clearTimeout(timer); resolve(null); };
            } catch (_) {
                clearTimeout(timer);
                resolve(null);
            }
        });
    };

    var setStatus = function(el, msg, isError) {
        el.textContent = msg;
        el.className = 'auth-nostr-status' + (isError ? ' auth-nostr-error' : '');
    };

    var handleLogin = async function(loginUrl, relay, btn, statusEl) {
        btn.disabled = true;

        setStatus(statusEl, STR.status_looking);
        var nostr = await waitForExtension(3000);
        if (!nostr) {
            setStatus(statusEl, STR.error_no_extension, true);
            btn.disabled = false;
            return;
        }

        setStatus(statusEl, STR.status_pubkey);
        var pubkey;
        try {
            pubkey = await nostr.getPublicKey();
        } catch (_) {
            setStatus(statusEl, STR.error_extension_denied, true);
            btn.disabled = false;
            return;
        }

        setStatus(statusEl, STR.status_profile);
        var metadata = await fetchKind0(relay, pubkey, 3000);

        setStatus(statusEl, STR.status_challenge);
        var nonce, signUrl;
        try {
            var challengeResp = await fetch(loginUrl + '?action=challenge');
            if (!challengeResp.ok) { throw new Error(); }
            var challengeData = await challengeResp.json();
            nonce   = challengeData.nonce;
            signUrl = challengeData.url;
        } catch (_) {
            setStatus(statusEl, STR.error_challenge, true);
            btn.disabled = false;
            return;
        }

        setStatus(statusEl, STR.status_signing);
        var signedEvent;
        try {
            signedEvent = await nostr.signEvent({
                kind:       27235,
                created_at: Math.floor(Date.now() / 1000),
                content:    '',
                tags: [
                    ['u',         signUrl],
                    ['method',    'POST'],
                    ['challenge', nonce]
                ]
            });
        } catch (_) {
            setStatus(statusEl, STR.error_signing_cancelled, true);
            btn.disabled = false;
            return;
        }

        setStatus(statusEl, STR.status_verifying);
        var redirectUrl;
        try {
            var verifyResp = await fetch(loginUrl, {
                method:  'POST',
                headers: {'Content-Type': 'application/json'},
                body:    JSON.stringify({event: signedEvent, metadata: metadata})
            });
            var verifyData;
            try {
                verifyData = await verifyResp.json();
            } catch (_) {
                setStatus(statusEl, STR.error_login_failed, true);
                btn.disabled = false;
                return;
            }
            if (!verifyResp.ok || verifyData.error || !verifyData.redirect) {
                setStatus(statusEl, verifyData.error || STR.error_login_failed, true);
                btn.disabled = false;
                return;
            }
            redirectUrl = verifyData.redirect;
        } catch (err) {
            setStatus(statusEl, STR.error_network, true);
            btn.disabled = false;
            return;
        }

        setStatus(statusEl, STR.status_success);
        window.location.href = redirectUrl;
    };

    return {
        init: function(loginUrl, relay) {
            var ready = function(fn) {
                if (document.readyState !== 'loading') {
                    fn();
                } else {
                    document.addEventListener('DOMContentLoaded', fn);
                }
            };

            ready(function() {
                // Find the standard Moodle login form.
                var loginBtn = document.getElementById('loginbtn');
                if (!loginBtn) { return; }
                var form = loginBtn.closest('form');
                if (!form) { return; }

                loadStrings().then(function() {
                    // Build and inject the Nostr login widget after the form.
                    var wrapper = document.createElement('div');
                    wrapper.className = 'auth-nostr-wrapper mt-3 text-center';

                    var sep = document.createElement('div');
                    sep.className = 'auth-nostr-sep mb-2';
                    var sepText = document.createElement('span');
                    sepText.className = 'auth-nostr-sep-text px-2';
                    sepText.textContent = STR.or;
                    sep.appendChild(sepText);

                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.id = 'auth-nostr-btn';
                    btn.className = 'btn btn-secondary w-100';
                    btn.textContent = '⚡ ' + STR.login_with_nostr;

                    var statusEl = document.createElement('div');
                    statusEl.id = 'auth-nostr-status';
                    statusEl.className = 'auth-nostr-status mt-2 small';

                    wrapper.appendChild(sep);
                    wrapper.appendChild(btn);
                    wrapper.appendChild(statusEl);

                    form.parentNode.insertBefore(wrapper, form.nextSibling);

                    btn.addEventListener('click', function() {
                        handleLogin(loginUrl, relay, btn, statusEl);
                    });

                    return STR;
                });
            });
        }
    };
});

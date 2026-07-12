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
 * Spanish language strings for the Nostr authentication plugin.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']              = 'Autenticación con Nostr';
$string['auth_nostrname']          = 'Nostr';
$string['auth_nostrdescription']   = 'Autentícate usando un par de claves de Nostr mediante una extensión del navegador (NIP-07).';

$string['login_with_nostr']        = 'Iniciar sesión con Nostr';
$string['or']                      = 'o';
$string['status_looking']          = 'Buscando la extensión de Nostr…';
$string['status_pubkey']           = 'Solicitando la clave pública…';
$string['status_profile']          = 'Obteniendo tu perfil de Nostr…';
$string['status_challenge']        = 'Solicitando el desafío de inicio de sesión…';
$string['status_signing']          = 'Firmando la solicitud de inicio de sesión…';
$string['status_verifying']        = 'Verificando con el servidor…';
$string['status_success']          = '¡Sesión iniciada! Redirigiendo…';

$string['error_no_extension']      = 'No se encontró ninguna extensión de Nostr. Instala Alby o nos2x.';
$string['error_extension_denied']  = 'La extensión denegó el acceso.';
$string['error_signing_cancelled'] = 'Se canceló la firma.';
$string['error_challenge']         = 'No se pudo obtener el desafío de inicio de sesión. Recarga la página e inténtalo de nuevo.';
$string['error_network']           = 'Error de red. Inténtalo de nuevo.';
$string['error_login_failed']      = 'Error al iniciar sesión. Inténtalo de nuevo.';

$string['relay']                   = 'Relay de Nostr';
$string['relay_desc']              = 'URL del relay WebSocket usado para obtener los metadatos del perfil de Nostr (kind 0). Se usa solo para rellenar el nombre visible en el primer inicio de sesión.';
$string['autocreate']              = 'Crear cuentas automáticamente';
$string['autocreate_desc']         = 'Crea automáticamente una cuenta de Moodle para cualquier clave pública de Nostr válida en el primer inicio de sesión.';
$string['showlog']                 = 'Mostrar el registro de progreso del inicio de sesión';
$string['showlog_desc']            = 'Muestra los mensajes de estado paso a paso (buscando la extensión, firmando, verificando…) bajo el botón de Nostr durante el inicio de sesión. Los mensajes de error se muestran siempre, independientemente de esta opción.';

$string['privacy:metadata']        = 'El plugin de autenticación con Nostr no almacena ningún dato personal más allá de lo que Moodle guarda en la cuenta de usuario estándar.';

$string['gmprequired']             = 'El plugin de autenticación con Nostr (auth_nostr) requiere la extensión PHP GMP para verificar las firmas Schnorr.';

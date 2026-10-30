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
 * Brazilian Portuguese language strings for the Nostr authentication plugin.
 *
 * @package    auth_nostr
 * @copyright  2026 Librería de Satoshi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']              = 'Autenticação com Nostr';
$string['auth_nostrname']          = 'Nostr';
$string['auth_nostrdescription']   = 'Autentique-se com um par de chaves Nostr por meio de uma extensão do navegador (NIP-07).';

$string['login_with_nostr']        = 'Entrar com Nostr';
$string['provider_name']        = 'Nostr';
$string['or']                      = 'ou';
$string['status_looking']          = 'Procurando a extensão Nostr…';
$string['status_pubkey']           = 'Solicitando a chave pública…';
$string['status_profile']          = 'Buscando seu perfil Nostr…';
$string['status_challenge']        = 'Solicitando o desafio de login…';
$string['status_signing']          = 'Assinando a solicitação de login…';
$string['status_verifying']        = 'Verificando com o servidor…';
$string['status_success']          = 'Login feito! Redirecionando…';

$string['error_no_extension']      = 'Nenhuma extensão Nostr foi encontrada. Instale a Alby ou a nos2x.';
$string['error_extension_denied']  = 'A extensão negou o acesso.';
$string['error_signing_cancelled'] = 'A assinatura foi cancelada.';
$string['error_challenge']         = 'Não foi possível obter o desafio de login. Recarregue a página e tente de novo.';
$string['error_network']           = 'Erro de rede. Tente de novo.';
$string['error_login_failed']      = 'Não foi possível entrar. Tente de novo.';

$string['relay']                   = 'Relay Nostr';
$string['relay_desc']              = 'URL do relay WebSocket usado para buscar os metadados do perfil Nostr (kind 0). Usado só para preencher o nome de exibição no primeiro login.';
$string['autocreate']              = 'Criar contas automaticamente';
$string['autocreate_desc']         = 'Cria automaticamente uma conta Moodle para qualquer chave pública Nostr válida no primeiro login.';
$string['showlog']                 = 'Mostrar o registro de progresso do login';
$string['showlog_desc']            = 'Mostra as mensagens de status passo a passo (procurando a extensão, assinando, verificando…) abaixo do botão Nostr durante o login. As mensagens de erro aparecem sempre, independentemente desta opção.';
$string['challengeratelimit']      = 'Limite de desafios (por IP por minuto)';
$string['challengeratelimit_desc'] = 'Número máximo de desafios de login (GET ?action=challenge) servidos por minuto e por IP do cliente. Cada desafio cria uma linha de sessão no Moodle, então este limite protege contra o crescimento descontrolado da tabela de sessões. Padrão: 10. Use 0 para desativar (não recomendado). Em instalações com vários front-ends, mapeie o cache auth_nostr/ratelimit para um armazenamento compartilhado (Redis, memcached) em Administração do site → Plugins → Cache.';
$string['verifyratelimit']         = 'Limite de verificações (por IP por minuto)';
$string['verifyratelimit_desc']    = 'Número máximo de tentativas de login (POST com um evento assinado que chega à verificação de assinatura) por minuto e por IP do cliente. Limita o custo de CPU da verificação Schnorr no servidor. Padrão: 20. Use 0 para desativar (não recomendado).';

$string['privacy:metadata']        = 'O plugin de autenticação com Nostr não armazena nenhum dado pessoal além do que o Moodle guarda na conta de usuário padrão.';

$string['gmprequired']             = 'O plugin de autenticação com Nostr (auth_nostr) requer a extensão PHP GMP para verificar as assinaturas Schnorr.';

<?php

/**
 * @package   manukn/oauth2-server-proxify-laravel
 * @author    Michele Andreoli <michi.andreoli[at]gmail.com>
 * @copyright Copyright (c) Michele Andreoli
 * @author    Rik Schreurs <rik.schreurs[at]mail.com>
 * @copyright Copyright (c) Rik Schreurs
 * @license   http://mit-license.org/
 * @link      https://github.com/manukn/oauth2-server-proxify-laravel
 */

return array(
    'access_token_ok' => 'Access token retrieved successfully',
    'proxy_missing_param' => 'Missing mandatory parameter <b>:param</b> in the request call',
    'missing_client_secret' => 'Missing secret key for client id <b>:client</b>',
    'proxy_cookie_expired' => 'Cookie expired or not found. Return to the login form.',
    'proxy_cookie_invalid' => 'Cookie format not valid. Missing attribute <b>:param</b>.'
);

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

return [
    /*
      |--------------------------------------------------------------------------
      | Proxy input: define the skip attribute
      |--------------------------------------------------------------------------
      |
      | When you call the proxy helper with this attribute set as true, the proxy calls
      | the uri directly without pass to oauth server.
      |
     */
    'skip_param' => 'skip',

    /*
      |--------------------------------------------------------------------------
      | Set redirect URI
      |--------------------------------------------------------------------------
      |
      | Set a redirect URI to call when the cookie expires. If you don't specify
      | any URI, the proxy helper will return a 403 proxy_cookie_expired exception.
      |
     */
    'redirect_login' => '',

    /*
      |--------------------------------------------------------------------------
      |  Cookie configuration
      |--------------------------------------------------------------------------
      |
      | This is the cookie's configuration: name of cookie and expiration minutes.
      | If time is NULL the cookie doesn't expires.
      |
     */
    'cookie_info' => [
        'name' => 'proxify',
        'time' => 1440
    ],

    /*
      |--------------------------------------------------------------------------
      |  Access token send into header
      |--------------------------------------------------------------------------
      |
      | If it is true the access_token was sent to oauth server into request's header.
      |
     */
    'use_header' => false,

    /*
      |--------------------------------------------------------------------------
      |  List of client secret
      |--------------------------------------------------------------------------
      |
      | Define secrets key for each clients you need.
      |
     */
    'client_secrets' => [
        'client_1' => 'abc123',
        'client_2' => 'def456'
    ],
];

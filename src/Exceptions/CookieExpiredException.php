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

namespace Manukn\LaravelProxify\Exceptions;

/**
 * Exception class
 */
class CookieExpiredException extends ProxyException
{

    public function __construct()
    {
        $this->httpStatusCode = 403;
        $this->errorType = 'proxy_cookie_expired';
        parent::__construct(\Lang::get('api-proxy-laravel::messages.proxy_cookie_expired'));
    }
}

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
class ProxyMissingParamException extends ProxyException
{

    public function __construct($parameter)
    {
        $this->httpStatusCode = 400;
        $this->errorType = 'proxy_missing_param';
        parent::__construct(\Lang::get('api-proxy-laravel::messages.proxy_missing_param', array('param' => $parameter)));
    }
}

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

namespace Manukn\LaravelProxify;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Manukn\LaravelProxify\Exceptions\ProxyException;

class ApiProxyServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/proxy.php' => config_path('proxy.php'),
        ]);

        $this->loadTranslationsFrom(__DIR__ . '/lang', 'api-proxy-laravel');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //$this->registerErrorHandlers();
        $this->registerApiProxy();
    }

    /**
     * Register ApiProxy with the IoC container
     * @return void
     */
    public function registerApiProxy()
    {
        $this->app->singleton('api-proxy.proxy', function ($app) {
            $params = $app['config']['proxy'];
            $proxy = new Proxy($params);
            return $proxy;
        });

        $this->app->bind('Manukn\LaravelProxify\Proxy', function ($app) {
            return $app['api-proxy.proxy'];
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return array('api-proxy.proxy');
    }

    /**
     * Register the ApiProxy error handlers
     * @return void
     */
    private function registerErrorHandlers()
    {
        $this->app->error(function (ProxyException $ex) {
            if (\Request::ajax() && \Request::wantsJson()) {
                return new JsonResponse([
                    'error' => $ex->errorType,
                    'error_description' => $ex->getMessage()
                ], $ex->httpStatusCode, $ex->getHttpHeaders()
                );
            }

            return \View::make('api-proxy-laravel::proxy_error', array(
                'header' => $ex->getHttpHeaders()[0],
                'code' => $ex->httpStatusCode,
                'error' => $ex->errorType,
                'message' => $ex->getMessage()
            ));
        });
    }
}

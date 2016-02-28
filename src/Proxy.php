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

use Manukn\LaravelProxify\Exceptions\CookieExpiredException;
use Manukn\LaravelProxify\Exceptions\ProxyMissingParamException;
use Manukn\LaravelProxify\Managers\CookieManager;
use Manukn\LaravelProxify\Managers\RequestManager;
use Illuminate\Http\Response;

class Proxy
{

    private $callMode = null;
    private $uriParam = null;
    private $skipParam = null;
    private $redirectUri = null;
    private $clientSecrets = null;
    private $cookieManager = null;
    private $useHeader = false;

    /**
     * @param $params
     */
    public function __construct($params)
    {
        $this->skipParam = $params['skip_param'];
        $this->redirectUri = $params['redirect_login'];
        $this->clientSecrets = $params['client_secrets'];
        $this->useHeader = $params['use_header'];
        $this->cookieManager = new CookieManager($params['cookie_info']);
    }

    /**
     * @param $method
     * @param array $inputs
     * @return Response
     * @throws CookieExpiredException
     * @throws ProxyMissingParamException
     * @throws \Exception
     */
    public function makeRequest($method, array $inputs, $url)
    {
        $this->uri = $url;

        //Retrieve the call mode from input parameters
        $this->callMode = $this->getRequestMode($inputs);

        //Remove parameters from inputs
        $inputs = ProxyAux::removeQueryValue($inputs, $this->uriParam);
        $inputs = ProxyAux::removeQueryValue($inputs, $this->skipParam);

        //Read the cookie if exists
        $parsedCookie = null;
        if ($this->callMode !== ProxyAux::MODE_SKIP && $this->callMode !== ProxyAux::MODE_LOGIN) {
            try {
                $parsedCookie = $this->cookieManager->tryParseCookie($this->callMode);
            } catch (CookieExpiredException $ex) {
                if (isset($this->redirectUri) && !empty($this->redirectUri)) {
                    return \Redirect::to($this->redirectUri);
                }
                throw $ex;
            }
        }

        //Create the new request
        $requestManager = new RequestManager($this->uri, $method, $this->clientSecrets, $this->callMode, $this->cookieManager);
        if ($this->useHeader) {
            $requestManager->enableHeader();
        }
        $proxyResponse = $requestManager->executeRequest($inputs, $parsedCookie);

        return $this->setApiResponse($proxyResponse['response'], $proxyResponse['cookie']);
    }

    /**
     * @param $inputs
     * @return string
     */
    private function getRequestMode($inputs)
    {
        $grantType = ProxyAux::getQueryValue($inputs, ProxyAux::GRANT_TYPE);
        $skip = ProxyAux::getQueryValue($inputs, $this->skipParam);
        $mode = ProxyAux::MODE_TOKEN;

        if (isset($grantType)) {
            if ($grantType === ProxyAux::PASSWORD_GRANT) {
                $mode = ProxyAux::MODE_LOGIN;
            }
        } elseif (isset($skip) && strtolower($skip) === 'true') {
            $mode = ProxyAux::MODE_SKIP;
        }

        return $mode;
    }

    /**
     * @param $proxyResponse
     * @param $cookie
     * @return Response
     */
    private function setApiResponse($proxyResponse, $cookie)
    {
        $response = new Response($proxyResponse->getContent(), $proxyResponse->getStatusCode());

        if ($this->callMode === ProxyAux::MODE_LOGIN && $proxyResponse->getStatusCode() === 200) {
            $response->setContent(json_encode($this->successAccessToken()));
        }
        if (isset($cookie)) {
            $response->withCookie($cookie);
        }

        return $response;
    }

    /**
     * @return array
     */
    private function successAccessToken()
    {
        return array(
            'success_code' => 'access_token_ok',
            'success_message' => \Lang::get('api-proxy-laravel::messages.access_token_ok')
        );
    }
}

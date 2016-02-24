Laravel 5.2.* Proxy for OAuth
================

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)


## Summary

- [Introduction](#introduction)
- [Installation](#installation)
- [Setup](#setup)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Facade](#facade)
- [License](#license)

## Introduction
This package was originally created by [Michele Andreoli](https://github.com/thinkingmik), but the package was outdated and did not work for Laravel version 5. I have adapted it to work with Laravel 5.

This package would be a solution about the issue opened by [Alex Bilbie](http://alexbilbie.com/2014/11/oauth-and-javascript).
He says:

Let's assume that you've just made a shiny Angular/Ember/Backbone whatever single page web-app that gets all of it's
data from an API that you've written via ajax calls. You've also elected to secure the API with OAuth and you're
also securing the API endpoint with SSL (as the OAuth spec requires).

```
POST /auth HTTP/1.1
Host: api.example.com

grant_type=password
&client_id=webapp
&client_secret=abc123
&username=admin
&password=mypassword
```

The server will respond:

```json
{
    "access_token": "DDSHs55zpG51Mtxnt6H8vwn5fVJ230dF",
    "refresh_token": "24QmIt2aV1ubaenB2D6G0se5pFRk4W05",
    "token_type": "Bearer",
    "expires": 1415741799
}
```

Already there are major problems with this.

First in the app's request we're sending the client ID and secret which the API uses to ensure the request is
coming from a known source. As there is no backend to the web-app these will have to be stored in the front-end
code and they can't be encrypted in code because you can't do crypto in JavaScript securely.
So already we've got the problem that the only way of identifying the web-app - by using credentials - are already
leaked in public code and will allow an attacker to attempt to make authenticated requests independent of the app.
You can't use referrer headers to lock down requests either as they are easily faked. You can't store the
credentials in an encrypted form in a cookie either because that cookie can be just grabbed by the attacker as
easily as the client credentials that are baked into source code.

Moving on, in the response to the request the server has given us an access token which is used to
authenticate requests to the API and a refresh token which is used to acquire a new access token when it expires.

First we've got the issue that the access token is now available to the attacker. He doesn't need anything
else now to make requests to your API and go crazy grabbing all of the users' private data and performing any
actions that the API allows. The server has got no way of knowing that it isn't the web-app making the requests.

So because this is an app that you've written and it's talking to your backend you've decided that the
`resource owner password credentials grant` (aka the `password grant`) is the way that you're going to get an
access token. The access token can then be used to authenticate API requests.

The web-app is going to make an ajax request to the API to sign the user in once you've captured their credentials
(line breaks added for readability). This is how a valid OAuth 2 password grant access token request should look:

Valid request from the web-app:

```
GET /resource/123 HTTP/1.1
Authorization: Bearer DDSHs55zpG51Mtxnt6H8vwn5fVJ230dF
Host: api.example.com
```

Valid request from an attacker:

```
GET /resource/123 HTTP/1.1
Authorization: Bearer DDSHs55zpG51Mtxnt6H8vwn5fVJ230dF
Host: api.example.com
```

Even if your API has short lived access tokens then the refresh token was also in the response to the browser so
the attacker can use that to get a new access token when the original expires.

The simple story is here that you can't keep things safe in the front end. So don't.

**So how can you use OAuth securely in single page web-apps?**

It's simple; `proxy` all of your API calls via a thin server side component. This component (let's just call it a
`proxy` from here on) will authenticate ajax requests from the user's session. The access and refresh tokens can be
stored in an encrypted form in a cookie which only the `proxy` can decrypt. The application client credentials will
also be hardcoded into the `proxy` so they're not publicly accessible either.

To authenticate the user in the first place the web-app will make a request to the `proxy` with just
the user's credentials and client ID, **NOT CLIENT SECRET!**:

```
POST /ajax/auth HTTP/1.1
Host: example.com

grant_type=password
&username=admin
&password=mypassword
&client_id=webapp
```

The `proxy` will then add in the client secret which only it knows and forward the request onto the API:

```
POST /auth HTTP/1.1
Host: api.example.com

grant_type=password
&username=admin
&password=mypassword
&client_id=webapp
&client_secret=abc123
```

The server will respond:

```json
{
    "access_token": "DDSHs55zpG51Mtxnt6H8vwn5fVJ230dF",
    "refresh_token": "24QmIt2aV1ubaenB2D6G0se5pFRk4W05",
    "token_type": "Bearer",
    "expires": 1415741799
}
```

The `proxy` will encrypt the tokens in a cookie and return a success message to the user.

When the web-app makes a request to an API endpoint it will call the `proxy` instead of the API:

```
GET /ajax/resource/123 HTTP/1.1
Cookie: <encrypted cookie with tokens>
Host: example.com
```

The `proxy` will decrypt the cookie, add the Authorization header to the request and forward it on to the API:

```
GET /resource/123 HTTP/1.1
Authorization: Bearer DDSHs55zpG51Mtxnt6H8vwn5fVJ230dF
Host: api.example.com
```

The `proxy` will pass the response straight back to the browser.

With this setup there are no publicly visible or plain text client credentials or tokens which means that
attackers won't be make faked requests to the API. Also because the browser is no longer communicating with the
API directly you can remove it from the public Internet and lock down the firewall rules so that only requests
coming from the web server directly will be allowed.

To protect an attacker just stealing the cookie you can use CSRF protection measures.


Thank you to [Alex Bilbie](http://alexbilbie.com) for issue:
http://alexbilbie.com/2014/11/oauth-and-javascript

## Installation

Run the following `composer` command:

```
composer require manukn/oauth2-server-proxify-laravel
```

## Setup

1. Add `Manukn\LaravelProxify\ApiProxyServiceProvider::class,` to the service provider list in `app/config/app.php`.
2. Add `Proxify' => Manukn\LaravelProxify\Facades\ApiProxyFacade',` to the list of aliases in `app/config/app.php`.

## Configuration

In order to use the Api Proxy publish its configuration first

```
php artisan vendor:publish
```

Afterwards edit the file ```app/config/proxy.php``` to suit your needs.

## Usage

In the `app/config/routes.php` add a new endpoint like:

```php
Route::any('proxify/{url?}', function($url) {
	return Proxify::makeRequest(Request::method(), Input::all(), $url);
})->where('url', '(.*)');
```

This is your proxy endpoint, then you can call proxy to get an access token (the `client_id` parameter is optional):


```
POST proxify/example.com/oauth/access_token HTTP/1.1
Host: example.com

&grant_type=password
[&client_id=webapp]
&username=admin
&password=mypassword
```

And after you can call the protected resource:

```
POST proxify/example.com/protected_resource HTTP/1.1
Host: example.com

```

If the `access_token` expires and you have got a `refresh_token`, `ApiProxy` will call the OAuth server
for you and refresh the `access_token` with a new one. After that it makes a new call to the protected resource.

This `ApiProxy` package works great with [oauth2-server-laravel](https://github.com/lucadegasperi/oauth2-server-laravel) written
by [Luca Degasperi](https://github.com/lucadegasperi).
I have used this package for my tests.

### Facade

The `ApiProxy` is available through the Facade `Proxify` or through the proxy service in the IOC container.
The method available is:

```php
/**
 * Use this method in the laravel route file
 * @param $method
 * @param array $inputs
 * @return Response
 * @throws ProxyMissingParamException
 */
Proxify::makeRequest(Request::method(), Input::all());
```

## License

This package is released under the MIT License.

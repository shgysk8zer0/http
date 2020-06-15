# http
![PHP Lint](https://github.com/shgysk8zer0/http/workflows/PHP%20Lint/badge.svg)
[![GitHub license](https://img.shields.io/github/license/shgysk8zer0/http.svg)](https://github.com/shgysk8zer0/http/blob/master/LICENSE)
![GitHub last commit](https://img.shields.io/github/last-commit/shgysk8zer0/http.svg)
![GitHub release](https://img.shields.io/github/release/shgysk8zer0/http.svg)

[![Donate using Liberapay](https://img.shields.io/liberapay/receives/shgysk8zer0.svg?logo=liberapay)](https://liberapay.com/shgysk8zer0/donate "Donate using Liberapay")
![Keybase PGP](https://img.shields.io/keybase/pgp/shgysk8zer0.svg)
![Keybase BTC](https://img.shields.io/keybase/btc/shgysk8zer0.svg)

![GitHub followers](https://img.shields.io/github/followers/shgysk8zer0.svg?style=social)
![GitHub forks](https://img.shields.io/github/forks/shgysk8zer0/http.svg?style=social)
![GitHub stars](https://img.shields.io/github/stars/shgysk8zer0/http.svg?style=social)
![Twitter Follow](https://img.shields.io/twitter/follow/shgysk8zer0.svg?style=social)
- - -
> A PHP implementation of JavaScript's [`Request`](https://developer.mozilla.org/en-US/docs/Web/API/Request),
[`Response`](https://developer.mozilla.org/en-US/docs/Web/API/Response),
[`URL`](https://developer.mozilla.org/en-US/docs/Web/API/URL/),
[`URLSearchParams`](https://developer.mozilla.org/en-US/docs/Web/API/URLSearchParams),
[`FormData`](https://developer.mozilla.org/en-US/docs/Web/API/FormData),
and [`Headers`](https://developer.mozilla.org/en-US/docs/Web/API/Headers)
interfaces.

## Example
```php
<?php

use \shgysk8zer0\HTTP\{Request, Response, Body, FormData, Headers, File, URL};

use \shgysk8zer0\HTTP\Abstracts\{HTTPStatusCodes as HTTP};

use \shgysk8zer0\PHPAPI\{ConsoleLogger, FileCache};

use \DateInterval;

spl_autoload_regiser('spl_autoload');

set_include_path($classes_dir . PATH_SEPARATOR . get_include_path());


try {
  $url = new URL('../some/endpoint', 'https://example.com/wrong-path/');

  $cache = new FileCache();
  $logger = new ConsoleLogger();
  
  $req = new Request($url, [
    'method'      => 'POST',
    'referrer'    => 'no-referrer',
    'redirect'    => 'follow',
    'credentials' => 'omit',
    'cache'       => 'default',
    'headers'     => new Headers([
        'Accept' => 'application/json',
        'X-FOO'  => 'bar',
    ]),
    'body'        => new FormData([
        'username' => $username,
        'token'    => $token,
        'file'     => new File($filename, null, 'file'),
        'upload'   => new UploadFile('upload'),
    ])
  ]);
  
  $req->setLogger($logger);
  
  // For compatibility with `CacheInterface` `Request.cache = 'default'` -> `Request::setCacheMode('default')`
  // and `Request::setCache(CacheInterface $cache)`
  $req->setCache($cache);
  
  if ($resp = $req->send($timeout)) {
    $resp->headers->set('Content-SecurityPolicy', ContentSecurityPolicy::fromIniFile('./csp.ini'));
    
    $resp->headers->set('Feature-Policy', new FeaturePolicy([
      'geolocation' => 'self',
      'camera'      => 'self',
    ]));
    
    $resp->headers->append('Set-Cookie', new Cookie('name','value', [
      'secure'   => true,
      'httpOnly' => true,
      'expires'  => new DateInterval('P1D'),
      'sameSite' => 'Strict',
    ]);
    
    // `Response::send()` sends HTTP status code, headers, & body
    $resp->send();
  } else {
    $resp = new Response(new Body('An unknown error occured'), [
      'headers' => new Headers([
        'Content-Security-Policy' => new ContentSecurityPolicy(['default-src' => 'self']),
        'Content-Type'            => 'text/plain',
      ]),
      'status'                    => HTTP::BAD_GATEWAY,
    ]);
    
    $resp->send();
  }
} catch (Throwable $e) {
  $logger->error('[{class} {code}] "{message}" at {file}:{line}', [
    'class'   => get_class($e),
    'code'    => $e->getCode(),
    'message' => $e->getMessage(),
    'file'    => $e->getFile(),
    'line'    => $e->getLine(),
  ]);
  
  $resp = new Response(new Body('An error occured'), [
    'status'  => HTTP::INTERNAL_SERVER_ERROR,
    'headers' => new Headers([
      'Content-Type' => 'text/plain',
    ]),
  ]);
  
  $resp->send();
}
```

## Installation
This is built to be installed as a submodule and loaded using [`spl_autoload`](https://www.php.net/manual/en/function.spl-autoload)

To install, just
```
git submodule add https://github.com/shgysk8zer0/http.git $classes_dir/shgysk8zer0/http
```

## Dependencies
Loggers and caches are used from [shgysk8zer0/PHPAPI](https://github.com/shgysk8zer0/phpapi).
You will need to add that as a submodule to `$classes_dir/shgysk8zer0/phpapi`.

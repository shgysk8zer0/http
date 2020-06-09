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

use \\shgysk8zer0\\HTTP\\{Request, FormData, Headers, File, URL};

use \\shgysk8zer0\\HTTP\\Abstracts\\{HTTPStatusCodes as HTTP};

use \\shgysk8zer0\\PHPAPI\\{ConsoleLogger, FileCache};

spl_autoload_regiser('spl_autoload');

set_include_path($classes_dir . PATH_SEPARATOR . get_include_path());

$url = new URL('../some/endpoint', 'https://example.com/wrong-path/');

$cache = new FileCache();
$logger = new ConsoleLogger();

try {
  if ($cache->has($url)) {
    $resp = $cache->get($url);
    http_response_code($resp->getStatus());
    header('Content-Type: ' . $resp->headers->get('Content-Type');
    exit($resp->body);
  } else {
    $req = new Request($url, [
      'method' => 'POST',
      'headers' => new Headers([
        'Accept' => 'application/json',
      ]),
      'body' => new FormData([
        'username' => $username,
        'token'    => $token,
        'upload'   => new File($filename, null, 'upload'),
      ])
    ]);
    
    $req->setLogger($logger);
    $req->setCache($cache);
    
    if ($resp = $req->send($timeout)) {
      $cache->set($url, $resp, new DateInterval('PT1H30M'));
      http_response_code($resp->getStatus());
      header('Content-Type: ' . $resp->headers->get('Content-Type');
      exit($resp->body);
    }
  }
} catch (Throwable $e) {
  http_response_code(HTTP::INTERNAL_SERVER_ERROR);
  
  $logger->error('[{class} {code}] "{message} at {file}:{line}', [
    'class'   => get_class($e),
    'code'    => $e->getCode(),
    'message' => $e->getMessage(),
    'file'    => $e->getFile(),
    'line'    => $e->getLine(),
  ]);
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
You will need to add that as a submodule in a valid path as well.

<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{
	BodyInterface,
	CacheInterface,
	CookiesInterface,
	FormDataInterface,
	HeadersInterface,
	RequestInterface,
	ResponseInterface,
};

use \shgysk8zer0\HTTP\Abstracts\HTTPStatusCodes;

use \shgysk8zer0\PHPAPI\Interfaces\{
	CacheAwareInterface,
	LoggerAwareInterface,
	LoggerInterface,
};

use \shgysk8zer0\PHPAPI\Traits\{
	CacheAwareTrait,
	LoggerAwareTrait,
};

use \shgysk8zer0\PHPAPI\{NullLogger, NullCache};

use \JsonSerializable;

use \DateInterval;

use \Throwable;

use \InvalidArgumentException;

use \RuntimeException;

use \UnexpectedValueException;

class Request extends HTTPStatusCodes implements RequestInterface, LoggerAwareInterface, CacheAwareInterface, JsonSerializable
{
	use LoggerAwareTrait;

	use CacheAwareTrait;

	private const NO_BODY = [
		'GET',
		'HEAD',
		'OPTIONS',
		'DELETE',
	];

	// @SEE https://developer.mozilla.org/en-US/docs/Web/API/Request/cache
	private const CACHE_MODES = [
		'default',
		'no-cache',
		'reload',
		'force-cache',
		'only-if-cached',
		'no-store',
	];

	private const CREDENTIALS = [
		'omit',
		'include',
		'same-orign',
	];

	// @TODO enable & handle manual redirects
	private const REDIRECTS = [
		'follow',
		'error',
		// 'manual',
	];

	public const USER_AGENT = 'shgysk8zer0 HTTP';

	private $_url = null;

	private $_headers = null;

	private $_method = 'GET';

	private $_body   = null;

	private $_cache = 'no-store';

	private $_credentials = 'omit';

	private $_cookies;

	private $_expiration = null;

	private $_redirect = 'follow';

	private $_referrer = 'client';

	public function __construct(string $url, array $init = [])
	{
		$this->setLogger(new NullLogger());
		$this->setCache(new NullCache());

		$this->_cookies = new Cookies();

		$this->setUrl($url);

		if (array_key_exists('method', $init)) {
			$this->setMethod($init['method']);
		}

		if (array_key_exists('body', $init)) {
			$this->setBody($init['body']);
		}

		if (array_key_exists('cache', $init)) {
			$this->setCacheMode($init['cache']);
		}

		if (array_key_exists('credentials', $init)) {
			$this->setCredentials($init['credentials']);
		}

		if (array_key_exists('redirect', $init)) {
			$this->setRedirect($init['redirect']);
		}

		if (array_key_exists('referrer', $init)) {
			$this->setReferrer($init['referrer']);
		}

		if (! array_key_exists('headers', $init)) {
			$this->setHeaders(new Headers());
		} elseif (is_array($init['headers'])) {
			$this->setHeaders(new Headers($init['headers']));
		} elseif (! is_object($init['headers'])) {
			throw new InvalidArgumentException('Unsupported init data for Headers');
		} elseif ($init['headers'] instanceof HeadersInterface) {
			$this->setHeaders($init['headers']);
		} else {
			$this->setHeaders(new Headers(get_object_vars($init['headers'])));
		}
	}

	public function __debugInfo(): array
	{
		return [
			'url'         => $this->getUrl(),
			'method'      => $this->getMethod(),
			'headers'     => $this->getHeaders(),
			'redirect'    => $this->getRedirect(),
			'referrer'    => $this->getReferrer(),
			'body'        => $this->getBody(),
			'cache'       => $this->getCacheMode(),
			'credentials' => $this->getCredentials(),
			'cookies'     => $this->getCookies(),
		];
	}

	public function serialize(): string
	{
		return serialize([
			'url'         => $this->getUrl(),
			'method'      => $this->getMethod(),
			'headers'     => $this->getHeaders(),
			'redirect'    => $this->getRedirect(),
			'referrer'    => $this->getReferrer(),
			'body'        => $this->getBody(),
			'cache'       => $this->getCacheMode(),
			'credentials' => $this->getCredentials(),
			'expiration'  => $this->getExpiration(),
			'cookies'     =>$this->getCookies(),
		]);
	}

	public function unserialize($data): void
	{
		[
			'url'         => $url,
			'method'      => $method,
			'headers'     => $headers,
			'redirect'    => $redirect,
			'referrer'    => $referrer,
			'body'        => $body,
			'cache'       => $cache,
			'credentials' => $credentials,
			'expiration'  => $expiration,
			'cookies'     => $cookies,
		] = array_merge([
			'url'         => null,
			'method'      => null,
			'headers'     => new Headers(),
			'redirect'    => 'follow',
			'referrer'    => 'client',
			'body'        => null,
			'cache'       => 'default',
			'credentials' => 'omit',
			'expiration'  => null,
			'cookies'     => null,
		], unserialize($data));

		$this->setUrl($url);
		$this->setMethod($method);
		$this->setHeaders($headers);
		$this->setBody($body);
		$this->setCacheMode($cache);
		$this->setCredentials($credentials);
		$this->setExpiration($expiration);
		$this->setRedirect($redirect);
		$this->setReferrer($referrer);
		$this->_cookies = $cookies;
	}

	public function jsonSerialize(): array
	{
		return [
			'url'         => $this->getUrl(),
			'method'      => $this->getMethod(),
			'headers'     => $this->getHeaders(),
			'redirect'    => $this->getRedirect(),
			'referrer'    => $this->getReferrer(),
			'body'        => $this->getBody(),
			'cache'       => $this->getCacheMode(),
			'credentials' => $this->getCredentials(),
		];
	}

	public function __get(string $name)
	{
		switch($name) {
			case 'url':         return $this->getUrl();
			case 'method':      return $this->getMethod();
			case 'headers':     return $this->getHeaders();
			case 'redirect':    return $this->getRedirect();
			case 'referrer':    return $this->getReferrer();
			case 'body':        return $this->getBody();
			case 'cache':       return $this->getCacheMode();
			case 'credentials': return $this->getCredentials();
			case 'cookies':     return $this->getCookies();
			default: throw new InvalidArgumentException(sprintf('Invalid property: %s', $name));
		}
	}

	public function __invoke(?int $timeout = null, ?CacheInterface $cache = null):? ResponseInterface
	{
		if (isset($cache)) {
			$this->setCache($cache);
		}

		return $this->send($timeout);
	}

	public function text():? string
	{
		if ($body = $this->getBody()) {
			return $this->getBody()->text();
		} else {
			return null;
		}
	}

	public function json()
	{
		if ($text = $this->text()) {
			return @json_decode($text);
		} else {
			return null;
		}
	}

	public function formData():? FormDataInterface
	{
		if (isset($this->_body)) {
			return $this->getBody()->formData();
		} else {
			return null;
		}
	}

	public function getBody():? BodyInterface
	{
		return $this->_body;
	}

	public function setBody(?BodyInterface $val): void
	{
		$this->_body = $val;
	}

	public function getCacheMode(): string
	{
		return $this->_cache;
	}

	public function setCacheMode(string $val): void
	{
		if (in_array($val, self::CACHE_MODES)) {
			$this->_cache = $val;
		} else {
			throw new InvalidArgumentException(sprintf('Invalid cache mode: %s', $val));
		}
	}

	public function getCookies(): CookiesInterface
	{
		return $this->_cookies;
	}

	public function getCredentials(): string
	{
		return $this->_credentials;
	}

	public function setCredentials(string $val): void
	{
		if (in_array($val, self::CREDENTIALS)) {
			$this->_credentials = $val;
		} else {
			throw new InvalidArgumentException(sprintf('Invalid credentials mode: %s', $val));
		}
	}

	public function getExpiration(): DateInterval
	{
		if (isset($this->_expiration)) {
			return $this->_expiration;
		} else {
			return new DateInterval('PT1H');
		}
	}

	public function setExpiration(?DateInterval $val = null): void
	{
		if (isset($val)) {
			$this->_expiration = $val;
		} else {
			$this->_expiration = null;
		}
	}

	public function getMethod(): string
	{
		return $this->_method;
	}

	public function getHeaders(): HeadersInterface
	{
		if ($this->getCredentials() !== 'omit') {
			return $this->_headers;
		} else {
			$copy = clone($this->_headers);
			$copy->delete('cookie');
			return $copy;
		}
	}

	public function setHeaders(HeadersInterface $val): void
	{
		$this->_headers = $val;
	}

	public function setMethod(string $val): void
	{
		$this->_method = strtoupper($val);
	}

	public function getRedirect(): string
	{
		return $this->_redirect;
	}

	public function setRedirect(string $val): void
	{
		$this->_redirect = $val;
	}

	public function getReferrer(): string
	{
		return $this->_referrer;
	}

	public function setReferrer(string $val): void
	{
		$this->_referrer = $val;
	}

	public function getUrl():? string
	{
		return $this->_url;
	}

	public function setUrl(string $val): void
	{
		if (filter_var($val, FILTER_VALIDATE_URL)) {
			$this->_url = $val;
		}
	}

	/**
	 * Sends the HTTP request and returns the response
	 * @TODO Implement caching
	 * @return ResponseInterface
	 */
	public function send(?int $timeout = null):? ResponseInterface
	{
		if ($this->getMethod() === 'GET') {
			switch($this->getCacheMode()) {
				// @TODO determine cache expiration
				case 'default':
					if ($this->cache->has($this->getUrl())) {
						// @TODO check staleness
						return $this->cache->get($this->getUrl());
					} else {
						$resp = $this->_fetch($timeout);

						if (isset($resp) and $resp->getOk()) {
							$this->cache->set($this->getUrl(), $resp, $this->getExpiration());
						}

						return $resp;
					}

				case 'no-store':
					return $this->_fetch($timeout);

				case 'reload':
					$resp = $this->_fetch($timeout);

					if (isset($resp) and $resp->getOk()) {
						$this->cache->set($this->getUrl(), $resp, $this->getExpiration());
					}

					return $resp;

				case 'no-cache':
					if ($this->cache->has($this->getUrl())) {
						// @TODO check if stale
						return $this->cache->get($this->getUrl());
					} else {
						$resp = $this->_fetch($timeout);

						if (isset($resp) and $resp->getOk()) {
							$this->cache->set($this->getUrl(), $resp, $this->getExpiration());
						}

						return $resp;
					}

				case 'force-cache':
					if ($this->cache->has($this->getUrl())) {
						return $this->cache->get($this->getUrl());
					} else {
						$resp = $this->_fetch($timeout);

						if (isset($resp) and $resp->getOk()) {
							$this->cache->set($this->getUrl(), $resp, $this->getExpiration());
						}

						return $resp;
					}

				case 'only-if-cached':
					$fallback = new Response();
					$fallback->setUrl($this->getUrl());
					$fallback->setBody(new Body('Gateway Timeout'));
					$fallback->setHeaders(new Headers(['Content-Type' => 'text/plain']));
					$fallback->setStatus($this::GATEWAY_TIMEOUT);

					return $this->cache->get($this->getUrl(), $fallback);
			}
		} else {
			return $this->_fetch($timeout);
		}
	}

	public static function fromRequest(?LoggerInterface $logger = null): RequestInterface
	{
		$req = new self(URL::requestUrl(), [
			'headers'     => Headers::fromRequestHeaders(),
			'credentials' => 'include',
		]);

		$req->_cookies = Cookies::requestCookies();

		if (isset($logger)) {
			$req->setLogger($logger);
		} else {
			$logger = new NullLogger();
		}

		if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
			$req->setMethod($_SERVER['REQUEST_METHOD']);

			// These request methods are POST-like
			if (in_array($req->getMethod(), ['POST', 'PUT'])) {
				if ($req->getHeaders()->has('Content-Type')) {
					switch (strtolower(trim(explode(';', $req->getHeaders()->get('Content-Type'), 2)[0]))) {
						case 'application/x-www-form-urlencoded':
							$body = new FormData($_POST);
							break;
						case 'multipart/form-data':
							// @TODO handle all possible $_FILES structure (e.g. $_FILES['upload']['foo'][3])
							// @TODO determine how to handle errors
							$body = new FormData($_POST);

							foreach ($_FILES as $key => $value) {
								$logger->debug('Checking upload: $_FILES[\'{key}\']', ['key' => $key]);

								if (is_array($value['tmp_name'])) {
									for ($n = 0; $n < count($value['tmp_name']); $n++) {
										try {
											$body->append($key, new UploadFile(
												$value['tmp_name'][$n],
												$value['name'][$n] ?? $key,
												$value['type'][$n],
												$value['error'][$n],
												$value['size'][$n]
											));
										} catch (Throwable $e) {
											$logger->warning('[{class} {code}] "{message}" at {file}:{line}', [
												'class'   => get_class($e),
												'code'    => $e->getCode(),
												'message' => $e->getMessage(),
												'file'    => $e->getFile(),
												'line'    => $e->getLine(),
											]);
										}
									}
								} else {
									try {
										[
											'tmp_name' => $filename,
											'name'     => $name,
											'type'     => $mimetype,
											'error'    => $error,
											'size'     => $size,
										] = $value;

										$body->set($key, new UploadFile($filename, $mimetype, $name, $error, $size));
									} catch (Throwable $e) {
										$logger->warning('[{class} {code}] "{message}" at {file}:{line}', [
											'class'   => get_class($e),
											'code'    => $e->getCode(),
											'message' => $e->getMessage(),
											'file'    => $e->getFile(),
											'line'    => $e->getLine(),
										]);
									}

								}
							}
							$req->setBody($body);
							break;

						case 'text/plain':
						case 'text/json':
						case 'application/json':
						case 'text/html':
						case 'text/xml':
						case 'application/xml':
						case 'application/html':
						case 'application/xhtml+xml':
							$req->setBody(new Body(file_get_contents('php://input')));
							break;

						default:
							// @TODO handle missing text data and non-text data
							throw new UnexpectedValueException(sprintf('Unsupported Content-Type: %s', $req->getHeaders()->get('Content-Type')));
					}
				}

				unset($type);
			}
		} else {
			$req->setMethod('GET');
		}

		if (! $req->headers->has('cookie')) {
			$req->setCredentials('omit');
		}

		return $req;
	}

	private function _fetch(?int $timeout = null):? ResponseInterface
	{
		try {
			if (in_array($this->getMethod(), self::NO_BODY) and isset($this->_body)) {
				throw new RuntimeException(sprintf('%s requests cannot have a body', $this->getMethod()));
			}

			$ch = curl_init($this->getUrl());
			$referrer = $this->getReferrer();

			switch ($referrer) {
				case 'client':
					$url = URL::requestUrl();
					curl_setopt($ch, CURLOPT_REFERER, $url->getOrigin());
					unset($url);
					break;
				case 'no-referrer':
					// curl_setopt($ch, CURLOPT_REFERER, '');
					break;
				default:
					curl_setopt($ch, CURLOPT_REFERER, $referrer);
			}
			unset($referrer);

			if ($this->getRedirect()) {
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			} else {
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			}

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADEROPT,      CURLHEADER_UNIFIED);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_USERAGENT,      self::USER_AGENT);
			curl_setopt($ch, CURLOPT_HEADER,         true);
			curl_setopt($ch, CURLOPT_SAFE_UPLOAD,    true);

			// @TODO improve handling of cookies via CURLOPT_COOKIE

			if (is_int($timeout)) {
				curl_setopt($ch, CURLOPT_TIMEOUT_MS,     $timeout);
			}
			// @TODO implement cookies & auth
			$headers = [];

			switch($this->getMethod()) {
				case 'GET':
					curl_setopt($ch, CURLOPT_HTTPGET, true);
					break;

				case 'POST':
					// @TODO set body
					curl_setopt($ch, CURLOPT_POST, true);
					// Errors in POST by setting "Expect: 100-continue" header
					$headers[] = 'Expect:';
					break;

				default:
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->getMethod());
			}


			if ($this->getCredentials() !== 'include') {
				$this->getHeaders()->delete('cookie');
			}

			foreach ($this->getHeaders()->entries() as $entry) {
				$headers[] = "{$entry[0]}: {$entry[1]}";
			}

			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			if (isset($this->_body)) {
				if ($type = $this->getBody()->getContentTypeHeader()) {
					$headers = [$type];

					unset($type);
				} else {
					$headers = [];
				}

				if (! $this->getBody()->setPostFields($ch)) {
					$this->logger->error('Error setting post fields for {class}', [
						'class' => get_class($this->getBody()),
					]);

					return null;
				}
			} else {
				$headers = [];
			}

			$body = curl_exec($ch);

			$errno = curl_errno($ch);

			if ($errno !== CURLE_OK) {
				switch ($errno) {
					case CURLE_OPERATION_TIMEDOUT:
						$this->logger->error('{method} {url} timeout [{timeout}ms]', [
							'method'  => $this->getMethod(),
							'url'     => $this->getUrl(),
							'timeout' => $timeout,
						]);
						$resp = new Response();
						$resp->setStatus(self::GATEWAY_TIMEOUT);
						$resp->setHeaders(new Headers(['Content-Type' => 'text/plain']));
						$resp->setUrl($this->getUrl());
						$resp->setBody(new Body('Gateway Timeout'));
						return $resp;
						break;
					default:
						$this->logger->error('cURL error [{errno}] "{{error}', [
							'errno' => $errno,
							'error' => curl_error($ch),
						]);
						$resp = new Response();
						$resp->setStatus(self::BAD_GATEWAY);
						$resp->setHeaders(new Headers(['Content-Type' => 'text/plain']));
						$resp->setBody(new Body('An unknown error occured'));

						return $resp;
				}

				curl_close($ch);

				return null;
			} else {
				// handle success
				$status      = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
				$url         = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
				$cookies     = curl_getinfo($ch, CURLINFO_COOKIELIST);
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$headers     = Headers::parseFromCurlResponse(substr($body, 0, $header_size));
				$body        = substr($body, $header_size);

				if ($this->getCredentials() !== 'include') {
					$headers->delete('cookie');
					$cookies = new Cookies();
				} else {
					$cookies = Cookies::parseHeader($headers->get('cookie'));
				}
				\shgysk8zer0\PHPAPI\Console::info($cookies);
				if (! in_array($this->getMethod(), ['HEAD', 'OPTIONS'])) {
					$response = new Response(new Body($body), [
						'status'  => $status ?? self::INTERNAL_SERVER_ERROR,
						'headers' => $headers,
					]);
				} else {
					$response = new Response(null, [
						'status'  => $status ?? self::INTERNAL_SERVER_ERROR,
						'headers' => $headers,
					]);
				}

				$response->setUrl($url);
				$response->setCookies($cookies);
				$response->setRedirected($response->getUrl() !== $this->getUrl());
				$response->setLogger($this->logger);
				$response->setCache($this->cache);
			}

			curl_close($ch);

			return $response;
		} catch (Throwable $e) {
			$this->logger->error('[{class} {code}] "{message}"" at {file}:{line}', [
				'message' => $e->getMessage(),
				'class'   => get_class($e),
				'code'    => $e->getCode(),
				'file'    => $e->getFile(),
				'line'    => $e->getLine(),
			]);

			if (isset($ch)) {
				@curl_close($ch);
			}

			return null;
		}
	}

	private function _isStale(int $timeout = 200): bool
	{
		return false;
		/*if ($this->getMethod() === 'GET') {
			$cp = clone($this);
			$cp->setMethod('HEAD');

			if ($resp = $cp->send(500)) {
				return $resp->isOk();
			} else {
				return true;
			}
		} else {
			return true;
		}*/
	}
}

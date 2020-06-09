<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{
	BodyInterface,
	FormDataInterface,
	HeadersInterface,
	RequestInterface,
	ResponseInterface,
};

use \shgysk8zer0\HTTP\Abstracts\HTTPStatusCodes;

use \shgysk8zer0\PHPAPI\Interfaces\{
	CacheAwareInterface,
	LoggerAwareInterface,
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

	private $_url = null;

	private $_headers = null;

	private $_method = 'GET';

	private $_body   = null;

	public function __construct(string $url, array $init = [])
	{
		$this->setLogger(new NullLogger());
		$this->setCache(new NullCache());

		$this->setUrl($url);

		if (array_key_exists('method', $init)) {
			$this->setMethod($init['method']);
		}

		if (array_key_exists('body', $init)) {
			$this->setBody($init['body']);
		}

		if (array_key_exists('headers', $init)) {
			if (is_array($init['headers'])) {
				$this->setHeaders(new Headers($init['headers']));
			} elseif (is_object($init['headers'])) {
				if ($init['headers'] instanceof HeadersInterface) {
					$this->setHeaders($init['headers']);
				} else {
					$this->setHeaders(new Headers(get_object_vars($init['headrs'])));
				}
			} else {
				throw new InvalidArgumentException('Unsupported init data for Headers');
			}
		} else {
			$this->setHeaders(new Headers());
		}
	}

	public function __debugInfo(): array
	{
		return [
			'url'     => $this->getUrl(),
			'method'  => $this->getMethod(),
			'headers' => $this->getHeaders(),
			'body'    => $this->getBody(),
		];
	}

	public function serialize(): string
	{
		return serialize([
			'url'     => $this->getUrl(),
			'method'  => $this->getMethod(),
			'headers' => $this->getHeaders(),
			'body'    => $this->getBody(),
		]);
	}

	public function unserialize($data): void
	{
		[
			'url'     => $url,
			'method'  => $method,
			'headers' => $headers,
			'body'    => $body,
		] = array_merge([
			'url'     => null,
			'method'  => null,
			'headers' => null,
			'body'    => null,
		], unserialize($data));

		$this->setUrl($url);
		$this->setMethod($method);
		$this->setHeaders($headers);
		$this->setBody($body);
	}

	public function jsonSerialize(): array
	{
		return [
			'url'     => $this->getUrl(),
			'method'  => $this->getMethod(),
			'headers' => $this->getHeaders(),
			'body'    => $this->getBody(),
		];
	}

	public function __get(string $name)
	{
		switch($name) {
			case 'url': return $this->getUrl();
			case 'method': return $this->getMethod();
			case 'headers': return $this->getHeaders();
			case 'body': return $this->getBody();
			default: throw new InvalidArgumentException(sprintf('Invalid property: %s', $name));
		}
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

	public function setBody(BodyInterface $val): void
	{
		$this->_body = $val;
	}

	public function getMethod(): string
	{
		return $this->_method;
	}

	public function getHeaders(): HeadersInterface
	{
		return $this->_headers;
	}

	public function setHeaders(HeadersInterface $val): void
	{
		$this->_headers = $val;
	}

	public function setMethod(string $val): void
	{
		$this->_method = strtoupper($val);
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
	public function send(int $timeout = 2000):? ResponseInterface
	{
		try {
			if (in_array($this->getMethod(), self::NO_BODY) and isset($this->_body)) {
				throw new RuntimeException(sprintf('%s requests cannot have a body', $this->getMethod()));
			}

			$ch = curl_init($this->getUrl());

			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADEROPT,      CURLHEADER_UNIFIED);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_TIMEOUT_MS,     $timeout);
			curl_setopt($ch, CURLOPT_USERAGENT,      __CLASS__);
			curl_setopt($ch, CURLOPT_HEADER,         true);
			curl_setopt($ch, CURLOPT_SAFE_UPLOAD,    true);

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

			if (curl_errno($ch) !== CURLE_OK) {
				$errno = curl_errno($ch);
				switch ($errno) {
					case CURLE_OPERATION_TIMEDOUT:
					$this->logger->error('{method} {url} timeout [{timeout}ms]', [
						'method'  => $this->getMethod(),
						'url'     => $this->getUrl(),
						'timeout' => $timeout,
					]);
					break;

				}

				throw new RuntimeException(curl_error($ch), $errno);
			} else {
				// handle success
				$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
				$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
				$cookies = curl_getinfo($ch, CURLINFO_COOKIELIST);
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$headers = Headers::parseFromCurlResponse(substr($body, 0, $header_size));
				$body = substr($body, $header_size);

				$response = new Response();
				$response->setLogger($this->logger);
				$response->setUrl($url);

				if ($this->getMethod() !== 'HEAD') {
					$response->setBody(new Body($body));
				}

				$response->setStatus($status);
				$response->setHeaders($headers);
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
}

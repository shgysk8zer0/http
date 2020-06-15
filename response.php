<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{
	BodyInterface,
	CookieInterface,
	CookiesInterface,
	HeadersInterface,
	ResponseInterface,
};

use \shgysk8zer0\HTTP\Traits\{
	ResponseTrait,
};

use \shgysk8zer0\HTTP\Abstracts\HTTPStatusCodes;

use \shgysk8zer0\PHPAPI\Interfaces\{
	LoggerAwareInterface,
	CacheAwareInterface,
};

use \shgysk8zer0\PHPAPI\Traits\{
	LoggerAwareTrait,
	CacheAwareTrait,
};

use \shgysk8zer0\PHPAPI\{NullLogger, NullCache};

use \InvalidArgumentException;

use \JsonSerializable;

use \Throwable;

class Response extends HTTPStatusCodes implements ResponseInterface, LoggerAwareInterface, CacheAwareInterface, JsonSerializable
{
	use LoggerAwareTrait;

	use CacheAwareTrait;

	use ResponseTrait;

	public function __construct(?BodyInterface $body = null, array $init = [])
	{
		$this->setBody($body);
		$this->setLogger(new NullLogger());
		$this->setCache(new NullCache());

		$this->setCookies(new Cookies());

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

		if (array_key_exists('status', $init)) {
			$this->setStatus($init['status']);
		}
	}

	public function serialize(): string
	{
		return serialize([
			'url'        => $this->getUrl(),
			'status'     => $this->getStatus(),
			'headers'    => $this->getHeaders(),
			'redirect'   => $this->getRedirected(),
			'body'       => $this->getBody(),
			'cookies'    => $this->getCookies(),
		]);
	}

	public function unserialize($data): void
	{
		[
			'url'        => $url,
			'status'     => $status,
			'headers'    => $headers,
			'body'       => $body,
			'redirected' => $redirected,
			'cookies'    => $cookies,
		] = array_merge([
			'url'        => null,
			'status'     => self::OK,
			'headers'    => new Headers(),
			'body'       => null,
			'redirected' => false,
			'cookies'    => new Cookies(),
		], unserialize($data));

		$this->setUrl($url);
		$this->setStatus($status);
		$this->setHeaders($headers);
		$this->setBody($body);
		$this->_cookies = $cookies;
	}

	public function jsonSerialize(): array
	{
		return [
			'url'        => $this->getUrl(),
			'status'     => $this->getStatus(),
			'statusText' => $this->getStatusText(),
			'headers'    => $this->getHeaders(),
			'redirected' => $this->getRedirected(),
			'body'       => $this->getBody(),
		];
	}

	public function __toString(): string
	{
		return $this->text() ?? '';
	}

	public function __debugInfo(): array
	{
		return [
			'url'        => $this->getUrl(),
			'status'     => $this->getStatus(),
			'statusText' => $this->getStatusText(),
			'headers'    => $this->getHeaders(),
			'redirected' => $this->getRedirected(),
			'body'       => $this->getBody(),
			'cookies'    => $this->getCookies(),
		];
	}

	public function send(): bool
	{
		if (headers_sent()) {
			$this->logger->error('Cannot send response after headers have been sent');
			return false;
		} else {
			try {
				// @TODO check URL
				http_response_code($this->getStatus());


				if ($headers = clone($this->getHeaders())) {
					foreach ($this->_cookies->values() as $cookie) {
						$headers->append('Set-Cookie', $cookie);
					}
					$headers->delete('upgrade');
					$headers->delete('connection');
					$headers->delete('server');
					$headers->delete('date');
					$headers->delete('transfer-encoding');
					$headers->delete('host');
					$headers->delete('x-powered-by');

					$headers->send();
				}

				echo $this->text();
				return true;
			} catch (Throwable $e) {
				$this->logger->error('[{class} {code}] "{message}" at {file}:{line}', [
					'class'   => get_class($e),
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
					'file'    => $e->getFile(),
					'line'    => $e->getLine(),
				]);

				return false;
			}
		}
	}
}

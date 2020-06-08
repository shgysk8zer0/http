<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{
	BodyInterface,
	HeadersInterface,
	ResponseInterface,
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

	private $_status = self::OK;

	private $_url = null;

	private $_headers = null;

	private $_body = null;

	public function __construct()
	{
		$this->setLogger(new NullLogger());
		$this->setCache(new NullCache());
	}

	public function serialize(): string
	{
		return serialize([
			'url'        => $this->getUrl(),
			'status'     => $this->getStatus(),
			'headers'    => $this->getHeaders(),
			'body'       => $this->getBody(),
		]);
	}

	public function unserialize($data): void
	{
		if ($parsed = unserialize($data)) {
			$this->setUrl($parsed['url']);
			$this->setStatus($parsed['status']);
			$this->setHeaders($parsed['headers']);
			$this->setBody($parsed['body']);
		}
	}

	public function jsonSerialize(): array
	{
		return [
			'url'        => $this->getUrl(),
			'status'     => $this->getStatus(),
			'statusText' => $this->getStatusText(),
			'headers'    => $this->getHeaders(),
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
			'body'       => $this->getBody(),
		];
	}

	public function __get(string $name)
	{
		switch($name) {
			case 'url': return $this->getUrl();
			case 'status': return $this->getStatus();
			case 'statusText': return $this->getStatusText();
			case 'headers': return $this->getHeaders();
			case 'body': return $this->getBody();
			default: throw new InvalidArgumentException(sprintf('Invalid property: %s', $name));
		}
	}

	public function getStatus(): int
	{
		return $this->_status;
	}

	public function setStatus(int $val): void
	{
		$this->_status = $val;
	}

	public function getStatusText():? string
	{
		// @TODO Finish filling out status code text
		switch($this->getStatus()) {
			case self::CONT: return 'Continue';
			case self::SWITCHING_PROTOCOLS: return 'Switching Protocols';
			case self::PROCESSING: return 'Processing';
			case self::OK: return 'Ok';
			case self::CREATED: return 'Created';
			case self::ACCEPTED: return 'Accepted';
			case self::NON_AUTHORITATIVE: return 'Non-Authoritative';
			case self::NO_CONTENT: return 'No Content';
			case self::BAD_REQUEST: return 'Bad Request';
			case self::UNAUTHORIZED: return 'Unauthorized';
			case self::PAYMENT_REQUIRED: return 'Payment Required';
			case self::FORBIDDEN: return 'Forbidden';
			case self::NOT_FOUND: return 'Not Found';
			case self::METHOD_NOT_ALLOWED: return 'Not Found';
			case self::INTERNAL_SERVER_ERROR: return 'Internal Server Error';
			default: return 'Unknown';
		}
	}

	public function getHeaders():? HeadersInterface
	{
		return $this->_headers;
	}

	public function setHeaders(HeadersInterface $val): void
	{
		$this->_headers = $val;
	}

	public function getUrl():? string
	{
		return $this->_url;
	}

	public function setUrl(?string $val): void
	{
		if (isset($val) and filter_var($val, FILTER_VALIDATE_URL)) {
			$this->_url = $val;
		} elseif (isset($val)) {
			throw new InvalidArgumentException(sprintf('%s is not a valid URL', $val));
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

	public function getOk(): bool
	{
		$status = $this->getStatus();
		return ($status > 199 && $status < 300);
	}

	public function text():? string
	{
		if (isset($this->_body)) {
			return $this->getBody()->text();
		} else {
			return null;
		}
	}

	public function json()
	{
		if ($text = $this->text()) {
			return json_decode($text);
		} else {
			return null;
		}
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

<?php

namespace shgysk8zer0\HTTP\Traits;

use \shgysk8zer0\HTTP\{
	Headers,
	Body,
	URL,
	Cookies,
	Cookie,
};

use \shgysk8zer0\HTTP\Interfaces\{
	BodyInterface,
	CookieInterface,
	CookiesInterface,
	HeadersInterface,
	ResponseInterface,
};

use \shgysk8zer0\HTTP\Abstracts\{
	HTTPStatusCodes as HTTP,
};

use \shgysk8zer0\PHPAPI\Interfaces\{
	LoggerAwareInterface,
	CacheAwareInterface,
};

use \InvalidArgumentException;

trait ResponseTrait
{
	private $_status = HTTP::OK;

	private $_url = null;

	private $_headers = null;

	private $_body = null;

	private $_redirected = false;

	private $_cookies;

	public function __get(string $name)
	{
		switch($name) {
			case 'url':        return $this->getUrl();
			case 'ok':         return $this->getOk();
			case 'status':     return $this->getStatus();
			case 'statusText': return $this->getStatusText();
			case 'headers':    return $this->getHeaders();
			case 'redirectd':  return $this->getRedirected();
			case 'body':       return $this->getBody();
			case 'cookies':    return $this->getCookies();
			default: throw new InvalidArgumentException(sprintf('Invalid property: %s', $name));
		}
	}

	public function redirect(string $url, int $status = HTTP::FOUND): ResponseInterface
	{
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			$this->getHeaders()->set('Location', $url);
			$this->setStatus($status);
			$this->setBody(null);
			return $this;
		} else {
			throw new InvalidArgumentException(sprintf('%s is not a valid URL', $url));
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
			case HTTP::CONT:                            return 'Continue';
			case HTTP::SWITCHING_PROTOCOLS:             return 'Switching Protocols';
			case HTTP::PROCESSING:                      return 'Processing';
			case HTTP::OK:                              return 'Ok';
			case HTTP::CREATED:                         return 'Created';
			case HTTP::ACCEPTED:                        return 'Accepted';
			case HTTP::NON_AUTHORITATIVE:               return 'Non-Authoritative';
			case HTTP::NO_CONTENT:                      return 'No Content';
			case HTTP::BAD_REQUEST:                     return 'Bad Request';
			case HTTP::UNAUTHORIZED:                    return 'Unauthorized';
			case HTTP::PAYMENT_REQUIRED:                return 'Payment Required';
			case HTTP::FORBIDDEN:                       return 'Forbidden';
			case HTTP::NOT_FOUND:                       return 'Not Found';
			case HTTP::METHOD_NOT_ALLOWED:              return 'Not Found';
			case HTTP::NOT_IMPLEMENTED:                 return 'Not Implemented';
			case HTTP::BAD_GATEWAY:                     return 'Bad Gateway';
			case HTTP::REQUEST_HEADER_FILEDS_TOO_LARGE: return 'Request Header Fields Too Large';
			case HTTP::INTERNAL_SERVER_ERROR:           return 'Internal Server Error';
			case HTTP::SERVICE_UNAVAILABLE:             return 'Service Unavailable';
			case HTTP::GATEWAY_TIMEOUT:                 return 'Gateway Timeout';
			default:                                    return 'Unknown';
		}
	}

	public function getCookies(): CookiesInterface
	{
		return $this->_cookies;
	}

	public function setCookies(CookiesInterface $value): void
	{
		$this->_cookies = $value;
	}

	public function hasCookies(string ...$names): bool
	{
		return $this->_cookies->has(...$names);
	}

	public function expireCookie(string $name): bool
	{
		return $this->_cookies->expire($name);
	}

	public function addCookie(CookieInterface $value): bool
	{
		return $this->_cookies->add($value);
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

	public function getRedirected(): bool
	{
		return $this->_redirected;
	}

	public function setRedirected(bool $val): void
	{
		$this->_redirected = $val;
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

}

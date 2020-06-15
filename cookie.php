<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\CookieInterface;

use \shgysk8zer0\HTTP\Traits\SerializableCookieTrait;

use \DateInterval;

use \DateTimeInterface;

use \DateTimeImmutable;

use \InvalidArgumentException;

use \JsonSerializable;

use \Serializable;

class Cookie implements CookieInterface, Serializable
{
	use SerializableCookieTrait;

	public const HEADER_NAME = 'Set-Cookie';

	public function __construct(
		string  $name,
		?string $value = null,
		array   $init  = []
	)
	{
		$this->setName($name);
		$this->setValue($value);

		if (array_key_exists('maxAge', $init)) {
			$this->setMaxAge($init['maxAge']);
		}

		if (array_key_exists('expires', $init)) {
			if ($init['expires'] instanceof DateTimeInterface) {
				$this->setExpires($init['expires']);
			} elseif ($init['expires'] instanceof DateInterval) {
				$this->expiresIn($init['expires']);
			} else {
				$this->setExpires(new DateTimeImmutable($init['expires']));
			}
		}

		if (array_key_exists('path', $init)) {
			$this->setPath($init['path']);
		}

		if (array_key_exists('domain', $init)) {
			$this->setDomain($init['domain']);
		}

		if (array_key_exists('sameSite', $init)) {
			$this->setSameSite($init['sameSite']);
		}

		if (array_key_exists('secure', $init)) {
			$this->setSecure($init['secure']);
		}

		if (array_key_exists('httpOnly', $init)) {
			$this->setHttpOnly($init['httpOnly']);
		}
	}

	public function __toString(): string
	{
		$name      = $this->getName();
		$value     = $this->getValue();
		$path      = $this->getPath();
		$domain    = $this->getDomain();
		$max_age   = $this->getMaxAge();
		$expires   = $this->getExpiresAsString();
		$http_only = $this->getHttpOnly();
		$secure    = $this->getSecure();
		$same_site = $this->getSameSite();

		if (isset($value)) {
			$cookie = sprintf('%s=%s', urlencode($name), urlencode($value));
		} else {
			$cookie = sprintf('%s=', urlencode($name));
		}

		if (isset($max_age)) {
			$cookie .= sprintf(';Max-Age=%d', $max_age);
		} elseif (isset($expires)) {
			$cookie .= sprintf(';Expires=%s', $expires);
		}

		if (isset($path)) {
			$cookie .= sprintf(';Path=%s', $path);
		}

		if (isset($domain)) {
			$cookie .= sprintf(';Domain=%s', $domain);
		}

		if (isset($same_site)) {
			$cookie .= sprintf(';SameSite=%s', $same_site);
		}

		if ($http_only) {
			$cookie .= ';HttpOnly';
		}

		if ($secure) {
			$cookie .= ';Secure';
		}

		return $cookie;
	}

	public function __debugInfo(): array
	{
		return [
			'name'     => $this->getName(),
			'value'    => $this->getValue(),
			'domain'   => $this->getDomain(),
			'path'     => $this->getPath(),
			'maxAge'   => $this->getMaxAge(),
			'expires'  => $this->getExpiresAsString(),
			'sameSite' => $this->getSameSite(),
			'httpOnly' => $this->getHttpOnly(),
			'secure'   => $this->getSecure(),
		];
	}

	public function jsonSerialize(): string
	{
		return $this->__toString();
	}

	public function send(): bool
	{
		if (! function_exists('header')) {
			return false;
		} elseif (headers_sent()) {
			return false;
		} else {
			header(sprintf('%s: %s', self::HEADER_NAME, $this), true);
			return true;
		}
	}

	public function expire(): bool
	{
		$this->setMaxAge(-1);
		$this->setValue(null);
		return true;
	}
}

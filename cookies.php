<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{
	CookieInterface,
	CookiesInterface,
	HeadersInterface,
};

use \Serializable;

class Cookies implements CookiesInterface, Serializable
{
	private $_list = [];

	public const HEADER_NAME = 'Set-Cookie';

	public function __construct(CookieInterface ...$cookies)
	{
		foreach ($cookies as $cookie) {
			$this->add($cookie);
		}
	}

	public function __debugInfo(): array
	{
		return $this->_list;
	}

	public function serialize(): string
	{
		return serialize($this->_list);
	}

	public function unserialize($data): void
	{
		$this->_list = unserialize($data);
	}

	public function add(CookieInterface $cookie): bool
	{
		$this->_list[strtolower($cookie->getName())] = $cookie;
		return true;
	}

	public function set(string $name, ?string $value, ...$args): bool
	{
		$this->add(new Cookie($name, $value, $args));
	}

	public function get(string $name):? CookieInterface
	{
		if ($this->has($name)) {
			return $this->_list[strtolower($name)];
		}
	}

	public function has(string ...$names): bool
	{
		$has = true;

		foreach($names as $name) {
			if (! array_key_exists(strtolower($name), $this->_list)) {
				$has = false;
				break;
			}
		}

		return $has;
	}

	public function delete(string $name): bool
	{
		if ($this->has($name)) {
			unset($this->_list[strtolower($name)]);
			return true;
		} else {
			return false;
		}
	}

	public function expire(string $name): bool
	{
		if ($this->has($name)) {
			return $this->get($name)->expire();
		} else {
			return false;
		}
	}

	public function keys(): iterable
	{
		foreach (array_keys($this->_list) as $key) {
			yield $key;
		}
	}

	public function values(): iterable
	{
		foreach (array_values($this->_list) as $value) {
			yield $value;
		}
	}

	public function entries(): iterable
	{
		foreach ($this->_list as $key => $value) {
			yield [$key, $value];
		}
	}

	public function send(): bool
	{
		if (! function_exists('header')) {
			return false;
		} elseif (headers_sent()) {
			return false;
		} else {
			foreach ($this->values() as $cookie) {
				$cookie->send();
			}
			return true;
		}
	}

	public static function parseHeader(?string $cookie): CookiesInterface
	{
		$cookies = [];

		if (isset($cookie)) {
			foreach (explode(';', $cookie) as $entry) {
				[$name, $value] = explode('=', trim($entry), 2);
				$cookies[] = new Cookie($name, $value);
			}
		}

		return new self(...$cookies);
	}

	public static function fromHeadersObject(HeadersInterface $headers): CookiesInterface
	{
		if ($headers->has('cookie')) {
			return static::parseHeader($headers->get('cookie'));
		} else {
			return new self();
		}
	}

	/**
	 * Create a Cookies object containing all request cookies as key=value
	 * Note: Cannot determine any other params as they are not set in Cookie header
	 */
	public static function requestCookies(): CookiesInterface
	{
		if (is_array($_COOKIE)) {
			$cookies = new self();

			foreach ($_COOKIE as $name => $value) {
				$cookies->add(new Cookie($name, $value));
			}

			\shgysk8zer0\PHPAPI\Console::log($cookies);

			return $cookies;
		} elseif (array_key_exists('HTTP_COOKIE', $_SERVER)) {
			return static::parseHeader($_SERVER['HTTP_COOKIE']);
		} else {
			return new self();
		}
	}
}

<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{
	URLInterface,
	URLSearchParamsInterface,
};

use \InvalidArgumentException;

use \JsonSerializable;

class URL implements URLInterface, JsonSerializable
{
	private const DEFAULTS = [
		'scheme'   => null,
		'host'     => null,
		'path'     => '/',
		'port'     => null,
		'query'    => null,
		'fragment' => null,
		'user'     => null,
		'pass'     => null,
	];

	private $_protocol = null;

	private $_hostname = null;

	private $_port = null;

	private $_pathname = null;

	private $_search_params = null;

	private $_hash = null;

	private $_username = null;

	private $_password = null;

	public function __construct(string $url, ?string $base = null)
	{
		if (isset($base) and filter_var($base, FILTER_VALIDATE_URL)) {
			$parsed = array_merge(self::DEFAULTS, parse_url($base), parse_url($url));
			$this->setHostname($parsed['host']);
			$this->setProtocol($parsed['scheme']);
			$this->setPathname($parsed['path']);
			$this->setPort($parsed['port']);
			$this->setSearch($parsed['query']);
			$this->setHash($parsed['fragment']);
			$this->setusername($parsed['user']);
			$this->setPassword($parsed['pass']);
		} else {
			$this->setHref($url);
		}
	}

	public function __debugInfo(): array
	{
		return [
			'protocol'     => $this->getProtocol(),
			'username'     => $this->getUsername(),
			'password'     => $this->getPassword(),
			'hostname'     => $this->getHostname(),
			'port'         => $this->getPort(),
			'pathname'     => $this->getPathname(),
			'searchParams' => $this->getSearchParams(),
			'hash'         => $this->getHash(),
		];
	}

	public function jsonSerialize(): string
	{
		return $this->getHref();
	}

	public function __toString(): string
	{
		return $this->getHref();
	}

	public function __get(string $name)
	{
		switch($name) {
			case 'protocol': return $this->getProtocol();
			case 'host': return $this->getHost();
			case 'origin': return $this->getOrigin();
			case 'hostname': return $this->getHostname();
			case 'port': return $this->getPort();
			case 'pathname': return $this->getPathname();
			case 'search': return $this->getSearch();
			case 'searchParams': return $this->getSearchParams();
			case 'username': return $this->getUsername();
			case 'password': return $this->getPassword();
			case 'hash': return $this->gethash();
			case 'href': return $this->getHref();
			default: throw new InvalidArgumentException(sprintf('Unknown URL property: %s', $name));
		}
	}

	public function __set(string $name, $val): void
	{
		switch($name) {
			case 'protocol':
				$this->setProtocol($val);
				break;

			case 'host':
				$this->setHost($val);
				break;

			case 'origin':
				$this->setOrigin($val);
				break;

			case 'hostname':
				$this->setHostname($val);
				break;

			case 'pathname':
				$this->setPathname($val);
				break;

			case 'port':
				$this->setPort($val);
				break;

			case 'search':
				$this->setSearch($val);
				break;

			case 'searchParams':
				$this->setSearchParams($val);
				break;

			case 'username':
				$this->setUsername($val);
				break;

			case 'password':
				$this->setPassword($val);
				break;

			case 'hash':
				$this->setHash($val);
				break;

			case 'href':
				$this->setHref($val);
				break;

			default:
				throw new InvalidArgumentException(sprintf('Unknown URL property: %s', $name));
		}
	}

	public function __unset(string $name): void
	{
		switch($name) {
			case 'pathname':
				$this->setPathname(null);
				break;

			case 'username':
				$this->setUsername(null);
				break;

			case 'password':
				$this->setPassword(null);
				break;

			case 'hash':
				$this->setHash(null);
				break;

			case 'search':
			case 'searchParams':
				$this->setSearchParams(new URLSearchParams());
				break;

			default:
				throw new InvalidArgumentException(sprintf('Cannot delete property %s', $name));
		}
	}

	public function serialize(): string
	{
		return ['href' => $this->getHref()];
	}

	public function unserialize($data): void
	{
		$parsed = unserialize($data);

		if (is_array($parsed) and is_string($parsed['href'])) {
			$this->setHref($parsed['href']);
		}
	}

	public function getHref(): string
	{
		if (is_null($this->_username)) {
			$url = $this->getOrigin() . $this->getPathname();
		} elseif (is_null($this->_password)) {
			$url = "{$this->getProtocol()}//" . urlencode($this->getUsername()) . '@' . $this->getHostname();
			if (isset($this->_port)) {
				$url .= ":{$this->getPort()}";
			}
		} else {
			$url = "{$this->getProtocol()}//" . urlencode($this->getUsername()) . ':' . urlencode($this->getPassword()) . '@' . $this->getHostname();
			if (isset($this->_port)) {
				$url .= ":{$this->getPort()}";
			}
		}

		$url .= $this->getSearch();

		$url .= $this->getHash(true);

		return $url;
	}

	public function setHref(string $val): void
	{
		if (filter_var($val, FILTER_VALIDATE_URL)) {
			$parsed = array_merge(self::DEFAULTS, parse_url($val));
			$this->setHostname($parsed['host']);
			$this->setProtocol($parsed['scheme']);
			$this->setPathname($parsed['path']);
			$this->setPort($parsed['port']);
			$this->setSearch($parsed['query']);
			$this->setHash($parsed['fragment']);
			$this->setusername($parsed['user']);
			$this->setPassword($parsed['pass']);
		} else {
			throw new InvalidArgumentException(sprintf('%s is not a valid URL', $val));
		}
	}

	public function getProtocol(): string
	{
		return $this->_protocol . ':';
	}

	public function setProtocol(string $val): void
	{
		$this->_protocol = rtrim($val, ':');
	}

	public function getHostname(bool $escape = false): string
	{
		return $escape ? urlencode($this->_hostname) : $this->_hostname;
	}

	public function setHostname(string $val): void
	{
		$this->_hostname = $val;
	}

	public function getHost(): string
	{
		if (isset($this->_port)) {
			return "{$this->getHostname(true)}:{$this->getPort()}";
		} else {
			return $this->getHostname(true);
		}
	}

	public function setHost(string $val): void
	{
		[
			'port' => $port,
			'host' => $host
		] = array_merge([
			'port' => null,
			'host' => null,
		], parse_url($val));

		if (isset($host)) {
			$this->setHost($host);
			$this->setPort($port);
		} else {
			throw new InvalidArgumentException('%s is not a valid URL host', $val);
		}
	}

	public function getOrigin(): string
	{
		return "{$this->getProtocol()}//{$this->getHost()}";
	}

	public function setOrigin(string $val): void
	{
		[
			'scheme' => $scheme,
			'host'   => $host,
			'port'   => $port,
		] = array_merge([
			'scheme' => null,
			'host'   => null,
			'port'   => null,
		], parse_url($val));

		if (isset($scheme, $host)) {
			$this->setHostname($host);
			$this->setProtocol($scheme);
			$this->setPort($port);
		} else {
			throw new InvalidArgumentException(sprintf('%s is not a valid URL', $val));
		}
	}

	public function getPort():? int
	{
		return $this->_port;
	}

	public function setPort(?int $val): void
	{
		$this->_port = $val;
	}

	public function getPathname(bool $escape = false): string
	{
		if (isset($this->_pathname)) {
			return $escape ? urlencode($this->_pathname) : $this->_pathname;
		} else {
			return '/';
		}
	}

	public function setPathname(?string $val): void
	{
		if (isset($val)) {
			$this->_pathname = '/' . ltrim($val, '/');
		} else {
			$this->_pathname = '/';
		}
	}

	public function getSearch(): string
	{
		if ($this->getSearchParams()->length() === 0) {
			return '';
		} else {
			return "?{$this->getSearchParams()}";
		}
	}

	public function setSearch(?string $val): void
	{
		$this->setSearchParams(new URLSearchParams($val));
	}

	public function getSearchParams(): URLSearchParamsInterface
	{
		return $this->_search_params;
	}

	public function setSearchParams(URLSearchParamsInterface $val): void
	{
		$this->_search_params = $val;
	}

	public function getHash(bool $escape = false): string
	{
		if (isset($this->_hash)) {
			return $escape ? '#' . urlencode($this->_hash) : '#' . $this->_hash;
		} else {
			return '';
		}
	}

	public function setHash(?string $val): void
	{
		if (isset($val)) {
			$this->_hash = ltrim($val, '#');
		} else {
			$this->_hash = null;
		}
	}

	public function getUsername(bool $escape = false):? string
	{
		if (isset($this->_username)) {
			return $escape ? urlencode($this->_username) : $this->_username;
		} else {
			return null;
		}
	}

	public function setUsername(?string $val): void
	{
		$this->_username = $val;
	}

	public function getPassword(bool $escape = false):? string
	{
		if (isset($this->_password)) {
			return $escape ? urlencode($this->_password) : $this->_password;
		} else {
			return null;
		}
	}

	public function setPassword(?string $val): void
	{
		$this->_password = $val;
	}

	public static function requestUrl():? URLInterface
	{
		$url = '';

		if (array_key_exists('HTTPS', $_SERVER) and ! empty($_SERVER['HTTPS'])) {
			$url = 'https://';
		} else {
			$url = 'http://';
		}

		if (array_key_exists('PHP_AUTH_USER', $_SERVER)) {
			$url .= urlencode($_SERVER['PHP_AUTH_USER']);

			if (array_key_exists('PHP_AUTH_PW', $_SERVER)) {
				$url .= ':' . urlencode($_SERVER['PHP_AUTH_PW']);
			}

			$url .= '@';
		}

		if (array_key_exists('SERVER_NAME', $_SERVER)) {
			$url .= $_SERVER['SERVER_NAME'];
		} else {
			$url .= 'localhost';
		}

		if (array_key_exists('SERVER_PORT', $_SERVER)) {
			$url .= ':' . $_SERVER['SERVER_PORT'];
		}

		if (array_key_exists('PHP_SELF', $_SERVER)) {
			$url .= '/' . ltrim($_SERVER['PHP_SELF'], '/');
		} else {
			$url .= '/';
		}

		if (count($_GET) !== 0) {
			$url .= '?' . http_build_query($_GET);
		}

		return new self($url);
	}
}

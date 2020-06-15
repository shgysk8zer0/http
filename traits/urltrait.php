<?php

namespace shgysk8zer0\HTTP\Traits;

use \shgysk8zer0\HTTP\Interfaces\{
	URLSearchParamsInterface,
};

use \shgysk8zer0\HTTP\{
	URLSearchParams,
};

use \InvalidArgumentException;

trait URLTrait
{
	use PathsTrait;

	private $_protocol = null;

	private $_hostname = null;

	private $_port = null;

	private $_pathname = null;

	private $_search_params = null;

	private $_hash = null;

	private $_username = null;

	private $_password = null;



	final public function __debugInfo(): array
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

	final public function jsonSerialize(): string
	{
		return $this->getHref();
	}

	final public function __toString(): string
	{
		return $this->getHref();
	}

	final public function __get(string $name)
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

	final public function __set(string $name, $val): void
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

	final public function __unset(string $name): void
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

	final public function getHref(): string
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

	final public function setHref(string $val): void
	{
		if (filter_var($val, FILTER_VALIDATE_URL)) {
			$parsed = array_merge($this->_getDefaults(), parse_url($val));
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

	final public function getProtocol(): string
	{
		return $this->_protocol . ':';
	}

	final public function setProtocol(string $val): void
	{
		$this->_protocol = rtrim($val, ':');
	}

	final public function getHostname(bool $escape = false): string
	{
		return $escape ? urlencode($this->_hostname) : $this->_hostname;
	}

	final public function setHostname(string $val): void
	{
		$this->_hostname = $val;
	}

	final public function getHost(): string
	{
		if (isset($this->_port)) {
			return "{$this->getHostname(true)}:{$this->getPort()}";
		} else {
			return $this->getHostname(true);
		}
	}

	final public function setHost(string $val): void
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

	final public function getOrigin(): string
	{
		return "{$this->getProtocol()}//{$this->getHost()}";
	}

	final public function setOrigin(string $val): void
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

	final public function getPort():? int
	{
		return $this->_port;
	}

	final public function setPort(?int $val): void
	{
		$this->_port = $val;
	}

	final public function getPathname(bool $escape = false): string
	{
		if (isset($this->_pathname)) {
			return $escape ? urlencode($this->_pathname) : $this->_pathname;
		} else {
			return '/';
		}
	}

	final public function setPathname(?string $val): void
	{
		if (isset($val)) {
			$this->_pathname = '/' . ltrim($val, '/');
		} else {
			$this->_pathname = '/';
		}
	}

	final public function getSearch(): string
	{
		if ($this->getSearchParams()->length() === 0) {
			return '';
		} else {
			return "?{$this->getSearchParams()}";
		}
	}

	final public function setSearch(?string $val): void
	{
		$this->setSearchParams(new URLSearchParams($val));
	}

	final public function getSearchParams(): URLSearchParamsInterface
	{
		return $this->_search_params;
	}

	final public function setSearchParams(URLSearchParamsInterface $val): void
	{
		$this->_search_params = $val;
	}

	final public function getHash(bool $escape = false): string
	{
		if (isset($this->_hash)) {
			return $escape ? '#' . urlencode($this->_hash) : '#' . $this->_hash;
		} else {
			return '';
		}
	}

	final public function setHash(?string $val): void
	{
		if (isset($val)) {
			$this->_hash = ltrim($val, '#');
		} else {
			$this->_hash = null;
		}
	}

	final public function getUsername(bool $escape = false):? string
	{
		if (isset($this->_username)) {
			return $escape ? urlencode($this->_username) : $this->_username;
		} else {
			return null;
		}
	}

	final public function setUsername(?string $val): void
	{
		$this->_username = $val;
	}

	final public function getPassword(bool $escape = false):? string
	{
		if (isset($this->_password)) {
			return $escape ? urlencode($this->_password) : $this->_password;
		} else {
			return null;
		}
	}

	final public function setPassword(?string $val): void
	{
		$this->_password = $val;
	}

	final protected function _parse(string $url, ?string $base = null): void
	{
		if (isset($base) and filter_var($base, FILTER_VALIDATE_URL)) {
			$parsed_base = parse_url($base);
			$parsed_url  = parse_url($url);

			if (array_key_exists('path', $parsed_url)) {
				$parsed = array_merge(
					$this->_getDefaults(),
					$parsed_base,
					$parsed_url,
					[
						'path' => $this->_getRelativePath($parsed_url['path'], $parsed_base['path'] ?? null),
					]
				);

			} else {
				$parsed = array_merge($this->_getDefaults(), $parsed_base, $parsed_url);
			}
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

	abstract protected function _getDefaults(): array;
}

<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{
	URLInterface,
	URLSearchParamsInterface,
};

use \shgysk8zer0\HTTP\Traits\{
	PathsTrait,
	URLTrait,
	URLParserTrait,
};

use \InvalidArgumentException;

use \JsonSerializable;

class URL implements URLInterface, JsonSerializable
{
	use URLParserTrait;

	use URLTrait;

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

	public function __construct(string $url, ?string $base = null)
	{
		$this->_parse($url, $base);
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

	public static function requestUrl():? URLInterface
	{
		return new self(static::getRequestUrlString());
	}

	final protected function _getDefaults(): array
	{
		return self::DEFAULTS;
	}
}

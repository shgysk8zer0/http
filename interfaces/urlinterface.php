<?php

namespace shgysk8zer0\HTTP\Interfaces;

use \Serializable;

interface URLInterface extends Serializable
{
	public function __construct(string $url, ?string $base = null);

	public function __toString(): string;

	public function getHref(): string;

	public function setHref(string $val): void;

	public function getProtocol(): string;

	public function setProtocol(string $val): void;

	public function getHostname(bool $escape = false): string;

	public function setHostname(String $val): void;

	public function getHost(): string;

	public function getOrigin(): string;

	public function setOrigin(string $val): void;

	public function getPort():? int;

	public function setPort(?int $val): void;

	public function getPathname(bool $escape = false): string;

	public function setPathname(?string $val): void;

	public function getSearch(): string;

	public function setSearch(?string $val): void;

	public function getSearchParams(): URLSearchParamsInterface;

	public function setSearchParams(URLSearchParamsInterface $val): void;

	public function getHash(bool $escape = false): string;

	public function setHash(?string $val): void;

	public function getUsername(bool $escape = false):? string;

	public function setUsername(?string $val): void;

	public function getPassword(bool $escape = false):? string;

	public function setPassword(?string $val): void;

	public static function requestUrl():? URLInterface;
}

<?php

namespace shgysk8zer0\HTTP\Interfaces;

use \DateInterval;

use \DateTimeInterface;

interface CookieInterface
{
	public function __construct(
		string  $name,
		?string $value = null,
		array $init    = []
	);

	public function __toString(): string;

	public function getName(): string;

	public function setName(string $name): void;

	public function getValue():? string;

	public function setValue(?string $value): void;

	public function getDomain():? string;

	public function setDomain(?string $value): void;

	public function expiresIn(?DateInterval $value): void;

	public function getExpires():? DateTimeInterface;

	public function getExpiresAsString():? string;

	public function setExpires(?DateTimeInterface $value): void;

	public function getPath():? string;

	public function setPath(?string $value): void;

	public function getHttpOnly(): bool;

	public function setHttpOnly(bool $value): void;

	public function getSecure(): bool;

	public function setSecure(bool $value): void;

	public function getSameSite():? string;

	public function setSameSite(?string $value): void;

	public function send(): bool;
}

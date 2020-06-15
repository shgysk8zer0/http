<?php

namespace shgysk8zer0\HTTP\Interfaces;

interface CookiesInterface
{
	public function add(CookieInterface $cookie): bool;

	public function set(string $name, ?string $value, ...$args): bool;

	public function get(string $name):? CookieInterface;

	public function has(string ...$names): bool;

	public function delete(string $name): bool;

	public function expire(string $name): bool;

	public function keys(): iterable;

	public function values(): iterable;

	public function entries(): iterable;

	public function send(): bool;

	public static function parseHeader(?string $cookie): CookiesInterface;

	public static function fromHeadersObject(HeadersInterface $headers): CookiesInterface;

	public static function requestCookies(): CookiesInterface;
}

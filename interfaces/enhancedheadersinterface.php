<?php

namespace shgysk8zer0\HTTP\Interfaces;

interface EnhancedHeadersInterface extends HeadersInterface
{
	public function addHeaderPolicy(HeaderPolicyInterface $val): void;

	public function send(): bool;

	public static function parseFromCurlResponse(string $raw):? HeadersInterface;

	public static function sent(): bool;

	public static function fromRequestHeaders(string ...$include): EnhancedHeadersInterface;
}


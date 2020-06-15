<?php

namespace shgysk8zer0\HTTP\Interfaces;

interface HeaderPolicyInterface
{

	public function __toString(): string;

	public function headerName(): string;

	public function headerValue(): string;

	public function has(string ...$keys): bool;

	public function get(string $key):? string;

	public function set(string $key, string $value): bool;

	public function append(string $key, string $value): bool;

	public function delete(string $key): bool;

	public function entries(): iterable;

	public function keys(): iterable;

	public function values(): iterable;

	public static function fromObject(object $data): HeaderPolicyInterface;

	public static function fromJsonFile(string $filename):? HeaderPolicyInterface;

	public static function fromIniFile(string $filename):? HeaderPolicyInterface;
}

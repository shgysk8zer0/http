<?php

namespace shgysk8zer0\HTTP\Interfaces;

use \Serializable;

interface HeadersInterface extends Serializable
{
	public function append(string $name, string $value): bool;

	public function get(string $name):? string;

	public function set(string $name, string $value): bool;

	public function has(string $name): bool;

	public function delete(string $name): bool;

	public function keys(): iterable;

	public function values(): iterable;

	public function entries(): iterable;

	public static function fromRequestHeaders(string ...$include):? HeadersInterface;
}

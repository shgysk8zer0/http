<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\HeadersInterface;

use \JsonSerializable;

class Headers implements HeadersInterface
{
	private $_headers = [];

	public function __construct(array $init = null)
	{
		if (isset($init)) {
			foreach ($init as $key => $value) {
				$this->set($key, $value);
			}
		}
	}

	public function serialize(): string
	{
		return serialize($this->_headers);
	}

	public function unserialize($data): void
	{
		$parsed = unserialize($data);

		if (is_array($parsed)) {
			$this->_headers = $parsed;
		}
	}

	public function jsonSerialize(): array
	{
		return $this->_headers;
	}

	public function __debugInfo(): array
	{
		return $this->_headers;
	}

	public function append(string $name, string $value): bool
	{
		if ($this->has($name)) {
			$this->_headers[strtolower($name)][] = $value;
		} else {
			$this->_headers[strtolower($name)] = [$value];
		}
		return true;
	}

	public function get(string $name):? string
	{
		if ($this->has($name)) {
			return join(', ', $this->_headers[strtolower($name)]);
		} else {
			return null;
		}
	}

	public function getAll(string $name):? array
	{
		if ($this->has($name)) {
			return $this->_headers[strtolower($name)];
		} else {
			return null;
		}
	}

	public function set(string $name, string $value): bool
	{
		$this->_headers[strtolower($name)] = [$value];
		return true;
	}

	public function has(string $name): bool
	{
		return array_key_exists(strtolower($name), $this->_headers);
	}

	public function delete(string $name): bool
	{
		if ($this->has($name)) {
			unset($this->_headers[strtolower($name)]);
			return true;
		} else {
			return false;
		}
	}

	public function keys(): iterable
	{
		foreach (array_keys($this->_headers) as $key) {
			yield join('-', array_map('ucfirst', explode('-', $key)));
		}
	}

	public function values(): iterable
	{
		foreach (array_values($this->_headers) as $value) {
			yield $value;
		}
	}

	public function entries(): iterable
	{
		foreach ($this->keys() as $key) {
			yield [$key, $this->get($key)];
		}
	}

	public static function parseFromCurlResponse(string $raw):? HeadersInterface
	{
		$headers = new Headers();
		$raw = str_replace(["\r\n"], ["\n"], $raw);
		$lines = explode("\n", $raw);

		foreach ($lines as $combined) {
			$combined = trim($combined);

			if (! empty($combined)) {
				$parsed = explode(':', $combined);

				if (count($parsed) > 1) {
					[$key, $value] = $parsed;

					$key = trim($key);

					if (isset($value)) {
						$value = trim($value);

						foreach (explode(',', $value) as $sub) {
							$headers->append($key, trim($sub));
						}
					}
				}
			}
		}

		return $headers;
	}

	public function send(): bool
	{
		if (! headers_sent()) {
			foreach ($this->entries() as $entry) {
				header("{$entry[0]}: {$entry[1]}");
			}
			return true;
		} else {
			return false;
		}
	}

	public static function fromRequestHeaders():? HeadersInterface
	{
		if (function_exists('getallheaders')) {
			$headers = new self(getallheaders());
			$headers->delete('host');
			$headers->delete('cookie');
			return $headers;
		} else {
			return null;
		}
	}
}

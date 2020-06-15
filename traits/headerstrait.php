<?php

namespace shgysk8zer0\HTTP\Traits;

trait HeadersTrait
{
	private $_headers = [];

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

	public function append(string $name, string $value): bool
	{
		$name = trim(strtolower($name));
		$value = trim($value);

		if (! $this->has($name)) {
			$this->set($name, $value);
		} elseif (in_array($name, self::NO_SPLIT)) {
			$this->_headers[$name][] = $value;
		} else {
			$this->_headers[$name] = array_merge($this->_headers[$name], explode(',', $value));
		}

		return true;
	}

	public function get(string $name):? string
	{
		if ($this->has($name)) {
			return join(', ', $this->_headers[trim(strtolower($name))]);
		} else {
			return null;
		}
	}

	public function getAll(string $name): array
	{
		if ($this->has($name)) {
			return $this->_headers[trim(strtolower($name))];
		} else {
			return [];
		}
	}

	public function set(string $name, string $value): bool
	{
		$name = trim(strtolower($name));
		$value = trim($value);

		if (in_array($name, self::NO_SPLIT)) {
			$this->_headers[$name] = [$value];
		} else {
			$this->_headers[$name] = array_map('trim', explode(',', $value));
		}
		return true;
	}

	public function has(string $name): bool
	{
		return array_key_exists(trim(strtolower($name)), $this->_headers);
	}

	public function delete(string $name): bool
	{
		if ($this->has($name)) {
			unset($this->_headers[trim(strtolower($name))]);
			return true;
		} else {
			return false;
		}
	}

	public function keys(): iterable
	{
		foreach (array_keys($this->_headers) as $key) {
			yield $key;
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

	protected function _getHeaders(): array
	{
		return $this->_headers;
	}
}

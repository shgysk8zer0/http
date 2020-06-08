<?php
namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\URLSearchParamsInterface;

use \JsonSerializable;

class URLSearchParams implements URLSearchParamsInterface, JsonSerializable
{
	private $_params = [];

	public function __construct(?string $params = null)
	{
		if (isset($params)) {
			parse_str(ltrim($params, '?'), $parsed);

			if (is_array($parsed)) {
				$this->_params = $parsed;
			}
		}
	}

	public function __toString(): string
	{
		return http_build_query($this->_params);
	}

	public function jsonSerialize(): array
	{
		return $this->_params;
	}

	public function __debugInfo(): array
	{
		return $this->_params;
	}

	public function serialize(): string
	{
		return serialize($this->_params);
	}

	public function unserialize($data): void
	{
		$parsed = unserialize($data);

		if (is_array($parsed)) {
			$this->_params = $parsed;
		}
	}

	public function append(string $name, string $value): bool
	{
		if ($this->has($name)) {
			$this->_params[$name][] = $value;
		} else {
			$this->_params[$name] = [$value];
		}

		return true;
	}

	public function get(string $name):? string
	{
		if ($this->has($name)) {
			return $this->_params[$name][0];
		} else {
			return null;
		}
	}

	public function getAll(string $name):? array
	{
		if ($this->has($name)) {
			return $this->_params[$name];
		} else {
			return null;
		}
	}

	public function set(string $name, string $value): bool
	{
		$this->_params[$name] = $value;
		return true;
	}

	public function has(string $name): bool
	{
		return array_key_exists($name, $this->_params);
	}

	public function delete(string $name): bool
	{
		if ($this->has($name)) {
			unset($this->_params[$name]);
			return true;
		} else {
			return false;
		}
	}

	public function keys(): iterable
	{
		foreach (array_keys($this->_params) as $key) {
			yield $key;
		}
	}

	public function values(): iterable
	{
		foreach (array_values($this->_params) as $value) {
			yield $value;
		}
	}

	public function entries(): iterable
	{
		foreach ($this->_params as $key => $value) {
			yield [$key, $value];
		}
	}

	public function length(): int
	{
		return count($this->_params);
	}
}

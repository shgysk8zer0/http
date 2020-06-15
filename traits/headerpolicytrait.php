<?php

namespace shgysk8zer0\HTTP\Traits;

use \InvalidArgumentException;

trait HeaderPolicyTrait
{
	protected $_data = [];

	public function has(string ...$keys): bool
	{
		$has = true;

		foreach ($keys as $key) {
			if (! array_key_exists(strtolower($key), $this->_data)) {
				$has = false;
				break;
			}
		}

		return $has;
	}

	public function get(string $key):? string
	{
		if ($this->has($key)) {
			$value = $this->_data[strtolower($key)];

			if (is_array($value)) {
				return $this->_joinValue($value);
			} else {
				return $value;
			}
		} else {
			return null;
		}
	}

	public function set(string $key, string $value): bool
	{
		$this->_data[$key] = [$value];
		return true;
	}

	public function append(string $key, string $value): bool
	{
		if (! $this->has($key)) {
			return $this->set($key, $value);
		} elseif (! in_array(strtolower($key), $this->_data)) {
			$this->_data[strtolower($key)][] = $value;
			return true;
		} else {
			return false;
		}
	}

	public function delete(string $key): bool
	{
		if ($this->has($key)) {
			unset($this->_data[$key]);
			return true;
		} else {
			return false;
		}
	}

	public function entries(): iterable
	{
		foreach ($this->keys() as $key) {
			yield [$key, $this->get($key)];
		}
	}

	public function keys(): iterable
	{
		foreach (array_keys($this->_data) as $key) {
			yield $key;
		}
	}

	public function values(): iterable
	{
		foreach (array_values($this->_data) as $value) {
			yield $value;
		}
	}

	protected function _getData(): array
	{
		return $this->_data;
	}

	abstract protected function _join(array $value): string;

	abstract protected function _joinValue(array $value): string;
}

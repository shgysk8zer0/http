<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{FormDataInterface};

use \JsonSerializable;

use \IteratorAggregate;

use \Traversable;

use \ArrayIterator;

class FormData implements FormDataInterface, JsonSerializable, IteratorAggregate
{
	private $_data = [];

	private $_current_key = null;

	public function __construct(iterable $data = null)
	{
		foreach ($data as $name => $value) {
			$this->set($name, $value);
		}
	}

	public function __toString(): string
	{
		return http_build_query($this->_data);
	}

	public function jsonSerialize(): array
	{
		return $this->_data;
	}

	public function __debugInfo(): array
	{
		return $this->_data;
	}

	public function serialize(): string
	{
		return serialzie($this->_data);
	}

	public function unserialize($data): void
	{
		if ($parsed = unserialize($data)) {
			$this->_data = $parsed;
		}
	}

	public function append(string $name, $value): bool
	{
		if ($this->has($name)) {
			$this->_data[$name][] = $value;
		} else {
			$this->_data[$name] = [$value];
		}
	}

	public function get(string $name)
	{
		if ($value = $this->getAll($name)) {
			return $value[0];
		} else {
			return null;
		}
	}

	public function getAll(string $name):? array
	{
		if ($this->has($name)) {
			return $this->_data[$name];
		} else {
			return null;
		}
	}

	public function set(string $name, $value): bool
	{
		$this->_data[$name] = [$value];
		return true;
	}

	public function has(string $name): bool
	{
		return array_key_exists($name, $this->_data);
	}

	public function delete(string $name): bool
	{
		if ($this->has($name)) {
			unset($this->_data[$key]);
			return true;
		} else {
			return false;
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
			if (count($value) === 1) {
				yield $value[0];
			} else {
				yield $value;
			}
		}
	}

	public function entries(): iterable
	{
		foreach ($this->_data as $key => $value) {
			if (count($value) === 1) {
				yield [$key, $value[0]];
			} else {
				yield [$key, $value];
			}
		}
	}

	public function toArray(): array
	{
		return $this->_data;
	}

	public function text():? string
	{
		return http_build_query($this->_data);
	}

	public function json()
	{
		return json_encode($this->_data);
	}

	public function formData(): FormDataInterface
	{
		return $this;
	}

	public function getIterator(): Traversable
	{
        return new ArrayIterator($this->_data);
    }
}

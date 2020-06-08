<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{FormDataInterface};

use \JsonSerializable;

use \IteratorAggregate;

use \Traversable;

use \ArrayIterator;

use \CURLFile;

class FormData implements FormDataInterface, JsonSerializable, IteratorAggregate
{
	private $_data  = [];

	public const CONTENT_TYPE = 'multipart/form-data';

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
		return serialize($this->_data);
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

		return true;
	}

	public function attach(
		string $filename,
		string $type     = '',
		string $postname = '',
		bool   $append   = false
	): bool
	{
		if (! file_exists($filename)) {
			return false;
		} elseif (! $file = new CURLFile($filename, $type, $postname)) {
			return false;
		} elseif ($append) {
			return $this->append($file->getPostFilename(), $file);
		} else {
			return $this->set($file->getPostFilename(), $file);
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
		foreach ($this->_data as $key => $values) {
			if (is_array($values) and count($values) === 1) {
				yield [$key, $values[0]];
			} else {
				yield [$key, $values];
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

	public function setPostFields($ch): bool
	{
		if (is_resource($ch)) {
			$body = [];

			foreach ($this->_data as $key => $values) {
				if (is_array($values) and count($values) > 1) {
					$n = 0;

					foreach ($values as $value) {
						$body["{$key}[{$n}]"] = $value;
						$n++;
					}
				} else {
					$body[$key] = is_array($values) ? $values[0] : $values;
				}
			}

			return curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		} else {
			return false;
		}
	}

	public function getContentTypeHeader():? string
	{
		return null;//'Content-Type: ' . self::CONTENT_TYPE;
	}

	public function getIterator(): Traversable
	{
        return new ArrayIterator($this->_data);
    }
}

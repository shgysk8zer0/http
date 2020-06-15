<?php

namespace shgysk8zer0\HTTP\Abstracts;

use \shgysk8zer0\HTTP\Interfaces\HeaderPolicyInterface;

use \shgysk8zer0\HTTP\Traits\HeaderPolicyTrait;

abstract class AbstractHeaderPolicy implements HeaderPolicyInterface
{
	use HeaderPolicyTrait;

	public function __construct(array $init = [])
	{
		foreach ($init as $key => $value) {
			if (is_string($value)) {
				$this->set($key, $value);
			} elseif (is_array($value)) {
				$this->set($key, join(' ', $value));
			} elseif (is_bool($value)) {
				$this->set($key, $value ? '*' : 'none');
			}
		}
	}

	abstract function headerName(): string;

	public function headerValue(): string
	{
		$data = [];

		foreach ($this->entries() as $entry) {
			$data[] = "{$entry[0]} {$entry[1]}";
		}

		return $this->_join($data);
	}

	public function __toString(): string
	{
		return $this->headerValue();
	}

	public function __debugInfo(): array
	{
		return $this->_getData();
	}

	public static function fromObject(object $data): HeaderPolicyInterface
	{
		return new static(get_object_vars($data));
	}

	public static function fromJsonFile(string $filename):? HeaderPolicyInterface
	{
		if (@file_exists($filename)) {
			$json = json_decode(file_get_contents($filename), true);
			return new static($json);
		} else {
			return null;
		}
	}

	public static function fromIniFile(string $filename):? HeaderPolicyInterface
	{
		if (@file_exists($filename)) {
			return new static(parse_ini_file($filename, false, INI_SCANNER_TYPED));
		} else {
			return null;
		}
	}
}

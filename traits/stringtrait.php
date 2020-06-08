<?php

namespace shgysk8zer0\HTTP\Traits;

trait StringTrait
{
	protected function _stringStartsWith(string $str, string $pattern): bool
	{
		return strpos($str, $pattern) === 0;
	}

	protected function _stringEndsWith(string $str, string $pattern): bool
	{
		return strpos($str, $pattern) === strlen($str) - strlen($pattern);
	}

	protected function _stringContainers(string $string, string $pattern): bool
	{
		return strpos($str, $pattern) !== false;
	}
}

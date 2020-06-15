<?php

namespace shgysk8zer0\HTTP\Traits;

trait InputValidatorTrait
{
	final public function is(string $name, string $type, ...$args): bool
	{
		if (! $this->has($name)) {
			return false;
		} else {
			switch (strtolower($type)) {
				case 'email':
					return $this->isEmail($this->get($name));

				case 'url':
					return $this->isUrl($this->get($name));

				default:
					// Unknown type, so return false
					return false;
			}
		}
	}

	final public function isInt(string $name, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): bool
	{
		return filter_var($this->get($name), FILTER_VALIDATE_INT, [
			'options' => [
				'min_range' => $min,
				'max_range' => $max,
			]
		]);
	}

	final public function isEmail(string $name): bool
	{
		return filter_var($this->get($name), FILTER_VALIDATE_EMAIL);
	}

	final public function isUrl(string $name): bool
	{
		return filter_var($this->get($name), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED);
	}

	final public function isIp(string $name, bool $allow_restricted = false): bool
	{
		if ($allow_restricted) {
			return filter_var($this->get($name), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
		} else {
			return filter_var($this->get($name), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
		}
	}

	final public function matches(string $name, string $pattern): bool
	{
		if (! $this->has($name)) {
			return false;
		} else {
			return preg_match($pattern, $this->get($name)) !== false;
		}
	}

	abstract public function get(string $name);

	abstract public function has(string $name): bool;
}

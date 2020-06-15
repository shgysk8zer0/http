<?php

namespace shgysk8zer0\HTTP\Traits;

use \shgysk8zer0\HTTP\Interfaces\{
	HeadersInterface,
	HeaderPolicyInterface,
	EnhancedHeadersInterface,
};

trait EnhancedHeadersTrait
{
	use HeadersTrait;

	public function addHeaderPolicy(HeaderPolicyInterface $val): void
	{
		$this->set($val->headerName(), $val->headerValue());
	}

	public function send(): bool
	{
		if (! headers_sent()) {
			foreach ($this->keys() as $key) {
				if (in_array($key, self::NO_SPLIT)) {
					foreach ($this->getAll($key) as $value) {
						$key = join('-', array_map('ucfirst', explode('-', $key)));
						header("{$key}: {$value}", false);
					}
				} else {
					$value = $this->get($key);
					$key = join('-', array_map('ucfirst', explode('-', $key)));
					header("{$key}: {$this->get($key)}");
				}
			}
			return true;
		} else {
			return false;
		}
	}

	public static function sent(): bool
	{
		return ! function_exists('headers_sent') or headers_sent();
	}

	public static function parseFromCurlResponse(string $raw):? HeadersInterface
	{
		$headers = new self();
		$raw = str_replace(["\r\n"], ["\n"], $raw);
		$lines = explode("\n", $raw);

		foreach ($lines as $combined) {
			$combined = trim($combined);

			if (! empty($combined)) {
				$parsed = explode(':', $combined, 2);

				if (count($parsed) > 1) {
					[$key, $value] = $parsed;

					if (isset($value)) {
						switch (strtolower($key)) {
							case 'date':
								$headers->set('date', $value);
								break;

							case 'cookie':
								foreach (explode(';', $value) as $sub) {
									$headers->append($key, $sub);
								}
								break;

							default:
								foreach (explode(',', $value) as $sub) {
									$headers->append($key, $sub);
								}
						}
					}
				}
			}
		}

		return $headers;
	}

	public static function fromRequestHeaders(string ...$include): EnhancedHeadersInterface
	{
		if (! function_exists('getallheaders')) {
			return new self(['Accept' => '*/*']);
		} elseif (count($include) === 0) {
				return new self(getallheaders());
		} else {
			$include = array_map('strtolower', $include);

			return new self(array_filter(getallheaders(), function(string $key) use ($include): bool
			{
				return in_array(strtolower($key), $include);
			}, ARRAY_FILTER_USE_KEY));
		}
	}
}

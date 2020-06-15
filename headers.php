<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{
	EnhancedHeadersInterface,
};

use \shgysk8zer0\HTTP\Traits\{
	ContentSecurityPolicyHeaderTrait,
	EnhancedHeadersTrait,
};

use \JsonSerializable;

class Headers implements EnhancedHeadersInterface, JsonSerializable
{
	use ContentSecurityPolicyHeaderTrait;

	use EnhancedHeadersTrait;

	private const NO_SPLIT = [
		'date',
		'set-cookie',
	];

	public function __construct(array $init = null)
	{
		if (isset($init)) {
			foreach ($init as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $header) {
						$this->append($key, $header);
					}
				} else {
					$this->set($key, $value);
				}
			}
		}
	}

	public function jsonSerialize(): array
	{
		return $this->_getHeaders();
	}

	public function __debugInfo(): array
	{
		return $this->_getHeaders();
	}
}

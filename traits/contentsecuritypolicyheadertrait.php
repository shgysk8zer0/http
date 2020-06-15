<?php

namespace shgysk8zer0\HTTP\Traits;

use \shgysk8zer0\HTTP\Interfaces\ContentSecurityPolicyInterface;

trait ContentSecurityPolicyHeaderTrait
{
	public function setContentSecurityPolicy(?ContentSecurityPolicyInterface $policy): bool
	{
		return $this->set($policy->headerName(), $policy->headerValue());
	}

	abstract public function set(string $key, string $value): bool;
}

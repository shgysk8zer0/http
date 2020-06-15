<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Abstracts\AbstractHeaderPolicy;

final class FeaturePolicy extends AbstractHeaderPolicy
{
	public const HEADER_NAME = 'Feature-Policy';

	private const _REPLACEMENTS = [
		'self' => "'self'",
		'none' => "'none'",
	];

	public function headerName(): string
	{
		return self::HEADER_NAME;
	}

	final protected function _join(array $data): string
	{
		return join(';', $data);
	}

	final protected function _joinValue(array $values): string
	{
		return str_replace(array_keys(self::_REPLACEMENTS), array_values(self::_REPLACEMENTS), join(' ', $values));
	}
}

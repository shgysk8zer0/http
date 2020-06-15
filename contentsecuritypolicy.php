<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{ContentSecurityPolicyInterface, DataInterface};

use \shgysk8zer0\HTTP\Abstracts\AbstractHeaderPolicy;

use \JsonSerializable;

/**
 * @TODO Handle Upgrade-Insecure-Request & other value-less params
 */
final class ContentSecurityPolicy extends AbstractHeaderPolicy implements ContentSecurityPolicyInterface, JsonSerializable
{
	private $_report_only = false;

	private $_report_to = null;

	private const _REPLACEMENTS = [
		'self'           => "'self'",
		'none'           => "'none'",
		'unsafe-inline'  => "'unsafe-inline'",
		'unsafe-eval'    => "'unsafe-eval'",
		'strict-dynamic' => "'strict-dynamic'",
	];

	final public function __construct(array $init = ['default-src' => 'self'])
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

	final public function headerName(): string
	{
		if ($this->_report_only) {
			return 'Content-Security-Policy-Report-Only';
		} else {
			return 'Content-Security-Policy';
		}
	}

	final public function jsonSerialize(): array
	{
		return $this->_policy;
	}

	final public function hashFile(string $filename, string $algo = 'sha256'):? string
	{
		if (file_exists($filename)) {
			if (! in_array($algo, hash_algos())) {
				throw new InvalidArgumentExeption(sprintf('Unsupported hash algorithm: %s', $algo));
			}
			return sprintf('\'%s-%s\'', $algo, base64_encode(hash_file($algo, $filename, true)));
		} else {
			return null;
		}
	}

	final public function hash(string $content, string $algo = 'sha256'): string
	{
		if (! in_array($algo, hash_algos())) {
			throw new InvalidArgumentExeption(sprintf('Unsupported hash algorithm: %s', $algo));
		}

		return sprintf('\'%s-%s\'', $algo, base64_encode(hash($algo, $content, true)));
	}

	final public function __debugInfo(): array
	{
		return [
			'reportOnly' => $this->_report_only,
			'reportTo'   => $this->_report_to,
			'policy'     => $this->_policy,
		];
	}

	final public function defaultSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('default-src', join(' ', $srcs));
		return $this;
	}

	final public function scriptSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('script-src', join(' ', $srcs));
		return $this;
	}

	final public function styleSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('style-src', join(' ', $srcs));
		return $this;
	}

	final public function imgSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('img-src', join(' ', $srcs));

		return $this;
	}

	final public function mediaSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('media-src', join(' ', $srcs));

		return $this;
	}

	final public function fontSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('font-src', join(' ', $srcs));
		return $this;
	}

	final public function connectSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('connect-src', join(' ', $srcs));
		return $this;
	}

	final public function frameSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('frame-src', join(' ', $srcs));
		return $this;
	}

	final public function objectSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('object-src', join(' ', $srcs));
		return $this;
	}

	final public function prefetchSrc(string ...$srcs): ContentSecurityPolicyInterface
	{
		if (empty($srcs)) {
			$srcs = ['none'];
		}
		$this->set('prefetch-src', join(' ', $srcs));
		return $this;
	}

	final public function reportUri(string ...$srcs): ContentSecurityPolicyInterface
	{
		$this->set('report-uri', join(' ', $srcs));
		return $this;
	}

	final public function blockAllMixedContent(bool $block = true): ContentSecurityPolicyInterface
	{
		if ($block) {
			$this->set('block-all-mixed-content');
		} else {
			$this->_rm('block-all-mixed-content');
		}
		return $this;
	}

	final public function upgradeInsecureRequests(bool $upgrade = true): ContentSecurityPolicyInterface
	{
		if ($upgrade) {
			$this->set('upgrade-insecure-requests');
		} else {
			$this->_rm('upgrade-insecure-requests');
		}
		return $this;
	}

	final public function reportOnly(bool $report_only = true): ContentSecurityPolicyInterface
	{
		$this->_report_only = $report_only;
		return $this;
	}

	final public function reportTo(object $report_to): ContentSecurityPolicyInterface
	{
		$this->_report_to = $report_to;
		return $this;
	}

	final protected function _join(array $values): string
	{
		return join(';', $values);
	}

	final protected function _joinValue(array $values): string
	{
		return str_replace(array_keys(self::_REPLACEMENTS), array_values(self::_REPLACEMENTS), join(' ', $values));
	}

	final private function _rm(string $key)
	{
		unset($this->_policy[$key]);
	}
}

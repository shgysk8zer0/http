<?php

namespace shgysk8zer0\HTTP\Interfaces;

interface ContentSecurityPolicyInterface extends HeaderPolicyInterface
{

	public function hashFile(string $filename, string $algo = 'sha256'):? string;

	public function hash(string $content, string $algo = 'sha256'): string;

	public function defaultSrc(string ...$srcs): ContentSecurityPolicyInterface;

	public function scriptSrc(string ...$srcs): ContentSecurityPolicyInterface;

	public function styleSrc(string ...$srcs): ContentSecurityPolicyInterface;

	public function imgSrc(string ...$srcs): ContentSecurityPolicyInterface;

	public function mediaSrc(string ...$srcs): ContentSecurityPolicyInterface;

	public function fontSrc(string ...$srcs): ContentSecurityPolicyInterface;

	public function connectSrc(string ...$srcs): ContentSecurityPolicyInterface;

	public function frameSrc(string ...$srcs): ContentSecurityPolicyInterface;

	public function objectSrc(string ...$srcs): ContentSecurityPolicyInterface;

	public function reportUri(string ...$srcs): ContentSecurityPolicyInterface;

	public function blockAllMixedContent(bool $block = true): ContentSecurityPolicyInterface;

	public function upgradeInsecureRequests(bool $upgrade = true): ContentSecurityPolicyInterface;

	public function reportOnly(bool $report_only = true): ContentSecurityPolicyInterface;

	public function reportTo(object $report_to): ContentSecurityPolicyInterface;
}

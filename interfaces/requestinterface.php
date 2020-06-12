<?php

namespace shgysk8zer0\HTTP\Interfaces;

use \Serializable;

use \DateInterval;

interface RequestInterface extends Serializable
{
	public function __construct(string $url, array $init = []);

	public function __get(string $name);

	public function __invoke(?int $timeout = null, ?CacheInterface $cache = null):? ResponseInterface;

	public function text():? string;

	public function json();

	public function formData():? FormDataInterface;

	public function getBody():? BodyInterface;

	public function setBody(?BodyInterface $val): void;

	public function getCacheMode(): string;

	public function setCacheMode(string $val): void;

	public function getCredentials(): string;

	public function setCredentials(string $val): void;

	public function getExpiration(): DateInterval;

	public function setExpiration(?DateInterval $val = null): void;

	public function getMethod(): string;

	public function getHeaders(): HeadersInterface;

	public function setHeaders(HeadersInterface $val): void;

	public function setMethod(string $val): void;

	public function getRedirect(): string;

	public function setRedirect(string $val): void;

	public function getReferrer(): string;

	public function setReferrer(string $val): void;

	public function getUrl():? string;

	public function setUrl(string $val): void;

	/**
	 * Sends the HTTP request and returns the response
	 * @TODO Implement caching
	 * @return ResponseInterface
	 */
	public function send(?int $timeout = null):? ResponseInterface;

	public static function fromRequest(): RequestInterface;
}

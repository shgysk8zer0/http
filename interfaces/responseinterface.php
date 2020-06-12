<?php

namespace shgysk8zer0\HTTP\Interfaces;

use \Serializable;

interface ResponseInterface extends Serializable
{
	public function redirect(string $url, int $status = 302): ResponseInterface;

	public function getStatus(): int;

	public function getStatusText():? string;

	public function getUrl():? string;

	public function setUrl(?string $val): void;

	public function getHeaders():? HeadersInterface;

	public function setHeaders(HeadersInterface $val): void;

	public function getRedirected(): bool;

	public function setRedirected(bool $val): void;

	public function getBody():? BodyInterface;

	public function setBody(?BodyInterface $val): void;

	public function getOk(): bool;

	public function text():? string;

	public function json();
}

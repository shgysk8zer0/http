<?php

namespace shgysk8zer0\HTTP\Interfaces;

use \Serializable;

interface RequestInterface extends Serializable
{
	public function __construct(string $url, array $init = []);

	public function text():? string;

	public function json();

	public function formData():? FormDataInterface;

	public function getBody():? BodyInterface;

	public function setBody(BodyInterface $val): void;

	public function getMethod(): string;

	public function setMethod(string $val): void;

	public function getUrl():? string;

	public function setUrl(string $val): void;

	public function send():? ResponseInterface;
}

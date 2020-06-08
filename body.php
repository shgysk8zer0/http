<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{
	BodyInterface,
	FormDataInterface,
};

use \JsonSerializable;

class Body implements BodyInterface, JsonSerializable
{
	private $_data = null;

	private $_content_type = null;

	public function __construct(?string $data = null)
	{
		$this->_data = $data;
	}

	public function serialize(): string
	{
		return serialize(['data' => $this->_data]);
	}

	public function unserialize($data): void
	{
		if ($parsed = unserialize($data)) {
			$this->_data = $data['data'];
		}
	}

	public function __toString(): string
	{
		return $this->text();
	}

	public function jsonSerialize()
	{
		return $this->json();
	}

	public function __debugInfo(): array
	{
		return [
			'text' => $this->text(),
		];
	}

	public function text():? string
	{
		return $this->_data;
	}

	public function json()
	{
		return json_encode($this->text());
	}

	public function formdata():? FormDataInterface
	{
		return null;
	}

	public function setPostFields($ch): bool
	{
		if (is_resource($ch)) {
			return curl_setopt($ch, CURLOPT_POSTFIELDS, $this->text());
		} else {
			return false;
		}
	}

	public function getContentType():? string
	{
		return $this->_content_type;
	}

	public function setContentType(?string $val): void
	{
		$this->_content_type = $val;
	}

	public function getContentTypeHeader():? string
	{
		if ($type = $this->getContentType()) {
			return "Content-Type: {$type}";
		} else {
			return null;
		}
	}
}

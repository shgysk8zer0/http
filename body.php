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

	public function __construct(?string $response = null)
	{
		$this->_data = $response;
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
}

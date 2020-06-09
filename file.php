<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\FileInterface;

use \shgysk8zer0\HTTP\Traits\FileTrait;

use \JsonSerializable;

use \InvalidArgumentException;

class File implements JsonSerializable, FileInterface
{
	use FileTrait;

	final public function __construct(
		string  $filename,
		?string $mimetype = null,
		string  $postname = ''
	) {
		if (! $this->_setFile($filename, $mimetype, $postname)) {
			throw new InvalidArgumentException(sprintf('File %s not found', $filename));
		}
	}

	public function jsonSerialize(): array
	{
		return [
			'name'     => $this->getFileName(),
			'mime'     => $this->getMimeType(),
			'postname' => $this->getPostFileName(),
		];
	}

	public function __debugInfo(): array
	{
		return [
			'name'     => $this->getFileName(),
			'mime'     => $this->getMimeType(),
			'postname' => $this->getPostFileName(),
		];
	}
} 

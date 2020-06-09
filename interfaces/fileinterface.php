<?php
namespace shgysk8zer0\HTTP\Interfaces;

use \Serializable;

use \CURLFile;

interface FileInterface extends Serializable
{
	public function __construct(string $filename, ?string $mimetype = null, string $postname = '');

	public function jsonSerialize(): array;

	public function getFile(): CURLFile;

	public function getFilename(): string;

	public function getMimeType(): string;

	public function setMimeType(string $mime): void;

	public function getPostFilename():? string;

	public function setPostFilename(string $name): void;
}

<?php

namespace shgysk8zer0\HTTP\Traits;

use \CURLFile;

use \RuntimeException;

use \InvalidArgumentException;

trait FileTrait
{
	private $_file = null;

	private $_filepath = null;

	public function serialize(): string
	{
		return serialize([
			'filepath' => $this->_getFilePath(),
			'mimetype' => $this->getMimeType(),
			'postname' => $this->getPostFilename(),
		]);
	}

	public function unserialize($data): void
	{
		if (! $parsed = unserialize($data)) {
			throw new RuntimeException('Error unserializing File');
		} elseif (! $this->_setFile($parsed['filepath'], $parsed['mimetype'], $parsed['postname'])) {
			throw new InvalidArgumentException(sprintf('File %s not found', $parsed['filename']));
		}
	}

	public function getFile(): CURLFile
	{
		return $this->_file;
	}

	public function getFilename(): string
	{
		return $this->getFile()->getFilename();
	}

	public function getMimeType(): string
	{
		return $this->getFile()->getMimeType();
	}

	public function setMimeType(string $mime): void
	{
		$this->getFile()->setMimeType($mime);
	}

	public function getPostFilename():? string
	{
		return $this->getFile()->getPostFilename();
	}

	public function setPostFilename(string $name): void
	{
		$this->getFile()->setPostFilename($name);
	}

	final protected function _setFile(
		string  $filename,
		?string $mimetype = null,
		string  $postname = ''
	): bool
	{
		if (file_exists($filename)) {
			if (is_null($mimetype)) {
				// @TODO determine mime type from file info
				$mimetype = mime_content_type($filename);
			}

			$this->_file = new CURLFile($filename, $mimetype, $postname);
			$this->_filepath = realpath($filename);
			return true;
		} else {
			return false;
		}
	}

	final protected function _getFilePath():? string
	{
		return $this->_filepath;
	}
}

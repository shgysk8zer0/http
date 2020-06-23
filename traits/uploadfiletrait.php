<?php

namespace shgysk8zer0\HTTP\Traits;

use \Exception;

use \RuntimeException;

use \UnexpectedValueException;

trait UploadFileTrait
{
	public function checkUpload(
		string $filename = null,
		string $mimetype = null,
		string $postname = null,
		int    $error    = UPLOAD_ERR_NO_FILE,
		int    $size     = 0
	):? Exception
	{
		if ($error !== UPLOAD_ERR_OK) {
			switch ($error) {
				case UPLOAD_ERR_NO_FILE:
				    return new RuntimeException('No file was uploaded');

				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
				    return new RuntimeException('Exceeded filesize limit');

				case UPLOAD_ERR_PARTIAL:
					return new RuntimeException('The uploaded file was only partially uploaded');

				case UPLOAD_ERR_NO_TMP_DIR:
					return new RuntimeException('Missing a temporary folder');

				case UPLOAD_ERR_CANT_WRITE:
					return new RuntimeException('Failed to write file to disk');

				case UPLOAD_ERR_EXTENSION:
					return new RuntimeException('A PHP extension stopped the file upload');

				default:
				    return new RuntimeException('Unknown file upload error');
			}
		} elseif (! is_string($filename)) {
			return new UnexpectedValueException('No filename available for upload');
		} elseif (! is_uploaded_file($filename)) {
			return new UnexpectedValueException(sprintf('%s is not an uploaded file', $filename));
		}
	}

	public function moveUploadedFile(string $path): bool
	{
		return move_uploaded_file($this->getFileName(), $path);
	}

	abstract public function getFileName(): string;
}

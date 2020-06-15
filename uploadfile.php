<?php

namespace shgysk8zer0\HTTP;

use \shgysk8zer0\HTTP\Interfaces\{FileInterface};

use \shgysk8zer0\HTTP\Traits\{UploadFileTrait};

use \InvalidArgumentException;

use \UnexpectedValueException;

use \RuntimeException;

use \shgysk8zer0\PHPAPI\Console;

class UploadFile extends File
{
	use UploadFileTrait;

	public function __construct(
		string $filename = null,
		string $mimetype = null,
		string $postname = null,
		int    $error    = UPLOAD_ERR_NO_FILE,
		int    $size     = 0
	)
	{
		if ($exception = $this->checkUpload($filename, $mimetype, $postname, $error, $size)) {
			throw $exception;
		} else {
			parent::__construct($filename, $mimetype, $postname);
		}
	}
} 

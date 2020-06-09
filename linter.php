<?php

namespace shgysk8zer0\HTTP;
use \FilesystemIterator as FS;
use \InvalidArgumentException;

final class Linter
{
	private $_scan_exts    = ['php', 'phtml'];

	private $_ignored_dirs = [];

	final public function ignoreDirs(string ...$dirs): void
	{
		$dirs = array_filter($dirs, 'is_dir');

		$this->_ignored_dirs = array_map(function(string $path): string
		{
			return realpath($path);
		}, $dirs);
	}

	final public function scanExts(string ...$exts): void
	{
		$this->_scan_exts = array_map(function(string $ext): string
		{
			return strtolower($ext);
		}, $exts);
	}

	final public function isAllowedDir(FS $path): bool
	{
		return $path->isDir() and ! in_array($path->getPathname(), $this->_ignored_dirs);
	}

	final public function isLintable(FS $path): bool
	{
		return $path->isFile() and in_array($path->getExtension(), $this->_scan_exts);
	}

	final public function scan(string $path): bool
	{
		if (is_dir($path)) {
			$dir = new FS($path, FS::KEY_AS_PATHNAME | FS::CURRENT_AS_SELF | FS::SKIP_DOTS);
			$valid = true;

			foreach ($dir as $path => $fs) {
				if ($this->isAllowedDir($fs)) {
					static::scan($path);
				} elseif ($this->isLintable($fs)) {
					if (! $this->lintFile($fs)) {
						$valid = false;
					}
				}
			}

			return $valid;
		} else {
			throw new InvalidArgumentException(sprintf('%s is not a directory', $path));
		}
	}

	final public function lintFile(FS $path): bool
	{
		$valid = true;

		if ($this->isLintable($path)) {
			$msg = '';

			if (! php_check_syntax($path->getPathname(), $msg)) {
				$valid = false;
				echo $msg . PHP_EOL;
			}

			return $valid;
		} else {
			throw new InvalidArgumentException(sprintf('Cannot lint path: %s', $path->getPathne()));
		}
	}
}

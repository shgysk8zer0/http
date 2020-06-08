<?php

namespace shgysk8zer0\HTTP\Traits;

trait PathsTrait
{
	use StringTrait;

	/**
	 * Computes the relative path between two paths
	 */
	final protected function _getRelativePath(string $path, ?string $base = null): string
	{
		if ($this->_stringStartsWith($path, '/')) {
			/**
			 * If $path is absolute, just return it
			 */
			return $path;
		} elseif (is_null($base) ) {
			/**
			 * If no base path provided, just strip off the relative prefixes ('./', '../') of $path
			 */
			while ($this->_stringStartsWith($path, '../')) {
				$path = substr($path, 3);
			}

			if ($this->_stringStartsWith($path, './')) {
				$path = substr($path, 1);
			}

			return '/' . ltrim($path, '/');
		}  elseif ($this->_stringStartsWith($path, './')) {
			/**
			 * If $path starts with './', just strip off the "." and
			 * return the computed path appended to the given path, trimming off
			 * the filename if $base does not end with '/'
			 */
			if ($this->_stringEndsWith($base, '/')) {
				return '/' . trim($base, '/') . substr($path, 1);
			} else {
				$dirs = explode('/', $base);
				array_pop($dirs);
				return '/' . trim(join('/', $dirs), '/') . substr($path, 1);
			}
		} elseif (! $this->_stringStartsWith($path, '../')) {
			/**
			 * Should reach this point if give $path begins with an unprefixed
			 * file or directory name. Like previously, just append $path to
			 * $base, tripping off any non-directory component from $base
			 */
			if ($this->_stringEndsWith($base, '/')) {
				return '/' . trim($base, '/') . '/' . $path;
			} else {
				$dirs = explode('/', $base);
				array_pop($dirs);
				return '/' . trim(join('/', $dirs), '/') . '/' . $path;
			}
		} else {
			/**
			 * We now know we have to transverse some directories a bit because
			 * $path ends with "../". Start trimming from the beginning of $path
			 * and the end of $base until an absolute path may be determined.
			 */
			$dirs = explode('/', ltrim($base, '/'));

			if (! $this->_stringEndsWith($base, '/')) {
				array_pop($dirs);
			}

			while (count($dirs) !== 0 and $this->_stringStartsWith($path, '../')) {
				$path = substr($path, 3);

				array_pop($dirs);
			}

			return '/' . rtrim(join('/', $dirs), '/') . '/' . ltrim($path, '/');
		}
	}
}

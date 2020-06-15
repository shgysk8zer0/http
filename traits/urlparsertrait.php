<?php

namespace shgysk8zer0\HTTP\Traits;

trait URLParserTrait
{
	final public static function getRequestUrlString(): string
	{
		$url = '';

		if (array_key_exists('HTTPS', $_SERVER) and ! empty($_SERVER['HTTPS'])) {
			$url = 'https://';
		} else {
			$url = 'http://';
		}

		if (array_key_exists('PHP_AUTH_USER', $_SERVER)) {
			$url .= urlencode($_SERVER['PHP_AUTH_USER']);

			if (array_key_exists('PHP_AUTH_PW', $_SERVER)) {
				$url .= ':' . urlencode($_SERVER['PHP_AUTH_PW']);
			}

			$url .= '@';
		}

		if (array_key_exists('SERVER_NAME', $_SERVER)) {
			$url .= $_SERVER['SERVER_NAME'];
		} else {
			$url .= 'localhost';
		}

		if (array_key_exists('SERVER_PORT', $_SERVER)) {
			$url .= ':' . $_SERVER['SERVER_PORT'];
		}

		if (array_key_exists('PHP_SELF', $_SERVER)) {
			$url .= '/' . ltrim($_SERVER['PHP_SELF'], '/');
		} else {
			$url .= '/';
		}

		if (count($_GET) !== 0) {
			$url .= '?' . http_build_query($_GET);
		}

		return $url;
	}
}

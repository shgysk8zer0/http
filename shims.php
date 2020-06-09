<?php
if (! function_exists('apache_request_headers')) {
	function apache_request_headers(): array
	{
		$arh = [];
		$rx_http = '/\AHTTP_/';

		foreach ($_SERVER as $key => $val) {
			if (preg_match($rx_http, $key)) {
				$arh_key = preg_replace($rx_http, '', $key);
				$rx_matches = [];
				// do some nasty string manipulations to restore the original letter case
				// this should work in most cases
				$rx_matches = explode('_', strtolower($arh_key));
				if (count($rx_matches) !== 0 and strlen($arh_key) > 2) {
					foreach ($rx_matches as $ak_key => $ak_val) {
						$rx_matches[$ak_key] = ucfirst($ak_val);
					}
					$arh_key = implode('-', $rx_matches);
				}
				$arh[$arh_key] = $val;
			}
		}

		if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
			$arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
		}

		if (array_key_exists('CONTENT_LENGTH', $_SERVER)) {
			$arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
		}
		return $arh;
	}
}

if (! function_exists('getallheaders')) {
	function getallheaders(): array
	{
		return apache_request_headers();
	}
}

if (! function_exists('php_check_syntax')) {
	function php_check_syntax(string $filename, string &$error_message = ''): bool
	{
		$cmd = sprintf('php -l %s', escapeshellarg($filename));
		$error_message = exec($cmd, $output, $ret);
		return $ret === 0;
	}
}

<?php
namespace shgysk8zer0\HTTP;
use \shgysk8zer0\HTTP\{Linter};

const BASE = __DIR__ . DIRECTORY_SEPARATOR;

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit();
} else {
	require_once BASE . 'linter.php';
	require_once BASE . 'shims.php';

	$linter = new Linter();
	$linter->ignoreDirs('./.git', './docs', './.github');
	$linter->scanExts('php');

	if (! $linter->scan(__DIR__)) {
		exit(1);
	}
}

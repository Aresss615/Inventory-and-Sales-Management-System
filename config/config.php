<?php

$scriptPath = str_replace('\\', '/', dirname(dirname(__FILE__)));
$docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'));

if (strpos($scriptPath, $docRoot) === 0) {
	$basePath = substr($scriptPath, strlen($docRoot));
	$basePath = $basePath === '' ? '/' : $basePath;
} else {
	$basePath = '/' . trim(basename($scriptPath));
}

define('BASE_PATH', $basePath);
define('BASE_URL', $basePath);
define('API_URL', rtrim($basePath, '/') . '/api');

?>

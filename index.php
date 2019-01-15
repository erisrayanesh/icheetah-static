<?php

	define('SYSTEM_START', microtime(true));

	require_once "vendor/autoload.php";

	//Application bootstrap
	$app = \App\Kernel\Application::getInstance(realpath(__DIR__));
	$app->run();


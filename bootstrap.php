<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	require __DIR__.'/config.php';
	require __DIR__.'/core/application.class.php';

	$app = new Application($key, $secret, $db);

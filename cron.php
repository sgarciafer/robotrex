<?php

	require __DIR__.'/src/edsonmedina/bittrex/Client.php';
	require __DIR__.'/controller/controller.php';
	require __DIR__.'/config.php';

	use edsonmedina\bittrex\Client;

	$bittrex = new Client ($key, $secret);
	$controller = new controller($bittrex);

  $hist = $bittrex->getmarkethistory('USDT-ETH',50);
  $histCooked = $controller->parseHistory($hist,3);

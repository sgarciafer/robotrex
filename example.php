<?php

	require_once __DIR__.'/bootstrap.php';

	//use edsonmedina\bittrex\Client;
	// $bittrex = new Client ($key, $secret);

	//var_dump($b->getOrderBook('USDT-ETH','both','5'));

	/*
	$tickers = $bittrex->getTickerByDate ("USDT-ETH", "fiveMin", time());
	var_dump($tickers);
  */

	$app->frontEnd('USDT-ETH');

	// $hist = $app->bittrex->getmarkethistory('USDT-ETH',50);
	//$histCooked = $controller->parseHistory($hist,3);
	// $app->controller->outputHistory($hist,3);
	//echo('<pre>'.print_r($histCooked,-1).'</pre>');


	/*
	//var_dump ($b->getOrderHistory());
	$OrderHistory = $b->getOrderHistory();
	if(isset($OrderHistory[0])){
		//insertTx($type, $pair = "USDT-ETH", $amount, $price, $balance, $status = "OPEN")
		$c->insertTx($OrderHistory[0]->OrderType, $OrderHistory[0]->Exchange, $OrderHistory[0]->Quantity, $OrderHistory[0]->Limit, $OrderHistory[0]->Price, "OPEN");
	}
	//var_dump ($b->getBalance ("USDT"));
  */

	echo "\n\n";

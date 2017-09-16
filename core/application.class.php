<?php

require_once __DIR__.'/../src/edsonmedina/bittrex/Client.php';
require_once 'model.class.php';
require_once 'view.class.php';
require_once 'controller.class.php';

use edsonmedina\bittrex\Client;

class Application{

  public $model;
  public $view;
  public $controller;
  public $bittrex;

  public function __construct($key, $secret, $db){
    $this->bittrex = new Client($key, $secret);
    $this->model = new Model($db);
    $this->view = new View();
    $this->controller = new Controller();
  }

  public function frontEnd($pair) {
    if(isset($pair)) {
      $hist = $this->bittrex->getmarkethistory($pair,50);
      $this->view->outputHistory($hist);
    }
  }

  public function executeCron($pair, $timeRange) {
    $hist = $this->bittrex->getmarkethistory($pair,50);
    $this->controller->parseHistory($hist,$timeRange);
  }
}

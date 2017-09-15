<?php


class controller{

  private $dbPass = "root";
  private $dbLogin = "root";
  private $dbName = "robotrex";
  private $dbHost = "localhost";
  private $dbConn;
  private $logDir = __DIR__ . "/../logs/";

  private $bittrex;

  public function __construct($bittrex) {
    $this->bittrex = $bittrex;
  }

  public function getLastTickers(){

  }


  public function parseHistory($hist,$timeRange){
    $output = [];
    $output['hist'] = $this->bittrex->getmarkethistory('USDT-ETH',50);
    $output['bands'] = [];
    $output['bands']['average'] = [];
    $output['bands']['data'] = [];

		$stats = array();
		$rangeStamp = $this->getCurrentTimeRange($timeRange);
    $ordersCounter = 0;
    $statCounter = 0;
    $output['bands']['data'][$statCounter] = array(
      'timestamp' => $rangeStamp - 60 * $timeRange,
      'average' => 0,
      'orders' => $ordersCounter,
      'high' => '',
      'low' => '',
      'open' => '',
      'close' => '',
      'trend'=> 0,
    );
    $lastClosePrice = 0;

		foreach( $output['hist'] as $index => $filled) {
			if(isset($filled->OrderType)) {
        $lastClosePrice = $filled->Price;
        $filled->TimeStamp = $this->setRealTimestamp($filled->TimeStamp);

// DEBUG TIME OUTPUT (To delete or set inside a debug functionnality)
// echo ('n:' . time() .'<br/>');
// echo ('r:' . $rangeStamp.'<br/>');
// echo ('o:<b>' . $filled->TimeStamp .'</b><br/>');
// echo ('b:' . ($rangeStamp - (60 * $timeRange)).'<br/>'.'<br/>');

        // If the chronologically ordered actual order has been executed before the time range of the average, move the indexes.
        if( $filled->TimeStamp <= $rangeStamp - 60 * $timeRange ) {

          // Average can now be calculated.
          // Dividing by total number of orders to get the mean value
          $output['bands']['data'][$statCounter]['orders'] = $ordersCounter;
          if($ordersCounter > 0) {
            $output['bands']['data'][$statCounter]['average'] = $output['bands']['data'][$statCounter]['average']/$ordersCounter;
            if( $statCounter<20 ) $output['bands']['average'][$statCounter] = $output['bands']['data'][$statCounter]['average'];
          }
          else $output['bands']['data'][$statCounter]['average'] = 0;

          // Move X minutes back the timestamp marker and reset the number of orders counter to zero.
          $rangeStamp -= 60 * $timeRange;
          $ordersCounter = 0;

          // Start the next index for the the prior X minutes batch of transaction mean value calculation.
          $statCounter++;
          $output['bands']['data'][$statCounter] = array(
            'timestamp' => $rangeStamp - 60 * $timeRange,
            'average' => 0,
            'orders' => $ordersCounter,
            'high' => '',
            'low' => '',
            'open' => '',
            'close' => '',
            'trend'=> 0,
          );
        }

        // Adding the price to later divide and get the mean value.
        $output['bands']['data'][$statCounter]['average'] += $filled->Price;

        // Setting candlestick values. Probably not needed... (?)
        if($ordersCounter == 0) $output['bands']['data'][$statCounter]['close'] = $filled->Price;
        $output['bands']['data'][$statCounter]['open'] = $lastClosePrice;
        if($output['bands']['data'][$statCounter]['high'] == '' OR $filled->Price > $output['bands']['data'][$statCounter]['high']) $output['bands']['data'][$statCounter]['high'] = $filled->Price;
        if($output['bands']['data'][$statCounter]['low'] == '' OR $filled->Price < $output['bands']['data'][$statCounter]['low']) $output['bands']['data'][$statCounter]['low'] = $filled->Price;

        // TO TEST: (1) Adding +1 if it's a buy and -1 if it's a sell, multiplied by the volume to observe bullish bearish volatility potential.
        // TO TEST: (2) Adding +1 if it's a buy and -1 if it's a sell to observe bullish bearish volatility potential.
        $factor = 1;
        if($filled->OrderType == "SELL") {
          $factor = -1;
        }
        // (1):
        //$output['bands']['average'][$statCounter]['trend'] += $factor * $filled->Quantity;
        // (2):
        $output['bands']['data'][$statCounter]['trend'] += $factor;

        $ordersCounter++;

			}
		}
    if($ordersCounter > 0) $output['bands']['data'][$statCounter]['average'] = $output['bands']['data'][$statCounter]['average']/$ordersCounter;
    else $output['bands']['data'][$statCounter]['average'] = 0;

    // Store the results except the first and the last as there could be data missing.
    $this->storeMarketData($output['bands']['data']);

    return $output;
	}

  /**
  *  Helper function calculating the standard deviation of the actual moment.
  **/
  private function setStandardDeviation(&$output) {
    // The number of items back on the past used to calculate the stdv
    $nth = 20;
    // The average values on a simple array.
    $means = [];

    if(count($output['bands']['average'])>19) $means = array_slice($output['bands']['average'],0,20);
    else{
      $means = array_slice($output['bands']['average'],0,count($output['bands']['average']));
      $nth = count($output['bands']['average']);
    }
    $output['bands']['stdvPlusTwo'] = $output['bands']['average'][0] + 2 * ($this->standard_deviation($means)/sqrt($nth));
    $output['bands']['stdvMinusTwo'] = $output['bands']['average'][0] - 2 * ($this->standard_deviation($means)/sqrt($nth));
  }

  /**
  *  Helper function to calculate standard deviation without using PECL PHP library.
  **/
  private function standard_deviation($aValues)
  {
      $fMean = array_sum($aValues) / count($aValues);
      //print_r($fMean);
      $fVariance = 0.0;
      foreach ($aValues as $i)
      {
          $fVariance += pow($i - $fMean, 2);
      }
      $size = count($aValues) - 1;
      return (float) sqrt($fVariance)/sqrt($size);
  }

  /**
  *  Helper function to convert Bittrex date to local server timezone timestamp.
  **/
  private function setRealTimestamp($BittrexTime) {
    $time = strtotime($BittrexTime.' UTC');
    $dateInLocal = date("Y-m-d H:i:s", $time);
    return strtotime($dateInLocal);
  }

  public function outputHistory($hist) {
    echo('<div style="width:400px;height:200px;overflow:scroll;border:1px solid #666;"><pre>'.print_r($hist,-1)."</pre></div>");
  	echo('<div style="width:800px;height:400px;overflow:scroll;border:1px solid #666;">');
  	foreach( $hist as $index => $filled) {
  		if(isset($filled->OrderType)) {
  			$time = $filled->TimeStamp;
  			$bgColor = "#d18787";
  			if($filled->OrderType == "BUY") $bgColor = "#99cc99";
  			echo('<div style="background:'.$bgColor.'"><span style="padding-left:10px;display: inline-block; width: 100px;color:#222;">'.$filled->Price.'</span>&nbsp;<span style="display: inline-block;color: #222;width: 100px;">'.$filled->Quantity.'ETH</span> <span style="color: #222;text-align: right;display: inline-block; width: 200px;">'.$filled->Total.' USDT</span><span style="display: inline-block;color: #222;width:300px;text-align:right">'.$time.'</span></div>' );
  		}
  	}
  	echo('</div>');
  }

  public function checkEnoughRecentData($pair,$timerange){
    $output = [];
    $now = getCurrentTimeRange(time());
    $from = $now - (60 * $timerange);
    $sql = 'SELECT * FROM history WHERE `pair` = "'.$pair.'" AND  `timestamp` >= "'.$from.'" AND  `timestamp` <= "'.$now.'"';
    $this->connectSQL();
    $result = $this->dbConn->query($sql);
    
    $this->dbConn->close();
    return $output;
  }

  /**
  * Getting data into database so the standard deviation and other calculations can be done with data quality.
  **/
  public function storeMarketData($data){
    if(is_array($data) AND count($data) > 2){
      if(isset($data[1]['pair'])) $pair = $data[1]['pair'];
      else $pair = 'USDT-ETH';
      // var_dump($data);die();
      $timerange = false;
      if(isset($data[0]['timestamp']) && isset($data[1]['timestamp'])) {
        $timerange = ($data[0]['timestamp'] - $data[1]['timestamp']) / 60;
      } else {
        $this->setError("Timerange couldn't be detected while storing market data.");
      }
      $sql = 'INSERT INTO history (`average`, `orders`, `high`, `low`, `open`, `close`, `trend`, `timestamp`, `pair`, `timerange`) VALUES';

      $first=true;
      if(!$this->getSingleMarketData($pair,$timerange,$data[1]['timestamp'])) {
        $first = false;
        $sql .= '("'.$data[1]['average'].'","'.$data[1]['orders'].'","'.$data[1]['high'].'","'.$data[1]['low'].'","'.$data[1]['open'].'","'
              .$data[1]['close'].'","'.$data[1]['trend'].'","'.$data[1]['timestamp'].'","'.$pair.'","'.$timerange.'")';
      }
      for($i = 2; $i < count($data); $i++){

        if(!$this->getSingleMarketData($pair,$timerange,$data[$i]['timestamp'])) {
          if(!$first) {
            $sql.=', ';
          }
          $sql .= '("'.$data[$i]['average'].'","'.$data[$i]['orders'].'","'.$data[$i]['high'].'","'.$data[$i]['low'].'","'.$data[$i]['open'].'","'
          .$data[$i]['close'].'","'.$data[$i]['trend'].'","'.$data[$i]['timestamp'].'","'.$pair.'","'.$timerange.'")';
          $first = false;
        }
      }

      // If there is actually something to insert.
      if(!$first){
          $this->connectSQL();
          if ($this->dbConn->query($sql) === TRUE) {
              //All went well, let's mark it on the log.
              $this->writeLog("New market history batch with ".$timerange." min span time inserted for ".$pair." at " . date("Y-m-d H:i:s"));
          } else {
              //There was an error excuting the MySql operation.
              $this->setError("Error: " . $sql . "<br>" . $this->dbConn->error);
          }
          $this->dbConn->close();
      }
    } else {
        $this->writeLog("[".date("Y-m-d H:i:s")."]: Warning: Not enough data collected to storeMarketData." );
    }
  }

  private function getSingleMarketData($pair, $timerange, $timestamp) {
    $output = [];
    $this->connectSQL();
    $sql = 'SELECT * FROM history WHERE pair="'.$pair.'" AND timerange="'.$timerange.'" AND timestamp="'.$timestamp.'"';
    $result = $this->dbConn->query($sql);
    if ($result->num_rows > 0 ) {
        $i = 0;
        while($row = $result->fetch_assoc()) {
          $output[$i] = $row;
          $i++;
        }
        return $output;
    } else {
        return false;
    }
    $this->dbConn->close();
  }

  public function insertTx($type, $pair = "USDT-ETH", $amount, $price, $balance, $status = "OPEN"){
    $this->connectSQL();
    $sql = "INSERT INTO transactions (type, pair, amount, price, balance, status)
            VALUES ( '$type', '$pair', '$amount', '$price', '$balance', '$status')";
    if ($this->dbConn->query($sql) === TRUE) {
        $this->writeLog("New tx inserted ( ".$status." ): " . $type . " " . $pair . " ". $amount ." at " . $price . " producing " . $balance );
    } else {
        $this->setError("Error: " . $sql . "<br>" . $this->dbConn->error);
    }
    $this->dbConn->close();
  }

  private function connectSQL(){
    $this->dbConn = new mysqli($this->dbHost, $this->dbLogin, $this->dbPass, $this->dbName);
    if ($this->dbConn->connect_error) {
        $this->setError("Connection failed: " . $conn->connect_error);
    }
  }

  private function setError($message) {
    $this->writeLog($this->strictCleanUp($message));
    die('Error.');
  }

  private function writeLog($message) {
    $data = "[".time()."] ".$message. "\r\n";
    $logname = date('Ymd', time()).'.txt';
    file_put_contents( $this->logDir.$logname , $data , FILE_APPEND | LOCK_EX );
  }

  private function strictCleanUp($string) {
    return $string;
    return preg_replace('/[^A-Za-z0-9\-\(\) ]/', '', $string);
  }
  /**
   * Helper function, return the current X minutes max time range.
   * @timeRange: the time in minutes that would be represented by a single candlestick on a graphic chart.
   **/
  private function getCurrentTimeRange($timeRange){
    $min = date('i') +($timeRange - (date('i') % $timeRange));
    $result = strtotime(date('Y-m-d').'T'.date('H').':'.$min.':00');
    if(!$result > 1) {
      $this->writeLog("Error getting current time range. timeRange = ".$timeRange);
      echo('Error. Check logs.');
      die();
    }
    return $result;
  }
}

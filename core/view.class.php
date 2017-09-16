<?php


class View{

    private $tplPath;

    public function __construct() {

    }

    public function outputError($message) {

    }

    public function outputHistory($hist) {
      $variables = array();
      $variables['ordersHistory'] = '';
      //$variables['ordersHistory'] .= ('<div style="width:400px;height:200px;overflow:scroll;border:1px solid #666;"><pre>'.print_r($hist,-1)."</pre></div>");
    	$variables['ordersHistory'] .= ('<div style="width:800px;height:400px;overflow:scroll;border:1px solid #666;">');
    	foreach( $hist as $index => $filled) {
    		if(isset($filled->OrderType)) {
    			$time = $filled->TimeStamp;
    			$bgColor = "#d18787";
    			if($filled->OrderType == "BUY") $bgColor = "#99cc99";
    			$variables['ordersHistory'] .= ('<div style="background:'.$bgColor.'"><span style="padding-left:10px;display: inline-block; width: 100px;color:#222;">'.$filled->Price.'</span>&nbsp;<span style="display: inline-block;color: #222;width: 100px;">'.$filled->Quantity.'ETH</span> <span style="color: #222;text-align: right;display: inline-block; width: 200px;">'.$filled->Total.' USDT</span><span style="display: inline-block;color: #222;width:300px;text-align:right">'.$time.'</span></div>' );
    		}
    	}
    	$variables['ordersHistory'] .= ('</div>');
      $this->render($variables);
    }

    private function render($variables) {
      $tplBase = 'templates/dashboard/';
      include(__DIR__.'/../templates/dashboard/index.tpl.php');
    }

}

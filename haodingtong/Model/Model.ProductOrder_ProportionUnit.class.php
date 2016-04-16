<?php

class ProductOrder_ProportionUnit {
	public static $_instance = array();
	public static function getInstance ($proportion_id) {
		$instance = STATIC::$_instance[$proportion_id];
		if(!$instance) {
			$instance 	= new ProductOrder_ProportionUnit($proportion_id);
			STATIC::$_instance[$proportion_id]	= $instance;
		}
		return $instance;
	}
    public function __construct($proportion_id){
    	$this->proportion_id = $proportion_id;
    	$ProductProportion 	= new ProductProportion;
    	$this->proportion 	= $ProductProportion->findone("id={$proportion_id}");
    	$this->proportion_list = explode(":", $this->proportion['proportion']);
    	$num 	= 0;
    	foreach($this->proportion_list as $proportion) {
    		$num += $proportion;
    	}
    	$this->proportion_num 	= $num;
    }


}

<?php

/*
	method 1:
	ProductOrder::add($user_id, $product_id, $product_color_id, $product_size_id, $num);
	$result 	= ProductOrder::run();
	echo json_encode($result);
	
	method 2:
	$u 	= ProductOrder_User::getInstance($user_id);
	$u->add($product_id, $product_color_id, $product_size_id, $num);
	$u->execute();
	
	method 3: 此方法不更新用户汇总信息 需要在结束之后 执行 $OrderList->refresh_index_user($user_id);
	$ProductOrder = ProductOrder::getInstance($user_id, $product_id);
	$ProductOrder->set($product_color_id, $product_size_id, $num);
	$info 		= $ProductOrder->execute();

	4: 配比下单
	ProductOrder::proportion_add($user_id, $product_id, $product_color_id, $proportion_id, $num);
	ProductOrder::run();
*/

class ProductOrder {
	public static $_instance = array();

	public static function proportion_add($user_id, $product_id, $product_color_id, $proportion_id, $num) {
		$ProductProportion 	= ProductOrder_Proportion::getInstance($user_id, $product_id);
		$ProductProportion->add($product_color_id, $proportion_id, $num);
	}
	public static function add ($user_id, $product_id, $product_color_id, $product_size_id, $num) {
		$u 	= ProductOrder_User::getInstance($user_id);
		$u->add($product_id, $product_color_id, $product_size_id, $num);
	}

	public static function run () {
		ProductOrder_Proportion::run();
		$result 	= ProductOrder_User::run();
		return $result;
	}

	public static function getInstance ($user_id, $product_id) {
		$ProductOrder 	= STATIC::$_instance[$user_id][$product_id];
		if(!$ProductOrder){
			$ProductOrder 	= new ProductOrder($user_id, $product_id);
			STATIC::$_instance[$user_id][$product_id]	= $ProductOrder;
		}
		return $ProductOrder;
	}

	// 更新所有单价有变动的款的订单
	public static function refresh_product_price_change () {
		$OrderList 	= new OrderList;
		$list 		= $OrderList->get_product_price_changed_orderlist();
		foreach($list as $row){
			$user_id 		= $row['user_id'];
			$product_id 	= $row['product_id'];
			$product_color_id 	= $row['product_color_id'];
			$product_size_id 	= $row['product_size_id'];
			$num 				= $row['num'];
			ProductOrder::add($user_id, $product_id, $product_color_id, $product_size_id, $num);
		}
		ProductOrder::run();
	}

	// 更新单个用户的所有订单
	public static function refresh_user ($user_id, $params=array()) {
		$category_id 	= $params['category_id'];
		$OrderList 		= new OrderList;
		$options['limit']	= 10000;
		$condition[]	= "user_id={$user_id}";
		if($category_id)	$condition[]	= "product_id in (select id from product where category_id={$category_id})";
		$where 		= implode(" AND ", $condition);
		$list 		= $OrderList->find($where, $options);
		foreach($list as $row){
			$user_id 		= $row['user_id'];
			$product_id 	= $row['product_id'];
			$product_color_id 	= $row['product_color_id'];
			$product_size_id 	= $row['product_size_id'];
			$num 				= $row['num'];
			ProductOrder::add($user_id, $product_id, $product_color_id, $product_size_id, $num);
		}
		ProductOrder::run();
	}

    public function __construct($user_id, $product_id){
    	$this->user_id		= $user_id;
    	$this->product_id 	= $product_id;
    	$this->event 		= array();
    	$this->user 		= new User($user_id);
    	$this->product 		= new Product($product_id);
    	$this->order 		= new ProductOrder_Order($user_id, $product_id);
    }

    public function set ($product_color_id, $product_size_id, $num) {
    	$this->order->add($product_color_id, $product_size_id, $num);
    }

    public function execute () {
    	$info 	= array();
    	$isspot = $this->product->isspot;
    	$valid  = $this->order->validate($this->product);
    	if($valid['error'] == 0){ // 单款下单要求验证成功
    		$this->order->save($this);
    		$this->emit('Save');
    	}else{ // 单款下单要求验证失败
    		$info['error']		= 1;
    		$info['message']	= $valid['message'];
    	}
    	return $info;
    }

    public function on ($event, $listener) {
    	$this->event[$event][] 	= $listener;
    }

    public function emit ($event) {
    	$listeners 	= $this->event[$event];
    	if($listeners) {
    		foreach($listeners as $listener) {
    			$method_name = "on{$event}";
    			if(method_exists($listener, $method_name)){
    				call_user_func_array(array($listener, $method_name), array($this));
    			}else{
    				throw new Expection("方法[{$method_name}]找不到");
    			}
    		}
    	}
    }

}




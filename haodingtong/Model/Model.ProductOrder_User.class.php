<?php

class ProductOrder_User {
    private static $_instance       = array();
    private $_ProductOrderInstance  = array();

    public static function getInstance ($user_id) {
        if(!STATIC::$_instance[$user_id]){
            STATIC::$_instance[$user_id]    = new ProductOrder_User($user_id);
        }
        return STATIC::$_instance[$user_id];
    }

    public static function getAllInstance () {
        return STATIC::$_instance;
    }

    public static function run () {
        $_instance  = STATIC::getAllInstance();
        foreach($_instance as $user_id => $u) {
            $info   = $u->execute();
            if($info['error']){
                $result['error']    = 1;
                $result['message']  = implode(",", $info['error_list']);
            }
            $result['orderinfo']    = $info['orderinfo'];
        }
        return $result;
    }
    
    public function __construct($user_id){
        $this->user_id  = $user_id;
        $this->user     = new User($user_id);
    }

    public function add ($product_id, $product_color_id, $product_size_id, $num){
        $ProductOrder   = $this->getProductOrderInstance($product_id);
        $ProductOrder->set($product_color_id, $product_size_id, $num);
    }

    public function execute(){
        $result     = array();
        foreach($this->_ProductOrderInstance as $ProductOrder){
            $info   = $ProductOrder->execute();
            if($info['error']){
                $result['error']    = 1;
                $result['error_list'][] = $info['message'];
            }
        }
        $OrderList  = new OrderList;
        $orderinfo  = $OrderList->refresh_index_user($this->user_id);
        $result['orderinfo']    = $orderinfo;
        return $result;
    }

    private function getProductOrderInstance ($product_id) {
        if(!$this->_ProductOrderInstance[$product_id]){
            $this->_ProductOrderInstance[$product_id] =  ProductOrder::getInstance($this->user_id, $product_id);
        }
        return $this->_ProductOrderInstance[$product_id];
    }
}




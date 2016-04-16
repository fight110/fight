<?php

class ProductOrder_UserMaster {
    private static $_instance       = array();
    private static $_master_hash    = array();

    public static function getMasteruser ($slave_user_id) {
        $master_id      = STATIC::$_master_hash[$slave_user_id];
        if(!isset($master_id)){
            $UserSlave  = new UserSlave;
            $master_id  = $UserSlave->get_master_uid($slave_user_id);
            STATIC::$_master_hash[$slave_user_id]   = $master_id;
        }
        return STATIC::getInstance($master_id);
    }

    public static function getInstance ($user_id) {
        if(!STATIC::$_instance[$user_id]){
            STATIC::$_instance[$user_id]    = new ProductOrder_UserMaster($user_id);
        }
        return STATIC::$_instance[$user_id];
    }

    public function __construct($user_id){
        $this->user_id  = $user_id;
        $this->user = new User($user_id);
    }

    public function get_user_product_discount (Product $product) {
        return $this->user->get_user_product_discount($this->user, $product);
    }
}




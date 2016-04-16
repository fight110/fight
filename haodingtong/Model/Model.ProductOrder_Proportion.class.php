<?php

class ProductOrder_Proportion {
    public static $_instance = array();
    public static function getInstance ($user_id, $product_id) {
        $instance   = STATIC::$_instance[$user_id][$product_id];
        if(!$instance) {
            $instance = new ProductOrder_Proportion($user_id, $product_id);
            STATIC::$_instance[$user_id][$product_id]   = $instance;
        }
        return $instance;
    }
    public static function run () {
        foreach(STATIC::$_instance as $user_id => $product_hash) {
            foreach($product_hash as $product_id => $instance) {
                $instance->execute();
            }
        }
    }
    public function __construct($user_id, $product_id){
        $this->user_id      = $user_id;
        $this->product_id   = $product_id;
        $this->data         = array();
        $this->color_data   = array();
        $ProductSize        = new ProductSize;
        $this->size_list    = $ProductSize->get_size_list($product_id);
    }

    public function add($color_id, $proportion_id, $xnum) {
        $this->data[$color_id][$proportion_id]  = $xnum;
    }

    public function execute() {
        $user_id            = $this->user_id;
        $product_id         = $this->product_id;
        foreach($this->data as $color_id => $proportion_hash) {
            foreach($proportion_hash as $proportion_id => $xnum) {
                if($xnum >= 0) {
                    $proportion     = ProductOrder_ProportionUnit::getInstance($proportion_id);
                    $proportion_list = $proportion->proportion_list;
                    foreach($this->size_list as $key => $size) {
                        $size_id    = $size['size_id'];
                        $num  = $xnum * $proportion_list[$key];
                        $this->add_color_data($color_id, $size_id, $num);
                    }
                }
            }
        }
        foreach($this->color_data as $color_id => $size_hash) {
            foreach($size_hash as $size_id => $num) {
                ProductOrder::add($user_id, $product_id, $color_id, $size_id, $num);
            }
        }
        $ProductOrder   = ProductOrder::getInstance($user_id, $product_id);
        $ProductOrder->on('Save', $this);
    }

    public function add_color_data ($color_id, $size_id, $num) {
        $n = $this->color_data[$color_id][$size_id];
        $this->color_data[$color_id][$size_id] = $n + $num;
    }

    public function onSave ($ProductOrder) {
        $user_id            = $this->user_id;
        $product_id         = $this->product_id;
        $OrderListProportion    = new OrderListProportion;
        foreach($this->data as $color_id => $proportion_hash) {
            foreach($proportion_hash as $proportion_id => $xnum) {
                $proportion     = ProductOrder_ProportionUnit::getInstance($proportion_id);
                $num            = $proportion->proportion_num * $xnum;
                $OrderListProportion->add($user_id, $product_id, $color_id, $proportion_id, $xnum, $num);
            }
        }
    }


}

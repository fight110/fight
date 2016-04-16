<?php

class ProductImage Extends BaseClass {
    private $clear_product_ids = array();
    private static $_instance = null;
    public static function getInstance () {
        if(!STATIC::$_instance) {
            STATIC::$_instance = new ProductImage;
        }
        return STATIC::$_instance;
    }
    public static function getProductColorImage ($product_id, $color_id=0) {
        if($color_id) {
            $instance = STATIC::getInstance();
            $list   = $instance->get_image_list($product_id);
            foreach($list as $image) {
                if($image['color_id'] == $color_id) {
                    return $image;
                }
            }
        }
        return null;
    }
    public function add_clear_product_ids($product_id) {
        $this->clear_product_ids[$product_id]++;
    }
    public function __construct(){
        $this->setFactory('product_image');
    }

    public function create_image($product_id, $filepath, $color_id=0){
        $id = $this->create(array('product_id'=>$product_id, 'image'=>$filepath, 'color_id'=>$color_id))->insert(); 
        $this->add_clear_product_ids($product_id);
        return $id;
    }

    public function get_image_list($product_id){
    	$that = $this;
        $cache  = new Cache(function($product_id) use ($that){
            $where                  = "product_id={$product_id}";
            $options['limit']       = 100;
            $options['fields']      = "*";
            $options['order']       = "id asc";
            // $options['db_debug']    = true;
            return $that->find($where, $options);
        }, 60);
        return $cache->get("ProductImage_{$product_id}", array($product_id));
    }

    public function __destruct() {
        foreach($this->clear_product_ids as $product_id => $val) {
            Cache::clearCache("ProductImage_{$product_id}");
        }
    }
}





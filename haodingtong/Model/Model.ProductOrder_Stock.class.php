<?php

class ProductOrder_Stock {
    public function __construct($product_id){
        $this->product_id   = $product_id;
        $ProductStock       = new ProductStock;
        $this->data         = array();
        $this->error_list   = array();
        $this->change_stock = array();
        $this->stock_list   = $ProductStock->get_product_stock_list($product_id);
        foreach($this->stock_list as $stock){
            $this->set_stock($stock['product_color_id'], $stock['product_size_id'], $stock);
        }
    }

    public function set_stock($product_color_id, $product_size_id, $stock){
        $this->data[$product_color_id][$product_size_id]    = $stock;
    }

    public function get_stock($product_color_id, $product_size_id){
        $stock = $this->data[$product_color_id][$product_size_id];
        return new ProductStockUnit($stock);
    }

    public function set_dif_order($dif_order){
        foreach($dif_order as $product_color_id => $color_hash){
            foreach($color_hash as $product_size_id => $dif_num){
                $stock  = $this->get_stock($product_color_id, $product_size_id);
                $live_num   = $stock->get_live_num();
                if($live_num < $dif_num){
                    $color  = Keywords::cache_get($product_color_id);
                    $size   = Keywords::cache_get($product_size_id);
                    $this->error("{$color}{$size}库存不足");
                }else{
                    $stock->add_ordernum($dif_num);
                    $this->change_stock[]   = $stock;
                }
            }
        }
        return count($this->error_list) ? false : true;
    }

    public function error($message){
        $this->error_list[] = $message;
    }

    public function save () {
        if(0 === count($this->error_list)){
            foreach($this->change_stock as $stock){
                $stock->save();
            }
        }
    }

}




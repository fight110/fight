<?php

class ProductStockTable {
    public function __construct($product_id, $stock_list){
        $this->product_id   = $product_id;
        $this->stock_list   = $stock_list;
        $this->template     = "product/stocktable.html";
    }

    public function make(){
        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $product_id     = $this->product_id;
        $stock_list     = $this->stock_list;

        $color_list     = $ProductColor->get_color_list($product_id);
        $size_list      = $ProductSize->get_size_list($product_id);
        $hash       = array();
        foreach($stock_list as $row){
            $color_id   = $row['product_color_id'];
            $size_id    = $row['product_size_id'];
            $hash["{$color_id}_{$size_id}"] = $row;
        }
        $list   = array();
        $total  = 0;
        foreach($color_list as $color){
            $tr     = array();
            $tr['color']    = $color;
            $color_id       = $color['color_id'];
            foreach($size_list as $size){
                $size_id    = $size['size_id'];
                $key        = "{$color_id}_{$size_id}";
                $stock      = new ProductStockUnit($hash[$key]);
                $td         = array('num'=>$stock->get_live_num());
                $tr['td'][] = $td;
                $tr['count']    += $td['num'];
            }
            $list[] = $tr;
            $total += $tr['count'];
        }
        $result['list']         = $list;
        $result['size_list']    = $size_list;
        $result['color_list']   = $color_list;
        $result['total']        = $total;
        return $result;
    }
}





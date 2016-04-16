<?php

class ProductStock Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_stock');
    }

    public function create_stock($product_id, $color_id, $size_id, $total_num){
        return $this->create(array('product_id'=>$product_id, 'product_color_id'=>$color_id, 'product_size_id'=>$size_id, 'totalnum'=>$total_num))->insert(true);
    }

    public function get_product_stock($product_id){
        $options['tablename']   = "product_stock";
        $options['group']       = "product_id";
        $options['fields']      = "sum(totalnum) as num";
        $condition[]    = "product_id={$product_id}";
        $where  = implode(" AND ", $condition);
        return $this->findone($where, $options);
    }

    public function get_product_stock_list ($product_id, $product_color_id=0, $size_id=0) {
        $options['limit']   = 1000;
        $condition[]    = "product_id={$product_id}";
        if($product_color_id){
            $condition[]    = "product_color_id={$product_color_id}";
        }
        if($product_size_id){
            $condition[]    = "product_size_id={$product_size_id}";
        }
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }

}





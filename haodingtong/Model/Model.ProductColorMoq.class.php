<?php

class ProductColorMoq Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_color_moq');
    }

    public function create_moq($product_id, $color_id, $num){
        return $this->create(array('product_id'=>$product_id, 'product_color_id'=>$color_id, 'num'=>$num))->insert(true);
    }

    public function get_status($product_id,$color_id){
        $OrderListProductColor  =   new OrderListProductColor();
        $OrderNum   =   $OrderListProductColor->get_product_color_num($product_id, $color_id);
        
        $MoqNum    =   $this->findone("product_id={$product_id} AND product_color_id={$color_id}",array("fields"=>"num"));

        return $OrderNum['num'] - $MoqNum['num'] >= 0 ? 1 :0 ;
    }
    
    public function get_num($product_id,$color_id){    
        $MoqNum    =   $this->findone("product_id={$product_id} AND product_color_id={$color_id}",array("fields"=>"num"));
    
        return $MoqNum['num'];
    }
    
    public function set_num($product_id,$color_id,$num){
        $result = $this->create(array("num"=>$num, "product_id"=>$product_id, "product_color_id"=>$color_id))->insert(true);
        return $result;
    }
}





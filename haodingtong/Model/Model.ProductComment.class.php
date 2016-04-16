<?php

class ProductComment Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_comment');
    }

    public function create_comment($user_id, $data){
        $comment    = $this->create($data);
        $comment->user_id   = $user_id;
        $comment->create_ip = Flight::IP();
        $comment->insert();
        return $comment->getData();
    }
    
    public function get_product_comment($user_id,$product_id){
        $row = $this->findone("user_id={$user_id} AND product_id={$product_id}");
    
        return $row;
    }
    
    public function getAvgScore($product_id, $is_iphone=false){
        $max_width  = $is_iphone    ? 55 : 60;
        $options    = array();
        $options['fields']  = "AVG(score) as score, COUNT(DISTINCT user_id) as unum";
        $options['group']   = "product_id";
        $row    = $this->findone("product_id={$product_id} AND status=1", $options);
        $row['score']       = sprintf('%.1f', $row['score']);
        $row['width']       = $row['unum']  ? sprintf('%d', $row['score'] / 5 * $max_width)     : $max_width * 0.6;
        return $row;
    }
    
}




<?php

class Moq Extends BaseClass {
    public function __construct(){
        $this->setFactory('moq');
    }

    public function create_moq($data){
        foreach($data as $key => $val){
            $target[$key]   = $val;
        }
        $moq    = $this->create($target);
        return $moq->insert(true);
    }

    public function get_user_product_moq ($user_level, $product_id) {
        $moq    = $this->findone("keyword_id={$user_level} AND product_id={$product_id}");
        return $moq;
    }
    
}





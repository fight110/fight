<?php

class ProductDisplay Extends BaseClass {
    public function __construct($id=null){
        $this->setFactory('product_display');
        if(is_numeric($id)){
            $this->setAttribute($this->findone("id={$id}"));
        }
    }

    public function create_display($bianhao, $name,$pd_type,$pd_type2, $defaultimage){
        return $this->create(array('bianhao'=>$bianhao, 'name'=>$name,'pd_type'=>$pd_type,'pd_type2'=>$pd_type2, 'defaultimage'=>$defaultimage))->insert();
    }

    
    
}





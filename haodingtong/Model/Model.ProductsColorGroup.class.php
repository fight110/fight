<?php

class ProductsColorGroup Extends BaseClass {
    public function __construct(){
        $this->setFactory('products_color_group');
    }
    
    public function get_rgb($keyword_id){
        return $this->findone("keyword_id='$keyword_id'");
    }
    
    public function createItem($keyword_id,$rgb){
        return $this->create(array('field'=>'color_group','keyword_id'=>$keyword_id,'rgb'=>$rgb))->insert(true);
    }
    
    public function get_hash($key='keyword_id'){
        $options['fields'] = "keyword_id,rgb";
        $options['key']    = $key;
        $where  =   1;
        
        return $this->find($where,$options);
    }
    
    public function get_list($options=array()){
        $options['limit']   = 1000;
        $where  =   1;
        
        return $this->find($where,$options);
    }
}





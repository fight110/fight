<?php

class ProductGroupImage Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_group_image');
    }

    public function create_image($group_id, $filepath){
        return $this->create(array('group_id'=>$group_id, 'image'=>$filepath))->insert(); 
    }

    public function get_image_list ($group_id) {
    	$options['limit']	= 20;
    	return $this->find("group_id={$group_id}", $options);
    }
    
}





<?php

class ProductDisplayImage Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_display_image');
    }

    public function create_image($display_id, $filepath){
        return $this->create(array('display_id'=>$display_id, 'image'=>$filepath))->insert(); 
    }
    public function get_image_list ($display_id) {
    	$options['limit']	= 20;
    	//$options['db_debug']	= true;
    	return $this->find("id={$display_id}", $options);
    }
    
    public function get_image_list_by_did ($display_id) {
        $options['limit']	= 20;
        return $this->find("display_id={$display_id}", $options);
    }
}





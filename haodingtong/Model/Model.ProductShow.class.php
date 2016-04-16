<?php

class ProductShow Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_show');
    }

    public function create_show($room_id, $dp_num=0, $bianhaos='', $product_ids=''){
        return $this->create(array('room_id'=>$room_id, 'dp_num'=>$dp_num, 'bianhaos'=>$bianhaos, 'product_ids'=>$product_ids))->insert();
    }

    public function update_show($id, $room_id, $dp_num=0, $bianhaos='', $product_ids=''){
        return $this->update(array('room_id'=>$room_id, 'dp_num'=>$dp_num, 'bianhaos'=>$bianhaos, 'product_ids'=>$product_ids), "id={$id}");
    }

    public function get_show_list($show_id, $room_id){
    	$options['order']	= "id desc";
    	$options['limit']	= 2;
    	$where 	= "id<={$show_id} and room_id={$room_id}";
    	$list 	= $this->find($where, $options);
    	$Product 	= new Product;
    	foreach($list as &$row){
    		if($product_ids 	= $row['product_ids']){
    			$row['plist']	= $Product->find("id in ({$product_ids})");
    		}
    	}
    	return $list;
    }

    
}


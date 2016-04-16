<?php

class ProductsSizeGroup Extends BaseClass {
    public function __construct(){
        $this->setFactory('products_size_group');
    }

    public function get_size_hash ($size_group_id) {
    	$options['key']	= 'size_id';
    	$options['limit']	= 100;
    	$hash = $this->find("size_group_id={$size_group_id}", $options);
    	return $hash;
    }

    public function get_size_list ($size_group_id) {
        $options['tablename']   = "products_size_group as sg left join keywords as k on sg.size_id=k.id left join products_attr as pa on pa.field='size' and pa.keyword_id=sg.size_id";
        $options['fields']      = "k.name,sg.*";
        $options['limit']   = 100;
        $options['order']   = "pa.rank";
        $list = $this->find("sg.size_group_id={$size_group_id}", $options);
        return $list;
    }

    public function add_size_group_unit ($size_group_id, $size_id) {
        $data['size_group_id']  = $size_group_id;
    	$data['size_id']		= $size_id;
    	$this->create($data)->insert();
    }

    public function del_size_group_unit ($size_group_id, $size_id) {
    	$this->delete("size_group_id={$size_group_id} and size_id={$size_id}");
    }

}




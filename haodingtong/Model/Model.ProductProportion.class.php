<?php

class ProductProportion Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_proportion');
    }

    public function create_proportion ($user_id, $size_group_id, $proportion) {
        $data['user_id']        = $user_id;
        $data['size_group_id']      = $size_group_id;
        $data['proportion']     = $proportion;
        return $this->create($data)->insert();
    }

    public function get_proportion_list($user_id, $size_group_id='') {
        $options['order']       = "id desc";
        $condition[]            = "user_id in ({$user_id}, 0)";
        if($size_group_id) {
            $condition[]        = "size_group_id='{$size_group_id}'";
        }
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        foreach($list as &$proportion) {
            $proportion['proportion_list']  = explode(":", $proportion['proportion']);
        }
        return $list;
    }

}

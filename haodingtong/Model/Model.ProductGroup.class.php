<?php

class ProductGroup Extends BaseClass {
    public function __construct($id=null){
        $this->setFactory('product_group');
        if(is_numeric($id)){
            $this->setAttribute($this->findone("id={$id}"));
        }
    }

    public function create_group($dp_num, $name,$dp_type,$dp_type2, $defaultimage){
        return $this->create(array('dp_num'=>$dp_num, 'name'=>$name,'dp_type'=>$dp_type,'dp_type2'=>$dp_type2, 'defaultimage'=>$defaultimage))->insert();
    }

    public function getGroupByDpnum($dp_num){
    	$where 	= "dp_num='{$dp_num}'";
    	return $this->findone($where);
    }

    public function getGroupListByProductId($product_id, $options=array()){
        $where  = "product_id={$product_id}";
        $options['tablename']   = "product_group as g left join product_group_member as m on g.id=m.group_id";
        $options['fields']      = "g.*";
        return $this->find($where, $options);
    }
    
    public function get_dp_type_list(){
        $options['group']  =   "dp_type";
        $options['fields'] = "dp_type";
        return $this->find("1",$options);
    }

    public function get_skc_info($dp_num){
        $options['tablename'] = "product_group as pg left join product_group_member as pgm on pg.id=pgm.group_id
                                 left join product_color as pc on pgm.product_id=pc.product_id and pgm.color_id=pc.color_id";
        $options['fields']    = "pc.product_id,pc.color_id,pc.skc_id";
        $options['limit']     = 1000;
        $where  = "pg.dp_num={$dp_num}";
        return $this->find($where,$options);
    }
    
}





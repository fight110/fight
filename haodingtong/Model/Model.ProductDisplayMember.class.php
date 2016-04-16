<?php

class ProductDisplayMember Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_display_member');
    }

    public function create_member($display_id, $product_id){
        return $this->create(array('display_id'=>$display_id, 'product_id'=>$product_id))->insert();
    }

    public function getDisplayOtherMember($product_id, $display_id=null){
        $condition  = array();
        if(is_numeric($display_id)){
            $condition[]    = "m1.display_id={$display_id}";
        }
        $condition[]    = "m1.product_id={$product_id}";
        $condition[]    = "m1.product_id<>m2.product_id";
        $where      = implode(' AND ', $condition);
        $fields     = "m2.*";
        $tablename  = "product_display_member as m1 left join product_display_member as m2 on m1.display_id=m2.display_id";
        $sql        = "SELECT {$fields} FROM {$tablename} WHERE {$where} group by m2.product_id";
        $sth        = $this->dbh->prepare($sql);
        $sth->execute();
        $result     = array();
        while($row  = $sth->fetch()){
            $result[]   = $row;
        }
        return $result;
    }

    public function getDisplayMember($display_id, $get_detail=false){
        $condition      = array();
        $condition[]    = "display_id={$display_id}";
        $where  = implode(' AND ', $condition);
        $options['limit']   = 100;
        $options['order']   = 'rank desc';
        $list   = $this->find($where, $options);
        if($get_detail){
            $list   = Flight::listFetch($list, "product", "product_id", "id");
        }
        return $list;
    }
    public function getDisplayList($options=array(),$returnType=1,$condition=array()){
        $where = implode(' AND ', $condition);
        $list   = $this->find($where, $options);
        if($returnType==2){
            $keys=array_keys($list);
            return $keyStr=implode(',', $keys);
        }else{
            return $list;
        }
    }
}





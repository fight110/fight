<?php

class ProductGroupMember Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_group_member');
    }

    public function create_member($group_id, $product_id, $color_id=0, $image_path=''){
        return $this->create(array('group_id'=>$group_id, 'product_id'=>$product_id, 'color_id'=>$color_id, 'image'=>$image_path))->insert();
    }

    public function get_member_list ($group_id) {
        $options['limit']   = 100;
        $condition[]    = "group_id={$group_id}";
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        return $list;
    }

    public function getGroupOtherMember($product_id, $group_id=null){
        $condition  = array();
        if(is_numeric($group_id)){
            $condition[]    = "m1.group_id={$group_id}";
        }
        $condition[]    = "m1.product_id={$product_id}";
        $condition[]    = "m1.product_id<>m2.product_id";
        $where      = implode(' AND ', $condition);
        $fields     = "m2.*";
        $tablename  = "product_group_member as m1 left join product_group_member as m2 on m1.group_id=m2.group_id";
        $sql        = "SELECT {$fields} FROM {$tablename} WHERE {$where} group by m2.product_id";
        $sth        = $this->dbh->prepare($sql);
        $sth->execute();
        $result     = array();
        while($row  = $sth->fetch()){
            $result[]   = $row;
        }
        return $result;
    }

    public function getGroupMember($group_id, $get_detail=false){
        $condition      = array();
        $condition[]    = "group_id={$group_id}";
        $where  = implode(' AND ', $condition);
        $options['limit']   = 100;
        $list   = $this->find($where, $options);
        if($get_detail){
            $list   = Flight::listFetch($list, "product", "product_id", "id");
        }
        return $list;
    }

    public function getGroupMemberByDpnum($dp_num){
        $ProductGroup   = new ProductGroup;
        $group  = $ProductGroup->getGroupByDpnum($dp_num);
        if($group_id = $group['id']){
            return $this->getGroupMember($group_id);
        }
        return array();
    }

    public function getGroupList($options=array(),$returnType=1,$condition=array()){
        $where = implode(' AND ', $condition);
        $list   = $this->find($where, $options);
        if($returnType==2){
            $keys=array_keys($list);
            return $keyStr=implode(',', $keys);
        }else{
            return $list;
        }
    }

    public function get_color_list($gid,$pid){
        if($gid&&$pid){
            $fields='pc.product_id,pc.color_id,pc.skc_id,k.name,pc.id,pc.status,pgm.group_id';
            $where='  pc.product_id = "'.$pid.'" ';
            $tablename=' product_color pc left join product_group_member pgm   on pgm.product_id=pc.product_id AND pgm.color_id=pc.color_id left join keywords k on pc.color_id=k.id ';
            $sql    = "SELECT {$fields} FROM {$tablename} WHERE {$where} group by pgm.group_id ";
            echo $sql;
            $sth    = $this->dbh_slave->prepare($sql);
            $sth->execute();
            while($row = $sth->fetch()){
                $result[]   = $row;
            }
            return $result;

        }else{
            return false;
        }
    }
    
    public function get_group_color_list($gid,$pid){
        if($gid&&$pid){
            $fields='color_id';
            $where='  product_id = "'.$pid.'" AND group_id = "'.$gid.'" ';
            $tablename='  product_group_member   ';
            $sql    = "SELECT {$fields} FROM {$tablename} WHERE {$where}  ";
            $sth    = $this->dbh_slave->prepare($sql);
            $sth->execute();
            while($row = $sth->fetch()){
                $result[$row['color_id']]   = 1;
            }
            return $result;
    
        }else{
            return false;
        }
    }

    public function get_member_image_list ($group_id) {
        $options['limit']   = 100;
        $options['tablename'] = "product_group_member as pgm left join product as p on pgm.product_id=p.id";
        $options['fields']  =   "pgm.*,p.kuanhao";
        $condition[]    = "pgm.group_id={$group_id}";
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        return $list;
    }
}





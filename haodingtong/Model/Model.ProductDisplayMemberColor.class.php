<?php

class ProductDisplayMemberColor Extends BaseClass {
    public function __construct(){
        $this->setFactory('product_display_member_color');
        $this->keywords     = new Keywords;
    }

    public function create_color($display_id, $product_id, $keyword_id){
        return $this->create(array('display_id'=>$display_id, 'product_id'=>$product_id, 'keyword_id'=>$keyword_id))->insert(); 
    }

    public function remove_color($product_id, $keyword_id){
        $condition[]    = "product_id={$product_id}";
        $condition[]    = "keyword_id={$keyword_id}";
        $where  = implode(' AND ', $condition);
        $this->delete($where);
    }

    public function get_color_list($display_id, $product_id=0, $options=array()){
        $options = $options + array('limit'=>20);
        $condition[]    = "dc.display_id={$display_id}";
        $condition[]    = "pc.color_id>0";
        if($product_id) $condition[]    = "dc.product_id={$product_id}";
        $options['tablename']   = "product_display_member_color as dc left join product_color as pc on dc.product_id=pc.product_id AND dc.keyword_id=pc.color_id";
        $options['fields']      = "dc.*, pc.skc_id,pc.color_id,pc.color_code,pc.status";
        // $options['db_debug']    = true;
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        foreach($list as &$row){
            $row['name']    = $this->keywords->getName_File($row['keyword_id']);
        }
        return $list;
    }
    
    public function get_member_image_list ($display_id) {
        $options['limit']   = 100;
        $options['tablename'] = "product_display_member_color as pdmc left join product as p on pdmc.product_id=p.id";
        $options['fields']  =   "pdmc.*,pdmc.keyword_id as color_id,p.kuanhao";
        $condition[]    = "pdmc.display_id={$display_id}";
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        return $list;
    }

}





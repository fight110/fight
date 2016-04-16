<?php

class Control_group {
    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $limit      = $data->limit ? $data->limit : 6;
        $p          = $data->p;
        $ProductGroup           = new ProductGroup;
        $ProductGroupMember     = new ProductGroupMember;
        $cond           = array();
        $fields         = "*";

        $where      = implode(' AND ', $cond);
        $list       = $ProductGroup->find($where, array("limit"=>$limit, "page"=>$p, "order"=>$order, 'fields'=>$fields));
        foreach($list as &$row){
            $dp_list        = $ProductGroupMember->find("group_id={$row['id']}", array());
            $row['dp_list'] = Flight::listFetch($dp_list, 'product', 'product_id', 'id'); 
        }

        $result['list'] = $list;

        Flight::display("group/index.html", $result);
    }



}
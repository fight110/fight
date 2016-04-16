<?php

class Control_location {
    public static function Action_json($r){
        $Location   = new Location;
        $list       = $Location->getAllChildren(0);

        Flight::json($list);
    }
    
    public static function Action_get_classes_list ($r) {
        $data   = $r->query;
        $category_id    = $data->category_id;
    
        $options['fields']  = "classes_id";
        $options['group']   = "classes_id";
        $options['limit']   = 1000;
        $Product        = new Product;
        $where          = $category_id ? "category_id={$category_id}" : "1";
        $list           = $Product->find($where, $options);
        $result['list'] = $list;
    
        Flight::display("location/classes_list.html", $result);
    }
}
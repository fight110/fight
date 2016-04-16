<?php

class Control_search {
    public static function Action_index($r){
        $data   = $r->query;
        $q      = $data->q;
        if($q){
            $qt         = addslashes($q);
            $Product    = new Product;
            $where      = "bianhao='{$qt}'";
            $product    = $Product->findone($where);
            if($product['id']){
                $returl     = "http://ipad.haodingtong.com/index/detail/". $product['id'];
            }
        }
        if(!$returl){
            $returl     = "http://ipad.haodingtong.com";
        }
        Flight::redirect($returl);
    }
}
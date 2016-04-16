<?php

class Control_import {
    public static function Action_index($r){
        $data       = $r->query;
        $t          = $data->t ? $data->t : 'index';
        $template   = "import/{$t}.html";
        Flight::display($template, $result);
    }

    public static function Action_clear ($r) {
        $data       = $r->data;
        $k          = $data->k;
        $import     = new HDTImport;
        if($k == 1){
            $import->clear_orderlist();
            SESSION::message("订单数据初始化完成");
        }elseif($k == 2){
            $import->clear_all();
            SESSION::message("全部数据初始化完成");
        }elseif($k == 3){
            $import->clear_product();
            SESSION::message("商品数据初始化完成");
        }elseif($k == 4){
            $import->clear_user();
            SESSION::message("用户数据初始化完成");
        }elseif($k == 5){
            $import->clear_cache();
            SESSION::message("全部缓存清除完成");
        }else{
            SESSION::message("参数错误");
        }
        Flight::redirect($r->referrer);
    }

    public static function Action_data ($r) {
        $data       = $r->data;
        $k          = $data->k;
        $import     = new HDTImport;
        if($k) {
            $result = $import->run($k, array($r));
            if($result['error']){
                SESSION::message($result['message']);
            }
        }
        Flight::redirect($r->referrer);
    }

}
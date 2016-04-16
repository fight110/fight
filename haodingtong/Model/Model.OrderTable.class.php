<?php

class OrderTable {
    public function __construct($product_id, $orderlist){
        $this->product_id   = $product_id;
        $this->orderlist    = $orderlist;
        $this->template_user    = "orderlist/masterlistuser.table.html";
        $this->template_all     = "orderlist/masterlistall.table.html";
    }

    public function byHtml($by="user"){
        if($by == "all"){
            $result     = $this->byAll();
            $template   = $this->template_all;
        }else{
            $result     = $this->byUser();
            $template   = $this->template_user;
        }
        return Flight::fetch($template, $result);
    }

    public function byUser(){
        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $product_id     = $this->product_id;
        $orderlist      = $this->orderlist;

        $color_list     = $ProductColor->get_color_list($product_id);
        $size_list      = $ProductSize->get_size_list($product_id);
        
        $uhash      = array();
        $utotal     = 0;
        foreach($orderlist as $row){
            $user_id    = $row['user_id'];
            $color_id   = $row['product_color_id'];
            $size_id    = $row['product_size_id'];
            $uhash[$user_id]['name']    = $row['name'];
            $uhash[$user_id]['user_id'] = $row['user_id'];
            $uhash[$user_id][$color_id][$size_id] = $row;
            $uhash[$user_id][$color_id]['count'] += $row['num'];
            $utotal     += $row['num'];
        }

        foreach($uhash as $user_id => $udata){
            $name   = $udata['name'];
            foreach($color_list as $color){
                $color_id       = $color['color_id'];
                if($udata[$color_id]){
                    $tr     = array();
                    $tr['name']     = $name;
                    $tr['color']    = $color;
                    foreach($size_list as $size){
                        $size_id    = $size['size_id'];
                        $td         = $udata[$color_id][$size_id];
                        if(!$td)    $td = array();
                        $tr['td'][] = $td;
                        $tr['count']    += $td['num'];
                    }
                    $ulist[]    = $tr;
                    $utotal     += $tr['count'];
                }
            }
        }

        $result['list']    = $ulist;
        $result['total']   = $utotal;
        $result['size_list']    = $size_list;
        $result['color_list']   = $color_list;
        return $result;
    }

    public function byAll(){
        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $product_id     = $this->product_id;
        $orderlist      = $this->orderlist;

        $color_list     = $ProductColor->get_color_list($product_id);
        $size_list      = $ProductSize->get_size_list($product_id);
        $hash       = array();
        foreach($orderlist as $row){
            $color_id   = $row['product_color_id'];
            $size_id    = $row['product_size_id'];
            $hash["{$color_id}_{$size_id}"]['num'] += $row['num'];
        }
        $list   = array();
        $total  = 0;
        foreach($color_list as $color){
            $tr     = array();
            $tr['color']    = $color;
            $color_id       = $color['color_id'];
            foreach($size_list as $size){
                $size_id    = $size['size_id'];
                $key        = "{$color_id}_{$size_id}";
                $td         = array_key_exists($key, $hash)     ? $hash[$key] : array();
                $tr['td'][] = $td;
                $tr['count']    += $td['num'];
            }
            if($tr['count']){
                $list[] = $tr;
                $total += $tr['count'];
            }
        }
        $result['list']         = $list;
        $result['size_list']    = $size_list;
        $result['color_list']   = $color_list;
        $result['total']        = $total;
        return $result;
    }
}





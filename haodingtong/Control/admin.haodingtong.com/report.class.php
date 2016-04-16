<?php

class Control_report {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $data   = $r->query;
        $p      = $data->p;
        $submit = $data->submit;
        if(!$p) $p  = 1;
        $condition  = array();
        $options    = array();
        $fields     = array();
        $keys   = array('area1', 'area2', 'date_start', 'date_end', 'style_id', 'wave_id', 'category_id', 'series_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }
        $options['group']       = "o.user_id,o.product_id,o.product_color_id";
        $options['fields_more'] = "u.name,u.username,u.area1,u.area2," .
            "p.kuanhao,p.huohao,p.name as pname,p.style_id,p.category_id,p.wave_id,p.classes_id,p.series_id,p.price as pprice, p.price_purchase,p.designer,".
            "o.product_color_id,GROUP_CONCAT(o.product_size_id,':',o.num) as sizes";
        if($status  = $data->status){
            $result['status']   = $status;
            $options['status']  = $status;
        }

        $width      = 68 * 3;
        $keys = array("k_rownum","k_username","k_name","k_location","k_huohao","k_series","k_wave","k_category","k_pname","k_designer","k_price","k_style","k_price_purchase");
        foreach($keys as $key){
            $val    = $data->$key;
            if($val){
                $result[$key]   = $val;
                $width += 68;
                $n++;
            }
        }
        if(!$n){
            $keys   = array('k_name', 'k_pname');
            foreach($keys as $key){
                $result[$key]   = 'on';
                $width += 68;
                $n++;
            }
        }
        if($width <= 685){
            $width = 685;
        }
        $result['width']    = $width;

        if($n){
            $Factory    = new ProductsAttributeFactory('size');
            $size_list  = $Factory->getAllList();
            $OrderList  = new OrderList;
            $limit      = 10;
            if(!$submit){
                $options['page']    = $p;
                $options['limit']   = $limit;
            }
            $options['count']   = true;
            $list       = $OrderList->getOrderAnalysisList($condition, $options);
            $total      = $OrderList->get_count_total();
            $start      = ($p - 1) * $limit;
            $pagelist   = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
            foreach($list as &$r1){
                $size_hash  = array();
                $size_list1 = explode(',', $r1['sizes']);
                foreach($size_list1 as $size_string){
                    list($size_id, $num) = explode(':', $size_string);
                    $size_hash[$size_id]    = $num;
                }
                foreach($size_list as $size_row){
                    $id     = $size_row['keyword_id'];
                    $r1['size_list'][]   = array("size_id"=>$id, "num"=>$size_hash[$id]);
                }
            }
            if($result['k_location']){
                $location = new Location;
                $location_cache = new Cache(function($id) use($location){
                    return $location->getCurrent($id);
                });
                foreach($list as &$row){
                    $areaid     = $row['area2'] ? $row['area2'] : $row['area1'];
                    $row['location']    = $location_cache->get("location-{$areaid}", array($areaid));
                }
            }
            if($result['k_series'])     $list  = Flight::listFetch($list, 'keywords', 'series_id', 'id', '', 'series');
            if($result['k_wave'])       $list  = Flight::listFetch($list, 'keywords', 'wave_id', 'id', '', 'wave');
            if($result['k_category'])   $list  = Flight::listFetch($list, 'keywords', 'category_id', 'id', '', 'category');
            if($result['k_style'])      $list  = Flight::listFetch($list, 'keywords', 'style_id', 'id', '', 'style');
            $list  = Flight::listFetch($list, 'keywords', 'product_color_id', 'id', '', 'color');
            
            $result['list'] = $list;
            $result['size_list']    = $size_list;
            $result['pagelist']     = $pagelist;
        }


        if($submit){
            $Company    = new Company;
            $excel_name = $Company->name . date('YmdHis', time()) . '.xls';
            $Response   = Flight::response();
            $Response->header("Content-type","application/vnd.ms-excel");
            $Response->header("Content-Disposition", "attachment; filename={$excel_name}");
            Flight::display('report/excel.tpl', $result);
        }else{
            $Factory    = new ProductsAttributeFactory('style');
            $result['style_list']       = $Factory->getAllList();
            $Factory    = new ProductsAttributeFactory('series');
            $result['series_list']      = $Factory->getAllList();
            $Factory    = new ProductsAttributeFactory('wave');
            $result['wave_list']        = $Factory->getAllList();
            $Factory    = new ProductsAttributeFactory('category');
            $result['category_list']    = $Factory->getAllList();

            Flight::display('report/index.html', $result);
        }

    }


}
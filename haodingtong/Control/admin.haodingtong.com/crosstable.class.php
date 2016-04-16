<?php

class Control_crosstable {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $data           = $r->query;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 20;
        $condition      = array();
        $options        = array();
        $keys   = array('search', 'search_user', 'area1', 'area2', 'style_id', 'wave_id', 'category_id', 'series_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }

        $options['fields_more'] = "o.product_color_id,GROUP_CONCAT(o.product_size_id,':',o.num) as sizes";
        $options['group']       = "o.product_id,o.product_color_id";
        $options['order']       = "p.bianhao";

        $Factory    = new ProductsAttributeFactory('size');
        $result['size_list']        = $Factory->getAllList();
        $result['size_count']       = count($result['size_list']);

        $OrderList  = new OrderList;
        $options['page']    = $p;
        $options['limit']   = $limit;
        $options['count']   = true;
        $list0      = $OrderList->getOrderProductList($condition, $options);
        $total      = $OrderList->get_count_total();
        $start      = ($p - 1) * $limit;
        for($i = 0; $i < $limit && $i < $total; $i++){
            $row    = $list0[$i];
            $sizes  = explode(',', $row['sizes']);
            $hash   = array();
            foreach($sizes as $size){
                $l  = explode(':', $size);
                $size_id    = $l[0];
                $size_num   = $l[1];
                $hash[$size_id] += $size_num;
            }
            foreach($result['size_list'] as $size_row){
                $size_id    = $size_row['keyword_id'];
                $row['size_list'][] = array('id'=>$size_id, 'num'=>$hash[$size_id]);
            }
            $list[] = $row;
        }
        $list       = Flight::listFetch($list, 'keywords', 'product_color_id', 'id', '', 'color');
        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();

        Flight::display('crosstable/index.html', $result);
    }

}
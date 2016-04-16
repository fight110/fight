<?php

class Control_order_analysis {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $t              = $r->query->t;
        if(!$t){
            $t          = "series";
        }
        $OrderList      = new OrderList;
        $Keywords       = new Keywords;
        $Location       = new Location;
        $data           = $r->query;
        $condition      = array();
        $options        = array();
        $options['limit']   = $data->limit  ? $data->limit  : 10;
        $options['page']    = $data->p      ? $data->p      : 1;
        // $options['db_debug']    = true;
        $keys   = array('area1', 'area2', 'date_start', 'date_end');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }
        if($data->area1){
            $area1              = $Location->getCurrent($data->area1);
            $result['area1name']    = $area1['name'];   
        }
        if($data->area2){
            $area2              = $Location->getCurrent($data->area2);
            $result['area2name']    = $area2['name'];
        }
        switch ($t) {
            case 'size'     :
            case 'color'    : 
                $options['group']   = "o.product_{$t}_id";
                $options['fields_more'] = "o.product_{$t}_id as keyword_id";
                break;
            case 'color_group'  :
                $options['group']   = "g.group_id";
                $options['fields_more'] = "g.group_id as keyword_id";
                $options['tables_more'] = "left join products_attr_group as g on o.product_color_id=g.attr_id";
                break;
            case 'product' :
                $options['group']   = "o.product_id";
                $options['fields_more'] = "p.name, p.bianhao, p.kuanhao";
                break;
            default :
                $options['group']   = "p.{$t}_id";
                $options['fields_more'] = "p.{$t}_id as keyword_id";
        }
        $options['count']       = true;
        $list           = $OrderList->getOrderAnalysisList($condition, $options);
        $total          = $OrderList->get_count_total();
        $count          = $OrderList->getOrderAnalysisCount($condition, array());
        $pnum           = $count['pnum'];
        $num            = $count['num'];
        $price          = $count['price'];
        foreach($list as &$row){
            $row['keywords']['name']    = $Keywords->getName_File($row['keyword_id']);
            if($pnum){
                $row['percent_pnum']    = sprintf("%.2f%%", $row['pnum']/$pnum * 100);
            }
            if($num){
                $row['percent_num']     = sprintf("%.2f%%", $row['num']/$num * 100);
            }
            if($price){
                $row['percent_price']   = sprintf("%.2f%%", $row['price']/$price * 100);
            }
        }

        $result['list'] = $list;
        $result['t']    = $t;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total);

        Flight::display('order_analysis/index.html', $result);
    }


}
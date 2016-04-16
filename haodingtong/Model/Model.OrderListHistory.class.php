<?php

class OrderListHistory Extends BaseClass {
    public function __construct(){
        $this->setFactory('orderlisthistory');
    }

    public function createOrderListHistory($name, $series, $category, $classes, $wave, $kuanhao, $price_band, $price, $color, $num, $nannvzhuan, $season){
        $User       = new User;
        $u          = $User->findone("name='".addslashes($name)."'");
        if($user_id = $u['id']){
            $series_id      = Keywords::cache_get_id($series);
            $category_id    = Keywords::cache_get_id($category);
            $classes_id     = Keywords::cache_get_id($classes);
            $wave_id        = Keywords::cache_get_id($wave);
            $price_band_id  = Keywords::cache_get_id($price_band);
            $color_id       = Keywords::cache_get_id($color);
            $nannvzhuan_id  = Keywords::cache_get_id($nannvzhuan);
            $season_id      = Keywords::cache_get_id($season);
            return $this->create(array(
                "user_id"       => $user_id, 
                "series_id"     => $series_id, 
                "category_id"   => $category_id, 
                "classes_id"    => $classes_id, 
                "wave_id"       => $wave_id, 
                "kuanhao"       => $kuanhao,
                "price_band_id"     => $price_band_id,
                "price"         => $price,
                "color_id"      => $color_id,
                "num"           => $num,
                "nannvzhuan_id" => $nannvzhuan_id,
                "season_id"     => $season_id
            ))->insert();
        }
        return false;
    }


    public function getAnalysis($params=array(), $options=array()){
        $options['fields']      = "*,sum(num) as exp_num, sum(num*price) as exp_price,count(DISTINCT kuanhao) as exp_pnum";
        $options['limit']       = 1000;
        // $options['db_debug']    = true;
        $user_id    = $params['user_id'];
        $fliter_uid = $params['fliter_uid'];
        $brand_id   = $params['brand_id'];
        $season_id  = $params['season_id'];
        $nannvzhuan_id= $params['nannvzhuan_id'];
        $condition  = array();
        if($user_id)    $condition[]    = "user_id={$user_id}";
        if($fliter_uid)    $condition[]    = "user_id={$fliter_uid}";
        if($season_id)    $condition[]    = "season_id={$season_id}";
        if($nannvzhuan_id)    $condition[]    = "nannvzhuan_id={$nannvzhuan_id}";
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        return $list;
    }

    public function getOrderListHistoryAnalysis($user_id, $type=0, $mid=0){
        switch ($type) {
            case 2  : 
                $columns    = array('wave_id', 'color_id');
                $attr       = "wave";
                $orderColumns   = array('p.wave_id', "o.product_color_id");
                break;
            case 1  :
                $columns    = array('wave_id', 'classes_id');
                $attr       = "wave";
                $orderColumns   = array('p.wave_id', "p.classes_id");
                break;
            default : 
                $columns    = array('wave_id', 'category_id');
                $attr       = "wave";
                $orderColumns   = array('p.wave_id', "p.category_id");
        }
        $count              = array();
        $column0            = $columns[0];
        $column1            = $columns[1];
        $Factory            = new ProductsAttributeFactory($attr);
        $attr_hash          = $Factory->getAllHash();

        $options['limit']   = 10000;
        $options['group']   = $column0;
        $options['fields']  = "{$column0},COUNT(DISTINCT kuanhao) as pnum,SUM(num) as num, SUM(num*price) / SUM(num) as price";
        $condition[]        = "user_id={$user_id}";
        $where              = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        foreach($list as $row){
            $count[$row[$column0]]   = $row;
        }

        $string_columns     = implode(',', $columns);
        $options['group']   = $string_columns;
        $options['fields']  = "{$string_columns},COUNT(DISTINCT kuanhao) as pnum,SUM(num) as num, SUM(num*price) / SUM(num) as price";
        // $options['db_debug']    = true;
        $list   = $this->find($where, $options);
        

        $orderCondition = array();
        $orderOptions   = array();
        $orderHash      = array();
        $orderCount     = array();
        $string_columns_order   = implode(',', $orderColumns);
        $orderOptions['fields'] = "{$string_columns_order}, COUNT(DISTINCT o.product_id) as pnum, SUM(o.num) as num, SUM(o.num*p.price) / SUM(o.num) as price";
        $orderOptions['group']  = $string_columns_order;
        if($mid){
            $orderCondition['master_uid']  = $mid;
            // $orderCondition['user_id']  = $user_id;
        }else{
            $orderCondition['user_id']  = $user_id;
        }
        // $orderOptions['db_debug']   = true;
        $OrderList  = new OrderList;
        $orderList  = $OrderList->getOrderAnalysisList($orderCondition, $orderOptions);
        foreach($orderList as $row){
            $orderHash[$row[$column0]][$row[$column1]]  = $row;
            $orderCount[$row[$column0]]['num']          += $row['num'];
            $orderCount[$row[$column0]]['pnum']         += $row['pnum'];
            $orderCount[$row[$column0]]['price_all']    += $row['price'] * $row['num'];
            $orderCountNum  = $orderCount[$row[$column0]]['num'];
            if($orderCountNum){
                $orderCount[$row[$column0]]['price']        = $orderCount[$row[$column0]]['price_all'] / $orderCountNum;
            }
        }

        $newhash    = array();
        foreach($list as $row){
            if($row['num']){
                $string     = $row[$column0] . '_'. $row[$column1];
                $newhash[$string]['history']    = $row;
            }
        }
        foreach($orderList as $row){
            if($row['num']){
                $string     = $row[$column0] . '_'. $row[$column1];
                $newhash[$string]['order']      = $row;
            }
        }

        $list   = array_values($newhash);
        $last       = 0;
        $rowspan    = 0;
        $index      = 0;
        $first      = 0;
        $total      = count($list);

        usort($list, function($a, $b) use ($column0, $attr_hash){
            $a_key      = $a['history'][$column0] ? $a['history'][$column0] : $a['order'][$column0];
            $b_key      = $b['history'][$column0] ? $b['history'][$column0] : $b['order'][$column0];
            $a_rank     = $attr_hash[$a_key]['rank'];
            $b_rank     = $attr_hash[$b_key]['rank'];
            if($a_rank < 1) return true;
            if($b_rank < 1) return false;
            return $a_rank > $b_rank;
        });

        foreach($list as &$item){
            $history    = &$item['history'];
            if($history['num']){
                $count_data     = $count[$history[$column0]];
                $history['per_pnum']   = sprintf("%0.2f%%", $history['pnum'] / $count_data['pnum'] * 100);
                $history['per_num']    = sprintf("%0.2f%%", $history['num'] / $count_data['num'] * 100);
                $item['key0']   = $history[$column0];
                $item['key1']   = $history[$column1];
            }
            $order      = &$item['order'];
            if($order['num']){
                $item['key0']   = $order[$column0];
                $item['key1']   = $order[$column1];
            }
            
            if($list[$last]['key0'] != $item['key0']){
                if($last){
                    $list[$first]['rowspan']    = $rowspan;
                    $list[$last]['count']       = $count[$list[$first]['key0']];
                    $list[$last]['count']['order']  = $orderCount[$list[$last]['key0']];
                    $rowspan = 0;
                }
                $first = $index;
            }

            $rowspan++;

            if($total == $index + 1){
                $list[$first]['rowspan']    = $rowspan;
                $list[$last + 1]['count']   = $count_data;
                $list[$last + 1]['count']['order']  = $orderCount[$list[$last]['key0']];
            }

            $last   = $index;
            $index++;
        }
        return $list;
    }
    
}





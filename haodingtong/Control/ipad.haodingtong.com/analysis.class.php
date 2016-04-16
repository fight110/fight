<?php

class Control_analysis {
    public static function Action_list($r){
        Flight::validateUserHasLogin();

        $data           = $r->query;
        $t              = $data->t      ? $data->t      : "category";
        $limit          = $data->limit  ? $data->limit  : 500;
        $p              = $data->p      ? $data->p      : 1;
        $zongdai        = $data->zongdai;
        $fliter_uid     = $data->fliter_uid;
        $category_id    = $data->category_id;
        $season_id      = $data->season_id;
        $wave_id        = $data->wave_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $brand_id       = $data->brand_id;
        $price_band_id  = $data->price_band_id;
        $status_val     = $data->status_val;
        $show_all       = is_numeric($data->show_all) ? $data->show_all : 1;
        $area1          = $data->area1;
        $area2          = $data->area2;
        $OrderList      = new OrderList;
        $User           = new User;
        $Keywords       = new Keywords;
        $Product        = new Product;
        $data           = $r->query;
        $condition      = array();
        $options        = array();
        // $options['page']    = $p;
        // $options['limit']   = $limit;
        // $options['db_debug']    = true;

        switch ($t) {
            case 'size'     :
                $options['group']   = "ps.{$t}_id";
                $options['fields_more'] = "ps.{$t}_id as keyword_id";
                $options['tables_more'] = "left join product_size as ps on p.id=ps.product_id and o.product_size_id=ps.size_id";
                // $ProductSize    = new ProductSize;
                // $product_analysis_info  = $ProductSize->find("product_id in (SELECT id FROM product where status=1)", array("key"=>"size_id", "group"=>"size_id", "fields"=>"size_id,COUNT(*) as num", "limit"=>1000));
                break;
            case 'color'    :
                $options['group']   = "pc.{$t}_id";
                $options['fields_more'] = "pc.{$t}_id as keyword_id";
                $options['tables_more'] = "left join product_color as pc on p.id=pc.product_id and o.product_color_id=pc.color_id";
                // $ProductColor   = new ProductColor;
                // $product_analysis_info  = $ProductColor->find("product_id in (SELECT id FROM product where status=1)", array("key"=>"color_id", "group"=>"color_id", "fields"=>"color_id,COUNT(*) as num"));
                break;
            case 'color_group'  :
                $options['group']   = "g.group_id";
                $options['fields_more'] = "g.group_id as keyword_id";
                $options['tables_more'] = "left join products_attr_group as g on o.product_color_id=g.attr_id";
                break;
            case 'product' :
                $options['group']   = "o.product_id";
                $options['fields_more'] = "p.name, p.bianhao, p.kuanhao, p.id";
                break;
            case 'user'     :
                $options['group']   = "o.user_id";
                $options['fields_more'] = "u.name,o.user_id";
                break;
            default :
                $options['group']   = "p.{$t}_id";
                $options['fields_more'] = "p.{$t}_id as keyword_id";
        }

        if($category_id){
                $condition['category_id']   = $category_id;
                $pcond[]        = "category_id={$category_id}";
        }
        if($series_id){
                $condition['series_id']   = $series_id;
                $pcond[]        = "series_id={$series_id}";
        }
        if($wave_id){
                $condition['wave_id']   = $wave_id;
                $pcond[]        = "wave_id={$wave_id}";
        }
        if($price_band_id){
                $condition['price_band_id']   = $price_band_id;
                $pcond[]        = "price_band_id={$price_band_id}";
        }
        if($season_id){
                $condition['season_id']   = $season_id;
                $pcond[]        = "season_id={$season_id}";
        }
        if($brand_id){
            $condition['brand_id']      = $brand_id;
            $pcond[]    = "brand_id={$brand_id}";
        }
        if($nannvzhuan_id){
            $condition['nannvzhuan_id'] = $nannvzhuan_id;
            $pcond[]    = "nannvzhuan_id={$nannvzhuan_id}";
        }
        if($area1)  $condition['area1'] = $area1;
        if($area2)  $condition['area2'] = $area2;
        if($status_val) $options['status_val'] = $status_val;

        if($User->type == 3){
            if($User->area1) $condition['area1']  = $User->area1;
            if($User->area2) $condition['area2']  = $User->area2;
        }else{
            $condition['user_id']   = $User->id;
            $options['show_all']    = $show_all     ? true : false;
        }
        if($fliter_uid){
            $condition['fliter_uid']    = $fliter_uid;
        }

        if($zongdai){
            $list           = $OrderList->getDealer2OrderList($condition, $options);
            $options['group'] = "";
            $orderCountInfo = $OrderList->getDealer2OrderCount($condition, $options);
        }else{
		if($User->username == "0"){
			$CACHE_USER0_KEY = "ANALYSIS_0_" . md5(serialize($condition) . serialize($options));
			$cache = new Cache(function() use ($condition, $options, $OrderList){
            			$list           = $OrderList->getOrderAnalysisList($condition, $options);
            			$options['group'] = "";
            			$orderCountInfo = $OrderList->getOrderAnalysisCount($condition, $options);
				return array($list, $orderCountInfo);
			}, 60);
			list($list, $orderCountInfo) = $cache->get($CACHE_USER0_KEY);
		}else{
            $list           = $OrderList->getOrderAnalysisList($condition, $options);
            $options['group'] = "";
            $orderCountInfo = $OrderList->getOrderAnalysisCount($condition, $options);
		}
        }
        $pnum           = $orderCountInfo['pnum'];
        $num            = $orderCountInfo['num'];
        $price          = $orderCountInfo['price'];
        $discount_price = $orderCountInfo['discount_price'];
        $skc            = $orderCountInfo['skc'];
        $sku            = $orderCountInfo['sku'];

        $list   = ProductsAttributeFactory::fetch($list, $t, 'keyword_id', 'order_key');
        usort($list, function($a, $b){
            $rank_a     = $a['order_key']['rank'];
            $rank_b     = $b['order_key']['rank'];
            if($rank_a<1) return true;
            if($rank_b<1) return false;
            return $rank_a > $rank_b;
        });

        $pcond[]        = $status_val ? "status={$status_val}" : "status=1";
        $pwhere         = implode(" AND ", $pcond);
        $product_analysis_info  = $Product->find($pwhere, array("key"=>"{$t}_id", "group"=>"{$t}_id", "fields"=>"{$t}_id,COUNT(*) as num", 'limit'=>10000));
        if($product_analysis_info){
            $result['total_product_all_num']    = $Product->getCount($pwhere);
        }

        // if($t == 'size'){
        //     $list   = ProductsAttributeFactory::fetch($list, 'size', 'keyword_id', 'products_size');
        //     usort($list, function($a, $b){
        //         return $a['products_size']['rank']  > $b['products_size']['rank'];
        //     });
        // }else{
        //     usort($list, function($a, $b){
        //         return $a['price']  < $b['price'];
        //     });
        // }

        $start  = ($p - 1) * $limit;
        $list   = array_slice($list, $start, $limit);

        if($User->type == 1){
            $Rule           = new Rule;
            $Budget         = new Budget;
            $BudgetCount    = new BudgetCount;
            $RuleHash   = $Rule->getUserRule($User->id, $t, array('key'=>'keyword_id'));
            $BudgetHash = $Budget->find("user_id={$User->id} AND field='{$t}'", array('limit'=>100, 'key'=>'keyword_id'));
            $result['budget']   = $BudgetCount->getBudget($User->id);
            $isBudgeted     = count($BudgetHash) ? true : false;
        }

        foreach($list as &$row){
            if($pnum){
                $row['percent_pnum']    = sprintf("%.1f%%", $row['pnum']/$pnum * 100);
            }
            if($num){
                $row['percent_num']     = sprintf("%.1f%%", $row['num']/$num * 100);
            }
            if($price){
                $row['percent_price']   = sprintf("%.1f%%", $row['price']/$price * 100);
            }
            if($skc){
                $row['percent_skc']     = sprintf("%.1f%%", $row['skc']/$skc * 100);
            }
            if($t != "product" && $t != "user"){
                $row['name']    = $Keywords->getName_File($row['keyword_id']);
            }
            if($product_analysis_info){
                $row['product_all_num'] = $product_analysis_info[$row['keyword_id']]['num'];
            }
            if($RuleHash){
                $row['percent_rule']    = $RuleHash[$row['keyword_id']]['percent'];
            }
            if($isBudgeted){
                $row['budget']  = $BudgetHash[$row['keyword_id']]['percent'];
                $row['data_b']  = $BudgetHash[$row['keyword_id']]['percent'];
            }
        }



        // if($p == 1){
        //     $len    = count($list);
        //     while($len < 15){
        //         $list[]     = array();
        //         $len++;
        //     }
        // }


        $result['list']     = $list;
        $result['t']        = $t;
        $result['start']    = $start;
        $result['sku']      = $sku;
        $result['skc']      = $skc;
        $result['num']      = $num;
        $result['pnum']     = $pnum;
        $result['price']    = $price;

        $result['sx_area1'] = $area1;
        $result['sx_area2'] = $area2;
        $result['sx_fliter'] = $fliter_uid;
        $result['sx_category'] = $category_id;
        $result['sx_brand'] = $brand_id;
        $result['sx_season'] = $season_id;
        $result['sx_nannvzhuan'] = $nannvzhuan_id;
        $result['discount_price']    = $discount_price;

        $tmpl = $User->type == 1 ? 'analysis/list.html' : 'analysis/list.other.html';
        Flight::display($tmpl, $result);
    }

    private static function _summary_fetch_rank($list, $group, $n){
        switch ($group) {
            case 'p.series_id'  :
                $list   = ProductsAttributeFactory::fetch($list, 'series', "group{$n}_id", "group{$n}_rank");
                break;
            case 'p.wave_id'    :
                $list   = ProductsAttributeFactory::fetch($list, 'wave', "group{$n}_id", "group{$n}_rank");
                break;
            case 'p.classes_id' :
                $list   = ProductsAttributeFactory::fetch($list, 'classes', "group{$n}_id", "group{$n}_rank");
                break;
            case 'p.category_id':
                $list   = ProductsAttributeFactory::fetch($list, 'category', "group{$n}_id", "group{$n}_rank");
                break;
            case 'p.theme_id'   :
                $list   = ProductsAttributeFactory::fetch($list, 'theme', "group{$n}_id", "group{$n}_rank");
                break;
            case 'p.price_band_id'  :
                $list   = ProductsAttributeFactory::fetch($list, 'price_band', "group{$n}_id", "group{$n}_rank");
                break;
            case 'o.product_size_id':
                $list   = ProductsAttributeFactory::fetch($list, 'size', "group{$n}_id", "group{$n}_rank");
                break;
            case 'o.product_color_id':
                $list   = ProductsAttributeFactory::fetch($list, 'color', "group{$n}_id", "group{$n}_rank");
                break;
            case 'g.group_id'   :
                $list   = ProductsAttributeFactory::fetch($list, 'color_group', "group{$n}_id", "group{$n}_rank");
                break;
            case 'p.silhouette_id'  :
                $list   = ProductsAttributeFactory::fetch($list, 'silhouette', "group{$n}_id", "group{$n}_rank");
                break;
            case 'p.collar_id'  :
                $list   = ProductsAttributeFactory::fetch($list, 'collar', "group{$n}_id", "group{$n}_rank");
                break;
            case 'p.origin_id'  :
                $list   = ProductsAttributeFactory::fetch($list, 'origin', "group{$n}_id", "group{$n}_rank");
                break;
            case 'p.designer_id'    :
                $list   = ProductsAttributeFactory::fetch($list, 'designer', "group{$n}_id", "group{$n}_rank");
                break;
            default : 1;
        }
        return $list;
    }

    public static function Action_summary($r){
        Flight::validateUserHasLogin();

        $User       = new User;
        $Keywords   = new Keywords;
        $data       = $r->query;
        $search_user    = $data->search_user;
        $fliter_uid     = $data->fliter_uid;
        $area1          = $data->area1;
        $area2          = $data->area2;
        $brand_id       = $data->brand_id;
        $season_id      = $data->season_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $category_id    = $data->category_id;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit  ? $data->limit  : 500;
        $group1     = isset($data->group1)     ? $data->group1     : 1;
        $group2     = isset($data->group2)     ? $data->group2     : 2;

        $group_list[]   = array('name'=>'系列', 'column'=>'p.series_id');
        $group_list[]   = array('name'=>'波段', 'column'=>'p.wave_id');
        $group_list[]   = array('name'=>'大类', 'column'=>'p.category_id');
        $group_list[]   = array('name'=>'小类', 'column'=>'p.classes_id');
        $group_list[]   = array('name'=>'尺码', 'column'=>'o.product_size_id');
        // $group_list[]   = array('name'=>'色系', 'column'=>'g.group_id');
        $group_list[]   = array('name'=>'价格带', 'column'=>'p.price_band_id');
        $group_list[]   = array('name'=>'主题', 'column'=>'p.theme_id');
        $group_list[]   = array('name'=>'品牌', 'column'=>'p.brand_id');
        $group_list[]   = array('name'=>'性别', 'column'=>'p.nannvzhuan_id');
        $group_list[]   = array('name'=>'上下装', 'column'=>'p.sxz_id');
        $group_list[]   = array('name'=>'款式', 'column'=>'p.style_id');
        $group_list[]   = array('name'=>'季节', 'column'=>'p.season_id');
        $group_list[]   = array('name'=>'单色', 'column'=>'o.product_color_id');
        $group_list[]   = array('name'=>'中类', 'column'=>'p.medium_id');
        // if($User->type == 3){
        //     $group_list[]   = array('name'=>'廓形', 'column'=>'p.silhouette_id');
        //     $group_list[]   = array('name'=>'领型', 'column'=>'p.collar_id');
        //     $group_list[]   = array('name'=>'来源', 'column'=>'p.origin_id');
        // }


        $result['group1']   = $group1;
        $result['group2']   = $group2;
        $result['group_list']   = $group_list;

        // if($group1 == 1 && $group2 == 2){
        //     $Product    = new Product;
        //     $analysis_option['limit']   = 10000;
        //     $analysis_option['group']   = "wave_id,category_id";
        //     $analysis_option['fields']  = "COUNT(*) as num,CONCAT(wave_id, '_', category_id) as mykey";
        //     $analysis_option['key']     = "mykey";
        //     $product_analysis_info  = $Product->find("status=1", $analysis_option);
        // }

        $group1     = $group_list[$group1]  ? $group_list[$group1]['column']    : $group_list[1]['column'];
        $group2     = $group_list[$group2]  ? $group_list[$group2]['column']    : $group_list[2]['column'];

        $condition  = array();
        $options    = array();
        switch ($User->type) {
            case 2  :
                $condition['master_uid']    = $User->id;
                if($search_user){
                    $condition['search_user']  = $search_user;
                }
                break;
            case 3  :
                if($search_user){
                    $condition['search_user']  = $search_user;
                }
                if($User->username!="0"){
                    $condition['ad_id']  = $User->id;
                }
                break;
            default :
                $condition['user_id']   = $User->id;
        }
        if($fliter_uid) $condition['fliter_uid']    = $fliter_uid;
        if($area1)      $condition['area1']         = $area1;
        if($area2)      $condition['area2']         = $area2;
        if($brand_id)   $condition['brand_id']      = $brand_id;
        if($season_id)  $condition['season_id']     = $season_id;
        if($nannvzhuan_id)  $condition['nannvzhuan_id']     = $nannvzhuan_id;
        if($category_id)    $condition['category_id']       = $category_id;

        $options['page']        = $p;
        $options['limit']       = $limit;

        $options['fields_more'] = "{$group1} as group1_id, {$group2} as group2_id";
        $options['group']       = "{$group1}, {$group2}";
        // $options['order']       = "{$group1}, {$group2}";
        // $options['db_debug']    = true;
        if($group1 == "g.group_id" || $group2 == "g.group_id"){
            $options['tables_more'] = "left join products_attr_group as g on o.product_color_id=g.attr_id";
        }
        //$options['db_debug']=true;
        $options['order']=" {$group1} , num desc ";

        $OrderList  = new OrderList;
        $list       = $OrderList->getOrderAnalysisList($condition, $options);
        $list       = STATIC::_summary_fetch_rank($list, $group1, 1);
        $list       = STATIC::_summary_fetch_rank($list, $group2, 2);
        /*usort($list, function($a, $b){
            $rank_a = $a['group1_rank']['rank'];
            $rank_b = $b['group1_rank']['rank'];
            if($rank_a == $rank_b)
                return $a['group2_rank']['rank']    > $b['group2_rank']['rank'];
            return $rank_a > $rank_b;
        });*/
        $current_group1_id  = null;
        $current_key        = 0;

        $total_num          = 0;
        $total_pnum         = 0;
        $total_sku          = 0;
        $total_skc          = 0;
        $total_price        = 0;
        //print_r($list);

        $groupTotal = array();
        $groupTotalPrice = array();

        foreach($list as $key => $val){
            if($val['group1_id'] != $current_group1_id){
                $current_group1_id  = $val['group1_id'];
                $current_key        = $key;
                if($key > 0){
                    $list[$key - 1]['group']    = $total_group;
                    $groupTotal[$list[$key - 1]['group1_id']] = $total_group['num'];
                    $groupTotalPrice[$list[$key - 1]['group1_id']] = $total_group['price'];
                }
                $total_group      = array();
                $list[$current_key]['rowspan']  = 1;
            }
            $list[$current_key]['rowspan']++;
            $list[$key]['group1_name'] = $Keywords->getName_File($val['group1_id']);
            $list[$key]['group2_name'] = $Keywords->getName_File($val['group2_id']);
            if($product_analysis_info){//$group1 == 2 && $group2 == 3 &&
                $list[$key]['total_num'] = $product_analysis_info[$val['group1_id'] . "_" . $val['group2_id']]['num'];
            }

            $total_num      += $val['num'];
            $total_pnum     += $val['pnum'];
            $total_sku      += $val['sku'];
            $total_skc      += $val['skc'];
            $total_price    += $val['price'];

            $total_group['num']       += $val['num'];
            $total_group['pnum']      += $val['pnum'];
            $total_group['sku']       += $val['sku'];
            $total_group['skc']       += $val['skc'];
            $total_group['price']     += $val['price'];
        }

        //print_r($list);
        if($count = count($list)){
            $list[$count - 1]['group']    = $total_group;
            $groupTotal[$val['group1_id']] = $total_group['num'];
            $groupTotalPrice[$val['group1_id']] = $total_group['price'];
        }
       // print_r($list);
        foreach($list as $key => $val){
            if($total_num)      $list[$key]['percent_num']    = sprintf("%.1f%%", $val['num'] / $total_num * 100);
            if($total_price)    $list[$key]['percent_price']  = sprintf("%.1f%%", $val['price'] / $total_price * 100);
            if($val['group']){
                $list[$key]['group']['percent_num']   = sprintf("%.1f%%", $val['group']['num'] / $total_num * 100);
                $list[$key]['group']['percent_price'] = sprintf("%.1f%%", $val['group']['price'] / $total_price * 100);
            }
            $list[$key]['group_percent_num']    = sprintf("%.1f%%", $val['num'] / $groupTotal[$val['group1_id']] * 100);
            $list[$key]['group_percent_price']  = sprintf("%.1f%%", $val['price'] / $groupTotalPrice[$val['group1_id']] * 100);
        }
        //print_r($list);
        $result['list']         = $list;
        $result['total_num']    = $total_num;
        $result['total_pnum']   = $total_pnum;
        $result['total_sku']    = $total_sku;
        $result['total_skc']    = $total_skc;
        $result['total_price']  = $total_price;


        $start      = ($p - 1) * $limit;
        $result['start']    = $start;
        $result['type']     = $User->type;
        $result['search_user'] = $search_user;
        if($data->download && $fliter_uid){
            $user   = new User($fliter_uid);
            new Analysissummaryexport($user->name, array('波段', '大类', '订款量', 'SKU', '订量', '金额', '订数占比'), $result);
        }else{
            //经理增加二维导出判断
            // $tmpl = $User->type == 1 ? 'analysis/summary.html' : 'analysis/summary_ad.html';
            $tmpl = 'analysis/summary.html';
            Flight::display($tmpl, $result);
            //Flight::display("analysis/summary.html", $result);
        }
    }

    public static function Action_explist($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = 1000;
        $area1      = $data->area1;
        $area2      = $data->area2;
        $fliter_uid = $data->fliter_uid;
        $category_id = $data->category_id;
        $brand_id   = $data->brand_id;
        $season_id  = $data->season_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $is_lock    = $data->is_lock;
        $is_finished = $data->is_finished;
        $order = $data->order;
        $options['page']    = $p;
        $options['limit']   = $limit;
        $result['pageinfo'] = array('index'=>$p,'limit'=>$limit);
        // $options['db_debug']    = true;

        $condition  = array();
        $User       = new User;
        if($User->type == 3 && $User->username != "0"){
            $condition['area1'] = $User->area1 ? $User->area1 : $area1;
            $condition['area2'] = $User->area2 ? $User->area2 : $area2;
            // var_dump($condition);
        }else{
            if($area1)  $condition['area1'] = $area1;
            if($area2)  $condition['area2'] = $area2;
        }
        if($fliter_uid)     $condition['fliter_uid'] = $fliter_uid;
        if($category_id) 	  $condition['category_id'] = $category_id;
        if($brand_id)       $condition['brand_id'] = $brand_id;
        if($season_id)      $condition['season_id'] = $season_id;
        if($nannvzhuan_id)  $condition['nannvzhuan_id'] = $nannvzhuan_id;
        if(isset($is_lock)&&is_numeric($is_lock))        $condition['is_lock']   = $is_lock;
        if($is_finished)		$condition['is_finished'] = $is_finished;
        $list       = $User->get_exp_list($condition, $options);
        $exp_price_all  = 0;
        foreach($list as &$row){
            $row['exp_num_percent']     = $row['exp_num']   ? sprintf('%.2f%%', $row['num'] / $row['exp_num'] * 100)  : '-';
            $row['exp_price_percent']   = $row['exp_price'] ? sprintf('%.2f%%', $row['discount_price'] / $row['exp_price'] * 100)  : '-';
            $exp_price_all += $row['exp_price'];
        }
        if($p <= 1){
                $info           = $User->get_exp_list_count($condition);
                $info['exp']['exp_price']  = $exp_price_all;
                $info['exp_num_percent']        = $info['exp']['exp_num'] ? sprintf("%.2f%%", $info['ord']['num'] / $info['exp']['exp_num'] * 100) : '-';
                $info['exp_price_percent']      = $info['exp']['exp_price'] ? sprintf("%.2f%%", $info['ord']['discount_price'] / $info['exp']['exp_price'] * 100) : '-';
                $result['info'] = $info;
        }
        if(!$order){
            $company = new Company;
            $order  = $company->ad_order;
        }
        if($order){
            usort($list, function($a, $b) use ($order){
                return $a[$order] < $b[$order];
            });
        }
       
        $result['list'] = $list;
        $company = new Company;
        $result['company']  = $company->getData();
        Flight::display("ad/explist.html", $result);
    }
    
    public static function Action_explist_print($r){
        Flight::validateUserHasLogin();
        $data       = $r->query;
        $is_type    = $data->is_type;
        if($is_type&&$is_type!="isUser"){
            static::Action_explist_print2($r);
        }else{
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit;
        $area1      = $data->area1;
        $area2      = $data->area2;
        $zongdai    = $data->fliter_zd;
        $fliter_uid = $data->fliter_uid;
        $category_id = $data->category_id;
        $brand_id   = $data->brand_id;
        $season_id  = $data->season_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $property   = $data->property;
        $is_lock    = $data->is_lock;
        $is_finished = $data->is_finished;
        $order_status = $data->order_status;
        $order = $data->order;
        $sname = $data->sname;
        $company = new Company;
        $companyData = $company->getData();
        if(!$order){            
            $order  = $company->ad_order;
        }
        $options['page']    = $p;
        $options['limit']   = $limit;
        $result['pageinfo'] = array('index'=>$p,'limit'=>$limit);
    
        $condition  = array();
        $User       = new User;
        $result['user'] = $User->getAttribute();
        if($User->type == 3 && $User->username != "0"){
            $condition['ad_id'] = $User->id;
        }elseif($User->type==2){
            $condition['mid'] = $User->id;
            $condition['master_uid'] = $User->id;
        }
        if($area1)  $condition['area1'] = $area1;
        if($area2)  $condition['area2'] = $area2;
        if($zongdai)$condition['zongdai']=$zongdai;
        if($fliter_uid)     $condition['fliter_uid'] = $fliter_uid;
        if($category_id) 	  $condition['category_id'] = $category_id;
        if($brand_id)       $condition['brand_id'] = $brand_id;
        if($season_id)      $condition['season_id'] = $season_id;
        if($nannvzhuan_id)  $condition['nannvzhuan_id'] = $nannvzhuan_id;
        if(isset($is_lock)&&is_numeric($is_lock))        $condition['is_lock']   = $is_lock;
        if(is_numeric($property))      $condition['property']  = $property;
        if($is_finished)		$condition['is_finished'] = $is_finished;
        if($sname) $condition['search_user'] = $sname;
        
        if($companyData['check_order']){
            $condition['order_status'] = $order_status ? $order_status : 'all';
        }
        
        $options['table_more'] = ' left join user_slave us on u.id = us.user_slave_id ';
        $options['fields_more'] = ' us.user_id as mid';
        $options['order'] = $order;
        // $options['db_debug']    = true;
        $list       = $User->get_exp_list_print($condition, $options);
    
        foreach($list as &$row){
            $row['userMaster'] = $User->getUserInfoById($row['mid']);
            $row['price_percent'] = (round($row['price_percent'],4)*100).'%';
            $row['num_percent'] = (round(($row['num']/$row['exp_num']),4)*100).'%';
        }
        if($p <= 1){
            $info           = $User->get_exp_list_count2($condition);
            $result['info'] = $info;
            $result['info']['price_percent'] = (round(($info['ord']['discount_price']/$info['exp']['exp_price']),4)*100).'%';
            $result['info']['num_percent'] = (round(($info['ord']['num']/$info['exp']['exp_num']),4)*100).'%';
        }
        $result['list'] = $list;
        $result['company']  = $companyData;    
        if($User->type==2){
            Flight::display("dealer2/explist_print.html", $result);
        }else{
            Flight::display("ad/explist_print.html", $result);
        }
        }         
    }
    
    public static function Action_explist_print2($r){
        $data       = $r->query;
        $uname      = $data->uname;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit;
        $is_type    = $data->is_type;
        $area1      = $data->area1;
        $area2      = $data->area2;
        $zongdai    = $data->fliter_zd;
        $is_lock    = $data->is_lock;
        $property   = $data->property;
        $order      = $data->order;
        
        $list       = array();
        $options    = array();
        $condition  = array();
        $options['limit']  = $limit;
        $options['page']   = $p;
        $result['pageinfo'] = array('index'=>$p,'limit'=>$limit);
        if($area1)      $condition[] = "u.area1={$area1}";
        if($area2)      $condition[] = "u.area2={$area2}";
        if($property)   $condition[] = "u.property={$property}";
        $company = new Company;
        $companyData = $company->getData();
        if(!$order) $order  = $company->ad_order;
        switch ($order){
            case 'num': $options['order'] = ' num desc'; break;
            case 'price': $options['order'] = 'price desc'; break;
            case 'discount_price' : $options['order'] = 'discount_price desc'; break;
            case 'price_perecent' : $options['order'] = 'price_percent desc'; break;
            default: $options['order'] = 'u.id';
        }
        if(!isset($uname)){
            if($is_type=='isZongdai'){
                //$options['tablename']   = "orderlist as o left join user as u on u.id=o.zd_user_id";
                $options['tablename']   = "user_slave as us left join user as u on us.user_id=u.id left join orderlist as o on us.user_slave_id=o.user_id left join product as p on o.product_id=p.id";
                $options['fields']      = "u.name, u.username, u.id,u.type, u.exp_price as exp_price, u.exp_num as exp_num, sum( o.num ) AS num, (sum(o.num)/u.exp_num) as num_percent, sum( o.amount ) AS price, sum( o.zd_discount_amount ) AS discount_price,(sum(o.zd_discount_amount))/u.exp_num as price_percent";
                $options['group']       = "us.user_id";
                //$condition[]            = "u.type=2";
                if($zongdai)    $condition[] = "u.id={$zongdai}";
            }elseif($is_type=='isArea'){
                //$options['tablename']   = "orderlist as o left join user as u on u.id=o.zd_user_id left join location as l on u.area1=l.id";
                $options['tablename']   = "location as l left join user as u on u.area1=l.id left join orderlist as o on u.id=o.user_id left join product as p on o.product_id=p.id";
                $options['fields']      = "l.name as areaname,u.area1,u.name, u.username, u.id, sum( u.exp_price ) as exp_price, sum( u.exp_num ) as exp_num, sum( o.num ) AS num, (sum(o.num)/sum(u.exp_num)) as num_percent, sum( o.amount ) AS price, sum( o.zd_discount_amount ) AS discount_price,(sum(o.zd_discount_amount))/sum(u.exp_num) as price_percent";
                $options['group']       = "u.area1";
                $condition[]            = "l.pid=0";
                $condition[]            = "u.type=1";
            }elseif($is_type=='isProperty'){
                //$options['tablename']   = "orderlist as o left join user as u on u.id=o.zd_user_id";
                $options['tablename']   = "user as u left join orderlist as o on u.id=o.user_id left join product as p on o.product_id=p.id";
                $options['fields']      = "u.property,u.name, u.username, u.id, sum(u.exp_price) as exp_price, sum(u.exp_num) as exp_num, sum( o.num ) AS num, (sum(o.num)/sum(u.exp_num)) as num_percent, sum( o.amount ) AS price, sum( o.zd_discount_amount ) AS discount_price,(sum(o.zd_discount_amount)/sum(u.exp_price)) as price_percent";
                $options['group']       = "u.property";
                $condition[]            = "u.type=1";
            }
        }
        //$condition[]            = "p.status<>0";
        $where      = implode(' AND ', $condition);
        $orderlist  = new OrderList;
        //$options['db_debug']    =   true;
        $list       = $orderlist->find($where,$options);
        
        if($is_type=='isZongdai'&&$p <=1){
            $options['tablename']   =  "user as u left join orderlist as o on u.id=o.user_id ";
            $options['fields']      =  "sum(o.num) as num,sum(o.amount) as price,sum(o.discount_amount) as discount_price,u.id,u.name,u.username,u.exp_num,u.exp_price";
            $options['group']       = "u.id";
            $options['order']       = " price desc ";
            //$condition              = array();
            $condition[]            = " u.id in (SELECT u.id from user as u where u.id not in (SELECT us.user_slave_id from user_slave as us) and u.type=1) ";
            $where                  = implode(' AND ', $condition);
            $list_more              = $orderlist->find($where,$options);
            foreach ($list_more as $row){
                $list[]             = $row;
            }
        }
        
        if($p <=1){
            /* if($is_type=='isZongdai')   $options['tablename']   = "user as u left join orderlist as o on u.id=o.zd_user_id";
            $options['fields']      =   "sum( o.num ) as num,sum(o.amount) as price,sum( u.exp_num ) as exp_num,sum( u.exp_price ) as exp_price,sum(o.zd_discount_amount) as discount_price";
            $options['group']       = "";
            $result['info']         = $orderlist->findone($where,$options); */
            $result['info']         = array();
            $result['info']['num']              = 0;
            $result['info']['price']            = 0;
            $result['info']['exp_price']        =0;
            $result['info']['exp_num']          =0;
            $result['info']['discount_price']   =0;
            
            foreach ($list as $row){
                $result['info']['num']              += $row['num'];
                $result['info']['price']            += $row['price'];
                $result['info']['exp_price']        += $row['exp_price'];
                $result['info']['exp_num']          += $row['exp_num'];
                $result['info']['discount_price']   += $row['discount_price'];
            }
            if($result['info']['exp_price']){
                $result['info']['price_percent']=(round(($result['info']['discount_price']/$result['info']['exp_price']),4)*100).'%';
            }
            if($result['info']['exp_num']){
                $result['info']['num_percent']=(round(($result['info']['num']/$result['info']['exp_num']),4)*100).'%';
            }
        }
        foreach ($list as &$row){
            if($row['price_percent']){
                $row['price_percent']   = (round($row['price_percent'],4)*100).'%';
            }
            if($row['num_percent']){
                $row['num_percent']     = (round(($row['num']/$row['exp_num']),4)*100).'%';
            }
        }   
        $result['is_type']  =$is_type;
        $result['list']     = $list;
        $result['company']  =$companyData; 
        Flight::display("ad/explist_print2.html", $result);
    }
    
    public static function Action_analysis_hpgc ($r) {
        Flight::validateUserHasLogin();

        $data           = $r->query;
        $t              = $data->t      ? $data->t      : "category";
        $limit          = $data->limit  ? $data->limit  : 500;
        $p              = $data->p      ? $data->p      : 1;
        $orderby        = $data->orderby;
        $brand_id       = $data->brand_id;
        $season_id      = $data->season_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;

        switch ($t) {
            case 'color'    :
                $tid    = "pc.color_id";
                $options['group']   = "{$tid}";
                break;
            default :
                $tid    = "p.{$t}_id";
                $options['group']   = "{$tid}";
        }
        // $options['db_debug']    = true;
        if($brand_id){
            $condition[]    = "brand_id={$brand_id}";
            $cond['brand_id']   = $brand_id;
        }
        if($season_id){
            $condition[]    = "season_id={$season_id}";
            $cond['season_id']   = $season_id;
        }
        if($nannvzhuan_id){
            $condition[]    = "nannvzhuan_id={$nannvzhuan_id}";
            $cond['nannvzhuan_id']   = $nannvzhuan_id;
        }
        $condition[]    = "p.status=1";
        $options['fields']  = "count(DISTINCT pc.product_id) as pnum, count(DISTINCT pc.product_id,pc.color_id) as skc, p.*,{$tid} as tid";
        $options['tablename']   = "product as p left join product_color as pc on p.id=pc.product_id";
        $options['limit']   = $limit;
        $Product        = new Product;
        $where          = implode(" AND ", $condition);
        $list           = $Product->find($where, $options);

        $options['group']   = "";
        $productcount   = $Product->findone($where, $options);

        $options        = array();
        $User           = new User;
        $OrderList      = new OrderList;
        switch ($User->type) {
            case 2 :
                $cond['master_uid']    = $User->id;
                $cond['fliter_uid']    = $data->fliter_uid;
                break;
            case 3 :
                $cond['fliter_uid']    = $data->fliter_uid;
               
                $cond['area1']         = $data->area1;
                
                $cond['area2']         = $data->area2;
                
                if($User->username!='0'){
                    $cond['ad_id']         = $User->id;
                }

                break;
            default :
                $cond['fliter_uid']    = $User->id;
        }
        $options['key'] = "tid";
        $options['group']   = "{$tid}";
        $options['fields_more'] = "{$tid} as tid";
        if($t == "color"){
            $options['tables_more'] = "left join product_color as pc on p.id=pc.product_id and o.product_color_id=pc.color_id";
        }
        //$options['db_debug']    = true;
        $orderinfo      = $OrderList->getOrderAnalysisList($cond, $options);
        $options['group']   = "";
        $options['key']     = "";
        // $options['db_debug']    = true;
        $ordercount     = $OrderList->getOrderAnalysisCount($cond, $options);

        foreach ($list as &$row) {
            $tid            = $row['tid'];
            $ord            = $orderinfo[$tid];
            $row['percent_pnum']    = $row['pnum'] ? sprintf("%.1f%%", $ord['pnum'] / $row['pnum'] * 100) : '-';
            $row['skc_width']       = $row['skc']  ? sprintf("%.1f%%", $ord['skc'] / $row['skc'] * 100) : '-';
            if($ord['skc']){
                $row['skc_depth']   = sprintf("%d", $ord['num'] / $ord['skc']);
            }
            $row['order']   = $ord;
            if($ordercount['num']){
                $row['percent_num'] = sprintf("%.1f%%", $ord['num'] / $ordercount['num'] * 100);
            }
        }

        switch ($orderby) {
            case 1  :
                $callback   = function($a){ return $a['pnum']; };
                $orderAsc   = false;
                break;
            case 2  :
                $callback   = function($a){ return $a['order']['pnum']; };
                $orderAsc   = false;
                break;
            case 3  :
                $callback   = function($a){ return $a['percent_pnum']>>0; };
                $orderAsc   = false;
                break;
            case 4  :
                $callback   = function($a){ return $a['skc']; };
                $orderAsc   = false;
                break;
            case 5  :
                $callback   = function($a){ return $a['order']['skc']; };
                $orderAsc   = false;
                break;
            case 6  :
                $callback   = function($a){ return $a['skc_width']>>0; };
                $orderAsc   = false;
                break;
            case 7  :
                $callback   = function($a){ return $a['skc_depth']; };
                $orderAsc   = false;
                break;
            case 8  :
                $callback   = function($a){ return $a['order']['num']; };
                $orderAsc   = false;
                break;
            default :
                $list   = ProductsAttributeFactory::fetch($list, $t, 'tid', 'order_key');
                $callback   = function($a){ return $a['order_key']['rank']; };
                $orderAsc   = true;
        }
        usort($list, function($a, $b) use ($callback, $orderAsc){
            $rank_a     = $callback($a);
            $rank_b     = $callback($b);
            if($rank_a<1) return true;
            if($rank_b<1) return false;
            return $orderAsc ? $rank_a > $rank_b : $rank_a < $rank_b;
        });

        $result['list'] = $list;
        $result['ordercount']   = $ordercount;
        $result['productcount'] = $productcount;
        $result['percent_pnum'] = $productcount['pnum'] ? sprintf("%.1f%%", $ordercount['pnum'] / $productcount['pnum'] * 100) : '';
        $result['skc_width']    = $productcount['skc']  ? sprintf("%.1f%%", $ordercount['skc'] / $productcount['skc'] * 100) : '';
        if($ordercount['skc']){
            $result['skc_depth']   = sprintf("%d", $ordercount['num'] / $ordercount['skc']);
        }

        Flight::display('analysis/hpgc.html', $result);
    }


    public static function Action_exp_complete ($r) {
        Flight::validateUserHasLogin();

        $data           = $r->query;
        $t              = $data->t      ? $data->t      : "category";
        $limit          = $data->limit  ? $data->limit  : 500;
        $p              = $data->p      ? $data->p      : 1;
        $orderby        = $data->orderby;
        $brand_id       = $data->brand_id;
        $season_id      = $data->season_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;

        switch ($t) {
            case 'color'    :
                $tid    = "pc.color_id";
                $options['group']   = "{$tid}";
                break;
            default :
                $tid    = "p.{$t}_id";
                $options['group']   = "{$tid}";
        }
        // $options['db_debug']    = true;
        if($brand_id){
            $condition[]    = "p.brand_id={$brand_id}";
            $cond['brand_id']   = $brand_id;
        }
        if($season_id){
            $condition[]    = "p.season_id={$season_id}";
            $cond['season_id']   = $season_id;
        }
        if($nannvzhuan_id){
            $condition[]    = "p.nannvzhuan_id={$nannvzhuan_id}";
            $cond['nannvzhuan_id']   = $nannvzhuan_id;
        }
        $condition[]    = "p.status=1";
        $options['fields']  = "count(DISTINCT p.id) as pnum, count(DISTINCT pc.id) as skc, ROUND(AVG(p.price)) as average_price, p.*,{$tid} as tid";
        $options['tablename']   = "product as p left join product_color as pc on p.id=pc.product_id";
        $options['limit']   = $limit;
        $Product        = new Product;
        $where          = implode(" AND ", $condition);
        $list           = $Product->find($where, $options);

        $options['group']   = "";
        $productcount   = $Product->findone($where, $options);

        $options        = array();
        $User           = new User;
        $OrderList      = new OrderList;
        $UserExpComplete    = new UserExpComplete;
        switch ($User->type) {
            case 2 :
                $cond['master_uid']    = $User->id;
                $cond['fliter_uid']    = $data->fliter_uid;
                break;
            case 3 :
                $cond['fliter_uid']    = $data->fliter_uid;
                $cond['area1']         = $data->area1;
                $cond['area2']         = $data->area2;
                if($User->username!='0'){
                    $cond['ad_id']         = $User->id;
                }
                break;
            default :
                $cond['fliter_uid']    = $User->id;
        }
        $exp_complete_info  = $UserExpComplete->get_exp_complete_list(array("user_id"=>$User->id, "field"=>$t), array("key"=>"keyword_id"));
        $options['key'] = "tid";
        $options['group']   = "{$tid}";
        $options['fields_more'] = "{$tid} as tid";
        if($t == "color"){
            $options['tables_more'] = "left join product_color as pc on p.id=pc.product_id and o.product_color_id=pc.color_id";
        }
        // $options['db_debug']    = true;
        $orderinfo      = $OrderList->getOrderAnalysisList($cond, $options);
        $options['group']   = "";
        $options['key']     = "";
        // $options['db_debug']    = true;
        $ordercount     = $OrderList->getOrderAnalysisCount($cond, $options);

        if(!count($exp_complete_info)){
            $OrderListHistory   = new OrderListHistory;
            $his    = $OrderListHistory->getAnalysis($cond, array('group'=>$t . "_id", 'key'=>$t . "_id"));
            $exp_complete_info  = $his;
        }
        foreach ($list as &$row) {
            $tid            = $row['tid'];
            $ord            = $orderinfo[$tid];
            $exp            = $exp_complete_info[$tid];

            $row['order']   = $ord;
            $row['average_price_order']   = $ord['num']   ? sprintf("%d", $ord['price'] / $ord['num']) : '-';
            $row['exp']     = $exp;
            $row['exp_pnum_percent'] = $exp['exp_pnum'] ? sprintf("%.1f%%", $ord['pnum'] / $exp['exp_pnum'] * 100) : '-';
            $row['exp_skc_percent'] = $exp['exp_skc'] ? sprintf("%.1f%%", $ord['skc'] / $exp['exp_skc'] * 100) : '-';
            $row['exp_num_percent'] = $exp['exp_num'] ? sprintf("%.1f%%", $ord['num'] / $exp['exp_num'] * 100) : '-';
            $row['exp_price_percent'] = $exp['exp_price'] ? sprintf("%.1f%%", $ord['price'] / $exp['exp_price'] * 100) : '-';
            $result['exp_num']  += $exp['exp_num'];
            $result['exp_pnum']  += $exp['exp_pnum'];
            $result['exp_skc']  += $exp['exp_skc'];
            $result['exp_price']  += $exp['exp_price'];
        }

        switch ($orderby) {
            case 1  :
                $callback   = function($a){ return $a['pnum']; };
                $orderAsc   = false;
                break;
            case 2  :
                $callback   = function($a){ return $a['order']['pnum']; };
                $orderAsc   = false;
                break;
            case 3  :
                $callback   = function($a){ return $a['exp']['exp_num']; };
                $orderAsc   = false;
                break;
            case 4  :
                $callback   = function($a){ return $a['order']['num']; };
                $orderAsc   = false;
                break;
            case 5  :
                $callback   = function($a){ return $a['exp_num_percent']>>0; };
                $orderAsc   = false;
                break;
            case 6  :
                $callback   = function($a){ return $a['exp']['exp_price']; };
                $orderAsc   = false;
                break;
            case 7  :
                $callback   = function($a){ return $a['order']['price']; };
                $orderAsc   = false;
                break;
            case 8  :
                $callback   = function($a){ return $a['exp_price_percent']>>0; };
                $orderAsc   = false;
                break;
            case 9  :
                $callback   = function($a){ return $a['average_price']; };
                $orderAsc   = false;
                break;
            case 10  :
                $callback   = function($a){ return $a['average_price_order']; };
                $orderAsc   = false;
                break;
            default :
                $list   = ProductsAttributeFactory::fetch($list, $t, 'tid', 'order_key');
                $callback   = function($a){ return $a['order_key']['rank']; };
                $orderAsc   = true;
        }
        usort($list, function($a, $b) use ($callback, $orderAsc){
            $rank_a     = $callback($a);
            $rank_b     = $callback($b);
            if($rank_a<1) return true;
            if($rank_b<1) return false;
            return $orderAsc ? $rank_a > $rank_b : $rank_a < $rank_b;
        });

        $result['list'] = $list;
        $result['ordercount']   = $ordercount;
        $result['productcount'] = $productcount;
        $result['exp_pnum_percent']      = $result['exp_pnum'] ? sprintf("%.1f%%", $result['ordercount']['pnum'] / $result['exp_pnum'] * 100) : '-';
        $result['exp_skc_percent']      = $result['exp_skc'] ? sprintf("%.1f%%", $result['ordercount']['skc'] / $result['exp_skc'] * 100) : '-';
        $result['exp_num_percent']      = $result['exp_num'] ? sprintf("%.1f%%", $result['ordercount']['num'] / $result['exp_num'] * 100) : '-';
        $result['exp_price_percent']    = $result['exp_price'] ? sprintf("%.1f%%", $result['ordercount']['price'] / $result['exp_price'] * 100) : '-';
        $pinfo  = $Product->findone("status=1", array("fields"=>"ROUND(sum(price) / count(*)) as average_price"));
        $result['average_price']    = $pinfo['average_price'];
        $result['average_price_order']  = $ordercount['num'] ? round($ordercount['price'] / $ordercount['num']) : '-';

        Flight::display('analysis/exp_complete.html', $result);
    }

		public static function Action_export($r){
        Flight::validateUserHasLogin();

        $data           = $r->query;
        $limit          = $data->limit  ? $data->limit  : 500;
        $p              = $data->p      ? $data->p      : 1;
        $zongdai        = $data->zongdai;
        $area1          = $data->sx_area1;
        $area2          = $data->sx_area2;
        $fliter_uid     = $data->sx_fliter;
        $season_id      = $data->sx_season;
        $nannvzhuan_id  = $data->sx_nannvzhuan;
        $brand_id       = $data->sx_brand;
        $category_id    = $data->sx_category;
        $wave_id        = $data->wave_id;
        $price_band_id  = $data->price_band_id;
        $status_val     = $data->status_val;
        $show_all       = is_numeric($data->show_all) ? $data->show_all : 1;
        $OrderList      = new OrderList;
        $User           = new User;
        $Keywords       = new Keywords;
        $Product        = new Product;
        $data           = $r->query;
        $condition      = array();
        $options        = array();
		$keys   = array('category', 'classes', 'wave', 'color', 'size', 'price_band', 'series', 'nannvzhuan', 'sxz', 'brand', 'silhouette', 'collar', 'origin', 'designer');
		$alllist = array();
		foreach($keys as $key){
				$t = $key;

        switch ($t) {
            case 'size'     :
                $options['group']   = "ps.{$t}_id";
                $options['fields_more'] = "ps.{$t}_id as keyword_id";
                $options['tables_more'] = "left join product_size as ps on p.id=ps.product_id and o.product_size_id=ps.size_id";
                break;
            case 'color'    :
                $options['group']   = "pc.{$t}_id";
                $options['fields_more'] = "pc.{$t}_id as keyword_id";
                $options['tables_more'] = "left join product_color as pc on p.id=pc.product_id and o.product_color_id=pc.color_id";
                break;
            case 'color_group'  :
                $options['group']   = "g.group_id";
                $options['fields_more'] = "g.group_id as keyword_id";
                $options['tables_more'] = "left join products_attr_group as g on o.product_color_id=g.attr_id";
                break;
            case 'product' :
                $options['group']   = "o.product_id";
                $options['fields_more'] = "p.name, p.bianhao, p.kuanhao, p.id";
                break;
            case 'user'     :
                $options['group']   = "o.user_id";
                $options['fields_more'] = "u.name,o.user_id";
                break;
            default :
                $options['group']   = "p.{$t}_id";
                $options['fields_more'] = "p.{$t}_id as keyword_id";
        }

        if($category_id){
                $condition['category_id']   = $category_id;
                $pcond[]        = "category_id={$category_id}";
        }
        if($series_id){
                $condition['series_id']   = $series_id;
                $pcond[]        = "series_id={$series_id}";
        }
        if($wave_id){
                $condition['wave_id']   = $wave_id;
                $pcond[]        = "wave_id={$wave_id}";
        }
        if($price_band_id){
                $condition['price_band_id']   = $price_band_id;
                $pcond[]        = "price_band_id={$price_band_id}";
        }
        if($season_id){
                $condition['season_id']   = $season_id;
                $pcond[]        = "season_id={$season_id}";
        }
        if($brand_id){
            $condition['brand_id']      = $brand_id;
            $pcond[]    = "brand_id={$brand_id}";
        }
        if($nannvzhuan_id){
            $condition['nannvzhuan_id'] = $nannvzhuan_id;
            $pcond[]    = "nannvzhuan_id={$nannvzhuan_id}";
        }
        if($area1)  $condition['area1'] = $area1;
        if($area2)  $condition['area2'] = $area2;
        if($status_val) $options['status_val'] = $status_val;

        if($User->type == 3){
            // $condition['ad_area1']  = $User->area1;
            // $condition['ad_area2']  = $User->area2;
        }else{
            $condition['user_id']   = $User->id;
            $options['show_all']    = $show_all     ? true : false;
        }
        if($fliter_uid){
            $condition['fliter_uid']    = $fliter_uid;
        }
        if($zongdai){
            $list           = $OrderList->getDealer2OrderList($condition, $options);
            $options['group'] = "";
            $orderCountInfo = $OrderList->getDealer2OrderCount($condition, $options);
        }else{
						if($User->username == "0"){
								$CACHE_USER0_KEY = "ANALYSIS_0_" . md5(serialize($condition) . serialize($options));
								$cache = new Cache(function() use ($condition, $options, $OrderList){
            			$list           = $OrderList->getOrderAnalysisList($condition, $options);
            			$options['group'] = "";
            			$orderCountInfo = $OrderList->getOrderAnalysisCount($condition, $options);
									return array($list, $orderCountInfo);
								}, 60);
								list($list, $orderCountInfo) = $cache->get($CACHE_USER0_KEY);
						}else{
            		$list           = $OrderList->getOrderAnalysisList($condition, $options);
            		$options['group'] = "";
            		$orderCountInfo = $OrderList->getOrderAnalysisCount($condition, $options);
						}
        }
        $pnum           = $orderCountInfo['pnum'];
        $num            = $orderCountInfo['num'];
        $price          = $orderCountInfo['price'];
        $discount_price = $orderCountInfo['discount_price'];
        $skc            = $orderCountInfo['skc'];
        $sku            = $orderCountInfo['sku'];

        $list   = ProductsAttributeFactory::fetch($list, $t, 'keyword_id', 'order_key');
        usort($list, function($a, $b){
            $rank_a     = $a['order_key']['rank'];
            $rank_b     = $b['order_key']['rank'];
            if($rank_a<1) return true;
            if($rank_b<1) return false;
            return $rank_a > $rank_b;
        });

        $pcond[]        = $status_val ? "status={$status_val}" : "status=1";
        $pwhere         = implode(" AND ", $pcond);
        $product_analysis_info  = $Product->find($pwhere, array("key"=>"{$t}_id", "group"=>"{$t}_id", "fields"=>"{$t}_id,COUNT(*) as num", 'limit'=>10000));
        if($product_analysis_info){
            $result['total_product_all_num']    = $Product->getCount($pwhere);
        }

        // if($t == 'size'){
        //     $list   = ProductsAttributeFactory::fetch($list, 'size', 'keyword_id', 'products_size');
        //     usort($list, function($a, $b){
        //         return $a['products_size']['rank']  > $b['products_size']['rank'];
        //     });
        // }else{
        //     usort($list, function($a, $b){
        //         return $a['price']  < $b['price'];
        //     });
        // }

        $start  = ($p - 1) * $limit;
        $list   = array_slice($list, $start, $limit);

        if($User->type == 1){
            $Rule           = new Rule;
            $Budget         = new Budget;
            $BudgetCount    = new BudgetCount;
            $RuleHash   = $Rule->getUserRule($User->id, $t, array('key'=>'keyword_id'));
            $BudgetHash = $Budget->find("user_id={$User->id} AND field='{$t}'", array('limit'=>100, 'key'=>'keyword_id'));
            $result['budget']   = $BudgetCount->getBudget($User->id);
            $isBudgeted     = count($BudgetHash) ? true : false;
        }

        foreach($list as &$row){
            if($pnum){
                $row['percent_pnum']    = sprintf("%.1f%%", $row['pnum']/$pnum * 100);
            }
            if($num){
                $row['percent_num']     = sprintf("%.1f%%", $row['num']/$num * 100);
            }
            if($price){
                $row['percent_price']   = sprintf("%.1f%%", $row['price']/$price * 100);
            }
            if($skc){
                $row['percent_skc']     = sprintf("%.1f%%", $row['skc']/$skc * 100);
            }
            if($t != "product" && $t != "user"){
                $row['name']    = $Keywords->getName_File($row['keyword_id']);
            }
            if($product_analysis_info){
                $row['product_all_num'] = $product_analysis_info[$row['keyword_id']]['num'];
            }
            if($RuleHash){
                $row['percent_rule']    = $RuleHash[$row['keyword_id']]['percent'];
            }
            if($isBudgeted){
                $row['budget']  = $BudgetHash[$row['keyword_id']]['percent'];
                $row['data_b']  = $BudgetHash[$row['keyword_id']]['percent'];
            }
        }

        $alllist[$t] = $list;
		}

				$excel_name     = sprintf("%analysis", 2014);
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array());

        $keys   = array('category', 'classes', 'wave', 'color', 'size', 'price_band', 'series', 'nannvzhuan', 'sxz', 'brand');
        $keys_title = array('category'=>'大类分析', 'classes'=>'小类分析','wave'=>'波段分析','color'=>'单色分析','size'=>'尺码分析','price_band'=>'价格带分析','series'=>'系列分析','nannvzhuan'=>'性别分析','sxz'=>'上下装分析','brand'=>'品牌分析');
        foreach($keys as $key){
        	$key_title = $keys_title[$key];
        	$ExcelWriter->row(array('','','',$key_title));
					$titles = array("名称", "开发款量", "已订款量", "已订数量", "订量占比", "SKC", "金额", "金额占比");
					$ExcelWriter->row($titles);
					$singlelist = $alllist[$key];
					foreach($singlelist as $kk => $vv){
						$data2 = array();
						$data2[] = $vv['name'];
						$data2[] = $vv['product_all_num'];
						$data2[] = $vv['pnum'];
						$data2[] = $vv['num'];
						$data2[] = $vv['percent_num'];
						$data2[] = $vv['skc'];
						$data2[] = $vv['price'];
						$data2[] = $vv['percent_price'];
						$ExcelWriter->row($data2);
						//$ExcelWriter->row($vv['name'],$vv['product_all_num'],$vv['pnum'],$vv['num'],$vv['percent_num'],$vv['skc'],$vv['price'],$vv['percent_price']);
					}
					$ExcelWriter->row(array("", ""));
        	$ExcelWriter->row(array("", ""));
				}
    }

    public static function Action_list_all ($r) {
        Flight::validateUserHasLogin();
        $data           = $r->query;
        $t              = $data->t      ? $data->t      : "category";
        $limit          = $data->limit  ? $data->limit  : 500;
        $p              = $data->p      ? $data->p      : 1;
        $orderby        = $data->orderby;
        $brand_id       = $data->brand_id;
        $season_id      = $data->season_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $category_id    = $data->category_id;
        $Company        = new Company;
        $user_guide     = $Company->user_guide;
        switch ($t) {
            case 'color'    :
                $tid    = "pc.color_id";
                $options['group']   = "{$tid}";
                break; 
            case 'size'    :
                $tid    = "ps.{$t}_id";
                $options['group']   = "{$tid}";
                //$options['fields_more'] = "ps.{$t}_id as keyword_id";
                break;
            default :
                $tid    = "p.{$t}_id";
                $options['group']   = "{$tid}";
        }
        //$options['db_debug']    = true;
        if($brand_id){
            $condition[]    = "brand_id={$brand_id}";
            $cond['brand_id']   = $brand_id;
        }
        if($season_id){
            $condition[]    = "season_id={$season_id}";
            $cond['season_id']   = $season_id;
        }
        if($nannvzhuan_id){
            $condition[]    = "nannvzhuan_id={$nannvzhuan_id}";
            $cond['nannvzhuan_id']   = $nannvzhuan_id;
        }
        if($category_id){
            $condition[]    = "category_id={$category_id}";
            $cond['category_id']   = $category_id;
        }
        $condition[]    = "p.status=1 and pc.status=1";
        $options['fields']  = "count(DISTINCT pc.product_id) as pnum, count(DISTINCT pc.product_id,pc.color_id) as skc, p.*,{$tid} as tid";
        if($t == "size"){
            $options['tablename']   = "product as p left join product_color as pc on p.id=pc.product_id left join product_size as ps on p.id=ps.product_id";
        }else{
            $options['tablename']   = "product as p left join product_color as pc on p.id=pc.product_id";
        }
        
        $User           = new User;
        if($user_guide && $User->type ==1){
            $options['fields']      = $options['fields'] . " ,sum(ug.num) as user_guide";
            $options['tablename']   = $options['tablename'] . " left join user_guide as ug on p.id=ug.product_id and pc.color_id=ug.product_color_id and ug.user_id={$User->id}";
            $result['company']['user_guide']    = $user_guide;
        }
        $options['limit']   = $limit;
        $Product        = new Product;
        $where          = implode(" AND ", $condition);
        //$options['db_debug']=true;
        $list           = $Product->find($where, $options);

        $options['group']   = "";
        $productcount   = $Product->findone($where, $options);

        $options        = array();
        $OrderList      = new OrderList;
        switch ($User->type) {
            case 2 :
                $cond['master_uid']    = $User->id;
                $cond['fliter_uid']    = $data->fliter_uid;
                break;
            case 3 :
                $cond['fliter_uid']    = $data->fliter_uid;
                
                $cond['area1']         = $data->area1;
                
                $cond['area2']         = $data->area2;
                
                if($User->username!='0'){
                    $cond['ad_id']         = $User->id;
                }
                
                break;
            default :
                $cond['fliter_uid']    = $User->id;
        }
        $options['key'] = "tid";
        $options['group']   = "{$tid}";
        $options['fields_more'] = "{$tid} as tid";
        if($t == "color"){
            $options['tables_more'] = "left join product_color as pc on p.id=pc.product_id and o.product_color_id=pc.color_id";
        }elseif($t == "size"){
            $options['tables_more'] = "left join product_size as ps on p.id=ps.product_id and o.product_size_id=ps.size_id";
        }
        //$options['db_debug']    = true;
        $orderinfo      = $OrderList->getOrderAnalysisList($cond, $options);
        //print_r($orderinfo);
        $options['group']   = "";
        $options['key']     = "";
        //$options['db_debug']    = true;
        $ordercount         = $OrderList->getOrderAnalysisCount($cond, $options);
        $total_user_guide   = 0;
        //print_r($ordercount);
        foreach ($list as &$row) {
            $tid            = $row['tid'];
            $ord            = $orderinfo[$tid];
            // $row['percent_pnum']    = $row['pnum'] ? sprintf("%.1f%%", $ord['pnum'] / $row['pnum'] * 100) : '-';
            $row['percent_pnum']    = $row['pnum'] ? sprintf("%.1f%%", $ord['pnum'] / $ordercount['pnum'] * 100) : '-';
            $row['skc_width']       = $row['skc']  ? sprintf("%.1f%%", $ord['skc'] / $row['skc'] * 100) : '-';
            if($ord['skc']){
                $row['skc_depth']   = sprintf("%.2f", $ord['num'] / $ord['skc']);
            }
            $row['percent_skc'] = sprintf("%.1f%%", $ord['skc'] / $ordercount['skc'] * 100);
            $row['difference_num']  = $row['pnum'] - $ord['pnum'];
            $row['difference_user_guide'] = $ord['num']-$row['user_guide'];
            $row['order']   = $ord;
            if($ordercount['num']){
                $row['percent_num'] = sprintf("%.1f%%", $ord['num'] / $ordercount['num'] * 100);
            }
            if($ordercount['price']){
                $row['percent_price'] = sprintf("%.1f%%", $ord['price'] / $ordercount['price'] * 100);
            }
            $total_user_guide += $row['user_guide'];
        }

        switch ($orderby) {
            case 1  :
                $callback   = function($a){ return $a['pnum']; };
                $orderAsc   = false;
                break;
            case 2  :
                $callback   = function($a){ return $a['order']['pnum']; };
                $orderAsc   = false;
                break;
            case 3  :
                $callback   = function($a){ return $a['percent_pnum']>>0; };
                $orderAsc   = false;
                break;
            case 4  :
                $callback   = function($a){ return $a['skc']; };
                $orderAsc   = false;
                break;
            case 5  :
                $callback   = function($a){ return $a['order']['skc']; };
                $orderAsc   = false;
                break;
            case 6  :
                $callback   = function($a){ return $a['skc_width']>>0; };
                $orderAsc   = false;
                break;
            case 7  :
                $callback   = function($a){ return $a['skc_depth']; };
                $orderAsc   = false;
                break;
            case 8  :
                $callback   = function($a){ return $a['order']['num']; };
                $orderAsc   = false;
                break;
            case 9  :
                $callback   = function($a){ return $a['order']['price']; };
                $orderAsc   = false;
                break;
            case 10  :
                $callback   = function($a){ return $a['difference_num']; };
                $orderAsc   = false;
                break;
            default :
                $list   = ProductsAttributeFactory::fetch($list, $t, 'tid', 'order_key');
                $callback   = function($a){ return $a['order_key']['rank']; };
                $orderAsc   = true;
        }
        usort($list, function($a, $b) use ($callback, $orderAsc){
            $rank_a     = $callback($a);
            $rank_b     = $callback($b);
            if($rank_a<1) return true;
            if($rank_b<1) return false;
            return $orderAsc ? $rank_a > $rank_b : $rank_a < $rank_b;
        });
        $result['list']             = $list;
        
        $ordercount['user_guide']   = $total_user_guide;
        $result['ordercount']       = $ordercount;
        $result['difference_num']   = $productcount['pnum']-$ordercount['pnum'];
        $result['difference_user_guide']   = $ordercount['num']-$ordercount['user_guide'];
        $result['productcount']     = $productcount;
        $result['percent_pnum']     = $productcount['pnum'] ? sprintf("%.1f%%", $ordercount['pnum'] / $productcount['pnum'] * 100) : '';
        $result['skc_width']        = $productcount['skc']  ? sprintf("%.1f%%", $ordercount['skc'] / $productcount['skc'] * 100) : '';
        if($ordercount['skc']){
            $result['skc_depth']    = sprintf("%.2f", $ordercount['num'] / $ordercount['skc']);
        }
        $result['t'] = $t;
        //经理增加导出判断
        //$tmpl = $User->type == 1 ? 'analysis/list_all.html' : 'analysis/list_ad.html';
        $tmpl = 'analysis/list_all.html';
        Flight::display($tmpl, $result);
        //Flight::display('analysis/list_ad.html', $result);
    }
    
    public static function Action_explist_new($r){
        Flight::validateUserHasLogin();  
        $data       = $r->query;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit;
        
        Flight::display("ad/explist_new.html", $result);
    }
    
    
    public static function Action_explist_print_zd($r){
        Flight::validateUserHasLogin();	
		$data 		= $r->query;
		$p          = $data->p      ? $data->p      : 1;
		$limit      = $data->limit;
		$User 		= new User;
		$company    = new Company();
		$params 	= array();
		$options     = array();
		$options['limit'] = $limit;
		$options['page']    = $p;
		$result['pageinfo'] = array('index'=>$p,'limit'=>$limit);
		$user_exp_list	= $User->get_user_order_list_ad($params,$options);
		if($p <= 1){
		    $info           = $User->get_order_list_count($condition);
		    $result['info'] = $info;
		}
		//print_r($user_exp_list);
		$result['list'] = $user_exp_list;
		$result['company'] = $company->getData();
        Flight::display("ad/explist_print_zd.html", $result);
    }

    public static function Action_export_forward($r){
        $data           = $r->query;
        if($data->one){
                static::Action_export_one ($r);
        }elseif ($data->two){
                static::Action_export_two ($r);
        }
    }
    
    public static function Action_export_one ($r) {//导出一维表
        Flight::validateUserHasLogin();
        $data           = $r->query;
        $data->area2    = $data->area2=='NULL' ? $data->area2 : '';
        /* $keys = array('category'=>'大类分析', 'classes'=>'小类分析','wave'=>'波段分析','color'=>'单色分析','size'=>'尺码分析','price_band'=>'价格带分析','series'=>'系列分析','nannvzhuan'=>'性别分析','sxz'=>'上下装分析','brand'=>'品牌分析','theme'=>'主题分析');

        $group_list[]   = array('name'=>0, 'column'=>'series');
        $group_list[]   = array('name'=>1, 'column'=>'wave');
        $group_list[]   = array('name'=>2, 'column'=>'category');
        $group_list[]   = array('name'=>3, 'column'=>'classes');
        $group_list[]   = array('name'=>4, 'column'=>'size');
        // $group_list[]   = array('name'=>'色系', 'column'=>'g.group_id');
        $group_list[]   = array('name'=>5, 'column'=>'price_band');
        $group_list[]   = array('name'=>6, 'column'=>'theme');
        $group_list[]   = array('name'=>7, 'column'=>'brand');
        $group_list[]   = array('name'=>8, 'column'=>'nannvzhuan');
        $group_list[]   = array('name'=>9, 'column'=>'sxz'); */
        
        //$t      = $group_list[$data->group1]['column'];
        
        //print_r($t);exit;
        $Keywords       = new Keywords;    
        //$excel_name     = $keys[$t];
        $excel_name =   "一维分析表";
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array());
        
        $keys   = array('category', 'classes', 'wave', 'color', 'size', 'price_band', 'series', 'nannvzhuan', 'sxz', 'brand', 'season');
        $keys2  = array('category'=>'大类分析', 'classes'=>'小类分析','wave'=>'波段分析','color'=>'单色分析','size'=>'尺码分析','price_band'=>'价格带分析','series'=>'系列分析','nannvzhuan'=>'性别分析','sxz'=>'上下装分析','brand'=>'品牌分析','theme'=>'主题分析','season'=>'季节分析');
        foreach ($keys as $key){
            $t = $key;
        
        switch ($t) {
            case 'color'    :
                $tid    = "pc.color_id";
                $options['group']   = "{$tid}";
                break; 
            case 'size'    :
                $tid    = "ps.{$t}_id";
                $options['group']   = "{$tid}";
                //$options['fields_more'] = "ps.{$t}_id as keyword_id";
                break;
            default :
                $tid    = "p.{$t}_id";
                $options['group']   = "{$tid}";
        }
        
        $condition[]    = "p.status=1 and pc.status=1";
        $options['fields']  = "count(DISTINCT pc.product_id) as pnum, count(DISTINCT pc.product_id,pc.color_id) as skc, p.*,{$tid} as tid";
        if($t == "size"){
            $options['tablename']   = "product as p left join product_color as pc on p.id=pc.product_id left join product_size as ps on p.id=ps.product_id";
        }else{
            $options['tablename']   = "product as p left join product_color as pc on p.id=pc.product_id";
        }
        
        $options['limit']   = $limit;
        $Product        = new Product;
        $where          = implode(" AND ", $condition);
        //$options['db_debug']=true;
        $list           = $Product->find($where, $options);

        $options['group']   = "";
        $productcount   = $Product->findone($where, $options);

        $options        = array();
        $User           = new User;
        $OrderList      = new OrderList;
        switch ($User->type) {
            case 2 :
                $cond['master_uid']    = $User->id;
                $cond['fliter_uid']    = $data->fliter_uid;
                break;
            case 3 :
                $cond['fliter_uid']    = $data->fliter_uid;
                
                $cond['area1']         = $data->area1;
                
                $cond['area2']         = $data->area2;
                
                if($User->username!='0'){
                    $cond['ad_id']         = $User->id;
                }
                
                break;
            default :
                $cond['fliter_uid']    = $User->id;
        }
        $options['key'] = "tid";
        $options['group']   = "{$tid}";
        $options['fields_more'] = "{$tid} as tid";
        if($t == "color"){
            $options['tables_more'] = "left join product_color as pc on p.id=pc.product_id and o.product_color_id=pc.color_id";
        }elseif($t == "size"){
            $options['tables_more'] = "left join product_size as ps on p.id=ps.product_id and o.product_size_id=ps.size_id";
        }
        //$options['db_debug']    = true;
        $orderinfo      = $OrderList->getOrderAnalysisList($cond, $options);
        //print_r($orderinfo);
        $options['group']   = "";
        $options['key']     = "";
        //$options['db_debug']    = true;
        $ordercount     = $OrderList->getOrderAnalysisCount($cond, $options);
        //print_r($ordercount);
        foreach ($list as &$row) {
            $tid            = $row['tid'];
            $ord            = $orderinfo[$tid];
            $row['percent_pnum']    = $row['pnum'] ? sprintf("%.1f%%", $ord['pnum'] / $row['pnum'] * 100) : '-';
            $row['skc_width']       = $row['skc']  ? sprintf("%.1f%%", $ord['skc'] / $row['skc'] * 100) : '-';
            if($ord['skc']){
                $row['skc_depth']   = sprintf("%d", $ord['num'] / $ord['skc']);
            }
            $row['difference_num']  = $row['pnum'] - $ord['pnum'];
            $row['order']   = $ord;
            if($ordercount['num']){
                $row['percent_num'] = sprintf("%.1f%%", $ord['num'] / $ordercount['num'] * 100);
            }
            if($ordercount['price']){
                $row['percent_price'] = sprintf("%.1f%%", $ord['price'] / $ordercount['price'] * 100);
            }
        }
        $difference_num = $productcount['pnum']-$ordercount['pnum'];
        $percent_pnum   = $productcount['pnum'] ? sprintf("%.1f%%", $ordercount['pnum'] / $productcount['pnum'] * 100) : '';
        $skc_width      = $productcount['skc']  ? sprintf("%.1f%%", $ordercount['skc'] / $productcount['skc'] * 100) : '';
        $skc_depth      = sprintf("%d", $ordercount['num'] / $ordercount['skc']);
        
        $list   = ProductsAttributeFactory::fetch($list, $t, 'tid', 'order_key');
        $callback   = function($a){ return $a['order_key']['rank']; };
        $orderAsc   = true;
        usort($list, function($a, $b) use ($callback, $orderAsc){
            $rank_a     = $callback($a);
            $rank_b     = $callback($b);
            if($rank_a<1) return true;
            if($rank_b<1) return false;
            return $orderAsc ? $rank_a > $rank_b : $rank_a < $rank_b;
        });
        
        $keyname    =   array($keys2[$t]);
        $ExcelWriter->row($keyname);
        $titles = array("子项目","","","款量","","","", "款色量","","","订货量","","订量金额");
        $ExcelWriter->row($titles);
        $titles2 = array("","开发","已定","差异","占比","开发","已定","宽度","深度","件数","占比","金额","占比");
        $ExcelWriter->row($titles2);
        
        $orders = array('汇总',$productcount['pnum'],$ordercount['pnum'],$difference_num,$percent_pnum,
            $productcount['skc'],$ordercount['skc'],$skc_width,$skc_depth,$ordercount['num'],'100%',
            $ordercount['price'],'100%'
        );
        $ExcelWriter->row($orders);
        
        foreach ($list as &$row) {
            $data2   = array();
            $data2[] = $Keywords->getKeywordName($row['tid']);            
            $data2[] = $row['pnum'];
            $data2[] = $row['order']['pnum'];
            $data2[] = $row['difference_num'];
            $data2[] = $row['percent_num'];
            $data2[] = $row['skc'];
            $data2[] = $row['order']['skc'];
            $data2[] = $row['skc_width'];
            $data2[] = $row['skc_depth'];
            $data2[] = $row['order']['num'];
            $data2[] = $row['percent_num'];
            $data2[] = $row['order']['price'];
            $data2[] = $row['percent_price'];
            $ExcelWriter->row($data2);
        }
        $ExcelWriter->row(array(''));
        $ExcelWriter->row(array(''));
        }
    }
    
    public static function Action_export_two($r){//导出二维表
        Flight::validateUserHasLogin();
        //print_r($r);exit;
        $User       = new User;
        $Keywords   = new Keywords;
        $data       = $r->query;
        $search_user    = $data->search_user;
        $fliter_uid     = $data->fliter_uid;
        $area1          = $data->area1;
        $area2          = $data->area2=='NULL' ? $data->area2 : '';
        $brand_id       = $data->brand_id;
        $season_id      = $data->season_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $category_id    = $data->category_id;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit  ? $data->limit  : 500;
   
        //$group1     = isset($data->group1)     ? $data->group1     : 1;
        //$group2     = isset($data->group2)     ? $data->group2     : 2;
        //$data           = $r->query;
        $group1    = $data->group1;
        $group2    = $data->group2;
    
        $group_list[]   = array('name'=>'系列', 'column'=>'p.series_id');
        $group_list[]   = array('name'=>'波段', 'column'=>'p.wave_id');
        $group_list[]   = array('name'=>'大类', 'column'=>'p.category_id');
        $group_list[]   = array('name'=>'小类', 'column'=>'p.classes_id');
        $group_list[]   = array('name'=>'尺码', 'column'=>'o.product_size_id');
        // $group_list[]   = array('name'=>'色系', 'column'=>'g.group_id');
        $group_list[]   = array('name'=>'价格带', 'column'=>'p.price_band_id');
        $group_list[]   = array('name'=>'主题', 'column'=>'p.theme_id');
        $group_list[]   = array('name'=>'品牌', 'column'=>'p.brand_id');
        $group_list[]   = array('name'=>'性别', 'column'=>'p.nannvzhuan_id');
        $group_list[]   = array('name'=>'上下装', 'column'=>'p.sxz_id');
        $group_list[]   = array('name'=>'款式', 'column'=>'p.style_id');
        $group_list[]   = array('name'=>'季节', 'column'=>'p.season_id');
        $group_list[]   = array('name'=>'单色', 'column'=>'o.product_color_id'); 
        //$group2         = $group_list[$group2_name]['column'];
        $key_title = $group_list[$group1]['name'] . '&' . $group_list[$group2]['name'] . '分析';

        $excel_name     = $key_title;
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array());
        /* foreach ($groups_name as $group_name1){
            foreach ($groups_name as $group_name2){
                if($group_name1==$group_name2){
                    continue;
                } */
        $group1     = $group_list[$group1]  ? $group_list[$group1]['column']    : $group_list[1]['column'];
        $group2     = $group_list[$group2]  ? $group_list[$group2]['column']    : $group_list[2]['column'];
                
        $condition  = array();
        $options    = array();
        switch ($User->type) {
            case 2  :
                $condition['master_uid']    = $User->id;
                if($search_user){
                    $condition['search_user']  = $search_user;
                }
                break;
            case 3  :
                if($search_user){
                    $condition['search_user']  = $search_user;
                }
                if($User->username!="0"){
                    $condition['ad_id']  = $User->id;
                }
                break;
            default :
                $condition['user_id']   = $User->id;
        }
        if($fliter_uid) $condition['fliter_uid']    = $fliter_uid;
        if($area1)      $condition['area1']         = $area1;
        if($area2)      $condition['area2']         = $area2;
        if($brand_id)   $condition['brand_id']      = $brand_id;
        if($season_id)  $condition['season_id']     = $season_id;
        if($nannvzhuan_id)  $condition['nannvzhuan_id']     = $nannvzhuan_id;
        if($category_id)    $condition['category_id']       = $category_id;
    
        $options['page']        = $p;
        $options['limit']       = $limit;
    
        $options['fields_more'] = "{$group1} as group1_id, {$group2} as group2_id";
        $options['group']       = "{$group1}, {$group2}";
        // $options['order']       = "{$group1}, {$group2}";
        // $options['db_debug']    = true;
        if($group1 == "g.group_id" || $group2 == "g.group_id"){
            $options['tables_more'] = "left join products_attr_group as g on o.product_color_id=g.attr_id";
        }
        //$options['db_debug']=true;
        $options['order']=" {$group1} , num desc ";
    
        $OrderList  = new OrderList;
        $list       = $OrderList->getOrderAnalysisList($condition, $options);
        $list       = STATIC::_summary_fetch_rank($list, $group1, 1);
        $list       = STATIC::_summary_fetch_rank($list, $group2, 2);
        
        $current_group1_id  = null;
        $current_key        = 0;
    
        $total_num          = 0;
        $total_pnum         = 0;
        $total_sku          = 0;
        $total_skc          = 0;
        $total_price        = 0;
        //print_r($list);
    
        $groupTotal = array();
        $groupTotalPrice = array();
    
        foreach($list as $key => $val){
            if($val['group1_id'] != $current_group1_id){
                $current_group1_id  = $val['group1_id'];
                $current_key        = $key;
                if($key > 0){
                    $list[$key - 1]['group']    = $total_group;
                    $groupTotal[$list[$key - 1]['group1_id']] = $total_group['num'];
                    $groupTotalPrice[$list[$key - 1]['group1_id']] = $total_group['price'];
                }
                $total_group      = array();
                $list[$current_key]['rowspan']  = 1;
            }
            $list[$current_key]['rowspan']++;
            $list[$key]['group1_name'] = $Keywords->getName_File($val['group1_id']);
            $list[$key]['group2_name'] = $Keywords->getName_File($val['group2_id']);
            if($product_analysis_info){//$group1 == 2 && $group2 == 3 &&
                $list[$key]['total_num'] = $product_analysis_info[$val['group1_id'] . "_" . $val['group2_id']]['num'];
            }
    
            $total_num      += $val['num'];
            $total_pnum     += $val['pnum'];
            $total_sku      += $val['sku'];
            $total_skc      += $val['skc'];
            $total_price    += $val['price'];
    
            $total_group['num']       += $val['num'];
            $total_group['pnum']      += $val['pnum'];
            $total_group['sku']       += $val['sku'];
            $total_group['skc']       += $val['skc'];
            $total_group['price']     += $val['price'];
        }
    
        //print_r($list);
        if($count = count($list)){
            $list[$count - 1]['group']    = $total_group;
            $groupTotal[$val['group1_id']] = $total_group['num'];
            $groupTotalPrice[$val['group1_id']] = $total_group['price'];
        }
        // print_r($list);
        foreach($list as $key => $val){
            if($total_num)      $list[$key]['percent_num']    = sprintf("%.1f%%", $val['num'] / $total_num * 100);
            if($total_price)    $list[$key]['percent_price']  = sprintf("%.1f%%", $val['price'] / $total_price * 100);
            if($val['group']){
                $list[$key]['group']['percent_num']   = sprintf("%.1f%%", $val['group']['num'] / $total_num * 100);
                $list[$key]['group']['percent_price'] = sprintf("%.1f%%", $val['group']['price'] / $total_price * 100);
            }
            $list[$key]['group_percent_num']    = sprintf("%.1f%%", $val['num'] / $groupTotal[$val['group1_id']] * 100);
            $list[$key]['group_percent_price']  = sprintf("%.1f%%", $val['price'] / $groupTotalPrice[$val['group1_id']] * 100);
        }
        
            
            $ExcelWriter->row(array($key_title));
            $titles = array("","","","","","订数","","","金额");
            $ExcelWriter->row($titles);
            $titles2 = array("","","款量","款色","订数","占比","总占比","金额","占比","总占比");
            $ExcelWriter->row($titles2);
            foreach($list as $key => $val){
                $data2 = array();
                $data2[] = $list[$key]['group1_name'];
                $data2[] = $list[$key]['group2_name'];
                $data2[] = $list[$key]['pnum'];
                $data2[] = $list[$key]['skc'];
                $data2[] = $list[$key]['num'];
                $data2[] = $list[$key]['group_percent_num'];
                $data2[] = $list[$key]['percent_num'];
                $data2[] = $list[$key]['price'];
                $data2[] = $list[$key]['group_percent_price'];
                $data2[] = $list[$key]['percent_price'];
                $ExcelWriter->row($data2);
                if($val['group']){
                    $data3 = array();
                    $data3[] = "";
                    $data3[] = "总计";
                    $data3[] = $list[$key]['group']['pnum'];
                    $data3[] = $list[$key]['group']['skc'];
                    $data3[] = $list[$key]['group']['num'];
                    $data3[] = "100%";
                    $data3[] = $list[$key]['group']['percent_num'];
                    $data3[] = $list[$key]['group']['price'];
                    $data3[] = "100%";
                    $data3[] = $list[$key]['group']['percent_price'];
                    $ExcelWriter->row($data3);
                }
            }
            $ExcelWriter->row(array("总计","",$total_pnum,$total_skc,$total_num,"100%","100%",$total_price,"100%","100%"));
            $ExcelWriter->row(array("", ""));
            $ExcelWriter->row(array("", ""));        
    }
    /* }
    } */
    
    public static function Action_indicator($r){        
        Flight::validateUserHasLogin();
        
        $result = FrontSetting::build();

        $type       =   $r->query->type;
        $show_all   =   $r->query->show_all;
        $UserIndicator 	= new UserIndicator;
        $User = new User();
        if($User->type!=1){
            $userIndicator  = UserIndicator::getInstance($User->id);
            $userIndicator->refresh();
        }
        $indicator_list = $UserIndicator->get_user_indicator_list($User->id);
        foreach ($indicator_list as $row) {
            if($row['status']){
                $row['diff_pnum']       =   $row['exp_pnum']-$row['ord_pnum'];
                $row['diff_skc']        =   $row['exp_skc']-$row['ord_skc'];
                $row['diff_num']        =   $row['exp_num']-$row['ord_num'];
                $row['diff_amount']     =   $row['exp_amount']-$row['ord_discount_amount'];
                $row['percent_pnum']    =   $row['exp_pnum'] ? sprintf("%.1f%%",($row['ord_pnum']/$row['exp_pnum'])*100) : '-';
                $row['percent_skc']     =   $row['exp_skc'] ? sprintf("%.1f%%",($row['ord_skc']/$row['exp_skc'])*100) : '-';
                $row['percent_num']     =   $row['exp_num'] ? sprintf("%.1f%%",($row['ord_num']/$row['exp_num'])*100) : '-';
                $row['percent_amount']  =   $row['ord_discount_amount'] ? sprintf("%.1f%%",($row['ord_discount_amount']/$row['exp_amount'])*100) : '-';
                $list[$row['field']][$row['field2']][] = $row;
            }
        }
        $result['list']		= $list;
        $result['type']     =  $type;
        $result['show_all'] = $show_all;
        
        $result['control']	= "indicator";
    
        Flight::display('analysis/indicator.html', $result);
    }
    
    public static function Action_ad_indicator($r){
        $data   =   $r->query;
        
        $type   =   $data->type ? $data->type : 1;
        $p      =   $data->p;
        $limit  =   $data->limit ? $data->limit : 15;
        $field  =   $data->field;
        $keyword_id=$data->keyword_id;
        $show_all=  $data->show_all;
        $order  = $data->order ? $data->order : 'ord_num';
        $search_user = addslashes($data->search_user);
        $master_id = $data->master_id;

        $UserIndicator 	= new UserIndicator;
        
        $options['page']        =   $p;
        $options['limit']       =   $limit;
        $options['type']        =   $type;
        if($order){
            $options['order_more']       = $order;
        }
        //$options['db_debug']    =   true;

        $User   =   new User;
        $condition  =   array();
        if($search_user){
            $condition[] = "u.username like '{$search_user}%' or u.name like '{$search_user}%'";
        }
        if($master_id){
            $condition[] = " u.id in (select user_slave_id from user_slave where user_id={$master_id})";
        }
        if($type==1){
            switch ($User->type){
                case 2:
                    $condition[]    =   "ui.user_id in (select user_slave_id from user_slave where user_id={$User->id})";
                    break;
                case 3:
                    if($User->username != 0){
                        $condition[]    =   "u.ad_id={$User->id}";
                    }
                    break;
                default:
                    break;
            }
        }elseif($type==2){
            if($User->username != 0){
                $condition[]    =   "u.ad_id={$User->id}";
                if($p==1){
                    // $user_list   =   $User->find("ad_id={$User->id} and type=2",array("limit"=>$limit,"page"=>$p));
                    $user_list   =   $User->find("ad_id={$User->id} and type=2",array("limit"=>100));
                    foreach ($user_list as $row){
                        $userIndicator  = UserIndicator::getInstance($row['user_slave_id']);
                        $userIndicator->refresh();
                    }
                }
            }else{
                if($p==1){
                    // $user_list   =   $User->find("type=2",array("limit"=>$limit,"page"=>$p));
                    $user_list   =   $User->find("type=2",array("limit"=>100));
                    foreach ($user_list as $row){
                        $userIndicator  = UserIndicator::getInstance($row['id']);
                        $userIndicator->refresh();
                    }
                }
            }
        }elseif($type ==3){
            $user_list   =   $User->find("type=3 and username!='0'",array("limit"=>$limit,"page"=>$p));
            foreach ($user_list as $row){
                $userIndicator  = UserIndicator::getInstance($row['id']);
                $userIndicator->refresh();
            }
        }elseif($type == 5){
            $Location       = new Location;
            $location_list  = $Location->getAllChildren(0);
            foreach ($location_list as $row) {
                if($row['pid']==0){
                    $userIndicator = UserIndicator::getInstance($row['id']);
                    $userIndicator->refresh();
                }
            }
        }elseif($type == 6){
            $userIndicator = UserIndicator::getInstance(0);
            $userIndicator->refresh();
            $userIndicator = UserIndicator::getInstance(1);
            $userIndicator->refresh();
        }

        if($type)   $condition[]    = "ui.type={$type}";
        if($keyword_id) $condition[]= "ui.keyword_id='{$keyword_id}'";
        $condition[]	= "ui.field='{$field}'";
        $condition[]    = "ui.status=1";
        $where  =   implode(' AND ', $condition);
        
        $indicator_list   =   $UserIndicator->get_indicator_type_list($where,$options,$type);

        $list   =   array();
        foreach ($indicator_list as $row) {
            if($type==6)    $row['name'] = Keywords::cache_get($row['user_id']);
            $row['diff_pnum']       =   $row['exp_pnum']-$row['ord_pnum'];
            $row['diff_skc']        =   $row['exp_skc']-$row['ord_skc'];
            $row['diff_num']        =   $row['exp_num']-$row['ord_num'];
            $row['diff_amount']     =   $row['exp_amount']-$row['ord_discount_amount'];
            $row['percent_pnum']    =   $row['exp_pnum'] ? sprintf("%.1f%%",($row['ord_pnum']/$row['exp_pnum'])*100) : '-';
            $row['percent_skc']     =   $row['exp_skc'] ? sprintf("%.1f%%",($row['ord_skc']/$row['exp_skc'])*100) : '-';
            $row['percent_num']     =   $row['exp_num'] ? sprintf("%.1f%%",($row['ord_num']/$row['exp_num'])*100) : '-';
            $row['percent_amount']  =   $row['exp_amount'] ? sprintf("%.1f%%",($row['ord_discount_amount']/$row['exp_amount'])*100) : '-';

            $row['percent_pnum_status']    =   $row['exp_pnum'] && ($row['ord_pnum']/$row['exp_pnum']) >= 1 ? 1 : 0;
            $row['percent_skc_status']     =   $row['exp_skc'] && ($row['ord_skc']/$row['exp_skc']) >= 1 ? 1 : 0;
            $row['percent_num_status']     =   $row['exp_num'] && ($row['ord_num']/$row['exp_num']) >= 1 ? 1 : 0;
            $row['percent_amount_status']  =   $row['exp_amount'] && ($row['ord_discount_amount']/$row['exp_amount']) >= 1 ? 1 : 0;

            $list[$row['user_id']]['listing'][] = $row;
            $list[$row['user_id']]['rowspan'] ++;
        }
        foreach ($list as &$row) {
            $row['listing'][0]['rowspan'] = $row['rowspan'];
        }
        
        if($p==1 && ($type==1 || $type==2) && !$field){
            $result['info']                     =   $UserIndicator->get_gather_info($where);
            $result['info']['diff_num']         =   $result['info']['exp_num']-$result['info']['ord_num'];
            $result['info']['diff_amount']      =   $result['info']['exp_amount']-$result['info']['ord_discount_amount'];
            $result['info']['percent_num']      =   $result['info']['exp_num'] ? sprintf("%.1f%%",($result['info']['ord_num']/$result['info']['exp_num'])*100) : '-';
            $result['info']['percent_amount']   =   $result['info']['exp_amount'] ? sprintf("%.1f%%",($result['info']['ord_discount_amount']/$result['info']['exp_amount'])*100) : '-';
        }
        $result['start']    =   $limit*($p-1)+1;
        $result['list']     =   $list;
        $result['page']     =   $p;
        $result['field']    =   $field;
        $result['show_all'] =   $show_all;
        $result['type']     =   $type;
            
        Flight::display('analysis/ad_indicator.html', $result);
    }
    
    public static function Action_indicator_analysis($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
        $result['type']     = $r->query->type;
        $result['control']  = "indicator";
    
        Flight::display("analysis/indicator_analysis.html",$result);
    }

    public static function Action_classa_indicator($r){
        Flight::validateUserHasLogin();

        $result = FrontSetting::build();
        $result['control']  = "indicator";

        $data       =   $r->query;
        $show_all   =   $data->show_all;
        $type       =   $data->type ? $data->type : 1;
        $p          =   $data->p;
        $limit      =   $data->limit ? $data->limit : 1000;

        $UserIndicator      = new UserIndicator;
        
        $options['page']    =   $p;
        $options['limit']   =   $limit;

        if($p==1){
            $user_list   =   $User->find("type=2",array("limit"=>100));
            foreach ($user_list as $row){
                $userIndicator  = UserIndicator::getInstance($row['id']);
                $userIndicator->refresh();
            }
        }

        $total      = array();
        $condition  = array();
        $condition[]= "ui.field=''";
        $condition[]= "ui.status=1";
        $condition[]= "ui.type=2";
        $where      = implode(' AND ', $condition);
        
        $zongdai_list   = $UserIndicator->get_indicator_type_list($where,$options,$type);

        $UserSlave      = new UserSlave;
        $op             = array();
        $op["tablename"]= "user as u left join user_slave as us on u.id=us.user_slave_id";
        $op["fields"]   = "group_concat(u.id) as in_user_id";

        $where          = "us.user_slave_id is null and u.type=1";
        $in_user_id     = $UserSlave->findone($where,$op);

        $condition      = array();
        $condition[]    = "ui.field=''";
        $condition[]    = "ui.status=1";
        $condition[]    = "ui.type=1";
        $condition[]    = "ui.user_id in ({$in_user_id['in_user_id']})";
        $where          = implode(' AND ', $condition);
        $dealer_list    = $UserIndicator->get_indicator_type_list($where,$options,$type);
        
        foreach ($zongdai_list as &$row) {
            $row['diff_pnum']       =   $row['exp_pnum']-$row['ord_pnum'];
            $row['diff_skc']        =   $row['exp_skc']-$row['ord_skc'];
            $row['diff_num']        =   $row['exp_num']-$row['ord_num'];
            $row['diff_amount']     =   $row['exp_amount']-$row['ord_discount_amount'];
            $row['percent_pnum']    =   $row['exp_pnum'] ? sprintf("%.1f%%",($row['ord_pnum']/$row['exp_pnum'])*100) : '-';
            $row['percent_skc']     =   $row['exp_skc'] ? sprintf("%.1f%%",($row['ord_skc']/$row['exp_skc'])*100) : '-';
            $row['percent_num']     =   $row['exp_num'] ? sprintf("%.1f%%",($row['ord_num']/$row['exp_num'])*100) : '-';
            $row['percent_amount']  =   $row['exp_amount'] ? sprintf("%.1f%%",($row['ord_discount_amount']/$row['exp_amount'])*100) : '-';

            $row['percent_pnum_status']    =   $row['exp_pnum'] && ($row['ord_pnum']/$row['exp_pnum']) >= 1 ? 1 : 0;
            $row['percent_skc_status']     =   $row['exp_skc'] && ($row['ord_skc']/$row['exp_skc']) >= 1 ? 1 : 0;
            $row['percent_num_status']     =   $row['exp_num'] && ($row['ord_num']/$row['exp_num']) >= 1 ? 1 : 0;
            $row['percent_amount_status']  =   $row['exp_amount'] && ($row['ord_discount_amount']/$row['exp_amount']) >= 1 ? 1 : 0;

            $total['ord_num']               += $row['ord_num'];
            $total['exp_num']               += $row['exp_num'];
            $total['diff_num']              += $row['diff_num'];
            $total['ord_discount_amount']   += $row['ord_discount_amount'];
            $total['diff_amount']           += $row['diff_amount'];
            $total['ord_pnum']              += $row['ord_pnum'];
            $total['exp_pnum']              += $row['exp_pnum'];
            $total['diff_pnum']             += $row['diff_pnum'];
            $total['exp_skc']               += $row['exp_skc'];
            $total['diff_skc']              += $row['diff_skc'];
        }
        
        unset($row);

        foreach ($dealer_list as &$row) {
            $row['diff_pnum']       =   $row['exp_pnum']-$row['ord_pnum'];
            $row['diff_skc']        =   $row['exp_skc']-$row['ord_skc'];
            $row['diff_num']        =   $row['exp_num']-$row['ord_num'];
            $row['diff_amount']     =   $row['exp_amount']-$row['ord_discount_amount'];
            $row['percent_pnum']    =   $row['exp_pnum'] ? sprintf("%.1f%%",($row['ord_pnum']/$row['exp_pnum'])*100) : '-';
            $row['percent_skc']     =   $row['exp_skc'] ? sprintf("%.1f%%",($row['ord_skc']/$row['exp_skc'])*100) : '-';
            $row['percent_num']     =   $row['exp_num'] ? sprintf("%.1f%%",($row['ord_num']/$row['exp_num'])*100) : '-';
            $row['percent_amount']  =   $row['exp_amount'] ? sprintf("%.1f%%",($row['ord_discount_amount']/$row['exp_amount'])*100) : '-';

            $row['percent_pnum_status']    =   $row['exp_pnum'] && ($row['ord_pnum']/$row['exp_pnum']) >= 1 ? 1 : 0;
            $row['percent_skc_status']     =   $row['exp_skc'] && ($row['ord_skc']/$row['exp_skc']) >= 1 ? 1 : 0;
            $row['percent_num_status']     =   $row['exp_num'] && ($row['ord_num']/$row['exp_num']) >= 1 ? 1 : 0;
            $row['percent_amount_status']  =   $row['exp_amount'] && ($row['ord_discount_amount']/$row['exp_amount']) >= 1 ? 1 : 0;


            $total['ord_num']               += $row['ord_num'];
            $total['exp_num']               += $row['exp_num'];
            $total['diff_num']              += $row['diff_num'];
            $total['ord_discount_amount']   += $row['ord_discount_amount'];
            $total['diff_amount']           += $row['diff_amount'];
            $total['ord_pnum']              += $row['ord_pnum'];
            $total['exp_pnum']              += $row['exp_pnum'];
            $total['diff_pnum']             += $row['diff_pnum'];
            $total['exp_skc']               += $row['exp_skc'];
            $total['diff_skc']              += $row['diff_skc'];
        }
        unset($row);
        $total['percent_pnum']    =   $total['exp_pnum'] ? sprintf("%.1f%%",($total['ord_pnum']/$total['exp_pnum'])*100) : '-';
        $total['percent_skc']     =   $total['exp_skc'] ? sprintf("%.1f%%",($total['ord_skc']/$total['exp_skc'])*100) : '-';
        $total['percent_num']     =   $total['exp_num'] ? sprintf("%.1f%%",($total['ord_num']/$total['exp_num'])*100) : '-';
        $total['percent_amount']  =   $total['exp_amount'] ? sprintf("%.1f%%",($total['ord_discount_amount']/$total['exp_amount'])*100) : '-';

        $total['percent_pnum_status']    =   $total['exp_pnum'] && ($total['ord_pnum']/$total['exp_pnum']) >= 1 ? 1 : 0;
        $total['percent_skc_status']     =   $total['exp_skc'] && ($total['ord_skc']/$total['exp_skc']) >= 1 ? 1 : 0;
        $total['percent_num_status']     =   $total['exp_num'] && ($total['ord_num']/$total['exp_num']) >= 1 ? 1 : 0;
        $total['percent_amount_status']  =   $total['exp_amount'] && ($total['ord_discount_amount']/$total['exp_amount']) >= 1 ? 1 : 0;

        $result['show_all']     = $show_all;
        $result['total']        = $total;
        $result['zongdai_list'] = $zongdai_list;
        $result['dealer_list']  = $dealer_list;
        $result['control']      = "indicator";

        Flight::display("analysis/classa_indicator.html",$result);
    }
    
    public static function Action_get_keyword_group($r){
        $field  =   $r->query->field;
        $len    =   strlen($field);
        if($len){
            $field  =   substr($field,0,$len-3);
            $Factory    =   new ProductsAttributeFactory($field);
            $result['list'] =   $Factory->getAllList();
            Flight::json($result);
        }
    }
    
    public static function Action_rank_distribute_table($r){  //排行分布
        Flight::validateUserHasLogin();
        $data   =   $r->query;
        $area1  =   $data->area1;
        $area2  =   $data->area2;
        $fliter_uid=$data->fliter_uid;
        $view   =   $data->view ? $data->view : 'T';
        
        $OrderListUserProductColor  =   new OrderList();
        $OrderListUser              =   new OrderListUser;
        $OrderListProductColor      =   new OrderListProductColor();
        $OrderListProduct           =   new OrderListProduct();
        
        $Company    =   new Company();
        $cominfo    =   $Company->getData();
        $interval   =   $cominfo['rank_interval'] ? $cominfo['rank_interval'] : "25;50;75;100";   //分号分隔的百分比 20;40;60
        $interval_list  =   explode(";", $interval);


        $User       =   new User();
        $condition  =   array();
        switch($User->type){
            case 2:
                if($fliter_uid){
                    $condition[] = "user_id in ($fliter_uid)";
                }else{
                    $condition[]    =   "user_id in (select user_slave_id from user_slave where user_id={$User->id})";
                }
                break;
            case 3:
                if($fliter_uid){ 
                    $condition[] = "user_id in ($fliter_uid)";
                }elseif($User->username=='0'){
                    if($area1)   $condition[]    =   "user_id in (select id from user where area1={$area1})";
                    if($area2)   $condition[]    =   "user_id in (select id from user where area2={$area2})";
                }else{
                    $condition[]    =   "user_id in (select id from user where ad_id={$User->id})";
                }
                break;
            default:
                $condition[]    =   "user_id in ($User->id)";
                break;
        }
        $where  =   implode(" AND ", $condition);
        $total_info =   $OrderListUser->get_total($where);
        
        switch($view){
            case 'ST':
                $rank_list   =   $OrderListProductColor->get_product_color_rank_list();
                $product_color_list  =   $OrderListUserProductColor->get_product_color_list($where);
                foreach($product_color_list as $row){
                    $product_color_list_tmp['p'.$row['product_id'].'_c'.$row['product_color_id']]   =   $row;
                }
                foreach($rank_list as &$val){
                    $key = 'p'.$val['product_id'].'_c'.$val['product_color_id'];
                    if(array_key_exists($key,$product_color_list_tmp))
                        $val += $product_color_list_tmp[$key];
                }
                break;
            case 'T':
                $rank_list   =   $OrderListProduct->get_product_rank_list();
                $product_list  =   $OrderListUserProductColor->get_product_list($where);
                foreach($product_list as $row){
                    $product_list_tmp[$row['product_id']]   =   $row;
                }
                foreach($rank_list as &$val){
                    $key = $val['product_id'];
                    if(array_key_exists($key,$product_list_tmp))
                        $val += $product_list_tmp[$key];
                }
                break;
            default:
                break;
        }

        $count      =   count($rank_list);
        $len        =   count($interval_list);
        sort($interval_list);
        
        foreach ($interval_list as $row){
            $show_interval[]    =   floor($row*$count/100);
        }
        $j = 0;
        $list   =   array();
        for($i=0;$i<$count;$i++){
            if($i<$show_interval[$j]){
                $list[$interval_list[$j]]['num']    +=  $rank_list[$i]['num'];
                $list[$interval_list[$j]]['price']  +=  $rank_list[$i]['price'];
                $list[$interval_list[$j]]['discount_price']  +=$rank_list[$i]['discount_price'];
            }else{
                $j++;
                if($j>=$len){
                    break;
                }
                $list[$interval_list[$j]]['num']    +=($list[$interval_list[$j-1]]['num']+$rank_list[$i]['num']);
                $list[$interval_list[$j]]['price']  +=($list[$interval_list[$j-1]]['price']+$rank_list[$i]['price']);
                $list[$interval_list[$j]]['discount_price']  +=($list[$interval_list[$j-1]]['discount_price']+$rank_list[$i]['discount_price']);
            }
        }
        unset($val);
        foreach ($list as &$val){
            $val['num_percent']             =   sprintf("%.1f%%",$val['num'] / $total_info['num'] * 100);
            $val['price_percent']           =   sprintf("%.1f%%",$val['price'] / $total_info['price'] * 100);
            $val['discount_price_percent']  =   sprintf("%.1f%%",$val['discount_price'] / $total_info['discount_price'] * 100);
        }
        $result['list'] =   $list;
        $result['view'] =   $view;
        Flight::display("analysis/rank_distribute_table.html",$result);
    }
    
    public static function Action_rank_distribute($r){
        Flight::validateUserHasLogin();
        $result =   FrontSetting::build();
        
        $User   =   new User;
        $result['user']     =   $User->getAttribute();
        $result['control']  =   "rank_distribute";
        Flight::display("analysis/rank_distribute.html",$result);
    }

    public static function Action_explist_print_new($r){
        Flight::validateUserHasLogin();
        $data       = $r->query;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit  ? $data->limit  : 50;
        $is_type    = $data->is_type;;
        $area1      = $data->area1;
        $area2      = $data->area2;
        $zongdai    = $data->fliter_zd;
        $fliter_uid = $data->fliter_uid;
        $property   = $data->property;
        $is_lock    = $data->is_lock;
        $order_status = $data->order_status;
        $order = $data->order;
        $sname = $data->sname;
        $company = new Company;
        $companyData = $company->getData();
        if(!$order){            
            $order  = $company->ad_order;
        }
        $options['page']    = $p;
        $options['limit']   = $limit;
        $result['pageinfo'] = array('index'=>$p,'limit'=>$limit);
    
        $condition  = array();
        $User       = new User;
        $OrderListUser = new OrderListUser;
        $result['user'] = $User->getAttribute();
        if($User->type == 3 && $User->username != "0"){
            $condition[] = "u.ad_id=$User->id";
        }elseif($User->type==2){
            $condition[]    = "us.user_id={$User->id}";
        }
        if($area1)          $condition[] = "u.area1={$area1}";
        if($area2)          $condition[] = "u.area2={$area2}";
        if($zongdai)        $condition[] = "us.user_id={$zongdai}";
        if($fliter_uid)     $condition[] = "u.id={$fliter_uid}";
        if(isset($is_lock)&&is_numeric($is_lock))       $condition[]  = "u.is_lock={$is_lock}";
        if(is_numeric($property))                       $condition[]  = "u.property={$property}";
        if($sname)          $condition[] = "( u.username = '{$sname}' or u.name like  '%{$sname}%' )";

        switch ($is_type) {
            case 'isZongdai':
                $options['tablename']   = "user_slave as us left join user as u on us.user_id=u.id 
                                            left join orderlistuser as o on us.user_slave_id=o.user_id
                                            left join user_indicator as ui on us.user_id = ui.user_id and field='' and ui.type=2";
                $options['fields']      = "u.id,u.name,u.username,sum(o.num) as num,sum(o.price) as price,sum(o.zd_discount_price) as discount_price,ui.exp_amount as exp_price,ui.exp_num as exp_num,ui.ord_num as ord_num,ui.ord_discount_amount as ord_discount_price ,ui.ord_num/ui.exp_num as num_percent,ui.ord_discount_amount/ui.exp_amount as price_percent";
                $options['group']       = "us.user_id";
                $condition[]            = "u.type=2";
                break;
            case 'isArea':
                $options['tablename']   = "location as l left join user as u on u.area1=l.id 
                                            left join orderlistuser as o on u.id=o.user_id 
                                            left join user_indicator as ui on u.id = ui.user_id and field='' and ui.type=1";
                $options['fields']      = "l.name as areaname,u.area1,sum(o.num) as num,sum(o.price) as price,sum(o.zd_discount_price) as discount_price,sum(ui.exp_amount) as exp_price,sum(ui.exp_num) as exp_num,sum(ui.ord_num) as ord_num,sum(ui.ord_discount_amount) as ord_discount_price,sum(ui.ord_num)/sum(ui.exp_num) as num_percent,sum(ui.ord_discount_amount)/sum(ui.exp_amount) as price_percent";
                $options['group']       = "l.id";
                $condition[]            = "l.pid=0";
                $condition[]            = "u.type=1";
                break;
            case 'isProperty':
                $options['tablename']   = 'user as u left join orderlistuser as o on u.id=o.user_id
                                          left join user_slave us on u.id = us.user_slave_id
                                          left join user_indicator as ui on u.id = ui.user_id and field="" and ui.type=1';
                $options['fields']      = "u.property,sum(o.num) as num,sum(o.price) as price,sum(o.zd_discount_price) as discount_price,sum(ui.exp_amount) as exp_price,sum(ui.exp_num) as exp_num,sum(ui.ord_num) as ord_num,sum(ui.ord_discount_amount) as ord_discount_price,sum(ui.ord_num)/sum(ui.exp_num) as num_percent,sum(ui.ord_discount_amount)/sum(ui.exp_amount) as price_percent";
                $options['group']       = "u.property";
                $condition[]            = "u.type=1";
                break;            
            default:
                $options['tablename']   = 'user as u left join orderlistuser as o on u.id=o.user_id
                                          left join user_slave us on u.id = us.user_slave_id
                                          left join user_indicator as ui on u.id = ui.user_id and field="" and ui.type=1';
                $options['fields']      = 'u.id,u.name,u.username,u.is_lock,u.order_status,o.num,o.price,o.discount_price,o.sku,o.skc,us.user_id as mid,ui.exp_amount as exp_price,ui.exp_num,ui.ord_num as ord_num,ui.ord_discount_amount as ord_discount_price ,(ui.ord_discount_amount/ui.exp_amount) as price_percent,(ui.ord_num/ui.exp_num) as num_percent';
                $condition[]            = "u.type=1";
                if($companyData['check_order']) 
                    $condition[] = $order_status ? "u.order_status = {$order_status}" :"u.order_status >= 1 ";
                break;
        }
        switch($order){
            case 'num':
                $options['order'] = ' num desc ';
                break;
            case 'price':
                $options['order'] = ' price desc ';
                break;
            case 'discount_price':
                $options['order'] = ' discount_price desc ';
                break;
            case 'price_percent':
                $options['order'] = ' price_percent desc ';
                break;
            default:  
                $options['order'] = ' u.id ';
        }
        
        //$options['db_debug']    = true;
        $where  =   implode(' AND ', $condition);
        $list   =   $OrderListUser->find($where, $options);
    
        foreach($list as &$row){
            $row['userMaster'] = $User->getUserInfoById($row['mid']);
            $row['price_percent'] =   $row['exp_price'] ? sprintf("%.1f%%",($row['ord_discount_price']/$row['exp_price'])*100) : "-";
            $row['num_percent'] =   $row['exp_num'] ? sprintf("%.1f%%",($row['ord_num']/$row['exp_num'])*100) : "-";
        }
        if($p <= 1){
            if($is_type&&$is_type=='isZongdai'){
                $options['fields']  =   "sum(o.num) as num,sum(o.price) as price,sum(o.zd_discount_price) as discount_price,sum(distinct ui.exp_num) as exp_num,sum(distinct ui.exp_amount) as exp_price,sum(distinct ui.ord_num) as ord_num,sum(distinct ui.ord_discount_amount) as ord_discount_price";
            }else{
                $options['fields']  =   "sum(o.num) as num,sum(o.price) as price,sum(o.discount_price) as discount_price,sum(ui.exp_num) as exp_num,sum(ui.exp_amount) as exp_price,sum(ui.ord_num) as ord_num,sum(ui.ord_discount_amount) as ord_discount_price";
            }
            $options['group']   =   "";
            $info           = $OrderListUser->findone($where,$options);
            $result['info'] = $info;
            $result['info']['price_percent']=   $info['exp_price'] ? sprintf("%.1f%%",($info['ord_discount_price']/$info['exp_price'])*100) : "-";
            $result['info']['num_percent']  =   $info['exp_num'] ? sprintf("%.1f%%",($info['ord_num']/$info['exp_num'])*100) : "-";
        }
        $result['is_type']  = $is_type;
        $result['list']     = $list;
        $result['company']  = $companyData;    
        //print_r($list);exit();
        Flight::display("analysis/explist_print_new.html", $result);     
    }

    public static function Action_explist_info($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit  ? $data->limit  : 10000;
        $area1      = $data->area1;
        $area2      = $data->area2;
        $zongdai    = $data->fliter_zd;
        $fliter_uid = $data->fliter_uid;
        $property   = $data->property;
        $is_lock    = $data->is_lock;
        $order_status = $data->order_status;
        $order = $data->order;
        $sname = $data->sname;
        $company = new Company;
        $companyData = $company->getData();
        if(!$order){            
            $order  = $company->ad_order;
        }
        $options['page']    = $p;
        $options['limit']   = $limit;
        $result['pageinfo'] = array('index'=>$p,'limit'=>$limit);
    
        $condition  = array();
        $User       = new User;
        if($User->type == 3 && $User->username != "0"){
            $condition[] = "u.ad_id=$User->id";
        }elseif($User->type==2){
            $condition[]    = "us.user_id={$User->id}";
        }
        if($area1)          $condition[] = "u.area1={$area1}";
        if($area2)          $condition[] = "u.area2={$area2}";
        if($zongdai)        $condition[] = "us.user_id={$zongdai}";
        if($fliter_uid)     $condition[] = "u.id={$fliter_uid}";
        if(isset($is_lock)&&is_numeric($is_lock))       $condition[]  = "u.is_lock={$is_lock}";
        if(is_numeric($property))                       $condition[]  = "u.property={$property}";
        if($sname)          $condition[] = "( u.username = '{$sname}' or u.name like  '%{$sname}%' )";
        
        if($companyData['check_order']) $condition[] = $order_status ? "u.order_status = {$order_status}" :"u.order_status >= 1 ";
        $condition[]    =   "ui.type = 1";
        
        $options['tablename']   = 'user as u left join orderlistuser as o on u.id=o.user_id
                                  left join user_slave us on u.id = us.user_slave_id
                                  left join user_indicator as ui on u.id = ui.user_id and field=""';
        //$options['fields']      = 'u.id,u.is_lock,o.num,o.price,o.discount_price,ui.exp_amount,ui.exp_num,(o.discount_price/ui.exp_amount) as price_percent,(o.num/ui.exp_num) as num_percent';
        $options['fields']  =   "count(u.id) as user_num";
        //$options['db_debug']=   true;
        $where          =   implode(" AND ", $condition);
        $where_lock     =   $where." AND u.is_lock = 1";
        $where_finish   =   $where." AND (o.discount_price>ui.exp_amount AND ui.exp_amount > 0 ) or (o.discount_price=0 AND o.num>ui.exp_num AND ui.exp_num > 0)";
        
        $options_lock   =   $options;
        $options_finish =   $options;

        $options_lock['fields']     = "count(u.id) as totallock,sum(o.num) as locknum,sum(o.discount_price) as lockprice";
        $options_finish['fields']   = "count(u.id) as finished";

        $info   =   $User->findone($where,$options);
        $lock   =   $User->findone($where_lock,$options_lock);
        $finish =   $User->findone($where_finish,$options_finish);

        $result['totallock']    =   $lock['totallock'];
        $result['locknum']      =   $lock['locknum'];
        $result['lockprice']    =   $lock['lockprice'];
        $result['totalunlock']  =   $info['user_num']-$lock['totallock'];
        $result['finished']     =   $finish['finished'];
        $result['unfinished']   =   $info['user_num']-$finish['finished'];
        // foreach ($list as $value) {
        //     if($value['is_lock']==1){
        //         $result['totallock']++;
        //         $result['locknum']+=$value['num'];
        //         $result['lockprice']+=$value['discount_price'];
        //     }else{
        //         $result['totalunlock']++;
        //     }
        //     if($value['price_percent']){
        //         if($value['price_percent']>=1)
        //             $result['finished']++;
        //         else
        //             $result['unfinished']++;
        //     }elseif($value['num_percent']>=1){
        //         $result['finished']++;
        //     }else{
        //         $result['unfinished']++;
        //     }
        // }

        Flight::display("analysis/explist_info.html",$result);
    }

    public static function Action_three_analysis($r){
        Flight::validateUserHasLogin();
        $result =   FrontSetting::build();
        
        $User   =   new User;
        $result['user']     =   $User->getAttribute();
        $result['control']  =   "three_analysis";
        Flight::display("analysis/three_analysis.html",$result);
    }

    public static function Action_three_analysis_table($r){//三维分析表
        $data       = $r->query;
        $fliter_uid = $data->fliter_uid;
        $area1      = $data->area1;
        $area2      = $data->area2;

        $bFactory   =   new ProductsAttributeFactory('brand');
        $nFactory   =   new ProductsAttributeFactory('nannvzhuan');
        $wFactory   =   new ProductsAttributeFactory('wave');
        $cFactory   =   new ProductsAttributeFactory('category');
        
        $b_list =   $bFactory->getAllList();
        $n_list =   $nFactory->getAllList();
        $w_list =   $wFactory->getAllList();
        $c_list =   $cFactory->getAllList();
       
        foreach ($b_list as $row){
            if($row['keywords']['name']!='饰品')      //饰品不分析，单独作为一个品牌
                $brand_list[] =   $row['keyword_id'];
        }
        foreach ($n_list as $row)
            $nannvzhuan_list[] =   $row['keyword_id'];
        foreach ($w_list as $row){       
            if($row['keywords']['name']!='4B')
                $wave_list[]  =   $row['keyword_id'];
        }
        foreach ($c_list as $row){
            if($row['keywords']['name']!='饰品')
                $category_list[] =   $row['keyword_id'];
        }
        
        $User           = new User;
        $OrderList      = new OrderList;
        switch ($User->type) {
            case 2 :
                if($master_uid)     $condition[]    = "o.zd_user_id=$User->id";
                if($fliter_uid)     $condition[]    = "u.id={$fliter_uid}";
                break;
            case 3 :
                if($area1)          $condition[]    = "u.area1 in ({$area1})";
                if($area2)          $condition[]    = "u.area2 in ({$area2})";
                if($fliter_uid)     $condition[]    = "u.id={$fliter_uid}";        
                if($User->username!='0'){
                    $condition[]         = "u.ad_id={$User->id}";
                }
        
                break;
            default :
                $condition[]    = "u.id={$User->id}"; 
        }
        $options['tablename']   = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id left join user_slave as us on u.id=us.user_slave_id";
        $options['fields']      =   "p.brand_id,p.nannvzhuan_id,p.wave_id,p.category_id,sum(o.num) as num,sum(o.discount_amount) as price,count(DISTINCT o.product_id,o.product_color_id) as pnum";
        $options['group']       =   "p.brand_id,p.nannvzhuan_id,p.wave_id,p.category_id";
        $options['limit']       =   10000;  
        //$options['db_debug']    =   true;
        $where  =   implode(" AND ", $condition);
        $orderlist  =   $OrderList->find($where,$options);
        
        foreach ($orderlist as $row){
            $brand_id       =   $row['brand_id'];
            $nannvzhuan_id  =   $row['nannvzhuan_id'];
            $wave_id        =   $row['wave_id'];
            $category_id    =   $row['category_id'];
            $list['info'][$brand_id]['bran_info'][$wave_id]['wave_info'][$category_id]['cate_info'][$nannvzhuan_id]['nannv_info']['num']     +=   $row['num'];
            $list['info'][$brand_id]['bran_info'][$wave_id]['wave_info'][$category_id]['cate_info'][$nannvzhuan_id]['nannv_info']['price']   +=   $row['price'];
            $list['info'][$brand_id]['bran_info'][$wave_id]['wave_info'][$category_id]['cate_info'][$nannvzhuan_id]['nannv_info']['pnum']    +=   $row['pnum'];
            
            $list['info'][$brand_id]['bran_info'][$wave_id]['wave_info'][$category_id]['cate_info']['num']     +=  $row['num'];
            $list['info'][$brand_id]['bran_info'][$wave_id]['wave_info'][$category_id]['cate_info']['price']   +=  $row['price'];
            $list['info'][$brand_id]['bran_info'][$wave_id]['wave_info'][$category_id]['cate_info']['pnum']    +=  $row['pnum'];
            
            $list['info'][$brand_id]['bran_info'][$wave_id]['wave_info']['num']     +=  $row['num'];
            $list['info'][$brand_id]['bran_info'][$wave_id]['wave_info']['price']   +=  $row['price'];
            $list['info'][$brand_id]['bran_info'][$wave_id]['wave_info']['pnum']    +=  $row['pnum'];
            
            $list['info'][$brand_id]['bran_info']['num']     +=  $row['num'];
            $list['info'][$brand_id]['bran_info']['price']   +=  $row['price'];
            $list['info'][$brand_id]['bran_info']['pnum']    +=  $row['pnum'];
            
            
            $list['num']     +=  $row['num'];
            $list['price']   +=  $row['price'];
            $list['pnum']    +=  $row['pnum'];
        }
        foreach ($brand_list as $bval){
            foreach ($wave_list as $wval){
                foreach ($category_list as $cval){
                    foreach ($nannvzhuan_list as $nval){
                        $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info'][$nval]['percent_num']     =   sprintf("%.1f%%", $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info'][$nval]['nannv_info']['num']/$list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info']['num'] * 100);
                        $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info'][$nval]['percent_price']   =   sprintf("%.1f%%", $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info'][$nval]['nannv_info']['price']/$list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info']['price'] * 100);
                        $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info'][$nval]['percent_pnum']    =   sprintf("%.1f%%", $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info'][$nval]['nannv_info']['pnum']/$list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info']['pnum'] * 100);
                    }
                    $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['percent_num']     =   sprintf("%.1f%%", $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info']['num']/$list['info'][$bval]['bran_info'][$wval]['wave_info']['num'] * 100);
                    $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['percent_price']   =   sprintf("%.1f%%", $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info']['price']/$list['info'][$bval]['bran_info'][$wval]['wave_info']['price'] * 100);
                    $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['percent_pnum']    =   sprintf("%.1f%%", $list['info'][$bval]['bran_info'][$wval]['wave_info'][$cval]['cate_info']['pnum']/$list['info'][$bval]['bran_info'][$wval]['wave_info']['pnum'] * 100);
                }
                $list['info'][$bval]['bran_info'][$wval]['percent_num']     =   sprintf("%.1f%%", $list['info'][$bval]['bran_info'][$wval]['wave_info']['num']/$list['info'][$bval]['bran_info']['num'] * 100);
                $list['info'][$bval]['bran_info'][$wval]['percent_price']   =   sprintf("%.1f%%", $list['info'][$bval]['bran_info'][$wval]['wave_info']['price']/$list['info'][$bval]['bran_info']['price'] * 100);
                $list['info'][$bval]['bran_info'][$wval]['percent_pnum']    =   sprintf("%.1f%%", $list['info'][$bval]['bran_info'][$wval]['wave_info']['pnum']/$list['info'][$bval]['bran_info']['pnum'] * 100);
            }
            $list['info'][$bval]['percent_num']     =   sprintf("%.1f%%", $list['info'][$bval]['bran_info']['num']/$list['num'] * 100);
            $list['info'][$bval]['percent_price']   =   sprintf("%.1f%%", $list['info'][$bval]['bran_info']['price']/$list['price'] * 100);
            $list['info'][$bval]['percent_pnum']    =   sprintf("%.1f%%", $list['info'][$bval]['bran_info']['pnum']/$list['pnum'] * 100);
        }
        foreach($wave_list as $wval){
            foreach ($category_list as $cval){
                if(!$k){ $k= $wval; $rowspanList[$wval][$cval]['rowspan']=count($category_list)+1;}
            }
            $k=0;
        }
        
        foreach ($orderlist as $row){
            $brand_id       =   $row['brand_id'];
            $nannvzhuan_id  =   $row['nannvzhuan_id'];
            $wave_id        =   $row['wave_id'];
            $category_id    =   $row['category_id'];
            
            $list2[$brand_id][$wave_id][$nannvzhuan_id]['num']    +=  $row['num'];
            $list2[$brand_id][$wave_id][$nannvzhuan_id]['price']  +=  $row['price'];
            $list2[$brand_id][$wave_id][$nannvzhuan_id]['pnum']   +=  $row['pnum'];
            $list2[$brand_id][$wave_id]['num']  +=  $row['num'];
            $list2[$brand_id][$wave_id]['price']+=  $row['price'];
            $list2[$brand_id][$wave_id]['pnum'] +=  $row['pnum'];
        }
        
        foreach ($brand_list as $bval){
            foreach ($wave_list as $wval){
                $list3[$bval][$wval]['price']    =   $list2[$bval][$wval]['price'];
                $list3[$bval]['price']  +=    $list2[$bval][$wval]['price'];
                $list3['price'] += $list2[$bval][$wval]['price'];
            } 
        }
        
        foreach ($brand_list as $bval){
            foreach ($wave_list as $wval){
                $list3[$bval][$wval]['percent_price']  =   sprintf("%.1f%%",$list3[$bval][$wval]['price']/$list3[$bval]['price']*100);
            }
            $list3[$bval]['percent_price']  =   sprintf("%.1f%%",$list3[$bval]['price']/$list3['price']*100);
        }
        
        $Product   =   new Product();
        $options    =   array();
        $options['tablename']   =   "product AS p LEFT JOIN product_color AS pc ON p.id = pc.product_id";
        $options['fields']      =   "p.brand_id,p.nannvzhuan_id,p.wave_id,p.category_id,count( DISTINCT pc.product_id, pc.color_id ) AS skc";
        $options['group']       =   "p.brand_id,p.nannvzhuan_id,p.wave_id,p.category_id";
        $options['limit']       =   10000;
        //$options['db_debug']    =   true;
        $product_list   =   $Product->find("p.status!=0",$options);
        foreach ($product_list as $row){
            $brand_id       =   $row['brand_id'];
            $nannvzhuan_id  =   $row['nannvzhuan_id'];
            $wave_id        =   $row['wave_id'];
            $category_id    =   $row['category_id'];
            $skc_list[$brand_id][$wave_id][$category_id][$nannvzhuan_id]['skc'] += $row['skc'];
            $skc_list2[$brand_id][$wave_id][$nannvzhuan_id]['skc']+=$row['skc'];
        }
        //print_r($list);exit;
        $result['list']             =   $list;
        $result['list2']            =   $list2;
        $result['list3']            =   $list3;
        $result['category_num']     =   count($category_list);
        
        $result['brand_list']       =   $brand_list;
        $result['nannvzhuan_list']  =   $nannvzhuan_list;
        $result['wave_list']        =   $wave_list;
        $result['category_list']    =   $category_list;
        $result['skc_list']         =   $skc_list;
        $result['skc_list2']        =   $skc_list2;
        $result['rowspanList']      =   $rowspanList;

        Flight::display("analysis/three_analysis_table.html",$result);
    }

    public static function Action_ranking_list($r){
        Flight::validateUserHasLogin();

        $result =   FrontSetting::build();
        
        $User   =   new User;
        $result['agent_list'] = $User->find("type=2",array("limit"=>1000));

        $Location       = new Location;
        $callback       = function($id) use ($Location){
            return $Location->getCurrent($id);
        };
        $Cache          = new Cache($callback);
        $result['area1']    =   $User->area1;
        $result['area1name']=   $Cache->get($User->area1, array($User->area1));

        if($User->type==1){
            $UserSlave  =   new UserSlave;
            $result['zd_id']     =   $UserSlave->get_master_uid($User->id);
        }elseif($User->type==2)
            $result['zd_id']     =   $User->id;
        $result['control']  =   "ranking_list";
        Flight::display("analysis/ranking_list.html",$result);

    }
    public static function Action_ranking_list_table($r){
        Flight::validateUserHasLogin();
        $data   =   $r->query;

        $limit      =   $data->limit ? $data->limit : 10;
        $p          =   $data->p     ? $data->p : 1;
        $view       =   $data->view  ? $data->view : 'T';
        $order      =   $data->order;
        $area1      =   $data->area1;
        $zd_id      =   $data->zd_id;
        $show_categroy =$data->show_category;
        $rank_search= addslashes($data->rank_search);

        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $medium_id      = $data->medium_id;
        $classes_id     = $data->classes_id;
        $series_id      = $data->series_id;
        $season_id      = $data->season_id;
        $wave_id        = $data->wave_id;
        $fabric_id      = $data->fabric_id;
        $price_band_id  = $data->price_band_id;
        $theme_id       = $data->theme_id;
        $brand_id       = $data->brand_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $sxz_id         = $data->sxz_id;
        $edition_id     = $data->edition_id;
        $contour_id     = $data->contour_id;

        if($style_id)       $cond[]    = "p.style_id in ({$style_id})";
        if($category_id)    $cond[]    = "p.category_id in ({$category_id})";
        if($medium_id)      $cond[]    = "p.medium_id in ({$medium_id})";
        if($classes_id)     $cond[]    = "p.classes_id in ({$classes_id})";
        if($wave_id)        $cond[]    = "p.wave_id in ({$wave_id})";
        if($series_id)      $cond[]    = "p.series_id in ({$series_id})";
        if($season_id)      $cond[]    = "p.season_id in ({$season_id})";
        if($fabric_id)      $cond[]    = "p.fabric_id in ({$fabric_id})";
        if($price_band_id)  $cond[]    = "p.price_band_id in ({$price_band_id})";
        if($brand_id)       $cond[]    = "p.brand_id in ({$brand_id})";
        if($theme_id)       $cond[]    = "p.theme_id in ({$theme_id})";
        if($nannvzhuan_id)  $cond[]    = "p.nannvzhuan_id in ({$nannvzhuan_id})";
        if($sxz_id)         $cond[]    = "p.sxz_id in ({$sxz_id})";
        if($edition_id)     $cond[]    = "p.edition_id in ({$edition_id})";
        if($contour_id)     $cond[]    = "p.contour_id in ({$contour_id})";
        if($rank_search)    $cond[]    = "(p.id in (select product_id from product_color where skc_id='{$rank_search}') or p.bianhao='{$rank_search}' or p.kuanhao='{$rank_search}')";

        $User           = new User;
        $Product        = new Product;
        $ProductColor   = new ProductColor;
        $OrderList      = new OrderList;
        $OrderListAgent = new OrderListAgent;
        $OrderListArea  = new OrderListArea;
        $OrderListProduct=new OrderListProduct;
        $OrderListProductColor=new OrderListProductColor;
        $OrderListUser  = new OrderListUser;
        $UserSlave      = new UserSlave;
        $user_id = $User->id;

        $permission_brand = $User->permission_brand;
        if($permission_brand) $cond[]  = "p.brand_id not in ({$permission_brand})";

        if($view=='ST'){
            $tablename  =   " product_color as pc left join product as p on pc.product_id=p.id 
                            left join orderlistproductcolor as opc on opc.product_id=p.id and opc.product_color_id=pc.color_id ";
            $fields     =   "p.*,pc.color_id,pc.color_code,pc.skc_id,sum(o.num) as num,sum(o.amount) as amount,opc.num as all_num";
            $group      =   "p.id,pc.color_id";
            $cond[]     =   "pc.status<>0";
            switch($User->type){
                case 2:
                    $tablename .= " left join orderlist as o on o.product_id=p.id and o.product_color_id=pc.color_id and o.zd_user_id={$user_id}";
                    $zd_id  = $user_id;
                    $area1  = $User->area1;
                    break;
                case 3:
                    if($User->username!="0"){
                        $tablename .= " left join user as u on u.ad_id={$user_id}
                                        left join orderlist as o on o.product_id=p.id and o.product_color_id=pc.color_id and o.user_id=u.id";
                    }else{
                        $tablename .= " left join orderlist as o on o.product_id=p.id and o.product_color_id=pc.color_id ";
                    }
                    break;
                default:
                    $tablename .= " left join orderlist as o on o.product_id=p.id and o.product_color_id=pc.color_id and o.user_id={$user_id}";
                    $zd_id     =   $UserSlave->get_master_uid($User->id);
                    $area1     =   $User->area1;
                    $order_rank=   1;
                    break;
            }
        }else{
            $tablename  =   " product as p left join orderlistproduct as op on p.id=op.product_id ";
            $fields     =   "p.*,op.num as all_num,sum(o.num) as num,sum(o.amount) as amount";
            $group      =   "p.id";
            $cond[]     =   "p.status<>0";
            switch($User->type){
                case 2:
                    $tablename .= " left join orderlist as o on o.product_id=p.id and o.zd_user_id={$user_id} ";
                    $zd_id  = $user_id;
                    $area1  = $User->area1;
                    break;
                case 3:
                    if($User->username!="0"){
                        $tablename .= " left join user as u on u.ad_id={$user_id}
                                        left join orderlist as o on o.product_id=p.id and o.user_id=u.id";
                    }else{
                        $tablename .= " left join orderlist as o on o.product_id=p.id ";
                    }
                    break;
                default:
                    $tablename .= " left join orderlist as o on o.product_id=p.id and o.user_id={$user_id} ";
                    $zd_id     =   $UserSlave->get_master_uid($User->id);
                    $area1     =   $User->area1;
                    $order_rank=   1;
                    break;
            }
        }

        if(!$order)  $order = " all_num DESC ";

        $options['tablename']   =   $tablename;
        $options['fields']      =   $fields;
        $options['group']       =   $group;
        $options['order']       =   $order;
        $options['limit']       =   $limit;
        $options['page']        =   $p;
        // $options['db_debug']  =  true;
        $where  = implode(" AND ", $cond);

        $CACHE_PARAMS   = $where . $fields . $_order . $p . $limit;
        $CACHE_KEY      = "RankList". md5($CACHE_PARAMS) . sizeof($CACHE_PARAMS);
        $cache_option   = $options;
        $cache          = new Cache(function() use ($Product, $where, $cache_option){
            $list       = $Product->find($where, $cache_option);
            return $list;
        }, 10);
        $list   = $cache->get($CACHE_KEY, array());
        // $list   = $Product->find($where,$options);
        if(count($list)){
            foreach ($list as &$row) {
                $product_id = $row['id'];
                $product_color_id = $row['color_id'] ? $row['color_id'] : 0;
                $params = array();
                $params['category_id'] = $row['category_id'];
                if($view=="ST"){
                    $row['global_rank'] = $OrderListProductColor->get_rank($product_id,$product_color_id);
                    if($show_categroy){
                        $row['global_rank_category'] = $OrderListProductColor->get_rank($product_id,$product_color_id,$params);
                    }
                    if($order_rank&&$row['num']){
                        $row['order_rank']  = $OrderList->get_user_product_color_rank($user_id,$row['num']);

                        if($show_categroy){
                            $row['order_rank_category']  = $OrderList->get_user_product_color_rank($user_id,$row['num'],$params);
                        }
                    }
                }else{
                    $row['global_rank'] = $OrderListProduct->get_rank($product_id);
                    if($show_categroy){
                        $row['global_rank_category'] = $OrderListProduct->get_rank($product_id,$params);
                    }
                    if($order_rank&&$row['num']){
                        $row['order_rank']  = $OrderList->get_user_product_rank($user_id,$row['num']);
                        $row['order_rank_category']  = $OrderList->get_user_product_rank($user_id,$row['num'],$params);
                    }
                }

                $row['color_list']  = $ProductColor->get_color_list($product_id);
                $i = 0;
                $row['rowspan'] =   count($row['color_list']);
                foreach ($row['color_list'] as &$value) {
                    if($i==0){
                        $value['row'] = 1;
                    }
                    switch ($User->type) {
                        case 2:
                            $value['num'] = $OrderList->findone("zd_user_id={$user_id} and product_id={$product_id} and product_color_id={$value['color_id']}",array("fields"=>"sum(num) as num"));
                            break;
                        case 3:
                            if($User->username!=0){
                                $value['num'] = $OrderList->findone("u.ad_id={$user_id} and o.product_id={$product_id} and o.product_color_id={$value['color_id']}",
                                            array("fields"=>"sum(o.num) as num",
                                                  "tablename"=>"orderlist as o left join user as u on o.user_id=u.id"));
                            }else{
                                $value['num'] = $OrderListProductColor->get_product_color_num($product_id,$value['color_id']);
                            }
                            break;                 
                        default:
                            $value['num'] = $OrderList->findone("user_id={$user_id} and product_id={$product_id} and product_color_id={$value['color_id']}",array("fields"=>"sum(num) as num"));
                            break;
                    }
                    $i++;
                }
                $row['zd_rank']  = $OrderListAgent->get_rank($zd_id,$product_id,$product_color_id);
                $row['area1_rank']  =  $OrderListArea->get_rank($area1,$product_id,$product_color_id);

                if($show_categroy){
                    $row['zd_rank_category']  = $OrderListAgent->get_rank($zd_id,$product_id,$product_color_id,$params);
                    $row['area1_rank_category']  =  $OrderListArea->get_rank($area1,$product_id,$product_color_id,$params);
                }

                if(!$row['area1_rank']){
                    $row['area1_rank'] = "";
                    $row['area1_rank_category'] = "";
                }
                if(!$row['zd_rank']){
                    $row['zd_rank'] = "";
                    $row['zd_rank_category'] = "";
                }
                if(!$row['global_rank']){
                    $row['global_rank'] = "";
                    $row['global_rank_category'] = "";
                }
            }
        }

        $Location       = new Location;
        $callback       = function($id) use ($Location){
            return $Location->getCurrent($id);
        };
        $Cache          = new Cache($callback);

        $result['zd_id']    =   $zd_id;
        $result['area1']    =   $area1;  
        if($area1){
            $result['area1name']=   $Cache->get($area1, array($area1));
        }
        if($zd_id){
            $result['zdname']   =   $User->findone("id={$zd_id}",array("fields"=>"name"));
        }
        $Company = new Company;
        $company = $Company->getData();
        $result['company'] = $company;
        $result['page']     =   $p;
        $result['start']    =   $limit * ($p-1);
        $result['view']     =   $view;
        $result['list']     =   $list;
        $result['show_categroy'] = $show_categroy;
        $result['rowspan']  = $show_categroy ? 2 : 1;

        Flight::display("analysis/ranking_list_table.html",$result);
    }

    public static function Action_dynamic_ranking($r){
        Flight::validateUserHasLogin();

        $Company = new Company;
        $company = $Company->getData();
        $result['company'] = $company;
        $result['control']  =   "dynamic_ranking";
        Flight::display("analysis/dynamic_ranking.html",$result);
    }

    public static function Action_dynamic_ranking_table($r){
        Flight::validateUserHasLogin();
        $data = $r->query;
        $limit= $data->limit? $data->limit: 40;
        $p    = $data->p    ? $data->p   : 1;
        
        $Company = new Company;
        $company = $Company->getData();
        $view = $company['rankType'] ? $company['rankType'] : "ST";
        $rankConfig = $company['rankConfig'] ? $company['rankConfig'] : "category";

        $Product = new Product;
        $User   = new User;
        $OrderListArea = new OrderListArea;
        $options =array();
        if($view == "T"){
            $options['tablename'] = " product as p left join orderlistproduct as op on p.id=op.product_id 
                                      left join products_attr as pa on p.".$rankConfig."_id=pa.keyword_id and pa.field='".$rankConfig."'";
            $options['fields']    = "p.*,op.num,op.price as all_price";
            $where = "p.status<>0";
        }else{
            $options['tablename'] = " product as p left join product_color as pc on p.id=pc.product_id left join orderlistproductcolor as opc on pc.product_id=opc.product_id and pc.color_id=opc.product_color_id
                                      left join products_attr as pa on p.".$rankConfig."_id=pa.keyword_id and pa.field='".$rankConfig."'";
            $options['fields']    = "pa.rank,p.*,pc.color_id,pc.skc_id,pc.color_code,opc.num,opc.price as all_price";
            $where = "pc.status<>0";
        }
        $options['order'] = "num DESC";
        if($company['rankConfig']) $options['order'] = "pa.rank,".$options['order'];
        $options['limit'] = $limit;
        $options['page']  = $p;
        // $options['db_debug']=true;
        $list   = $Product->find($where,$options);
        if(count($list)){
            $area1_list = $User->find("area1!=0",array("fields"=>"area1","group"=>"area1","limit"=>10000));
            $Location       = new Location;
            $callback       = function($id) use ($Location){
                return $Location->getCurrent($id);
            };
            $Cache          = new Cache($callback);
            foreach($area1_list as $area1){
                $result['area1name_list'][$area1['area1']] =   $Cache->get($area1['area1'], array($area1['area1']));
            }
            //print_r($area1_list);exit;
            $OrderListProduct = new OrderListProduct;
            $OrderListProductColor = new OrderListProductColor;
            foreach ($list as $key => &$row) {
                $product_id = $row['id'];
                $product_color_id = $row['color_id'] ? $row['color_id'] : 0;
                if($view=="T"){
                    $row['global_rank'] = $OrderListProduct->get_rank($product_id);
                }else{
                    $row['global_rank'] = $OrderListProductColor->get_rank($product_id,$product_color_id);
                }
                foreach($area1_list as $area1){
                    $area1_rank = $OrderListArea->get_rank($area1['area1'],$product_id,$product_color_id);
                    $area1_num  = $OrderListArea->get_num($area1['area1'],$product_id,$product_color_id);
                    $row['area1_rank'][$area1['area1']]  =  $area1_rank ? $area1_rank : '-';
                    $row['area1_num'][$area1['area1']]  =  $area1_num ? $area1_num : '-';
                }
            }
        }
        $result['start']= ($p-1)*$limit;
        $result['p']    = $p;
        $result['list'] = $list;
        $result['view'] = $view;
        Flight::display("analysis/dynamic_ranking_table.html",$result);
    }

    public static function Action_color_analysis($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $result['control']  = "color_analysis";
    
        Flight::display('analysis/color_analysis.html', $result);
    }

    public static function Action_color_analysis_table($r){
        $data           = $r->query;
        $t              = $data->t ? $data->t : 'color_group';
        $type           = $data->type ? $data->type : 'color';
    
        $where          = 1;
    
        $User                       = new User;
        $Factory                    = new ProductsAttributeFactory('color');
        $Factory_wave               = new ProductsAttributeFactory('wave');
        $ProductsAttrGroup          = new ProductsAttrGroup;
        $OrderList                  = new OrderList;
        $ProductsColorGroup         = new ProductsColorGroup;
    
        $color_group_list=$Factory->get_group_list();
        foreach ($color_group_list as $row){
            $color_group_hash[$row['keyword_id']]=$row['group_id'];
            $group_list[$row['group_id']]   =   $row['group_id'];
        }
    
        $rgb_hash   = $ProductsColorGroup->get_hash();
        //print_r($color_group_hash);exit;
    
        $options    = array();
        $options['tablename']= "orderlist as o left join product as p on o.product_id=p.id";
        $options['fields']   = "product_id,product_color_id,p.wave_id,num";
        $options['limit']    = 10000;
        $where               = "user_id='".$User->id."' ";
    
        $user_orderlist     = $OrderList->find($where,$options);
        //print_r($user_orderlist);exit;
        $wave_list  =   $Factory_wave->getAllList();
       
        foreach($wave_list as $row){
            $wave_hash[$row['keyword_id']]=$row['keywords']['name'];
        }        
        
        $sku_all    =   array();
        foreach ($group_list as $val){
            foreach ($user_orderlist as $oval){
                $group_id   =   $color_group_hash[$oval['product_color_id']];
                if($group_id==$val){
                    $list[$val][$oval['wave_id']]['info']['rgb']    =   $rgb_hash[$group_id]['rgb'];
                    $list[$val][$oval['wave_id']]['info']['num']    +=  $oval['num'];
                    $list[$val][$oval['wave_id']]['info']['sku_num']++;
                    $sku_all[$oval['wave_id']]['sku_all']++;
                    $num_all[$oval['wave_id']]['num_all']   += $oval['num'];
                }
            }
        }
    
        foreach ($group_list as $kg=>$vgal){
            foreach ($wave_hash as $kw=>$wval){
                    $list[$kg][$kw]['info']['sku_share'] = sprintf("%.2f",$list[$kg][$kw]['info']['sku_num']/$sku_all[$kw]['sku_all'])*100;
                    $list[$kg][$kw]['info']['num_share'] = sprintf("%.2f",$list[$kg][$kw]['info']['num']/$num_all[$kw]['num_all'])*100;
            }
        }
        $result['list']         = $list;
        $result['group_list']   = $group_list;
        $result['wave_hash']    = $wave_hash;
        $result['type']         = $type;
        
        Flight::display('analysis/color_analysis_table.html',$result);
    }
}





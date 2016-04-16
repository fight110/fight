<?php

class Control_dealer2 {
    public static function _beforeCall($r, $id=0){
        Flight::validateUserHasLogin();
        $User   = new User;
        switch ($User->type) {
            case 1 :
                if($mid = $User->mid) {
                    $u  = $User->findone("id={$mid}");
                    if($u['id']){
                        SESSION::set("user", $u);
                    }
                }else{
                    Flight::redirect("/");
                    return false;
                }
                break;
            case 3 :
                Flight::redirect("/");
                return false;
            default : 1;
        }
    }

    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $q              = $r->query->q;
        $result['q']    = $q;
        // if($q){
        //     $qt         = addslashes(trim($q));
        //     $Product    = new Product;
        //     $where      = "bianhao = '{$qt}'";
        //     $list       = $Product->find($where);
        //     if(count($list) == 1){
        //         $product_id     = $list[0]['id'];
        //         Flight::redirect("/dealer2/detail/{$product_id}");
        //         return;
        //     }
        // }

        $keys   = array('category_id','classes_id', 'wave_id','style_id','price_band_id','brand_id','series_id','theme_id','nannvzhuan_id','sxz_id','season_id', 'ordered', 'order');
        foreach($keys as $k){
            $result[$k]  = $r->query->$k;
        }
        $result['control']  = "adall";
        Flight::display("dealer2/index.html", $result);
    }

    public static function Action_detail($r, $id){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $Product        = new Product($id);
        $result['productdetail']    = $Product->getAttribute();
        if($Product->id){
            $ProductImage   = new ProductImage;
            $OrderList      = new OrderList;
            $User           = new User;
            $Keywords       = new Keywords;
            $ProductComment = new ProductComment;

            $condition      = array();
            $options        = array();
            $options['key']     = "product_id";
            $options['status']  = false;
            $options['fields_more'] = "o.product_id";
            $condition['product_id']    = $id;
            $order_all  = $OrderList->getOrderAnalysisList($condition, $options);
            $result['order_all']    = $order_all[$id];
            $condition['user_id']   = $User->id;
            $order_user = $OrderList->getDealer2OrderList($condition, $options);
            $result['order_user']   = $order_user[$id];
            $order_user_num     = $result['order_user']['num'];

            $keys   = array('style', 'classes', 'category', 'price_band', 'wave', 'series');
            foreach($keys as $key){
                $kid    = "{$key}_id";
                $result['productdetail'][$key]  = $Keywords->getName_File($result['productdetail'][$kid]);
            }


            $result['scoreinfo']    = $ProductComment->getAvgScore($id);

            $result['imagelist']    = $ProductImage->find("product_id={$id}", array());
            $result['orderlist']    = $OrderList->getSlaveOrderList($User->id, $id);
            foreach($result['orderlist'] as &$o){
                $o['color'] = $Keywords->getName_File($o['product_color_id']);
                $o['size']  = $Keywords->getName_File($o['product_size_id']);
            }
        }
        $result['control']  = "adall";

        Flight::display("dealer2/detail.html", $result);
    }

    public static function Action_orders($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        // $User       = new User;
        // $OrderList  = new OrderList;
        // $result['orderinfo']    = $OrderList->getSlaveOrderinfo($User->id);

        $result['control']  = "orders";
        Flight::display("dealer2/orders.html", $result);
    }

    public static function Action_analysis($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "analysis";
        Flight::display('dealer2/analysisall.html', $result);
    }
    public static function Action_analysisall($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $result['control']  = "analysis";
        Flight::display('dealer2/analysisall.html', $result);
    }
    public static function Action_analysis_product($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "analysis";

        Flight::display("dealer2/analysis_product.html", $result);
    }

    public static function Action_analysis_user($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "analysis";

        Flight::display("dealer2/analysis_user.html", $result);
    }

    public static function Action_summary($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "summary";

        Flight::display('dealer2/summary.html', $result);
    }

    public static function Action_orderdetailframe($r, $slave_id=''){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['slave_id'] = $slave_id;
        $result['control']  = $slave_id ? "exp" : "orderdetailframe";

        Flight::display("dealer2/orderdetailframe.html", $result);
    }

    public static function Action_orderdetail($r, $slave_id=''){
        Flight::validateUserHasLogin();

        $User           = new User;
        $OrderList      = new OrderList;
        $Keywords       = new Keywords;
        $u              = $User->getAttribute();
        $data           = $r->query;
        $key            = $data->key    ? $data->key : 'category';
        $result['key']  = $key;
        switch($key){
            case 'wave' :
                $keyword1   = "wave";
                $keyword2   = "wave_id";
                break;
            default:
                $keyword1   = $key;
                $keyword2   = "{$key}_id";
        }

        if($u['id'] && $u['type'] == 2){
            if(!is_numeric($slave_id)){
                $UserSlave  = new UserSlave;
                $master_uid     = $User->id;
                $slave_id   = $UserSlave->get_slave_user_id($u['id']);
                $name   = $User->name;
            }else{
                $slave_user = $User->findone("id=$slave_id");
                $name   = $slave_user['name'];
                $result['is_slave'] = 1;
                $result['slave_id'] = $slave_user['id'];
                $result['slave_lock_status']    = $slave_user['is_lock'];
                $rlog = new ReviewCancelLog();
                $reviewLog = $rlog->findone(' user_id="'.$slave_id.'" ');
                if($reviewLog['id']){
                    $result['hasCancelLog'] = 1;
                    $result['reviewLog'] = $reviewLog;
                }
            }
            $result['name']     = $name;
            $list       = $OrderList->get_user_orderlist_info($slave_id);
            $category   = array();
            $Factory        = new ProductsAttributeFactory('size');
            $size_group_list    = $Factory->get_group_list();
            $tmpl_array     = array();
            foreach($size_group_list as $s){
                $tmpl_array[$s['group_id']][]   = $s['keyword_id'];
            }

            $newSizeList    = array();
            $newSizeHash    = array();
            $newSizeLength  = 0;
            foreach($tmpl_array as $group_id => $ary){
                $len    = count($ary);
                if($len > $newSizeLength){
                    $newSizeLength = $len;
                }
            }
            foreach($tmpl_array as $group_id => $ary){
                for($i = 0; $i < $newSizeLength; $i++){
                    $size_id    = $ary[$i];
                    $newSizeList[$i][]  = $size_id;
                    $newSizeHash[$size_id]  = $i;
                }
            }
            $result['newSizeList']  = $newSizeList;

            foreach($list as &$row){
                $category_id    = $row[$keyword2];
                if(!$category[$category_id]){
                    $myrow  = array('category_id'=>$category_id);
                    $category[$category_id]     = $myrow;
                }

                $F_list     = preg_split('/,|:/', $row['F']);
                $mysize_list    = array_pad(array(), $newSizeLength, '');
                for($i = 0, $len = count($F_list); $i < $len; $i += 2){
                    $size_id    = $F_list[$i];
                    $size_num   = $F_list[$i+1];
                    $size_hash_num      = $newSizeHash[$size_id];
                    $mysize_list[$size_hash_num]  += $size_num;
                    $category[$category_id]['sizeinfo'][$size_hash_num] += $size_num;
                }

                $row['size_list']   = $mysize_list;
                $category[$category_id]['listing'][]    = $row;
                $category[$category_id]['price']        += $row['price'];
                $category[$category_id]['discount_price']   += $row['discount_price'];
                $category[$category_id]['num']          += $row['num'];
                $category[$category_id]['SKC']++;
                $category[$category_id]['HASH'][$row['product_id']]++;
            }

            $category   = ProductsAttributeFactory::fetch($category, $keyword1, 'category_id', 'attr');
            usort($category, function($a, $b){
                return $a['attr']['rank'] > $b['attr']['rank'];
            });

            $all_num    = 0;
            $all_price  = 0;
            $discount_price     = 0;
            foreach($category as $key => $val){
                $val['pnum']        = count($val['HASH']);
                $result['list'][]   = $val;
                $all_num            += $val['num'];
                $all_price          += $val['price'];
                $discount_price     += $val['discount_price'];
            }

            $result['all_num']      = $all_num;
            $result['all_price']    = $all_price;
            if($master_uid){
                $discount_price_info    = $OrderList->getSlaveOrderinfo($master_uid);
                $result['discount_price']    = sprintf("%.2f", $discount_price_info['discount_price']);
            }else{
                $result['discount_price']    = sprintf("%.2f", $discount_price);
            }

            $result['size_num']     = $newSizeLength;

            $Company        = new Company;
            $result['company']  = $Company->getData();
        }
        $result['u']    = $u;
        $result['category_array']=array('kuanhao'=>'款号','wave'=>'波段','category'=>'大类','theme'=>'主题','style'=>'款别','brand'=>'品牌','season'=>'季节','series'=>'系列');
        Flight::display("dealer2/orderdetail.html", $result);
    }


    public static function Action_exp($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $data       = $r->query;
        $byZongdai  = 1;
        $User       = new User;
        $OrderList  = new OrderList;
        $master_uid = $User->id;

        if($byZongdai){
            $options    = array('master_uid'=>$master_uid);
            $exp_list   = $User->get_zongdai_exp_order_list($options);
            $result['exp_list'] = $exp_list;
        }

        $exp        = $User->get_exp_info($options);
        $ord        = $OrderList->get_order_info($options);
        $result['exp']  = $exp;
        $result['ord']  = $ord;
        $result['exp_num_percent']      = $exp['exp_num']       ? sprintf('%.2f%%', $ord['num'] / $exp['exp_num'] * 100) : "-";
        $result['exp_price_percent']    = $exp['exp_price']     ? sprintf('%.2f%%', $ord['discount_price'] / $exp['exp_price'] * 100) : "-";

        $result['control']  = "exp";
        $result['area1']    = $area1;
        $result['area2']    = $area2;
        $result['byArea']   = $byArea;
        $result['byZongdai']    = $byZongdai;
        $result['master_uid']   = $master_uid;

        Flight::display("dealer2/exp.html", $result);
    }

    public static function Action_hpgc($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "hpgc";

        Flight::display('dealer2/hpgc.html', $result);
    }

    public static function Action_store($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $data       = $r->query;
        $order_key  = $data->order_key ? $data->order_key : "kuanhao";
        $order_ud   = $data->order_ud;
    
        $params     = array();
        $keys   = array("wave_id", "category_id", "style_id", "series_id", "classes_id", "brand_id");
        foreach($keys as $key){
            $val    = $data->$key;
            $result[$key]   = $val;
            $params[$key]   = $val;
        }
    
        $User           = new User;
        $UserProduct    = new UserProduct;
        $unum           = $User->getCount("type=1");
        $store_list     = $UserProduct->get_store_group_list($params);
        $list           = array();
        $hash           = array();
        foreach($store_list as $store){
            $rateval    = $store['rateval'];
            $bianhao    = $store['bianhao'];
            $hash[$bianhao]['data']     = $store;
            $hash[$bianhao][$rateval]   = $store;
            $hash[$bianhao]['score']    += $store['rateval'] * $store['num'];
            $hash[$bianhao]['unum']     += $store['num'];
            $hash[$bianhao]['kuanhao']  = $store['kuanhao'];
            $hash[$bianhao]['bianhao']  = $store['bianhao'];
        }
        foreach($hash as $bianhao => $store){
            $store['avg']   = sprintf("%.1f", $store['score'] / $store['unum']);
            $store['upercent']  = sprintf("%.1f", $store['unum'] / $unum * 100);
            $store['upercent_num']  = $store['unum'] / $unum * 100;
            $list[] = $store;
        }
    
        usort($list, function($a, $b) use ($order_key, $order_ud){
            if(!$order_ud){
                return $a[$order_key] > $b[$order_key];
            }else{
                return $a[$order_key] < $b[$order_key];
            }
        });
    
            $result['list']     = $list;
            $result['unum']     = $unum;
            $result['order_key']    = $order_key;
            $result['order_ud']     = $order_ud;
    
            $result['control']  = 'store';
    
            Flight::display("ad/store.html", $result);
    }
    
    public static function Action_wrongorders($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $result['control']  = "wrongorders";
    
        Flight::display('dealer2/wrongorders.html', $result);
    }
    
    public static function Action_filter($r) {
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $User       = new User;
        $Location   = new Location;
        $result['area1_list']    = $Location->getChildren(0);
        $result['ad_list']       = $User->get_ad_list();
    
        $result['control']  = "filter";
        Flight::display("dealer2/filter.html", $result);
    }
    
    public static function Action_ordersview($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $result['control']  = "ordersview";
        $result['category_array']=array('kuanhao'=>'款号','wave'=>'波段','category'=>'大类','theme'=>'主题','style'=>'款别','brand'=>'品牌','season'=>'季节','series'=>'系列');
        Flight::display('dealer2/ordersview.html', $result);
    }
    
    public static function Action_myorders_summary($r){
        Flight::validateUserHasLogin();
        $data       = $r->query;
        $result     = array();
        $condition  = array();
        $options    = array();
        $keys       = array('category_id', 'style_id', 'classes_id', 'wave_id', 'series_id', 'price_band_id', 'season_id', 'brand_id');
        foreach($keys as $key){
            $val    = $data->$key;
            if($val){
                $condition[]    = "{$key}={$val}";
                $options[$key]  = $val;
            }
        }
        if(isset($data->color_status)&&is_numeric($data->color_status)){
            $options['color_status'] =  $data->color_status;
        }
        $Product    = new Product;
        $condition[]    = "status=1";
        $where      = implode(' AND ', $condition);
        $productCount           = $Product->getCount($where);
        $SKC_ALL            = $Product->get_SKC($options);
    
        $OrderList  = new OrderList;
        $User       = new User;
        //list($rank, $orderinfo) = $OrderList->getRank($User->id, $options);
        $orderinfo = $OrderList->getZDBySkcStatus($User->id, $options);
        if($orderinfo['skc'])     $result['depth']    = sprintf("%d", $orderinfo['num'] / $orderinfo['skc']);
        if($SKC_ALL) $result['width']    = sprintf("%d%%", $orderinfo['skc'] / $SKC_ALL * 100);
        $result['SKC_ALL']  = $SKC_ALL;
        $exp_info   = $User->get_slave_exp_info($User->id);
        
        if($exp_info['exp_num']){
            $orderinfo['percent_exp_num']   = sprintf("%d%%", $orderinfo['num'] / $exp_info['exp_num'] * 100);
        }
        if($exp_info['exp_pnum']){
            $orderinfo['percent_exp_pnum']  = sprintf("%d%%", $orderinfo['pnum'] / $exp_info['exp_pnum'] * 100);
        }
        if($exp_info['exp_price']){
            $orderinfo['percent_exp_price'] = sprintf("%d%%", $orderinfo['discount_price'] / $exp_info['exp_price'] * 100);
        }
        if($productCount){
            $orderinfo['percent_pnum']  = sprintf("%d%%", $orderinfo['pnum']/ $productCount * 100);
        }
        $result['orderinfo']    = $orderinfo;
        $result['user']         = $User->getAttribute();
        $result['user']['exp_num'] = $exp_info['exp_num'];
        $result['user']['exp_pnum'] = $exp_info['exp_pnum'];
        $result['user']['exp_price'] = $exp_info['exp_price'];
        $result['productCount'] = $productCount;
    
        Flight::display('dealer2/myorders_summary.html', $result);
    }
    
    public static function Action_exp_print($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $condition  = array();
        $User       = new User;
        $condition['mid'] = $User->id;
        $result['printInfo'] = $User->get_print_info($condition);
    
        $result['control']  = "exp_print";
    
       
        Flight::display("dealer2/exp_print.html", $result);

    
    }
    public static function Action_exp_print_new2($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
        
        $User       = new User;
        
        $Factory  = new ProductsAttributeFactory('property');
        $result['property_list']    = $Factory->getAllList();
        
        $result['control']  = "exp_print_new2";
    
        Flight::display("dealer2/exp_print_new2.html", $result);
    
    }
}

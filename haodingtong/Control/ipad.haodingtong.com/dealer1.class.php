<?php

class Control_dealer1 {
    public static function _beforeCall($r, $id=0){
        Flight::validateUserHasLogin();
        $User   = new User;
        switch ($User->type) {
            case 2 :
                // $username = $_COOKIE[SESSION_LASTUNAME];
                // $u  = $User->findone("username='$username'");
                $mid    = $User->id;
                $u  = $User->findone("mid={$mid}");
                if($u['id']){
                    SESSION::set("user", $u);
                }
                break;
            case 3 :
                Flight::redirect("/");
                return false;
                break;
            default : 1;
        }
    }

    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $q              = $r->query->q;
        $result['q']    = $q;

        $keys   = array('category_id','medium_id','classes_id','edition_id','contour_id', 'wave_id','style_id','price_band_id','brand_id','series_id','theme_id','nannvzhuan_id','sxz_id','season_id','area1', 'area2', 'ordered', 'order', 'view', 'orderType');
        foreach($keys as $k){
            $result[$k]  = $r->query->$k;
        }

        $c              = $r->query->c;
        $tType          = $r->query->tType;
        if($c){
            $result['control']  = $c;
        }
        if($tType){
            $result['control']  = $tType;
        }
        $result['tType']    = $tType;

        Flight::display('dealer1/index.html', $result);
    }


    public static function Action_group($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "group";
        $result['search_f'] = "group";
        
        $ProductGroup   =   new ProductGroup();
        $result['dp_type_list'] =   $ProductGroup->get_dp_type_list();

        Flight::display('dealer1/group.html', $result);
    }

    public static function Action_groupdetail($r, $id){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $ProductGroup       = new ProductGroup($id);
        if($ProductGroup->id){
            $result['group']    = $ProductGroup->getAttribute();
            $ProductGroupImage  = new ProductGroupImage;
            $ProductGroupMember = new ProductGroupMember;
            $result['imagelist']    = $ProductGroupImage->find("group_id={$ProductGroup->id}", array("limit"=>100));
            $result['plist']        = $ProductGroupMember->getGroupMember($id, true);
        }

        $result['control']  = "group";
        $result['search_f'] = "group";

        Flight::display("dealer1/groupdetail.html", $result);
    }

    public static function Action_orderon($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "orderon";
        $result['ordered']  = "on";

        Flight::display('dealer1/orderon.html', $result);
    }

    public static function Action_orderoff($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "orderoff";
        $result['ordered']  = "off";

        Flight::display('dealer1/orderoff.html', $result);
    }

    public static function Action_orderunactive($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "orderunactive";
        $result['ordered']  = "unactive";

        Flight::display('dealer1/orderunactive.html', $result);
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
            $condition['user_id']   = $User->id;
            $order_user = $OrderList->getOrderAnalysisList($condition, $options);
            $result['order_user']   = $order_user[$id];
            $order_user_num     = $result['order_user']['num'];
            $OrderListProduct   = new OrderListProduct;
            // $result['rank']     = $OrderListProduct->get_rank($id);
            // $area1  = $User->area1;
            // if($area1){
            //     $result['area_rank']   = $OrderList->get_area_rank($id, $area1);
            // }
            // if($User->mid){
            //     $master_uid             = $User->mid;
            // }else{
            //     $UserSlave              = new UserSlave;
            //     $master_uid             = $UserSlave->get_master_uid($User->id);
            // }
            // if($master_uid){
            //     $result['master_rank'] = $OrderList->get_master_rank($id, $master_uid);
            // }

            $keys   = array('style', 'classes', 'category', 'price_band', 'wave', 'series', 'theme', 'season');
            foreach($keys as $key){
                $kid    = "{$key}_id";
                $result['productdetail'][$key]  = Keywords::cache_get($result['productdetail'][$kid]);
            }

            $ProductColor   = new ProductColor;
            $color_list     = $ProductColor->get_color_list($id);
            $result['color_list']   = $color_list;

            $ProductSize    = new ProductSize;
            $size_list      = $ProductSize->get_size_list($id);
            $result['size_list']    = $size_list;
            $result['size_count']   = count($size_list);

            $result['imagelist']    = $ProductImage->find("product_id={$id}", array());
            if($result['productdetail']['is_need']==1 && $order_user_num < 1){
                $result['unorder']  = true;
            }

            if($User->user_level){
                $Moq    = new Moq;
                $moq    = $Moq->findone("product_id={$id} AND keyword_id={$User->user_level}");
                $result['moq']  = $moq;
            }

            $UserProduct    = new UserProduct;
            $result['is_store'] = $UserProduct->is_store($User->id, $id);
            $store_info     = $UserProduct->get_store_info($User->id, $id);
            $result['is_store'] = $store_info['id'];
            $result['store_rateval']    = $store_info['rateval'];

            $UserDiscount   = new UserDiscount();
            $discount  = $UserDiscount->get_discount($User->id, $Product->category_id);
            if(!$discount){
                $discount   = $User->discount;
            }
            $result['discount_price']   = $Product->price * $discount;

            $result['has_permission_brand'] = $User->has_permission_brand($Product->brand_id);
            //获取用户评论
            $product_comment = $ProductComment->get_product_comment($User->id, $Product->id);
            $result['product_comment'] = $product_comment;
            // 促销政策
            $ProductOrder_Perferential  = new ProductOrder_Perferential($id);
            $result['perf_list']        = $ProductOrder_Perferential->perf_list;
        }
        $Company = new Company();
        $result['company']  =   $Company->getData();
        Flight::display('dealer1/detail.html', $result);
    }

    public static function Action_myorders($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "myorders";

        Flight::display('dealer1/myorders.html', $result);
    }
    public static function Action_ordersview($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $result['control']  = "ordersview";
        $result['category_array']=array('kuanhao'=>'款号','wave'=>'波段','category'=>'大类','classes'=>'小类','theme'=>'主题','style'=>'款别','brand'=>'品牌','season'=>'季节','series'=>'系列');
        Flight::display('dealer1/ordersview.html', $result);
    }
    public static function Action_myorders_summary($r){
        Flight::validateUserHasLogin();
        $data       = $r->query;
        $result     = array();
        $condition  = array();
        $options    = array();
        $keys   = array('category_id','medium_id','classes_id','edition_id','contour_id', 'wave_id','style_id','price_band_id','brand_id','series_id','theme_id','nannvzhuan_id','sxz_id','season_id');
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
        $User       = new User;
        $isspot     = $User->current_isspot;
        if($isspot){
            $options['isspot'] = $isspot;
            $condition[] = "isspot={$isspot}"; 
        }
        $condition[]    = "status=1";
        $where      = implode(' AND ', $condition);
        $productCount           = $Product->getCount($where);
        $SKC_ALL            = $Product->get_SKC($options);

        $OrderList  = new OrderList;
        //list($rank, $orderinfo) = $OrderList->getRank($User->id, $options);
        list($rank, $orderinfo) = $OrderList->getRankBySkcStatus($User->id, $options);
        if($orderinfo['skc'])     $result['depth']    = sprintf("%d", $orderinfo['num'] / $orderinfo['skc']);
        if($SKC_ALL) $result['width']    = sprintf("%d%%", $orderinfo['skc'] / $SKC_ALL * 100);
        $result['SKC_ALL']  = $SKC_ALL;

        if($User->exp_num){
            $orderinfo['percent_exp_num']   = sprintf("%d%%", $orderinfo['num'] / $User->exp_num * 100);
        }
        if($User->exp_pnum){
            $orderinfo['percent_exp_pnum']  = sprintf("%d%%", $orderinfo['pnum'] / $User->exp_pnum * 100);
        }
        if($User->exp_price){
            $orderinfo['percent_exp_price'] = sprintf("%d%%", $orderinfo['discount_price'] / $User->exp_price * 100);
        }
        if($productCount){
            $orderinfo['percent_pnum']  = sprintf("%d%%", $orderinfo['pnum']/ $productCount * 100);
        }
        $result['orderinfo']    = $orderinfo;
        $result['user']         = $User->getAttribute();
        $result['productCount'] = $productCount;

        Flight::display('dealer1/myorders_summary.html', $result);
    }

    public static function Action_myorderstop($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $User       = new User;
        $Product    = new Product;
        $result['productCount'] = $Product->getCount("");

        $OrderList          = new OrderList;
        $OrderListUser      = new OrderListUser;
        $user_first_price   = $OrderListUser->findone("", array("fields"=>"max(price) as price"));
        $user_first_num     = $OrderListUser->findone("", array("fields"=>"max(num) as num"));
        $user_first_pnum    = $OrderListUser->findone("", array("fields"=>"max(pnum) as pnum"));
        $result['orderinfo_price_first']    = $user_first_price;
        $result['orderinfo_num_first']      = $user_first_num;
        $result['orderinfo_pnum_first']     = $user_first_pnum;

        // $Product            = new Product;
        $SKC_ALL            = $Product->get_SKC();
        $result['depth']    = sprintf("%d", $result['orderinfo']['num'] / $result['orderinfo']['sku']);
        $result['width']    = sprintf("%d%%", $result['orderinfo']['sku'] / $SKC_ALL * 100);
        $result['SKC_ALL']  = $SKC_ALL;

        $product_list2      = $OrderList->getOrderMyProductTopList($User->id, 3, "price");
        foreach($product_list2 as &$product2){
            $product2['percent_num']    = sprintf("%d%%", $product2['num']/$result['orderinfo']['num'] * 100);
            $product2['percent_price']  = sprintf("%d%%", $product2['price']/$result['orderinfo']['price'] * 100);
        }
        $len2   = count($product_list2);
        while($len2 < 3){
            $product_list2[]    = array();
            $len2++;
        }
        $result['product_list2']    = $product_list2;

        $product_list1      = $OrderList->getOrderMyProductTopList($User->id, 3, "num");
        foreach($product_list1 as &$product1){
            $product1['percent_num']    = sprintf("%d%%", $product1['num']/$result['orderinfo']['num'] * 100);
            $product1['percent_price']  = sprintf("%d%%", $product1['price']/$result['orderinfo']['price'] * 100);
        }
        $len1   = count($product_list1);
        while($len1 < 3){
            $product_list1[]    = array();
            $len1++;
        }
        $result['product_list1']    = $product_list1;

        $result['control']  = "myorderstop";

        Flight::display('dealer1/myorderstop.html', $result);
    }

    public static function Action_analysis($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();
        $result['control']  = "analysis";

        Flight::display('dealer1/analysis.html', $result);
    }

    public static function Action_hpgc($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "hpgc";

        Flight::display('dealer1/hpgc.html', $result);
    }

    public static function Action_exp_complete($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "exp_complete";

        Flight::display('dealer1/exp_complete.html', $result);
    }

    public static function Action_analysis_product($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "analysis";

        Flight::display("dealer1/analysis_product.html", $result);
    }

    public static function Action_budget($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $data       = $r->query;
        $t          = $data->t  ? $data->t  : 'series';

        $User   = new User;
        $Rule   = new Rule;
        $Budget         = new Budget;
        $BudgetCount    = new BudgetCount;
        $RuleHash   = $Rule->getUserRule($User->id, $t, array('key'=>'keyword_id'));
        $BudgetHash = $Budget->find("user_id={$User->id} AND field='{$t}'", array('limit'=>100, 'key'=>'keyword_id'));
        $result['budget']   = $BudgetCount->getBudget($User->id);

        $Factory    = new ProductsAttributeFactory($t);
        $list       = $Factory->getAllList();
        foreach($list as &$row){
            $row['rule']    = $RuleHash[$row['keyword_id']];
            $row['budget']  = $BudgetHash[$row['keyword_id']];
        }

        $result['list']     = $list;
        $result['t']        = $t;
        $result['control']  = "budget";

        Flight::display('dealer1/budget.html', $result);
    }

    public static function Action_budget_save($r){
        Flight::validateUserHasLogin();

        $data       = $r->data;
        $budget     = $data->budget;
        if($budget){
            $User           = new User;
            $Budget         = new Budget;
            $BudgetCount    = new BudgetCount;
            $BudgetCount->create(array('user_id'=>$User->id, 'budget'=>$budget))->insert(true);
            $field          = $data->field;
            if($field){
                foreach($data as $key => $percent){
                    if(preg_match('/^budget_(\d+)$/', $key, $matches)){
                        $keyword_id     = $matches[1];
                        $Budget->create(array('keyword_id'=>$keyword_id, 'user_id'=>$User->id, 'field'=>$field, 'percent'=>$percent))->insert(true);
                    }
                }
            }
            $result['valid']    = true;
        }else{
            $result['valid']    = false;
            $result['message']  = "总预算为空";
        }

        Flight::json($result);
    }

    public static function Action_summary($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "summary";

        Flight::display('dealer1/summary.html', $result);
    }

    public static function Action_display($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "display";
        $result['search_f'] = "display";

        Flight::display('dealer1/display.html', $result);
    }

    public static function Action_displaydetail($r, $id){
        Flight::validateUserHasLogin();

        $result                 = FrontSetting::build();

        $ProductDisplay         = new ProductDisplay($id);
        if($ProductDisplay->id){
            $result['display']      = $ProductDisplay->getAttribute();
            $ProductDisplayImage    = new ProductDisplayImage;
            $ProductDisplayMember   = new ProductDisplayMember;
            $result['imagelist']    = $ProductDisplayImage->find("display_id={$ProductDisplay->id}", array("limit"=>100));
            // $result['plist']        = $ProductDisplayMember->getDisplayMember($id, true);
            $plist                  = $ProductDisplayMember->getDisplayMember($id, true);
            $plist2 = array();
            $plist1 = array();
            $plist0 = array();
            foreach($plist as $row){
                if($row['rank'] == 2) $plist2[]   = $row;
                if($row['rank'] == 1) $plist1[]   = $row;
                if($row['rank'] == 0) $plist0[]   = $row;
            }
            $result['plist2']   = $plist2;
            $result['plist1']   = $plist1;
            $result['plist0']   = $plist0;
        }

        $result['control']  = "display";
        $result['search_f'] = "display";

        Flight::display("dealer1/displaydetail.html", $result);
    }

    public static function Action_store($r){
        Flight::validateUserHasLogin();

        $result             = FrontSetting::build();
        $UserProduct        = new UserProduct;
        $User               = new User;
        $storeinfo          = $UserProduct->get_store_group_info($User->id);
        foreach($storeinfo as $val){
            $result["star{$val['rateval']}"]   = $val['num'];
            $result['totalnum'] += $val['num'];
        }


        $result['control']  = "store";

        Flight::display("dealer1/store.html", $result);
    }

    public static function Action_notice($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = 'notice';

        Flight::display("dealer1/notice.html", $result);
    }

    public static function Action_orderdetailframe($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = 'orderdetailframe';
        //$result['control']  = 'myorders';


        Flight::display("dealer1/orderdetailframe.html", $result);
    }

    public static function Action_orderdetail($r){
        Flight::validateUserHasLogin();

        $User           = new User;
        $OrderList      = new OrderList;
        $Keywords       = new Keywords;
        $u              = $User->getAttribute();
        $key            = $r->query->key;
        $result['key']  = $key;
        switch($key){
            case 'wave' :
                $keyword1   = 'wave';
                $keyword2   = "wave_id";
                break;
            default:
                $keyword1   = $key;
                $keyword2   = "{$key}_id";
        }
        if($u['id']){
            $list       = $OrderList->get_user_orderlist_info($u['id']);
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
            $result['discount_price']    = sprintf("%.2f", $discount_price);

            $result['size_num']     = $newSizeLength;

            $Company        = new Company;
            $result['company']  = $Company->getData();
        }
        $result['u']    = $u;

        $result['category_array']=array('kuanhao'=>'款号','wave'=>'波段','category'=>'大类','theme'=>'主题','style'=>'款别','brand'=>'品牌','season'=>'季节','series'=>'系列');
        Flight::display("dealer1/orderdetail.html", $result);
    }

    public static function Action_wrongorders($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "wrongorders";

        Flight::display('dealer1/wrongorders.html', $result);
    }

    public static function Action_set_lite($r){
        Flight::validateUserHasLogin();
        SESSION::set(STRING_HDT_LITE, !SESSION::get(STRING_HDT_LITE));
        Flight::redirect($r->referrer);
    }

    public static function Action_show_room($r, $room_id){
        Flight::validateUserHasLogin();
        $Company    = new Company;
        $show       = $Company->show;
        $show_id    = $show[$room_id];
        $returl     = $show_id  ? "/dealer1/show/{$show_id}" : "/dealer1/show/?room_id={$room_id}";
        Flight::redirect($returl);
    }

    public static function Action_show($r, $show_id=0){
        $Company            = new Company;
        $data               = $r->query;
        $room_id            = $data->room_id;
        $current_show_id    = $Company->get_show_id($room_id);
        if($show_id == 0 && $current_show_id > 0){
            Flight::redirect("/dealer1/show/{$current_show_id}");
            exit;
        }

        Flight::validateUserHasLogin();

        $result         = FrontSetting::build();
        $User           = new User;
        $Product        = new Product;
        $ProductShow    = new ProductShow;
        $ProductImage   = new ProductImage;
        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $ProductGroup   = new ProductGroup;
        $UserProduct    = new UserProduct;

        if($show_id){
            $show       = $ProductShow->findone("id={$show_id}");
            $user_id    = $User->id;
            if($show['product_ids']){
                $list   = $Product->find("id in ({$show['product_ids']})");
                foreach($list as &$product){
                    $product_id     = $product['id'];
                    $product['imagelist']   = $ProductImage->find("product_id={$product_id}");
                    $product['color_list']  = $ProductColor->get_color_list($product_id);
                    $product['size_list']   = $ProductSize->get_size_list($product_id);
                    $product['size_count']  = count($product['size_list']);
                    $product['grouplist']   = $ProductGroup->getGroupListByProductId($product_id, array('limit'=>3));
                    $store_info             = $UserProduct->get_store_info($user_id, $product_id);
                    $product['is_store']    = $store_info['id'];
                    $product['store_rateval']   = $store_info['rateval'];
                }
                $result['list'] = $list;
            }
            $room_id        = $show['room_id'];
            if(!$current_show_id){
                $current_show_id    = $Company->get_show_id($room_id);
            }
            $result['show_list']    = $ProductShow->get_show_list($current_show_id, $room_id);
        }

        $result['control']  = "show_{$room_id}";
        $result['show_id']  = $show_id;
        $result['room_id']  = $room_id;
        $result['current_show_id']  = $current_show_id;

        Flight::display("dealer1/show.html", $result);
    }

    public static function Action_agree_notice($r){
        Flight::validateUserHasLogin();

        $User   = new User;
        $User->update(array('is_read'=>1), "id={$User->id}");
        $u      = SESSION::get('user');
        $u['is_read']   = 1;
        SESSION::set('user', $u);

        Flight::json(array('valid'=>true));
    }

    public static function Action_lock_order($r, $user_id=null){
        Flight::validateUserHasLogin();

        $User   = new User;
        if($user_id === null){
            $User->update(array('is_lock'=>1), "id={$User->id}");
            $u      = SESSION::get('user');
            $u['is_lock']   = 1;
            SESSION::set('user', $u);
        }elseif(is_numeric($user_id)){
            $lock   = $r->query->lock;
            if(!is_numeric($lock)){
                $lock   = 1;
            }
            $User->update(array('is_lock'=>$lock), "id={$user_id}");
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_history($r){
        Flight::validateUserHasLogin();

        $result         = FrontSetting::build();

        $User               = new User;
        $OrderListHistory   = new OrderListHistory;
        $result['list']     = $OrderListHistory->getOrderListHistoryAnalysis($User->id, 0, $User->mid);

        $result['control']  = "history";

        Flight::display("dealer1/history.html", $result);
    }

    public static function Action_fabric($r){
        Flight::validateUserHasLogin();

        $result         = FrontSetting::build();

        $Product        = new Product;
        $Factory        = new ProductsAttributeFactory('fabric');
        $fabriclist     = $Factory->getAllList();
        $fabricCount    = $Product->find("status=1", array("limit"=>1000, "fields"=>"fabric_id, COUNT(*) as num", "key"=>"fabric_id", "group"=>"fabric_id"));
        foreach($fabriclist as &$row){
            $fabric_id  = $row['keyword_id'];
            $row['num'] = $fabricCount[$fabric_id]['num'];
        }
        $result['fabriclist']   = $fabriclist;

        $result['control']  = 'fabric';

        Flight::display("dealer1/fabric.html", $result);
    }

    public static function Action_zt_img ($r, $d) {
        Flight::validateUserHasLogin();
        $result         = FrontSetting::build();

        $dir    = DOCUMENT_ROOT . 'zt/' . $d;

        if ($handle = opendir($dir)) {
            $list   = array();
            while (false !== ($file = readdir($handle))) {
                if($file != "." && $file != ".."){
                    $list[] = iconv("GBK", "UTF-8", $file);
                }
            }
            closedir($handle);
        }
        usort($list, function($a, $b){
            return $a > $b;
        });

        $result['list'] = $list;
        $result['dir']  = $d;
        $result['control']  = "zt_{$d}";

        Flight::display("dealer1/zt_img.html", $result);

    }

    public static function Action_display_new($r,$display_id){
        Flight::validateUserHasLogin();
        $data = $r->query;
        $result     = FrontSetting::build();

        $result['control']  = "display_new";
        $result['search_f'] = "group_display";
        
        if($data->q){
            $result['q'] = $data->q;
            $result['t'] = $data->t;
            Flight::display('dealer1/display_new_search.html', $result);
            exit;       
        }

        $pDisplay = new ProductDisplay();
        $allDisplay = $pDisplay->find('status=1',array('limit'=>1000,'fields'=>'id,name,defaultimage,bianhao'));
        $currentDid=(is_numeric($display_id)&&$display_id)?$display_id:$allDisplay[0]['id'];

        $result['allDisplay']  = $allDisplay;
        $result['maxWidth'] = sizeof($allDisplay)*100;
        $result['currentDid']  = $currentDid;
        Flight::display('dealer1/display_new.html', $result);
    }

    public static function Action_display_group($r){
        Flight::validateUserHasLogin();
        $data = $r->query;
        $display_id = $data->did;
        if(is_numeric($display_id)&&$display_id){
            $currentDid=$display_id;
        }else{
            return false;
        }
        $pDisplay = new ProductDisplay();
        $nowDisplay = $pDisplay->findone('status=1 AND id="'.$currentDid.'" ');
        if(!sizeof($nowDisplay)){
            echo 'none';
            exit;
        }      
        $gtp=new GroupToDisplay();
        $odetail = new OrderListDetail();
        $option['page'] = $data->p;
        $option['limit'] = 6;
        $User = new User();      
        $groupMembers=$gtp->getGroupMember($currentDid,$option);
        //print_r($groupMembers);
        foreach($groupMembers as $gkey=>$gval){
            $orderInfo = $odetail->getUserDisplayOrder($currentDid,$User->id,$gval['group_id']);
            $groupMembers[$gkey]['ordernum'] = $orderInfo[$gval['group_id']]['num'];
            //$groupMembers['ordernum'] = $odetail->getUserDisplayOrder($currentDid,$User->id);
        }
        
       // print_r($groupMembers);
        
        //print_r($groupMembers);
        $result['groupMembers']  = $groupMembers;
        $result['currentDid']  = $currentDid;
        $result['orderInfo']  = $orderInfo;
        Flight::display('dealer1/display_group.html', $result);
    }
    
    public static function Action_display_group_search($r){
        Flight::validateUserHasLogin();
        $data = $r->query;
        $q = $data->q;
        $t = $data->t;
        if($q){
        $option['page'] = $data->p;
        $option['limit'] = 6;
        $odetail = new OrderListDetail();
        $gtp=new GroupToDisplay();
        $list = $gtp->getDisplayBySearch($q,$t,$option);
        $User = new User();
        foreach($list as &$row){
            $orderInfo = $odetail->getUserDisplayOrder($row['did'],$User->id,$row['gid']);
            $row['num'] = $orderInfo[$row['gid']]['num'];
        }
        //print_r($list);
        $result['list']=$list;
        Flight::display('dealer1/display_group_search.html', $result);
        }else{
            Flight::redirect("/dealer1/display_new");
        }
    }

    public static function Action_groupdetailnew($r, $id){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $ProductGroup       = new ProductGroup($id);
        if($ProductGroup->id){
            $result['group']    = $ProductGroup->getAttribute();
            $ProductGroupImage  = new ProductGroupImage;
            $ProductGroupMember = new ProductGroupMember;
            $result['imagelist']    = $ProductGroupImage->find("group_id={$ProductGroup->id}", array("limit"=>100));
            $result['plist']        = $ProductGroupMember->getGroupMember($id, true);
            if($r->query->did){
                $productDis=new ProductDisplay();
                $currentDisplay=$productDis->findone('id="'.$r->query->did.'"');
                if($currentDisplay['status']!=1){
                    Flight::redirect("/dealer1/display_new/");
                }
                $result['currentDisplay']  = $currentDisplay;
            }
        }

        $result['control']  = "display_new";
        $result['search_f'] = "group_display";

        Flight::display("dealer1/groupdetailnew.html", $result);
    }

    public static function Action_analysisall($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();
        $result['control']  = "analysisall";

        //$locationStr = $result['user']['area1'].','.$result['user']['area2'];
        //$location = new Location();
        // = $location->getLocationList('id in ('.$locationStr.') ',array('key'=>'id','fields'=>'id,name'));
        //$result['locationInfo']['area1']  = $locationInfo[$result['user']['area1']];
        //$result['locationInfo']['area2']  = $locationInfo[$result['user']['area2']];

        Flight::display('dealer1/analysisall.html', $result);
    }
    
    
    public static function Action_get_group_id_by_bianhao($r){
        $ProductGroup    = new ProductGroup();
        $data       = $r->query;
        $bianhao    = $data->bianhao;
        $f          = $data->f;
        if(is_numeric($bianhao)){
            $options    = array();
            $condition  = array();
            if($f == "up"){
                $condition[]    = "id<{$bianhao}";
                $options['order']   = "id DESC";
                $_message       = "已经是第一个搭配了";
            }elseif($f == "down"){
                $condition[]    = "id>{$bianhao}";
                $options['order']   = "id ASC";
                $_message       = "已经是最后一个搭配了";
            }else{
                $condition[]    = "id={$bianhao}";
                $_message       = "未找到该搭配";
            }
            // $options['db_debug']    = true;
            $where      = implode(' AND ', $condition);
            $productgroup    = $ProductGroup->findone($where, $options);
            if(!$productgroup['id']){
                $message = $_message;
            }
        }
        $result['message']  = $message;
        $result['group']  = $productgroup;
    
        Flight::json($result);
    }
    
    public static function Action_get_display_id_by_bianhao($r){
        $ProductDisplay    = new ProductDisplay();
        $data       = $r->query;
        $bianhao    = $data->bianhao;
        $f          = $data->f;
        if(is_numeric($bianhao)){
            $options    = array();
            $condition  = array();
            if($f == "up"){
                $condition[]    = "id<{$bianhao}";
                $options['order']   = "id DESC";
                $_message       = "已经是第一个搭配了";
            }elseif($f == "down"){
                $condition[]    = "id>{$bianhao}";
                $options['order']   = "id ASC";
                $_message       = "已经是最后一个搭配了";
            }else{
                $condition[]    = "id={$bianhao}";
                $_message       = "未找到该搭配";
            }
            // $options['db_debug']    = true;
            $where      = implode(' AND ', $condition);
            $productdisplay    = $ProductDisplay->findone($where, $options);
            if(!$productdisplay['id']){
                $message = $_message;
            }
        }
        $result['message']  = $message;
        $result['display']  = $productdisplay;
    
        Flight::json($result);
    }

    public static function Action_zt ($r) {
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $filetype_list  = array("jpg", "JPG", "png", "PNG", "gif", "GIF", "jpeg", "JPEG");
        $Company    = new Company;
        $dir    = DOCUMENT_ROOT . 'tmpl/images/zt/' . $Company->zt_dir;

        if ($handle = opendir($dir)) {
            $list   = array();
            while (false !== ($file = readdir($handle))) {
                if($file != "." && $file != ".."){
                    $name_list  = explode(".", $file);
                    $extend     = $name_list[count($name_list) - 1];
                    if(in_array($extend, $filetype_list)){
                        $list[] = iconv("GBK", "UTF-8", $file);
                    }
                }
            }
            closedir($handle);
        }
        usort($list, function($a, $b){
            return $a > $b;
        });

        $result['list'] = $list;

        $result['control']  = "zt";
        Flight::display('dealer1/zt.html', $result);
    }
    
    public static function Action_commit_order_status($r){
        $message = '订单提交失败!';
        Flight::validateUserHasLogin();
        $user = new User();
        $result['error']='false';
        if($user->id){
            $keywords = new Keywords();
            $product = new Product();
            $ProductColor = new ProductColor;
            $orderlist = new OrderList();
            $company = new Company();
            $comInfo = $company->getData();
            if($comInfo['check_order_need']){
            //$kid = $keywords->getKeywordId(STRING_BIDINGKUAN);         
            // $pcount = $product->findone('is_need=1',array('limit'=>1,'fields'=>'count(id) as total,GROUP_CONCAT(id) as cid')); 
            // if(sizeof($pcount)&&$pcount['total']>0){            
            //     $ocount = $orderlist->findone('product_id in ('.$pcount['cid'].') and user_id = "'.$user->id.'" ',array('limit'=>1,'fields'=>'count(DISTINCT  product_id) as total'));
            //     if($ocount['total']<$pcount['total']){
            //         $result['error']='true';
            //         $result['message']  = "订单提交失败！你有".($pcount['total']-$ocount['total'])."款必订款没有下单！";
            //         Flight::json($result);
            //     }
            // }
                $pccount = $ProductColor->findone('is_need=1',array('limit'=>1,'fields'=>'count(*) as total'));
                if(sizeof($pccount)){
                    $need_list= $ProductColor->find('is_need=1',array('limit'=>10000,'fields'=>'product_id,color_id'));
                    $ocount = 0;
                    foreach ($need_list as $value) {
                        if($orderlist->findone("product_id={$value['product_id']} and product_color_id={$value['color_id']} and user_id={$user->id}",array('fields'=>'num')))
                            $ocount +=1;
                    }
                    if($ocount<$pccount['total']){
                        $result['error']='true';
                        $result['message']  = "订单提交失败！你有".($pccount['total']-$ocount)."款必订款色没有下单！";
                        Flight::json($result);
                    }        
                }  
            }
            if($comInfo['check_order_exp']){
            $max = $comInfo['exp_max'];
            $min = $comInfo['exp_min'];
            $uinfo = $user->findone('id="'.$user->id.'"');
            if($uinfo['exp_price']){
                $orderNum = $orderlist->findone('user_id = "'.$user->id.'" ',array('db_debug'=>true,'fields'=>'sum(discount_amount) as amount'));
                $orderPercent = ($orderNum['amount']/$uinfo['exp_price'])*100;
                if($min&&$min>$orderPercent){
                    $result['error']='true';
                    $result['message']  = "订单提交失败！未达到最低订货指标:".$min."%！";
                    Flight::json($result);
                }
                if($max&&$max<$orderPercent){
                    $result['error']='true';
                    $result['message']  = "订单提交失败！超出最高订货指标:".$max."%！";
                    Flight::json($result);
                }
            }
            }
            $user->update(array('order_status'=>1), ' id = "'.$user->id.'" ');
            $message = '订单提交成功!';
        }
        //SESSION::message($message);
        $result['message']  = $message;
    
        Flight::json($result);
    }
    
    public static function Action_display_img($r){
       $data = $r->data;
       if($id = $data->id){
           $pdm = new ProductDisplayImage();
           $list = $pdm->get_image_list_by_did($id);
       }      
       $result['list'] = $list;
       Flight::display('dealer1/display_img.html', $result);
    }
    
    public static function Action_get_group_id_by_id_new($r){
        $gtd    = new GroupToDisplay();
        $data       = $r->query;
        $gid    = $data->gid;
        $did    = $data->did;
        $f          = $data->f;
        if(is_numeric($gid)&&is_numeric($did)){
            $options    = array();
            $condition  = array();
            if($f == "up"){
                $condition[]    = "((gtd.group_id<{$gid} AND gtd.display_id={$did} ) or (gtd.display_id<{$did}))";
                $options['order']   = "gtd.display_id DESC ,gtd.group_id DESC";
                $_message       = "已经是第一个搭配了";
            }elseif($f == "down"){
                $condition[]    = "((gtd.group_id>{$gid} AND gtd.display_id={$did} ) or (gtd.display_id>{$did}))";
                $options['order']   = "gtd.display_id ASC ,gtd.group_id ASC";
                $_message       = "已经是最后一个搭配了";
            }else{
                $condition[]    = "gtd.group_id={$gid} AND gtd.display_id={$did}";
                $_message       = "未找到该搭配";
            }
            // $options['db_debug']    = true;
            $condition[]=' pd.status=1 ';
            $options['tablename'] = 'group_to_display gtd left join product_display pd on gtd.display_id=pd.id';
            $options['fields'] = 'gtd.group_id,gtd.display_id';
            $where      = implode(' AND ', $condition);
            $productgroup    = $gtd->findone($where, $options);
            if(!sizeof($productgroup)){
                $message = $_message;
            }
        }
        $result['message']  = $message;
        $result['group']  = $productgroup;
    
        Flight::json($result);
    }
    
    public static function Action_product($r,$id){
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
            $Company        = new Company;
            $condition      = array();
            $options        = array();
            $options['key']     = "product_id";
            $options['status']  = false;
            $options['fields_more'] = "o.product_id";
            $condition['product_id']    = $id;
            $condition['user_id']   = $User->id;
            $order_user = $OrderList->getOrderAnalysisList($condition, $options);
            $result['order_user']   = $order_user[$id];
            $order_user_num     = $result['order_user']['num'];
             
            $keys   = array('style', 'classes', 'category','medium', 'price_band', 'wave', 'series', 'theme', 'season','designer','fabric','edition','contour');
            foreach($keys as $key){
                $kid    = "{$key}_id";
                $result['productdetail'][$key]  = Keywords::cache_get($result['productdetail'][$kid]);
            }
    
            $ProductColor   = new ProductColor;
            $color_list     = $ProductColor->get_color_list($id);
            

            foreach ($color_list as $row) {
                if($row['mininum'])
                    $mininum_list[] = $row['name'].":".$row['mininum'];
            }
            if($Company->user_guide){
                $UserGuide      = new UserGuide;
                foreach ($color_list as &$val) {
                    $val['user_guide']  = $UserGuide->get_guide_num($User->id, $Product->id,$val['color_id']);
                }
            }
            $result['color_list']   = $color_list;
            $result['mininum_list'] = implode("; ", $mininum_list);
            
            $ProductSize    = new ProductSize;
            $size_list      = $ProductSize->get_size_list($id);
            $result['size_list']    = $size_list;
            $result['size_count']   = count($size_list);
            $options = array();
            $options['tablename'] = "product_image as pi left join product_color as pc on pi.product_id=pc.product_id and pi.color_id=pc.color_id";
            $where = "pi.product_id={$id} AND (pc.status<>0 or pi.color_id=0)";
            $result['imagelist']    = $ProductImage->find($where, $options);
            if($result['productdetail']['is_need']==1 && $order_user_num < 1){
                $result['unorder']  = true;
            }
    
            /* if($User->user_level){
                $Moq    = new Moq;
                $moq    = $Moq->findone("product_id={$id} AND keyword_id={$User->user_level}");
                $result['moq']  = $moq;
            } */
    
            $UserProduct    = new UserProduct;
            $result['is_store'] = $UserProduct->is_store($User->id, $id);
            $store_info     = $UserProduct->get_store_info($User->id, $id);
            $result['is_store'] = $store_info['id'];
            $result['store_rateval']    = $store_info['rateval'];
    
            // $UserDiscount   = new UserDiscount();
            // $discount  = $UserDiscount->get_discount($User->id, $Product->category_id);
            // if(!$discount){
            //     $discount   = $User->discount;
            // }
            $discount   = $User->get_user_product_discount($User, $Product);
            $result['discount'] = $discount;
            $result['discount_price']   = $Product->price * $discount;
    
            $result['has_permission_brand'] = $User->has_permission_brand($Product->brand_id);
            //获取用户评论
            $product_comment = $ProductComment->get_product_comment($User->id, $Product->id);
            $result['product_comment'] = $product_comment;
            // 促销政策
            $ProductOrder_Perferential  = new ProductOrder_Perferential($id);
            $result['perf_list']        = $ProductOrder_Perferential->perf_list;

            $result['company']          = $Company->getData();
            $order_proportion_status    = $Company->order_proportion_status;
            if($order_proportion_status) {
                $size_group_id  = $Product->size_group_id;
                if($size_group_id) {
                    $SizeGroup  = SizeGroup::getInstance($size_group_id);
                    $ProductProportion  = new ProductProportion;
                    $proportion_list    = $ProductProportion->get_proportion_list($User->id, $size_group_id);
                    $result['proportion_list']  = $proportion_list;
                }
            }
            $skc_list   = $ProductColor->get_distinct_skc_ids($id);
            $result['skc_string']   = implode(",", $skc_list);
            
            $result['user_group_list']  = $User->get_user_mulit_list();

            $UserSizeHistory    =   new UserSizeHistory;
            $result['size_history_list'] =   $UserSizeHistory->get_size_list($User->id,$result['productdetail']['category_id']);
        }
        $result['slide_navi'] = $r->query->slide_navi;
        Flight::display("dealer1/product.html", $result);
    }
    public static function Action_structure ($r) {
        Flight::validateUserHasLogin();

        $result = FrontSetting::build();

        $result['control']  = 'structure';

        Flight::display("dealer1/structure.html", $result);
    }

    public static function Action_select_proportion ($r) {
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $size_group_list        = array();
        $size_group_instances   = SizeGroup::getAllInstance();
        foreach($size_group_instances as $size_instance) {
            $intro  = array();
            $intro['size_group_id']  = $size_instance->size_group_id;
            $size_group_list[]  = $intro;
        }
        $result['size_group_list']  = $size_group_list;
        $result['control']          = 'select_proportion';

        Flight::display("dealer1/select_proportion.html", $result);
    }

    public static function Action_select_proportion_list ($r) {
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $size_group_id  = $data->size_group_id;
        if($size_group_id) {
            $size_instance  = SizeGroup::getInstance($size_group_id);
            $User                   = new User;
            $ProductProportion      = new ProductProportion;
            $proportion_list        = $ProductProportion->get_proportion_list($User->id, $size_instance->size_group_id);
            $result['proportion_list']  = $proportion_list;
            $result['size_list']    = $size_instance->get_size_list();
            $result['num']          = $size_instance->option('num');
            $result['limit']        = $size_instance->option('restriction');
            $result['size_group_id']         = $size_instance->size_group_id;
        }

        Flight::display("dealer1/select_proportion_list.html", $result);
    }

    public static function Action_set_user_proportion ($r) {
        Flight::validateUserHasLogin();

        $data   = $r->data;
        $size_group_id      = $data->size_group_id;
        $proportion     = $data->proportion;
        if($proportion) {
            $User               = new User;
            $ProductProportion  = new ProductProportion;
            $ProductProportion->create_proportion($User->id, $size_group_id, $proportion);
        }
        $result     = array();

        Flight::json($result);
    }
    
    public static function Action_mulit_user_change ($r) {
        $User   = new User;
        $result['list'] = $User->get_user_mulit_list();
        $result['user'] = $User->getAttribute();
    
        Flight::display('dealer1/mulit_user_change.html', $result);
    }
    
    public static function Action_mulit_user_set ($r, $id) {
        $User   = new User($id);
        SESSION::set("user", $User->getAttribute());
        Flight::redirect($r->referrer);
    }

    public static function Action_isspot($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

		$Product = new Product;
		$list  	 = $Product->find("status<>0 and hot=1",array("limit"=>100));
        $result['list'] = $list;

        $User = new User;
        $permission_isspot_list = $User->get_permission_isspot();
        foreach ($permission_isspot_list as $val) {
            if($val==1){
                $result['futures']= 1;
            }elseif($val==2){
                $result["isspot"] = 2; 
            }
        }

        Flight::display("dealer1/isspot.html",$result);
    }

    public static function Action_set_user_isspot($r){
        $data   = $r->query;
        $isspot = $data->isspot;
        $User   = new User;
        $user_id= $User->id;
        if($user_id){
            $User->update(array("current_isspot"=>$isspot),"id={$user_id}");
            $user = new User($user_id);
            SESSION::set("user", $user->getAttribute());
            $result['valid'] = true;
        }else{
            $result['valid'] = false;
        }
        Flight::json($result);
    }
}

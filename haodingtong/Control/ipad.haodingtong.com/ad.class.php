<?php

class Control_ad {
    public static function _beforeCall($r, $id=0){
        $User   = new User;
        if($User->type != 3){
            Flight::redirect("/");
            return false;
        }
    }

    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $q              = $r->query->q;
        $result['q']    = $q;
        // if($q){
        //     $Product    = new Product;
        //     $where      = "bianhao={$q}";
        //     $list       = $Product->find($where, array());
        //     if(count($list) == 1){
        //         $product_id     = $list[0]['id'];
        //         Flight::redirect("/ad/detail/{$product_id}");
        //         return;
        //     }
        // }
        $keys   = array('category_id','classes_id', 'wave_id','style_id','price_band_id','brand_id','series_id','theme_id','nannvzhuan_id','sxz_id','season_id','area1', 'area2', 'ordered', 'order', 'view', 'orderType');
        foreach($keys as $k){
            $result[$k]  = $r->query->$k;
        }
        $c              = $r->query->c;
        if($c){
            $result['control']  = $c;
        }
        Flight::display("ad/index.html", $result);
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
            $ProductColor   = new ProductColor;

            $condition      = array();
            $options        = array();
            $options['key']     = "product_id";
            $options['status']  = false;
            $options['fields_more'] = "o.product_id";
            $condition['product_id']    = $id;
            $order_all  = $OrderList->getOrderAnalysisList($condition, $options);
            $result['order_all']    = $order_all[$id];
            //$condition['ad_area1']  = $User->area1;
            //$condition['ad_area2']  = $User->area2;
            if($User->username!='0'){
                $condition['ad_id'] = $User->id;
            }
            $order_user = $OrderList->getOrderAnalysisList($condition, $options);
            $result['order_user']   = $order_user[$id];
            $order_user_num     = $result['order_user']['num'];
            //print_r($order_user);

            $keys   = array('style', 'classes', 'category','medium', 'price_band', 'wave', 'series');
            foreach($keys as $key){
                $kid    = "{$key}_id";
                $result['productdetail'][$key]  = $Keywords->getName_File($result['productdetail'][$kid]);
            }

            $result['imagelist']    = $ProductImage->find("product_id={$id}", array());
            if($User->username!='0'){
                $orderlist              = $OrderList->getAdProOrderList($User->id,$id);
            }else{
                $orderlist              = $OrderList->getAdOrderList($User->area1, $User->area2, $id);
            }
            
            $OrderTable             = new OrderTable($id, $orderlist);
            $result['ordertable']   = $OrderTable->byHtml("user");
            $result['ordertableall']= $OrderTable->byHtml("all");
            $OrderListProduct       = new OrderListProduct;
            $result['rank']         = $OrderListProduct->get_rank($id);
            if($User->username!='0'){
                $hadBuy = $OrderList->findone("o.product_id={$id} and u.ad_id={$User->id}",array('tablename'=>'orderlist o left join user u on o.user_id = u.id','fields'=>'count(DISTINCT user_id) as total,GROUP_CONCAT(DISTINCT user_id) as cid'));         
            }else{
                $hadBuy = $OrderList->findone("product_id={$id}",array('fields'=>'count(DISTINCT user_id) as total,GROUP_CONCAT(DISTINCT user_id) as cid'));
            }
            $cond = array();
            $cond[] = 'type=1';
            if($User->username!='0'){
                $cond[] = ' ad_id = "'.$User->id.'" ';
            }
            if($hadBuy['cid']){
                $cond[] = ' id not in ('.$hadBuy['cid'].') ';                
            }
            $condwhere = implode(' AND ', $cond);
            //print_r($cond);exit;
            $ulist = $User->find($condwhere, array("limit"=>10000, "fields"=>"name"));
            $result['hadBuy'] = $hadBuy['total'];
            $result['hadBuyavg'] = $hadBuy['total']?round(($order_user_num/$hadBuy['total']),1):0;
            $color_list     = $ProductColor->get_color_list($id);
            $color_list   = ProductsAttributeFactory::fetch($color_list, 'color', "color_id", "products_color");
            usort($color_list, function($a, $b){
                return $a['products_color']['rank'] > $b['products_color']['rank'] ? 1 : -1;
            });
            $result['color_list']   = $color_list;
            $result['unorderulist'] = $ulist;
        }
        $result['control']  = "adall";

        Flight::display("ad/detail.html", $result);
    }

    public static function Action_orders($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        // $User       = new User;
        // $OrderList  = new OrderList;
        // $result['orderinfo']    = $OrderList->getAdOrderinfo($User->area1, $User->area2);
        // $exp        = $User->get_exp_info();
        // $result['exp_num']      = $exp['exp_num'];
        // $result['exp_price']    = $exp['exp_price'];
        // if($exp['exp_num']){
        //     $result['exp_num_percent']      = sprintf('%.2f%%', $result['orderinfo']['num'] / $exp['exp_num'] * 100);
        // }
        // if($exp['exp_price']){
        //     $result['exp_price_percent']    = sprintf('%.2f%%', $result['orderinfo']['price'] / $exp['exp_price'] * 100);
        // }

        $result['control']  = "orders";
        Flight::display("ad/orders.html", $result);
    }

    public static function Action_analysis($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "analysis";
        if($result['user']['type']==3 && $result['user']['username']!='0'){
            Flight::display('ad/analysis_zd.html', $result);
        }else{
            Flight::display('ad/analysis.html', $result);
        }
    }

    public static function Action_analysis_product($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "analysis";

        Flight::display("ad/analysis_product.html", $result);
    }

    public static function Action_analysis_user($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "analysis";

        Flight::display("ad/analysis_user.html", $result);
    }

    public static function Action_summary($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "summary";

        Flight::display('ad/summary.html', $result);
    }

    public static function Action_wrongorders($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "wrongorders";

        Flight::display('ad/wrongorders.html', $result);
    }

    public static function Action_exp($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "exp";

        Flight::display("ad/exp.html", $result);
    }
    
    public static function Action_exp_print($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
        
        $condition  = array();
        $User       = new User;
        
        if($User->type == 3 && $User->username != "0"){
            /*if($User->area1){
                $condition['area1'] = $User->area1;
            }
            if($User->area2){
                $condition['area2'] = $User->area2;
            }*/
            $condition['ad_id'] = $User->id;
        }
        
        $result['printInfo'] = $User->get_print_info($condition);
    
        $result['control']  = "exp_print";
    
        if($User->type == 3 && $User->username != "0"){
            Flight::display("ad/exp_print_ad.html", $result);
        }else{
            Flight::display("ad/exp_print.html", $result);
        }
        
    }
    
    public static function Action_exp_print2($r){
        Flight::validateUserHasLogin();  
        $result     = FrontSetting::build();
                        
        $result['control']  = "exp_print";
    
        Flight::display("ad/exp_print2.html", $result);
    }

	public static function Action_exp1($r){
        Flight::validateUserHasLogin();

		$User 		= new User;
		$OrderList 	= new OrderList;
        	$result     	= FrontSetting::build();
		$data 		= $r->query;
        $uname      = $data->uname;
        if(!isset($uname)){
            $isArea     = $data->isArea;
            $isZongdai  = $data->isZongdai;
            $master_uid = $data->master_uid;
            $area1      = $data->area1 ? $data->area1 : $User->area1;
            $area2      = $data->area2 ? $data->area2 : $User->area2;
        }
		$params 	= array();
		$params['isArea']	= $isArea;
		$params['isZongdai']	= $isZongdai;
		$params['master_uid']	= $master_uid;
		$params['area1']	= $area1;
		$params['area2']	= $area2;
        $params['uname']    = $uname;

		$user_exp_list	= $User->get_user_exp_list_ad($params);
		$num_all 	= 0;
		$exp_price_all 	= 0;
        $exp_num_all    = 0;
		$price_all 	= 0;
		$discount_price_all = 0;
		foreach($user_exp_list as &$user){
			$user_id 	= $user['id'];
			$mid 		= $user['mid'];
			$exp_price 	= $user['exp_price'];
            $exp_num    = $user['exp_num'];
			$area1 		= $user['area1'];
			$area2 		= $user['area2'];
			if(!$master_uid && $mid){
				$orderinfo 	= $OrderList->getSelfOrderinfo($mid);
			}elseif($area2){
				$orderinfo 	= $OrderList->getAdOrderInfo1(null, $area2, $user_id);
			}elseif($area1){
				$orderinfo 	= $OrderList->getAdOrderInfo1($area1, null);
			}else{
				if($user_id){
					$info 	= $OrderList->getOrderUserList(array('fliter_uid' => $user_id));
					$orderinfo 	= $info[0];
				}else{
					$orderinfo 	= array();
				}
			}
			if(!$master_uid){
				$exp_price_all 	+= $exp_price;
                $exp_num_all    += $exp_num;
			}else{
				if($master_uid == $mid){
					$exp_price_all = $exp_price;
                    $exp_num_all   = $exp_num;
				}
			}
			$price_all 	+= $orderinfo['price'];
			$num_all 	+= $orderinfo['num'];
			$discount_price_all	+= $orderinfo['discount_price'];
			$user['orderinfo'] 	= $orderinfo;
			$user['exp_price_percent'] 	= $exp_price ? sprintf('%.2f%%', $orderinfo['discount_price'] / $exp_price * 100) : '-';
            $user['exp_num_percent']    = $exp_num   ? sprintf('%.2f%%', $orderinfo['num'] / $exp_num * 100) : '-';
            $user['num']    = $orderinfo['num'];
            $user['price']    = $orderinfo['price'];
		}
        if($master_uid){
            // $masterinfo     = $User->findone("mid={$master_uid}");
            // $discount_price_all     = $price_all * $masterinfo['discount'];
        }
        $company    = new Company;
        $order      = $company->ad_order;
        if($order){
            usort($user_exp_list, function($a, $b) use($order){
                return $a[$order] < $b[$order];
            });
        }
		$result['exp_list']	= $user_exp_list;
		$result['exp_price_all']	= $exp_price_all;
        $result['exp_num_all']      = $exp_num_all;
		$result['price_all']		= $price_all;
		$result['num_all']		= $num_all;
		$result['exp_price_percent']	= $exp_price_all ? sprintf('%.2f%%', $discount_price_all / $exp_price_all * 100) : '-';
        $result['exp_num_percent']      = $exp_num_all   ? sprintf('%.2f%%', $num_all / $exp_num_all * 100) : '-';
		$result['discount_price_all']		= $discount_price_all;
		$result['control'] 	= "exp";
		$result['isArea'] 	= $isArea;
		$result['isZongdai'] 	= $isZongdai;
		$result['area1'] 	= $data->area1;
		$result['area2'] 	= $data->area2;
		$result['master_uid'] 	= $master_uid;
        $result['order']    = $order;

        	Flight::display("ad/exp1.html", $result);
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

    public static function Action_fabric($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $data       = $r->query;
        $complete   = $data->complete;
        $Product    = new Product;
        if(is_numeric($complete)){
            $params['complete']    = $complete;
        }
        $list       = $Product->getFabricOrderList($params);
        $result['list']     = $list;

        $result['control']  = 'fabric';

        Flight::display("ad/fabric.html", $result);
    }

    public static function Action_hpgc($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "hpgc";

        if($result['user']['type']==3 && $result['user']['username']!='0'){
            Flight::display('ad/hpgc_zd.html', $result);
        }else{
            Flight::display('ad/hpgc.html', $result);
        }
    }

    public static function Action_filter($r) {
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $User       = new User;
        $Location   = new Location;
        $result['area1_list']    = $Location->getChildren(0);
        $result['ad_list']       = $User->get_ad_list();

        $result['control']  = "filter";
        Flight::display("ad/filter.html", $result);
    }

    public static function Action_group($r){
        Flight::validateUserHasLogin();
        $result     = FrontSetting::build();
        $result['control']  = "group";
        $result['search_f'] = "group_ad";
        Flight::display('ad/group.html', $result);
    }

    public static function Action_display($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "display";
        $result['search_f'] = "display_ad";

        Flight::display('ad/display.html', $result);
    }
    
    public static function Action_display_new($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $result['control']  = "display_new";
        $result['search_f'] = "display_ad_new";
    
        Flight::display('ad/display_new.html', $result);
    }
    
    public static function Action_displaydetail($r, $id){


        Flight::validateUserHasLogin();
        $result = FrontSetting::build();
        $ProductDisplay         = new ProductDisplay($id);
        if($ProductDisplay->id){
            $result['group']    = $ProductDisplay->getAttribute();
        
            $locationList=new Location();
            $greatLocation=$locationList->getLocationList(' pid=0 ',array('fields'=>'id,name','limit'=>1000));
            array_unshift($greatLocation,array('id'=>0,'name'=>'所有大区'));
            $result['greatLocation']=$greatLocation;  //得到所有大区的信息
        
        
            $result['location_id']=($r->query->location_id?$r->query->location_id:0);//设置当前选择的大区信息
            $result['current_id']=$id;
        
            $chooseAllUser = ($result['location_id']?false:true);
            $locationWhere = ($result['location_id']?' pid="'.$result['location_id'].'" ' : ' pid>0 ');
            $locationInfo = $locationList->getLocationList($locationWhere,array('fields'=>'id,name','key'=>'id','limit'=>1000));//得到当前选择大区的区域的所有信息
        
            //print_r($locationInfo);
            $result['locationInfo']=$locationInfo;
        
            $userList=new User();
            if(!$chooseAllUser){
                $locationStr=implode(',', array_keys($locationInfo));
                $userWhere = ' type=1 AND area2 in ('.($locationStr?$locationStr:'\'\'').') ';
            }else{
                $userWhere=' type=1  ';
            }
            $userlistInfo=$userList->find($userWhere,array('fields'=>'id,area2,name','key'=>'id','limit'=>1000));//得到当前选择大区的所有用户信息
            $result['userlistInfo']=$userlistInfo;
            //print_r($userlistInfo);
        
            //print_r($userlistInfo);exit;
            if(!$chooseAllUser){
                $userListStr=implode(',', array_keys($userlistInfo));
            }
        
            if(!$chooseAllUser){
                $orderWhere = ' AND o.user_id in ('.($userListStr?$userListStr:'\'\'').') ';
            }else{
                $orderWhere = '';
            }
        
            $ProductGroupMember = new ProductDisplayMember;
            $OrderListProduct=new OrderListProduct();
            $orderLists=new OrderList();
            $orderList=array();
            $orderListNew = array();
        
        
        
            /*$orderList = $orderLists->find('o.product_id = pd.product_id and o.product_color_id = pd.keyword_id'.$orderWhere,array('tablename'=>'orderlist o, product_display_member_color pd','fields'=>'sum(o.num) as num ,pd.display_id','db_debug'=>false,'group'=>'pd.display_id','limit'=>1000,'key'=>'display_id'));
        
            usort($orderList, function($a, $b){
            $rank_a     = $a['num'];
            $rank_b     = $b['num'];
            return $rank_a < $rank_b ;
            });
            foreach($orderList as $olk=>$olv){
            $orderListNew[$olv['display_id']] = array('num'=>$olv['num'],'display_id'=>$olv['display_id'],'rank'=>$olk);
            }
        
            $currentDisplay = $orderListNew[$id];
            $result['ginfo']['rank']=$currentDisplay['rank']+1;*/
            $orderList = $orderLists->findone('o.product_id = pd.product_id and o.product_color_id = pd.keyword_id and pd.display_id = "'.$id.'" '.$orderWhere,array('tablename'=>'orderlist o, product_display_member_color pd','fields'=>'sum(o.num) as num ,pd.display_id','db_debug'=>false));
            $result['ginfo']['total']=$orderList['num'];
        
        
            $productList=new Product();
            $productSkcInfo=$productList->find('pdmc.product_id=p.id and pdmc.product_id = pc.product_id and pdmc.keyword_id = pc.color_id and pdmc.display_id= '.$id.' and pc.status=1 and p.status = 1 ',array('fields'=>'p.id,p.name,p.defaultimage,p.bianhao,p.price,p.category_id,pc.skc_id,pc.color_id','tablename'=>'product p , product_display_member_color pdmc , product_color pc ','db_debug'=>false,'limit'=>10000));
            $productInfo = array();
            $displayGinfo = array();
            foreach($productSkcInfo as $pi){
                if(!$productInfo[$pi['id']]){
                    $productInfo[$pi['id']] = $pi;
                    $productInfo[$pi['id']]['rank']=$OrderListProduct->get_rank($pi['id']);
                    $productInfo[$pi['id']]['category_name'] = Keywords::cache_get(array($pi['category_id']));
                }
                $displayGinfo[$pi['id'].'_'.$pi['color_id']] = array('num'=>0,'product_id'=>$pi['id'],'product_color_id'=>$pi['color_id'],'skc'=>$pi['skc_id']);
            }
        
            $userOrder=$orderLists->find(' o.product_id = pd.product_id and o.product_color_id = pd.keyword_id and pd.display_id = '.$id.' '.$orderWhere,array('fields'=>'o.user_id, o.product_id,sum(num) as num,o.product_color_id','limit'=>10000000,'db_debug'=>false,'group'=>'o.user_id,o.product_id,o.product_color_id','tablename'=>'orderlist o , product_display_member_color pd'));
        
        
            $userGroupOrder=array();
            $areaGroupOrder=array();
            $skcNum = array();
        
            foreach($userOrder as $uok => $uov){
                if(!$userGroupOrder[$uov['user_id']]){
                    $userGroupOrder[$uov['user_id']]['user_id'] = $uov['user_id'];
                    $userGroupOrder[$uov['user_id']]['user_name'] = $userlistInfo[$uov['user_id']]['name'];
                }
                $userGroupOrder[$uov['user_id']]['num'] += $uov['num'];
        
        
                if(!$areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]){
                    $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['area_id'] = $userlistInfo[$uov['user_id']]['area2'];
                    $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['area_name'] = $locationInfo[$userlistInfo[$uov['user_id']]['area2']]['name'];
                }
                $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['num'] += $uov['num'];
        
        
                $displayGinfo[$uov['product_id'].'_'.$uov['product_color_id']]['num'] += $uov['num'];
                $displayGinfo[$uov['product_id'].'_'.$uov['product_color_id']]['userInfo'][$uov['user_id']]['user_id']=$uov['user_id'];
                $displayGinfo[$uov['product_id'].'_'.$uov['product_color_id']]['userInfo'][$uov['user_id']]['num']+=$uov['num'];
        
            }
            foreach($userlistInfo as $uik=>$uiv){
                if(!isset($userGroupOrder[$uiv['id']])){
                    $userGroupOrder[$uiv['id']] = array('user_id'=>$uiv['id'],'user_name'=>$uiv['name'],'num'=>0);
                }
            }
        
            foreach($locationInfo as $lik=>$liv){
                if(!isset($areaGroupOrder[$liv['id']])){
                    $areaGroupOrder[$liv['id']] = array('area_id'=>$liv['id'],'area_name'=>$liv['name'],'num'=>0);
                }
            }
        
            usort($userGroupOrder,function($a,$b){
                $rank_a = $a['num'];
                $rank_b = $b['num'];
                return $rank_a<$rank_b;
            });
        
                $result['ginfo']['mostMember'] = $userGroupOrder[0];
                $result['ginfo']['leastMember'] = $userGroupOrder[(sizeof($userGroupOrder)-1)];
        
                usort($areaGroupOrder,function($a,$b){
                    $rank_a = $a['num'];
                    $rank_b = $b['num'];
                    return $rank_a<$rank_b;
                });
        
                    $result['ginfo']['mostArea'] = $areaGroupOrder[0];
                    $result['ginfo']['leastArea'] = $areaGroupOrder[(sizeof($areaGroupOrder)-1)];
        
                    usort($displayGinfo,function($a,$b){
                        $rank_a = $a['num'];
                        $rank_b = $b['num'];
                        return $rank_a<$rank_b;
                    });
                    foreach($displayGinfo as $dgk=>$dgv){
                        if(isset($dgv['userInfo'])){
                            $userInfo = $dgv['userInfo'];
                            $displayGinfo[$dgk]['hasbuy']=sizeof($userInfo);
                            foreach($userlistInfo as $uiok=>$uiov){
                                if(!isset($userInfo[$uiov['id']])){
                                    $userInfo[$uiov['id']] = array('user_id'=>$uiov['id'],'num'=>0);
                                }
                            }
                            usort($userInfo,function($a,$b){
                                $rank_a = $a['num'];
                                $rank_b = $b['num'];
                                return $rank_a<$rank_b;
                            });
                            $displayGinfo[$dgk]['userInfo'] = array($userInfo[0],$userInfo[(sizeof($userInfo)-1)]);
                            $areaInfo = array();
                            foreach($userInfo as $uok=>$uov){
                                $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['num']+=$uov['num'];
                                $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['area_id']=$userlistInfo[$uov['user_id']]['area2'];
                                $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['users'][]=$uov;
                                if($uov['num']>0){
                                    $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['hasbuy']+=1;
                                }
                            }
                            usort($areaInfo,function($a,$b){
                                $rank_a = $a['num'];
                                $rank_b = $b['num'];
                                return $rank_a<$rank_b;
                            });
                            $displayGinfo[$dgk]['areaInfo'] = $areaInfo;
                        }
                    }
        
                    $userAreaGroup=$userList->find(' type =1  ',array('fields'=>'area2 , count(id) as total','limit'=>1000,'group'=>'area2','key'=>'area2'));
                    $userNum=$userList->find($userWhere,array('fields'=>'count(*) as total'));
                    $userNum=$userNum[0]['total'];
                    $result['ginfo']['member']=$displayGinfo;
                    $result['ginfo']['productInfo']=$productInfo;
                    $result['ginfo']['userNum']=$userNum;
                    $result['ginfo']['userAreaGroup']=$userAreaGroup;
        }
        $result['control']  = "display";
        $result['search_f'] = "display_ad";
        $result['function']  = "displaydetail";
        Flight::display("ad/displaydetail.html", $result);
        
    }
    public static function Action_groupdetail($r, $id){

        Flight::validateUserHasLogin();
        
        $result     = FrontSetting::build();
        
        $ProductGroup       = new ProductGroup($id);
        
        if($ProductGroup->id){
        
            $ProductGroupMember = new ProductGroupMember();
            $pgmKuan = $ProductGroupMember->findone('group_id = "'.$id.'" AND color_id = 0');
            if(!sizeof($pgmKuan)){
                STATIC::Action_groupdetail_skc($r,$id);
                exit;
            }
        
            $result['group']    = $ProductGroup->getAttribute();//得到搭配的所有信息
        
            $locationList=new Location();
            $greatLocation=$locationList->getLocationList(' pid=0 ',array('fields'=>'id,name','limit'=>1000));
            array_unshift($greatLocation,array('id'=>0,'name'=>'所有大区'));
            $result['greatLocation']=$greatLocation;  //得到所有大区的信息
        
            $result['location_id']=($r->query->location_id?$r->query->location_id:0);//设置当前选择的大区信息
            $result['current_id']=$id;
        
            $chooseAllUser = ($result['location_id']?false:true);
            $locationWhere = ($result['location_id']?' pid="'.$result['location_id'].'" ' : ' pid>0 ');
            $locationInfo = $locationList->getLocationList($locationWhere,array('fields'=>'id,name','key'=>'id','limit'=>1000));//得到当前选择大区的区域的所有信息
            $result['locationInfo']=$locationInfo;
            $userList=new User();
            if(!$chooseAllUser){
                $locationStr=implode(',', array_keys($locationInfo));
                $userWhere = ' type=1 AND area2 in ('.($locationStr?$locationStr:'\'\'').') ';
            }else{
                $userWhere=' type=1 ';
            }
            $userlistInfo=$userList->find($userWhere,array('fields'=>'id,area2,name','key'=>'id','limit'=>1000));//得到当前选择大区的所有用户信息
            $result['userlistInfo']=$userlistInfo;
            if(!$chooseAllUser){
                $userListStr=implode(',', array_keys($userlistInfo));
            }
             
            if(!$chooseAllUser){
                $orderWhere = 'AND user_id in ('.($userListStr?$userListStr:'\'\'').') ';
            }else{
                $orderWhere = '';
            }
        
            $OrderListProduct=new OrderListProduct();
            $orderLists=new OrderList();
            $orderList=array();
        
            $orderList = $orderLists->findone('o.product_id = pgm.product_id  and pgm.group_id = "'.$id.'" '.$orderWhere,array('tablename'=>'orderlist o, product_group_member pgm','fields'=>'sum(o.num) as num ,pgm.group_id','db_debug'=>false));
            $result['ginfo']['total']=$orderList['num'];
        
            $productList=new Product();
            $productSkcInfo=$productList->find('pgm.product_id=p.id  and pgm.group_id= '.$id.'  and p.status = 1 ',array('fields'=>'p.id,p.name,p.defaultimage,p.bianhao,p.price,p.category_id','tablename'=>'product p , product_group_member pgm  ','db_debug'=>false,'limit'=>10000));
            $productInfo = array();
            $displayGinfo = array();
            foreach($productSkcInfo as $pi){
                if(!$productInfo[$pi['id']]){
                    $productInfo[$pi['id']] = $pi;
                    $productInfo[$pi['id']]['rank']=$OrderListProduct->get_rank($pi['id']);
                    $productInfo[$pi['id']]['category_name'] = Keywords::cache_get(array($pi['category_id']));
                }
                $displayGinfo[$pi['id']] = array('num'=>0,'product_id'=>$pi['id']);
            }
        
            $userOrder=$orderLists->find(' o.product_id = pdm.product_id  and pdm.group_id = '.$id.' '.$orderWhere,array('fields'=>'o.user_id, o.product_id,sum(num) as num','limit'=>10000000,'db_debug'=>false,'group'=>'o.user_id,o.product_id','tablename'=>'orderlist o , product_group_member pdm'));
             
        
            $userGroupOrder=array();
            $areaGroupOrder=array();
        
            foreach($userOrder as $uok => $uov){
                if(!$userGroupOrder[$uov['user_id']]){
                    $userGroupOrder[$uov['user_id']]['user_id'] = $uov['user_id'];
                    $userGroupOrder[$uov['user_id']]['user_name'] = $userlistInfo[$uov['user_id']]['name'];
                }
                $userGroupOrder[$uov['user_id']]['num'] += $uov['num'];
        
        
                if(!$areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]){
                    $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['area_id'] = $userlistInfo[$uov['user_id']]['area2'];
                    $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['area_name'] = $locationInfo[$userlistInfo[$uov['user_id']]['area2']]['name'];
                }
                $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['num'] += $uov['num'];
        
        
                $displayGinfo[$uov['product_id']]['num'] += $uov['num'];
                $displayGinfo[$uov['product_id']]['userInfo'][$uov['user_id']]['user_id']=$uov['user_id'];
                $displayGinfo[$uov['product_id']]['userInfo'][$uov['user_id']]['num']+=$uov['num'];
        
            }
        
            foreach($userlistInfo as $uik=>$uiv){
                if(!isset($userGroupOrder[$uiv['id']])){
                    $userGroupOrder[$uiv['id']] = array('user_id'=>$uiv['id'],'user_name'=>$uiv['name'],'num'=>0);
                }
            }
        
            foreach($locationInfo as $lik=>$liv){
                if(!isset($areaGroupOrder[$liv['id']])){
                    $areaGroupOrder[$liv['id']] = array('area_id'=>$liv['id'],'area_name'=>$liv['name'],'num'=>0);
                }
            }
        
            usort($userGroupOrder,function($a,$b){
                $rank_a = $a['num'];
                $rank_b = $b['num'];
                return $rank_a<$rank_b;
            });
        
                $result['ginfo']['mostMember'] = $userGroupOrder[0];
                $result['ginfo']['leastMember'] = $userGroupOrder[(sizeof($userGroupOrder)-1)];
        
                usort($areaGroupOrder,function($a,$b){
                    $rank_a = $a['num'];
                    $rank_b = $b['num'];
                    return $rank_a<$rank_b;
                });
        
                    $result['ginfo']['mostArea'] = $areaGroupOrder[0];
                    $result['ginfo']['leastArea'] = $areaGroupOrder[(sizeof($areaGroupOrder)-1)];
        
                    usort($displayGinfo,function($a,$b){
                        $rank_a = $a['num'];
                        $rank_b = $b['num'];
                        return $rank_a<$rank_b;
                    });
        
                        foreach($displayGinfo as $dgk=>$dgv){
                            if(isset($dgv['userInfo'])){
                                $userInfo = $dgv['userInfo'];
                                $displayGinfo[$dgk]['hasbuy']=sizeof($userInfo);
                                foreach($userlistInfo as $uiok=>$uiov){
                                    if(!isset($userInfo[$uiov['id']])){
                                        $userInfo[$uiov['id']] = array('user_id'=>$uiov['id'],'num'=>0);
                                    }
                                }
                                usort($userInfo,function($a,$b){
                                    $rank_a = $a['num'];
                                    $rank_b = $b['num'];
                                    return $rank_a<$rank_b;
                                });
                                $displayGinfo[$dgk]['userInfo'] = array($userInfo[0],$userInfo[(sizeof($userInfo)-1)]);
                                $areaInfo = array();
                                foreach($userInfo as $uok=>$uov){
                                    $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['num']+=$uov['num'];
                                    $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['area_id']=$userlistInfo[$uov['user_id']]['area2'];
                                    $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['users'][]=$uov;
                                    if($uov['num']>0){
                                        $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['hasbuy']+=1;
                                    }
                                }
                                usort($areaInfo,function($a,$b){
                                    $rank_a = $a['num'];
                                    $rank_b = $b['num'];
                                    return $rank_a<$rank_b;
                                });
                                $displayGinfo[$dgk]['areaInfo'] = $areaInfo;
                            }
                        }
        
                        $userAreaGroup=$userList->find(' type =1  ',array('fields'=>'area2 , count(id) as total','limit'=>1000,'group'=>'area2','key'=>'area2'));
                        $userNum=$userList->find($userWhere,array('fields'=>'count(*) as total'));
                        $userNum=$userNum[0]['total'];
                        $result['ginfo']['member']=$displayGinfo;
                        $result['ginfo']['productInfo']=$productInfo;
                        $result['ginfo']['userNum']=$userNum;
                        $result['ginfo']['userAreaGroup']=$userAreaGroup;
        }
        $result['control']  = "group";
        $result['search_f'] = "group_ad";
        $result['function']  = "groupdetail";
        Flight::display("ad/groupdetail.html", $result);       
    }
    public static function Action_displaydetail_bak($r, $id){
        Flight::validateUserHasLogin();

        $result                 = FrontSetting::build();

        $ProductDisplay         = new ProductDisplay($id);
        if($ProductDisplay->id){

            $result['group']    = $ProductDisplay->getAttribute();//得到陈列的所有信息


            $locationList=new Location();
            $greatLocation=$locationList->getLocationList(' pid=0 ',array('fields'=>'id,name','limit'=>1000));
            array_unshift($greatLocation,array('id'=>0,'name'=>'所有大区'));
            $result['greatLocation']=$greatLocation;  //得到所有大区的信息

            $result['location_id']=($r->query->location_id?$r->query->location_id:0);//设置当前选择的大区信息
            $result['current_id']=$id;

            $chooseAllUser = ($result['location_id']?false:true);
            $locationWhere = ($result['location_id']?' pid="'.$result['location_id'].'" ' : ' pid>0 ');
            $locationInfo = $locationList->getLocationList($locationWhere,array('fields'=>'id,name','key'=>'id','limit'=>1000));//得到当前选择大区的区域的所有信息
            $userList=new User();
            if(!$chooseAllUser){
                $locationStr=implode(',', array_keys($locationInfo));
                $userWhere = ' type=1 AND area2 in ('.($locationStr?$locationStr:'\'\'').') ';
            }else{
                $userWhere=' type=1  ';
            }
            $userlistInfo=$userList->find($userWhere,array('fields'=>'id,area2,name','key'=>'id','limit'=>1000));//得到当前选择大区的所有用户信息
            //print_r($userlistInfo);exit;
            if(!$chooseAllUser){
                $userListStr=implode(',', array_keys($userlistInfo));
            }

            //print_r($userlistInfo);

            $ProductGroupMember = new ProductDisplayMember;
            $listArr=$ProductGroupMember->getDisplayList(array('fields'=>'product_id,display_id','limit'=>10000),1);
            $groupInfo=array();
            foreach($listArr as $g){
                $groupInfo[$g['display_id']][]=$g['product_id'];
            }//获取所有陈列的产品信息

            $OrderListProduct=new OrderListProduct();
            $orderLists=new OrderList();
            $OrderListDetail=new OrderListDetail();
            $orderList=array();

            $productDisplay=new ProductDisplayMemberColor();
            $currentDisplayPro=$productDisplay->find('',array('fields'=>'product_id,keyword_id,display_id','limit'=>10000));
            $displayPro=array();
            $currentProduct=array();
            $skcStr='';
            $skcWhere=array();
            foreach($currentDisplayPro as $cdp){
                $displayPro[$cdp['display_id']][$cdp['product_id']][]=$cdp['keyword_id'];
                if($cdp['display_id']==$id){
                    $currentProduct[$cdp['product_id']]=1;
                    $skcWhere[]= ' ( product_id = '.$cdp['product_id'].' AND color_id = '.$cdp['keyword_id'].' ) ';
                }
            }
            $skcStr=implode(' OR ', $skcWhere);
            $skcFind=new ProductColor();
            $skcArr=$skcFind->find('  ('.($skcStr?$skcStr:0).')  ',array('fields'=>'product_id,color_id,skc_id','limit'=>10000));
            foreach($skcArr as $sk){
                $skcArray[$sk['product_id'].'_'.$sk['color_id']]=$sk['skc_id'];
            }

            $currentWhere='';
            //print_r($displayPro);
            foreach($displayPro as $dp=>$dv){
                $pstr='';
                $pArr=array();
                foreach($dv as $dvk => $dvv){
                    foreach($dvv as $dvvv){
                        $pArr[]=' ( product_id = '.$dvk.' AND product_color_id = '.$dvvv.' ) ';
                    }
                }
                $pstr=implode(' OR ', $pArr);
                if($dp==$id){
                    $currentWhere=$pstr;
                }

                if($chooseAllUser){
                    $orderList['g_'.$dp]['member']=$OrderListDetail->find('  ('.($pstr?$pstr:0).') AND display_id = "'.$dp.'" ',array('fields'=>'sum(num) as num,product_id,product_color_id','limit'=>1000,'group'=>'product_id,product_color_id','order'=>' num desc '));
                    $orderTotal=$OrderListDetail->find('  ('.($pstr?$pstr:0).') AND display_id = "'.$dp.'" ',array('fields'=>'sum(num) as total','limit'=>1000));
                }else{
                    $orderList['g_'.$dp]['member']=$OrderListDetail->find('  ('.($pstr?$pstr:0).')  '.' AND display_id = "'.$dp.'"  AND user_id in ('.($userListStr?$userListStr:'\'\'').') ',array('fields'=>'sum(num) as num,product_id,product_color_id','limit'=>1000,'group'=>'product_id,product_color_id','order'=>' num desc '));
                    $orderTotal=$OrderListDetail->find('  ('.($pstr?$pstr:0).')  '.'  AND display_id = "'.$dp.'" AND user_id in ('.($userListStr?$userListStr:'\'\'').') ',array('fields'=>'sum(num) as total','limit'=>1000));
                }
                $orderList['g_'.$dp]['sum']=$orderTotal[0]['total'];
                $orderTotalArr[]=$orderTotal[0]['total'];
            }
            //print_r($orderList);
            /*foreach($groupInfo as $gk=>$ginfo){
             $pstr='';
             $pArr=array();
             foreach($ginfo as $gv){
             $pArr[]=' ( product_id = '.$gv.' AND product_color_id = '.$displayPro[$gk][$gv].' ) ';
             }
             $pstr=implode(' OR ', $pArr);
             //echo $pstr.'<br>';
             if($gk==$id){
             $currentWhere=$pstr;
             }

             if($chooseAllUser){
             $orderList['g_'.$gk]['member']=$orderLists->find('  ('.($pstr?$pstr:0).')  ',array('fields'=>'sum(num) as num,product_id','limit'=>1000,'group'=>'product_id','key'=>'product_id','order'=>' num desc '));
             $orderTotal=$orderLists->find('  ('.($pstr?$pstr:0).')  ',array('fields'=>'sum(num) as total','limit'=>1000));
             }else{
             $orderList['g_'.$gk]['member']=$orderLists->find('  ('.($pstr?$pstr:0).')  '.' AND user_id in ('.($userListStr?$userListStr:'\'\'').') ',array('fields'=>'sum(num) as num,product_id','limit'=>1000,'group'=>'product_id','key'=>'product_id','order'=>' num desc '));
             $orderTotal=$orderLists->find('  ('.($pstr?$pstr:0).')  '.' AND user_id in ('.($userListStr?$userListStr:'\'\'').') ',array('fields'=>'sum(num) as total','limit'=>1000));
             }
             $orderList['g_'.$gk]['sum']=$orderTotal[0]['total'];
             $orderTotalArr[]=$orderTotal[0]['total'];
             }*/
            //
            array_multisort($orderTotalArr,SORT_DESC,$orderList);
            // print_r($orderList);exit;

            $rank=array_search('g_'.$id, array_keys($orderList))+1;
            $hasData=array();
            foreach($orderList['g_'.$id]['member'] as $om){
                $hasData[$om['product_id'].'_'.$om['product_color_id']]=1;
            }
            foreach($skcArray as $skk=>$skv){
                if(!isset($hasData[$skk])){
                    $skvA=explode('_', $skk);
                    $orderList['g_'.$id]['member'][]=array('num'=>0,'product_id'=>$skvA[0],'product_color_id'=>$skvA[1]);
                }
            }

            $result['ginfo']['rank']=$rank;
            $result['ginfo']['total']=$orderList['g_'.$id]['sum'];
            $result['ginfo']['member']=$orderList['g_'.$id]['member'];
            //print_r($orderList['g_'.$id]['member']);exit;
            $currentPid=implode(',', array_keys($currentProduct));

            $productList=new Product();
            $productInfo=$productList->find(' id in ('.($currentPid?$currentPid:'\'\'').') ',array('fields'=>'id,name,defaultimage,bianhao,price,category_id','limit'=>1000,'key'=>'id'));
            $categoryArr=array();

            //print_r();
            foreach($productInfo as $pi){
                $categoryArr[$pi['category_id']]=1;
                $productInfo[$pi['id']]['rank']=$OrderListProduct->get_rank($pi['id']);
            }
            $categoryStr=implode(',', array_keys($categoryArr));
            $keywordList=new Keywords();
            $categoryList=$keywordList->find(' id in ('.($categoryStr?$categoryStr:'\'\'').') ',array('fields'=>'id, name','limit'=>1000,'key'=>'id'));
            $result['ginfo']['categoryList']=$categoryList;

            if(!$chooseAllUser){
                $orderWhere = 'AND user_id in ('.($userListStr?$userListStr:'\'\'').') ';
            }else{
                $orderWhere = '';
            }

            $userOrder=$OrderListDetail->find('  ('.($currentWhere?$currentWhere:0).') AND display_id="'.$id.'" '.$orderWhere,array('fields'=>'user_id, product_id,num,product_color_id','limit'=>10000000));
            //print_r($userOrder);
            $userGroupOrder=array();
            $userGroupOrderSort=array();
            $productSortByUser=array();
            $productSortByUserSort=array();
            foreach($userOrder as $uo){
                $userGroupOrder[$uo['user_id']]['num']+=$uo['num'];
                $userGroupOrder[$uo['user_id']]['user_id']=$uo['user_id'];
                $userGroupOrderSort[$uo['user_id']]+=$uo['num'];
                $productSortByUser[$uo['product_id'].'_'.$uo['product_color_id']][$uo['user_id']]['num']+=$uo['num'];
                $productSortByUser[$uo['product_id'].'_'.$uo['product_color_id']][$uo['user_id']]['product_id']=$uo['product_id'];
                $productSortByUser[$uo['product_id'].'_'.$uo['product_color_id']][$uo['user_id']]['user_id']=$uo['user_id'];
                $productSortByUser[$uo['product_id'].'_'.$uo['product_color_id']][$uo['user_id']]['product_color_id']=$uo['product_color_id'];
                $productSortByUserSort[$uo['product_id'].'_'.$uo['product_color_id']][$uo['user_id']]+=$uo['num'];
            }//得到分组信息

            //print_r($userlistInfo);print_r($userGroupOrder);exit;
            foreach($userlistInfo as $ulk=>$ulv){
                if(!isset($userGroupOrder[$ulk])){
                    $userGroupOrder[$ulk]=array('num'=>0,'user_id'=>$ulk);
                }
                if(!isset($userGroupOrderSort[$ulk])){
                    $userGroupOrderSort[$ulk]=0;
                }
                foreach($result['ginfo']['member'] as $giv){
                    if(!isset($productSortByUser[$giv['product_id'].'_'.$giv['product_color_id']][$ulk])){
                        $productSortByUser[$giv['product_id'].'_'.$giv['product_color_id']][$ulk]=array('num'=>0,'product_id'=>$giv['product_id'],'user_id'=>$ulk,'product_color_id'=>$giv['product_color_id']);
                    }
                    if(!isset($productSortByUserSort[$giv['product_id'].'_'.$giv['product_color_id']][$ulk])){
                        $productSortByUserSort[$giv['product_id'].'_'.$giv['product_color_id']][$ulk]=0;
                    }
                }
            }
            foreach($productSortByUser as $key=>$val){
                array_multisort($productSortByUserSort[$key],SORT_DESC,$val);
                $productSortByUser[$key]=$val;
            }
            //print_r($productSortByUser);exit;

            array_multisort($userGroupOrderSort,SORT_DESC,$userGroupOrder);

            //print_r($userGroupOrder);exit;

            $userAreaInfo=array();
            $userAreaInfoSort=array();
            //$userAreaKey=array();
            foreach($userGroupOrder as $ugo){
                $userAreaInfo[$userlistInfo[$ugo['user_id']]['area2']]['num']+=$ugo['num'];
                $userAreaInfo[$userlistInfo[$ugo['user_id']]['area2']]['area2']=$userlistInfo[$ugo['user_id']]['area2'];
                $userAreaInfoSort[$userlistInfo[$ugo['user_id']]['area2']]+=$ugo['num'];
                //$userAreaKey[$userlistInfo[$ugo['user_id']]['area2']]=1;
            }
            array_multisort($userAreaInfoSort,SORT_DESC,$userAreaInfo);
            //print_r($userAreaInfo);
            //$areaIdKeyStr=implode(',', array_keys($userAreaKey));

            //$locationInfo=$locationList->getLocationList(' id in ('.($areaIdKeyStr?$areaIdKeyStr:'\'\'').') ',array('fields'=>'id,name','key'=>'id','limit'=>1000));
            //print_r($locationInfo);

            //print_r($userlistInfo);
            $productSortByErea=array();
            $productSortByEreaSort=array();
            //print_r($productSortByUser);
            $productCount=array();

            foreach($productSortByUser as $kp=>$psbu){
                foreach($psbu as $pv){
                    $productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['num']+=$pv['num'];
                    $productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['product_id']=$pv['product_id'];
                    $productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['product_color_id']=$pv['product_color_id'];
                    if(!isset($productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['user_id_most'])){
                        $productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['user_id_most']=array('id'=>$pv['user_id'],'num'=>$pv['num'],'name'=>$userlistInfo[$pv['user_id']]['name']);
                    }else{
                        if($pv['num']>$productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['user_id_most']['num']){
                            $productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['user_id_most']=array('id'=>$pv['user_id'],'num'=>$pv['num'],'name'=>$userlistInfo[$pv['user_id']]['name']);
                        }
                    }
                    if(!isset($productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['user_id_least'])){
                        $productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['user_id_least']=array('id'=>$pv['user_id'],'num'=>$pv['num'],'name'=>$userlistInfo[$pv['user_id']]['name']);
                    }else{
                        if($pv['num']<$productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['user_id_least']['num']){
                            $productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['user_id_least']=array('id'=>$pv['user_id'],'num'=>$pv['num'],'name'=>$userlistInfo[$pv['user_id']]['name']);
                        }
                    }
                    $productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['area2']=$userlistInfo[$pv['user_id']]['area2'];
                    $productSortByEreaSort[$kp][$userlistInfo[$pv['user_id']]['area2']]+=$pv['num'];
                    $productSortByErea[$kp][$userlistInfo[$pv['user_id']]['area2']]['areaName']=$locationInfo[$userlistInfo[$pv['user_id']]['area2']]['name'];
                    if($pv['num']>0){
                        $productCount[$kp]['hasbuy']++;
                        $productCount[$kp]['subhasbuy'][$userlistInfo[$pv['user_id']]['area2']]++;
                    }
                }
            }

            foreach($productSortByErea as $key2=>$val2){
                array_multisort($productSortByEreaSort[$key2],SORT_DESC,$val2);
                $productSortByErea[$key2]=$val2;
            }
            //print_r($productSortByErea);exit;
            $userAreaGroup=$userList->find(' type =1  ',array('fields'=>'area2 , count(id) as total','limit'=>1000,'group'=>'area2','key'=>'area2'));
            $userNum=$userList->find(' type =1  ',array('fields'=>'count(*) as total'));
            $userNum=$userNum[0]['total'];

            foreach($result['ginfo']['member'] as $gk=>$gm){
                $gmk=$gm['product_id'].'_'.$gm['product_color_id'];
                $result['ginfo']['member'][$gk]['mostMember']=$userlistInfo[$productSortByUser[$gmk][0]['user_id']]['name'];
                $result['ginfo']['member'][$gk]['leastMember']=$userlistInfo[$productSortByUser[$gmk][(sizeof($productSortByUser[$gmk])-1)]['user_id']]['name'];
                $aeraLength=sizeof($productSortByErea[$gmk]);
                $result['ginfo']['member'][$gk]['skcstr']=$gmk;
                if($aeraLength<=6){
                    $result['ginfo']['member'][$gk]['arealist']=$productSortByErea[$gmk];
                }else{
                    $result['ginfo']['member'][$gk]['arealist'][]=$productSortByErea[$gmk][0];
                    $result['ginfo']['member'][$gk]['arealist'][]=$productSortByErea[$gmk][1];
                    $result['ginfo']['member'][$gk]['arealist'][]=$productSortByErea[$gmk][2];
                    $result['ginfo']['member'][$gk]['arealist'][]=$productSortByErea[$gmk][$aeraLength-3];
                    $result['ginfo']['member'][$gk]['arealist'][]=$productSortByErea[$gmk][$aeraLength-2];
                    $result['ginfo']['member'][$gk]['arealist'][]=$productSortByErea[$gmk][$aeraLength-1];
                }
            }
            //print_r($result['ginfo']['member']);exit;
            /*foreach($productInfo as $pid=>$piv){
             $productInfo[$pid]['mostMember']=$userlistInfo[$productSortByUser[$pid][0]['user_id']]['name'];
             $productInfo[$pid]['leastMember']=$userlistInfo[$productSortByUser[$pid][(sizeof($productSortByUser[$pid])-1)]['user_id']]['name'];
             $aeraLength=sizeof($productSortByErea[$pid]);
             if($aeraLength<=6){
             $productInfo[$pid]['arealist']=$productSortByErea[$pid];
             }else{
             $productInfo[$pid]['arealist'][]=$productSortByErea[$pid][0];
             $productInfo[$pid]['arealist'][]=$productSortByErea[$pid][1];
             $productInfo[$pid]['arealist'][]=$productSortByErea[$pid][2];
             $productInfo[$pid]['arealist'][]=$productSortByErea[$pid][$aeraLength-3];
             $productInfo[$pid]['arealist'][]=$productSortByErea[$pid][$aeraLength-2];
             $productInfo[$pid]['arealist'][]=$productSortByErea[$pid][$aeraLength-1];
             }
             }*/
            $result['ginfo']['skcArray']=$skcArray;
            $result['ginfo']['productInfo']=$productInfo;
            $result['ginfo']['productCount']=$productCount;
            $result['ginfo']['userNum']=$userNum;
            $result['ginfo']['userAreaGroup']=$userAreaGroup;
            $result['ginfo']['mostMember']['name']=$userlistInfo[$userGroupOrder[0]['user_id']]['name'];
            $result['ginfo']['mostMember']['num']=$userGroupOrder[0]['num'];
            $result['ginfo']['leastMember']['name']=$userlistInfo[$userGroupOrder[(sizeof($userGroupOrder)-1)]['user_id']]['name'];
            $result['ginfo']['leastMember']['num']=$userGroupOrder[(sizeof($userGroupOrder)-1)]['num'];
            $result['ginfo']['mostArea']['name']=$locationInfo[$userAreaInfo[0]['area2']]['name'];
            $result['ginfo']['mostArea']['num']=$userAreaInfo[0]['num'];
            $result['ginfo']['leastArea']['name']=$locationInfo[$userAreaInfo[sizeof($userAreaInfo)-1]['area2']]['name'];
            $result['ginfo']['leastArea']['num']=$userAreaInfo[sizeof($userAreaInfo)-1]['num'];
        }
        $result['control']  = "display";
        $result['search_f'] = "display_ad";
        $result['function']  = "displaydetail";
        Flight::display("ad/displaydetail.html", $result);
    }

    public static function Action_count($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();
        $User       = new User;
        $OrderList  = new OrderList;
        if($result['user']['type']==3&&$result['user']['username']!='0'){
            $result['orderinfo']    = $OrderList->getOrderinfoByArea($User->area2);
            $result['control']  = "count";
            Flight::display('ad/orders_zd.html', $result);
        }
    }
    
    public static function Action_user_analysis($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $result['control']  = "user_analysis";
        Flight::display("ad/user_analysis.html", $result);
    }
    
    public static function Action_exp2($r){
        Flight::validateUserHasLogin();
    
        $User 		= new User;
        $OrderList 	= new OrderList;
        $result     	= FrontSetting::build();
        $data 		= $r->query;
        $uname      = $data->uname;
        if(!isset($uname)){
            $isArea     = $data->isArea;
            $isZongdai  = $data->isZongdai;
            $master_uid = $data->master_uid;
            $area1      = $data->area1 ? $data->area1 : $User->area1;
            $area2      = $data->area2 ? $data->area2 : $User->area2;
        }
        $params 	= array();
        $params['isArea']	= $isArea;
        $params['isZongdai']	= $isZongdai;
        $params['master_uid']	= $master_uid;
        $params['area1']	= $area1;
        $params['area2']	= $area2;
        $params['uname']    = $uname;
    
        $user_exp_list	= $User->get_user_exp_list_ad($params);
        //$user_exp_list	= $User->get_user_exp_list_ad2($params);
        $num_all 	= 0;
        $exp_price_all 	= 0;
        $exp_num_all    = 0;
        $price_all 	= 0;
        $discount_price_all = 0;
        foreach($user_exp_list as &$user){
            $user_id 	= $user['id'];
            $mid 		= $user['mid'];
            $exp_price 	= $user['exp_price'];
            $exp_num    = $user['exp_num'];
            $area1 		= $user['area1'];
            $area2 		= $user['area2'];
            if(!$master_uid && $mid){
                //$exp_info               = $User->get_slave_exp_info($mid);
                //$exp_price 	= $exp_info['exp_price'];
                //$exp_num    = $exp_info['exp_num'];
                $user['exp_price'] = $exp_price;
                $user['exp_num'] = $exp_num;
                $orderinfo 	= $OrderList->getSelfOrderinfo($mid);
                //$orderinfo 	= $OrderList->getAdSelfOrderinfo($mid);
            }elseif($area2){
                $orderinfo 	= $OrderList->getAdOrderInfo1(null, $area2, $user_id);
            }elseif($area1){
                $orderinfo 	= $OrderList->getAdOrderInfo1($area1, null);
            }else{
                if($user_id){
                    $info 	= $OrderList->getOrderUserList(array('fliter_uid' => $user_id));
                    $orderinfo 	= $info[0];
                }else{
                    $orderinfo 	= array();
                }
            }
                $exp_price_all 	+= $exp_price;
                $exp_num_all    += $exp_num;

            $price_all 	+= $orderinfo['price'];
            $num_all 	+= $orderinfo['num'];
            $discount_price_all	+= $orderinfo['discount_price'];
            $user['orderinfo'] 	= $orderinfo;
            $user['exp_price_percent'] 	= $exp_price ? sprintf('%.2f%%', $orderinfo['discount_price'] / $exp_price * 100) : '-';
            $user['exp_num_percent']    = $exp_num   ? sprintf('%.2f%%', $orderinfo['num'] / $exp_num * 100) : '-';
            $user['num']    = $orderinfo['num'];
            $user['price']    = $orderinfo['price'];
        }
        if($master_uid){
            // $masterinfo     = $User->findone("mid={$master_uid}");
            // $discount_price_all     = $price_all * $masterinfo['discount'];
        }
        $company    = new Company;
        $order      = $company->ad_order;
        if($order){
            usort($user_exp_list, function($a, $b) use($order){
                return $a[$order] < $b[$order];
            });
        }
        $result['exp_list']	= $user_exp_list;
        $result['exp_price_all']	= $exp_price_all;
        $result['exp_num_all']      = $exp_num_all;
        $result['price_all']		= $price_all;
        $result['num_all']		= $num_all;
        $result['exp_price_percent']	= $exp_price_all ? sprintf('%.2f%%', $discount_price_all / $exp_price_all * 100) : '-';
        $result['exp_num_percent']      = $exp_num_all   ? sprintf('%.2f%%', $num_all / $exp_num_all * 100) : '-';
        $result['discount_price_all']		= $discount_price_all;
        $result['control'] 	= "exp2";
        $result['isArea'] 	= $isArea;
        $result['isZongdai'] 	= $isZongdai;
        $result['area1'] 	= $data->area1;
        $result['area2'] 	= $data->area2;
        $result['master_uid'] 	= $master_uid;
        $result['order']    = $order;
    
        Flight::display("ad/exp2.html", $result);
    }
    
    public static function Action_update_order_status($r){
        Flight::validateUserHasLogin();
        $data = $r->data;
        $action = $data->action;
        $uid = $data->uid;
        $result['error'] = 'true';
        $user = new User();
        if($uid){
            $userinfo = $user->findone(' id = "'.$uid.'" ');
            if($userinfo['order_status']==3){
                $status = 1;
                $result['message']='待确认';
            }else{
                $status = 3;
                $result['message']='已确认';
            }
            $user->update(array('order_status'=>$status), ' id = "'.$uid.'"  ');
            $result['error'] = 'false';
        }       
        Flight::json($result);
    }
    
    
    public static function Action_exp3($r){
        Flight::validateUserHasLogin();
    
        $User 		= new User;
        $OrderList 	= new OrderList;
        $result     	= FrontSetting::build();
        $data 		= $r->query;
        $uname      = $data->uname;
        $params 	= array();
        $params['uname']    = $uname;
        $params['ad_id']    = $User->id;
    
        $user_exp_list	= $User->get_user_exp_list_ad3($params);
        $num_all 	= 0;
        $exp_price_all 	= 0;
        $exp_num_all    = 0;
        $price_all 	= 0;
        $discount_price_all = 0;
        foreach($user_exp_list as &$user){
            $user_id 	= $user['id'];
            $mid 		= $user['mid'];
            $exp_price 	= $user['exp_price'];
            $exp_num    = $user['exp_num'];
            $area1 		= $user['area1'];
            $area2 		= $user['area2'];
            if(!$master_uid && $mid){
                $orderinfo 	= $OrderList->getSelfOrderinfo($mid);
            }elseif($area2){
                $orderinfo 	= $OrderList->getAdOrderInfo1(null, $area2, $user_id);
            }elseif($area1){
                $orderinfo 	= $OrderList->getAdOrderInfo1($area1, null);
            }else{
                if($user_id){
                    $info 	= $OrderList->getOrderUserList(array('fliter_uid' => $user_id));
                    $orderinfo 	= $info[0];
                }else{
                    $orderinfo 	= array();
                }
            }
            if(!$master_uid){
                $exp_price_all 	+= $exp_price;
                $exp_num_all    += $exp_num;
            }else{
                if($master_uid == $mid){
                    $exp_price_all = $exp_price;
                    $exp_num_all   = $exp_num;
                }
            }
            $price_all 	+= $orderinfo['price'];
            $num_all 	+= $orderinfo['num'];
            $discount_price_all	+= $orderinfo['discount_price'];
            $user['orderinfo'] 	= $orderinfo;
            $user['exp_price_percent'] 	= $exp_price ? sprintf('%.2f%%', $orderinfo['discount_price'] / $exp_price * 100) : '-';
            $user['exp_num_percent']    = $exp_num   ? sprintf('%.2f%%', $orderinfo['num'] / $exp_num * 100) : '-';
            $user['num']    = $orderinfo['num'];
            $user['price']    = $orderinfo['price'];
        }
        $company    = new Company;
        $order      = $company->ad_order;
        if($order){
            usort($user_exp_list, function($a, $b) use($order){
                return $a[$order] < $b[$order];
            });
        }
        $result['exp_list']	= $user_exp_list;
        $result['exp_price_all']	= $exp_price_all;
        $result['exp_num_all']      = $exp_num_all;
        $result['price_all']		= $price_all;
        $result['num_all']		= $num_all;
        $result['exp_price_percent']	= $exp_price_all ? sprintf('%.2f%%', $discount_price_all / $exp_price_all * 100) : '-';
        $result['exp_num_percent']      = $exp_num_all   ? sprintf('%.2f%%', $num_all / $exp_num_all * 100) : '-';
        $result['discount_price_all']		= $discount_price_all;
        $result['control'] 	= "exp";
        $result['isArea'] 	= $isArea;
        $result['isZongdai'] 	= $isZongdai;
        $result['area1'] 	= $data->area1;
        $result['area2'] 	= $data->area2;
        $result['master_uid'] 	= $master_uid;
        $result['order']    = $order;
    
        Flight::display("ad/exp3.html", $result);
    }
    
    public static function Action_ordersview($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $result['control']  = "ordersview";
        $result['category_array']=array('kuanhao'=>'款号','wave'=>'波段','category'=>'大类','classes'=>'小类','theme'=>'主题','style'=>'款别','brand'=>'品牌','season'=>'季节','series'=>'系列');
        Flight::display('ad/ordersview.html', $result);
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
        if($User->username=="0"){
            if($data->area1){
                $options['area1'] = $data->area1;
            }
            if($data->area2){
                $options['area2'] = $data->area2;
            }
        }else{
            $options['ad_id'] = $User->id;
        }
        if($data->fliter_uid){
            $options['fliter_uid'] = $data->fliter_uid;
        }
        //list($rank, $orderinfo) = $OrderList->getRank($User->id, $options);
        $orderinfo = $OrderList->getZDBySkcStatus(0, $options);
        if($orderinfo['skc'])     $result['depth']    = sprintf("%d", $orderinfo['num'] / $orderinfo['skc']);
        if($SKC_ALL) $result['width']    = sprintf("%d%%", $orderinfo['skc'] / $SKC_ALL * 100);
        $result['SKC_ALL']  = $SKC_ALL;
        $exp_info   = $User->get_ad_exp_info($User->id);
    
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
    
        Flight::display('ad/myorders_summary.html', $result);
    }
    
    public static function Action_groupdetail_skc($r, $id){    
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $ProductGroup       = new ProductGroup($id);
    
        if($ProductGroup->id){
    
            $ProductGroupMember = new ProductGroupMember();
              
            $result['group']    = $ProductGroup->getAttribute();//得到搭配的所有信息
    
            $locationList=new Location();
            $greatLocation=$locationList->getLocationList(' pid=0 ',array('fields'=>'id,name','limit'=>1000));
            array_unshift($greatLocation,array('id'=>0,'name'=>'所有大区'));
            $result['greatLocation']=$greatLocation;  //得到所有大区的信息
    
            $result['location_id']=($r->query->location_id?$r->query->location_id:0);//设置当前选择的大区信息
            $result['current_id']=$id;
    
            $chooseAllUser = ($result['location_id']?false:true);
            $locationWhere = ($result['location_id']?' pid="'.$result['location_id'].'" ' : ' pid>0 ');
            $locationInfo = $locationList->getLocationList($locationWhere,array('fields'=>'id,name','key'=>'id','limit'=>1000));//得到当前选择大区的区域的所有信息
            $result['locationInfo']=$locationInfo;
            $userList=new User();
            if(!$chooseAllUser){
                $locationStr=implode(',', array_keys($locationInfo));
                $userWhere = ' type=1 AND area2 in ('.($locationStr?$locationStr:'\'\'').') ';
            }else{
                $userWhere=' type=1 ';
            }
            $userlistInfo=$userList->find($userWhere,array('fields'=>'id,area2,name','key'=>'id','limit'=>1000));//得到当前选择大区的所有用户信息
            $result['userlistInfo']=$userlistInfo;
            if(!$chooseAllUser){
                $userListStr=implode(',', array_keys($userlistInfo));
            }
             
            if(!$chooseAllUser){
                $orderWhere = 'AND user_id in ('.($userListStr?$userListStr:'\'\'').') ';
            }else{
                $orderWhere = '';
            }
    
            $OrderListProduct=new OrderListProduct();
            $orderLists=new OrderList();
            $orderList=array();
    
            $orderList = $orderLists->findone('o.product_id = pgm.product_id and o.product_color_id = pgm.color_id and pgm.group_id = "'.$id.'" '.$orderWhere,array('tablename'=>'orderlist o, product_group_member pgm','fields'=>'sum(o.num) as num ,pgm.group_id','db_debug'=>false));
            $result['ginfo']['total']=$orderList['num'];
    
            $productList=new Product();
            $productSkcInfo=$productList->find('pgm.product_id=p.id and pgm.product_id = pc.product_id and pgm.color_id = pc.color_id and pgm.group_id= '.$id.' and pc.status=1 and p.status = 1 ',array('fields'=>'p.id,p.name,p.defaultimage,p.bianhao,p.price,p.category_id,pc.skc_id,pc.color_id','tablename'=>'product p , product_group_member pgm , product_color pc  ','db_debug'=>false,'limit'=>10000));
            $productInfo = array();
            $displayGinfo = array();
            foreach($productSkcInfo as $pi){
                if(!$productInfo[$pi['id']]){
                    $productInfo[$pi['id']] = $pi;
                    $productInfo[$pi['id']]['rank']=$OrderListProduct->get_rank($pi['id']);
                    $productInfo[$pi['id']]['category_name'] = Keywords::cache_get(array($pi['category_id']));
                }
                $displayGinfo[$pi['id'].'_'.$pi['color_id']] = array('num'=>0,'product_id'=>$pi['id'],'product_color_id'=>$pi['color_id'],'skc'=>$pi['skc_id']);
            }
    
            $userOrder=$orderLists->find(' o.product_id = pdm.product_id and o.product_color_id = pdm.color_id and pdm.group_id = '.$id.' '.$orderWhere,array('fields'=>'o.user_id, o.product_id,sum(num) as num,o.product_color_id','limit'=>10000000,'db_debug'=>false,'group'=>'o.user_id,o.product_id,o.product_color_id','tablename'=>'orderlist o , product_group_member pdm'));
             
    
            $userGroupOrder=array();
            $areaGroupOrder=array();
    
            foreach($userOrder as $uok => $uov){
                if(!$userGroupOrder[$uov['user_id']]){
                    $userGroupOrder[$uov['user_id']]['user_id'] = $uov['user_id'];
                    $userGroupOrder[$uov['user_id']]['user_name'] = $userlistInfo[$uov['user_id']]['name'];
                }
                $userGroupOrder[$uov['user_id']]['num'] += $uov['num'];
    
    
                if(!$areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]){
                    $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['area_id'] = $userlistInfo[$uov['user_id']]['area2'];
                    $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['area_name'] = $locationInfo[$userlistInfo[$uov['user_id']]['area2']]['name'];
                }
                $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['num'] += $uov['num'];
    
    
                $displayGinfo[$uov['product_id'].'_'.$uov['product_color_id']]['num'] += $uov['num'];
                $displayGinfo[$uov['product_id'].'_'.$uov['product_color_id']]['userInfo'][$uov['user_id']]['user_id']=$uov['user_id'];
                $displayGinfo[$uov['product_id'].'_'.$uov['product_color_id']]['userInfo'][$uov['user_id']]['num']+=$uov['num'];
    
            }
    
            foreach($userlistInfo as $uik=>$uiv){
                if(!isset($userGroupOrder[$uiv['id']])){
                    $userGroupOrder[$uiv['id']] = array('user_id'=>$uiv['id'],'user_name'=>$uiv['name'],'num'=>0);
                }
            }
    
            foreach($locationInfo as $lik=>$liv){
                if(!isset($areaGroupOrder[$liv['id']])){
                    $areaGroupOrder[$liv['id']] = array('area_id'=>$liv['id'],'area_name'=>$liv['name'],'num'=>0);
                }
            }
    
            usort($userGroupOrder,function($a,$b){
                $rank_a = $a['num'];
                $rank_b = $b['num'];
                return $rank_a<$rank_b;
            });
    
                $result['ginfo']['mostMember'] = $userGroupOrder[0];
                $result['ginfo']['leastMember'] = $userGroupOrder[(sizeof($userGroupOrder)-1)];
    
                usort($areaGroupOrder,function($a,$b){
                    $rank_a = $a['num'];
                    $rank_b = $b['num'];
                    return $rank_a<$rank_b;
                });
    
                    $result['ginfo']['mostArea'] = $areaGroupOrder[0];
                    $result['ginfo']['leastArea'] = $areaGroupOrder[(sizeof($areaGroupOrder)-1)];
    
                    usort($displayGinfo,function($a,$b){
                        $rank_a = $a['num'];
                        $rank_b = $b['num'];
                        return $rank_a<$rank_b;
                    });
    
                        foreach($displayGinfo as $dgk=>$dgv){
                            if(isset($dgv['userInfo'])){
                                $userInfo = $dgv['userInfo'];
                                $displayGinfo[$dgk]['hasbuy']=sizeof($userInfo);
                                foreach($userlistInfo as $uiok=>$uiov){
                                    if(!isset($userInfo[$uiov['id']])){
                                        $userInfo[$uiov['id']] = array('user_id'=>$uiov['id'],'num'=>0);
                                    }
                                }
                                usort($userInfo,function($a,$b){
                                    $rank_a = $a['num'];
                                    $rank_b = $b['num'];
                                    return $rank_a<$rank_b;
                                });
                                $displayGinfo[$dgk]['userInfo'] = array($userInfo[0],$userInfo[(sizeof($userInfo)-1)]);
                                $areaInfo = array();
                                foreach($userInfo as $uok=>$uov){
                                    $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['num']+=$uov['num'];
                                    $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['area_id']=$userlistInfo[$uov['user_id']]['area2'];
                                    $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['users'][]=$uov;
                                    if($uov['num']>0){
                                        $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['hasbuy']+=1;
                                    }
                                }
                                usort($areaInfo,function($a,$b){
                                    $rank_a = $a['num'];
                                    $rank_b = $b['num'];
                                    return $rank_a<$rank_b;
                                });
                                $displayGinfo[$dgk]['areaInfo'] = $areaInfo;
                            }
                        }
    
                        $userAreaGroup=$userList->find(' type =1  ',array('fields'=>'area2 , count(id) as total','limit'=>1000,'group'=>'area2','key'=>'area2'));
                        $userNum=$userList->find($userWhere,array('fields'=>'count(*) as total'));
                        $userNum=$userNum[0]['total'];
                        $result['ginfo']['member']=$displayGinfo;
                        $result['ginfo']['productInfo']=$productInfo;
                        $result['ginfo']['userNum']=$userNum;
                        $result['ginfo']['userAreaGroup']=$userAreaGroup;
        }
        $result['control']  = "group";
        $result['search_f'] = "group_ad";
        $result['function']  = "groupdetail_skc";
        Flight::display("ad/groupdetail_skc.html", $result);
    }
    
    public static function Action_displaydetailnew($r, $id){
        Flight::validateUserHasLogin();
    
        $result                 = FrontSetting::build();
    
        $ProductDisplay         = new ProductDisplay($id);
        if($ProductDisplay->id){
    
            $result['group']    = $ProductDisplay->getAttribute();//得到陈列的所有信息
    
    
            $locationList=new Location();
            $greatLocation=$locationList->getLocationList(' pid=0 ',array('fields'=>'id,name','limit'=>1000));
            array_unshift($greatLocation,array('id'=>0,'name'=>'所有大区'));
            $result['greatLocation']=$greatLocation;  //得到所有大区的信息
    
            $result['location_id']=($r->query->location_id?$r->query->location_id:0);//设置当前选择的大区信息
            $result['search_uname']=($r->query->uname?$r->query->uname:'');
            $result['current_id']=$id;
    
            $chooseAllUser = ($result['location_id']?false:true);
            $searchUser = ($result['search_uname']?true:false);
            $locationWhere = ($result['location_id']?' pid="'.$result['location_id'].'" ' : ' pid>0 ');
            $locationInfo = $locationList->getLocationList($locationWhere,array('fields'=>'id,name','key'=>'id','limit'=>1000));//得到当前选择大区的区域的所有信息
            $result['locationInfo']=$locationInfo;
            $userList=new User();
            if(!$chooseAllUser){
                $locationStr=implode(',', array_keys($locationInfo));
                $userWhere = ' type=1 AND area2 in ('.($locationStr?$locationStr:'\'\'').') ';
            }else{
                $userWhere=' type=1  ';
            }
            if($searchUser){
                $userWhere.=' AND username ="'.$result['search_uname'].'"  ';
            }
            $userlistInfo=$userList->find($userWhere,array('fields'=>'id,area2,name','key'=>'id','limit'=>1000));//得到当前选择大区的所有用户信息
            $result['userlistInfo']=$userlistInfo;
            //print_r($locationInfo);
            //print_r($userlistInfo);exit;
            if(!$chooseAllUser||$searchUser){
                $userListStr=implode(',', array_keys($userlistInfo));
            }
    
            if(!$chooseAllUser||$searchUser){
                $orderWhere = ' AND o.user_id in ('.($userListStr?$userListStr:'\'\'').') ';
            }else{
                $orderWhere = '';
            }
            if($searchUser){              
                if($userlistInfo){
                    $tmplocation = array();
                    foreach($userlistInfo as $ulival){
                        $tmplocation[$ulival['area2']] = $locationInfo[$ulival['area2']];
                        continue;
                    }
                    $locationInfo = $tmplocation;
                }
            }
            //print_r($userlistInfo);
    
            $ProductGroupMember = new ProductDisplayMember;
            $OrderListProduct=new OrderListProduct();
            $orderLists=new OrderListDetail();
            $orderList=array();
            $orderListNew = array();
            
            $orderList = $orderLists->findone('o.product_id = pd.product_id and o.product_color_id = pd.keyword_id and pd.display_id = "'.$id.'" and o.display_id =pd.display_id '.$orderWhere,array('tablename'=>'orderlistdetail o, product_display_member_color pd','fields'=>'sum(o.num) as num ,pd.display_id','db_debug'=>false));
            $result['ginfo']['total']=$orderList['num'];
            
            $productList=new Product();
            $productSkcInfo=$productList->find('pdmc.product_id=p.id and pdmc.product_id = pc.product_id and pdmc.keyword_id = pc.color_id and pdmc.display_id= '.$id.' and pc.status=1 and p.status = 1 ',array('fields'=>'p.id,p.name,p.defaultimage,p.bianhao,p.price,p.category_id,pc.skc_id,pc.color_id','tablename'=>'product p , product_display_member_color pdmc , product_color pc ','db_debug'=>false,'limit'=>10000));
            $productInfo = array();
            $displayGinfo = array();
            foreach($productSkcInfo as $pi){
                if(!$productInfo[$pi['id']]){
                    $productInfo[$pi['id']] = $pi;
                    $productInfo[$pi['id']]['rank']=$OrderListProduct->get_rank($pi['id']);
                    $productInfo[$pi['id']]['category_name'] = Keywords::cache_get(array($pi['category_id']));
                }
                $displayGinfo[$pi['id'].'_'.$pi['color_id']] = array('num'=>0,'product_id'=>$pi['id'],'product_color_id'=>$pi['color_id'],'skc'=>$pi['skc_id']);
            }
    
            $userOrder=$orderLists->find(' o.product_id = pd.product_id and o.product_color_id = pd.keyword_id and pd.display_id = '.$id.' and o.display_id =pd.display_id '.$orderWhere,array('fields'=>'o.user_id, o.product_id,sum(num) as num,o.product_color_id','limit'=>10000000,'db_debug'=>false,'group'=>'o.user_id,o.product_id,o.product_color_id','tablename'=>'orderlistdetail o , product_display_member_color pd'));
            $userGroupOrder=array();
            $areaGroupOrder=array();
            $skcNum = array();
        
            foreach($userOrder as $uok => $uov){
                if(!$userGroupOrder[$uov['user_id']]){
                    $userGroupOrder[$uov['user_id']]['user_id'] = $uov['user_id'];
                    $userGroupOrder[$uov['user_id']]['user_name'] = $userlistInfo[$uov['user_id']]['name'];
                }
                $userGroupOrder[$uov['user_id']]['num'] += $uov['num'];
        
        
                if(!$areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]){
                    $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['area_id'] = $userlistInfo[$uov['user_id']]['area2'];
                    $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['area_name'] = $locationInfo[$userlistInfo[$uov['user_id']]['area2']]['name'];
                }
                $areaGroupOrder[$userlistInfo[$uov['user_id']]['area2']]['num'] += $uov['num'];
        
        
                $displayGinfo[$uov['product_id'].'_'.$uov['product_color_id']]['num'] += $uov['num'];
                $displayGinfo[$uov['product_id'].'_'.$uov['product_color_id']]['userInfo'][$uov['user_id']]['user_id']=$uov['user_id'];
                $displayGinfo[$uov['product_id'].'_'.$uov['product_color_id']]['userInfo'][$uov['user_id']]['num']+=$uov['num'];
        
            }
            foreach($userlistInfo as $uik=>$uiv){
                if(!isset($userGroupOrder[$uiv['id']])){
                    $userGroupOrder[$uiv['id']] = array('user_id'=>$uiv['id'],'user_name'=>$uiv['name'],'num'=>0);
                }
            }
        
            foreach($locationInfo as $lik=>$liv){
                if(!isset($areaGroupOrder[$liv['id']])){
                    $areaGroupOrder[$liv['id']] = array('area_id'=>$liv['id'],'area_name'=>$liv['name'],'num'=>0);
                }
            }
        
            usort($userGroupOrder,function($a,$b){
                $rank_a = $a['num'];
                $rank_b = $b['num'];
                return $rank_a<$rank_b;
            });
        
                $result['ginfo']['mostMember'] = $userGroupOrder[0];
                $result['ginfo']['leastMember'] = $userGroupOrder[(sizeof($userGroupOrder)-1)];
        
                usort($areaGroupOrder,function($a,$b){
                    $rank_a = $a['num'];
                    $rank_b = $b['num'];
                    return $rank_a<$rank_b;
                });
        
                    $result['ginfo']['mostArea'] = $areaGroupOrder[0];
                    $result['ginfo']['leastArea'] = $areaGroupOrder[(sizeof($areaGroupOrder)-1)];
        
                    usort($displayGinfo,function($a,$b){
                        $rank_a = $a['num'];
                        $rank_b = $b['num'];
                        return $rank_a<$rank_b;
                    });
                    foreach($displayGinfo as $dgk=>$dgv){
                        if(isset($dgv['userInfo'])){
                            $userInfo = $dgv['userInfo'];
                            $displayGinfo[$dgk]['hasbuy']=sizeof($userInfo);
                            foreach($userlistInfo as $uiok=>$uiov){
                                if(!isset($userInfo[$uiov['id']])){
                                    $userInfo[$uiov['id']] = array('user_id'=>$uiov['id'],'num'=>0);
                                }
                            }
                            usort($userInfo,function($a,$b){
                                $rank_a = $a['num'];
                                $rank_b = $b['num'];
                                return $rank_a<$rank_b;
                            });
                            $displayGinfo[$dgk]['userInfo'] = array($userInfo[0],$userInfo[(sizeof($userInfo)-1)]);
                            $areaInfo = array();
                            foreach($userInfo as $uok=>$uov){
                                $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['num']+=$uov['num'];
                                $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['area_id']=$userlistInfo[$uov['user_id']]['area2'];
                                $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['users'][]=$uov;
                                if($uov['num']>0){
                                    $areaInfo[$userlistInfo[$uov['user_id']]['area2']]['hasbuy']+=1;
                                }
                            }
                            usort($areaInfo,function($a,$b){
                                $rank_a = $a['num'];
                                $rank_b = $b['num'];
                                return $rank_a<$rank_b;
                            });
                            $displayGinfo[$dgk]['areaInfo'] = $areaInfo;
                        }
                    }
        
                    $userAreaGroup=$userList->find(' type =1  ',array('fields'=>'area2 , count(id) as total','limit'=>1000,'group'=>'area2','key'=>'area2'));
                    $userNum=$userList->find($userWhere,array('fields'=>'count(*) as total'));
                    $userNum=$userNum[0]['total'];
                    $result['ginfo']['member']=$displayGinfo;
                    $result['ginfo']['productInfo']=$productInfo;
                    $result['ginfo']['userNum']=$userNum;
                    $result['ginfo']['userAreaGroup']=$userAreaGroup;
        }
        $result['control']  = "display_new";
        $result['search_f'] = "display_ad_new";
        $result['function']  = "displaydetailnew";
        Flight::display("ad/displaydetailnew.html", $result);
    }
    
    public static function Action_exp_print_new($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
    
        $condition  = array();
        $User       = new User;
    
        if($User->type == 3 && $User->username != "0"){
            /*if($User->area1){
             $condition['area1'] = $User->area1;
             }
             if($User->area2){
             $condition['area2'] = $User->area2;
             }*/
            $condition['ad_id'] = $User->id;
        }
    
        $result['printInfo'] = $User->get_print_info($condition);
    
        $result['control']  = "exp_print_new";
    
        if($User->type == 3 && $User->username != "0"){
            Flight::display("ad/exp_print_ad.html", $result);
        }else{
            Flight::display("ad/exp_print_new.html", $result);
        }
    
    }
    
    public static function Action_product_color_moq($r){
        Flight::validateUserHasLogin();
        $result =   FrontSetting::build();
        
        
        $result['control']    =  "product_color_moq";

        Flight::display("ad/product_color_moq.html",$result);
    }
    
    public static function Action_product_color_moq_table($r){
        Flight::validateUserHasLogin();
        $data   =   $r->query;
        $limit      = $data->limit ? $data->limit : 20;
        $p          = $data->p  ? $data->p : 1;
        
        $moq_status     = $data->moq_status;
        
        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
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
        
        if($style_id)       $cond[]    = "p.style_id in ({$style_id})";
        if($category_id)    $cond[]    = "p.category_id in ({$category_id})";
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
        
        switch ($moq_status){
            case 'standard':
                $cond[] =   "pcm.num < o.num";
                break;
            case 'unstandard':
                $cond[] =   "pcm.num >= o.num";
                break;
            default:
                break;
        }
        $cond[] =   "pc.status <>0";
        $where  =   implode(" AND ", $cond);
        
        $options['limit']   =   $limit;
        $options['page']    =   $p;
        $options['tablename']   =   "product as p left join product_color as pc on p.id=pc.product_id 
                                     left join product_color_moq as pcm on p.id=pcm.product_id and pc.color_id=pcm.product_color_id
                                     left join orderlistproductcolor as o on p.id=o.product_id and pc.color_id=o.product_color_id";
        $options['fields']  =   "p.id,pc.color_id,p.kuanhao,p.name,p.category_id,p.classes_id,p.wave_id,pcm.num as moq_num,o.num as order_num";
        $options['order']   =   "p.id,pc.color_id";
        //$options['db_debug']=   true;
        $Product    =   new Product();
        $list   =   $Product->find($where,$options);
        $result['page'] =   $p;
        $result['list'] =   $list;
        Flight::display("ad/product_color_moq_table.html",$result);
    }

    public static function Action_exp_print_new2($r){
        Flight::validateUserHasLogin();
    
        $result     = FrontSetting::build();
        
        $User       = new User;

        $Factory  = new ProductsAttributeFactory('property');
        $result['property_list']    = $Factory->getAllList();
        $result['control']  = "exp_print_new2";
    
        Flight::display("ad/exp_print_new2.html", $result);
    
    }

    public static function Action_product($r, $id){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $Product        = new Product($id);
        $result['productdetail']    = $Product->getAttribute();
        if($Product->id){
            $ProductImage   = new ProductImage;
            $OrderList      = new OrderList;
            $User           = new User;
            $Keywords       = new Keywords;
            $ProductColor   = new ProductColor;

            $condition      = array();
            $options        = array();
            $options['key']     = "product_id";
            $options['status']  = false;
            $options['fields_more'] = "o.product_id";
            $condition['product_id']    = $id;
            $order_all  = $OrderList->getOrderAnalysisList($condition, $options);
            $result['order_all']    = $order_all[$id];
            //$condition['ad_area1']  = $User->area1;
            //$condition['ad_area2']  = $User->area2;
            if($User->username!='0'){
                $condition['ad_id'] = $User->id;
            }
            $order_user = $OrderList->getOrderAnalysisList($condition, $options);
            $result['order_user']   = $order_user[$id];
            $order_user_num     = $result['order_user']['num'];
            //print_r($order_user);

            $keys   = array('style', 'classes', 'category','medium', 'price_band', 'wave', 'series');
            foreach($keys as $key){
                $kid    = "{$key}_id";
                $result['productdetail'][$key]  = $Keywords->getName_File($result['productdetail'][$kid]);
            }

            $result['imagelist']    = $ProductImage->find("product_id={$id}", array());
            if($User->username!='0'){
                $orderlist              = $OrderList->getAdProOrderList($User->id,$id);
            }else{
                $orderlist              = $OrderList->getAdOrderList($User->area1, $User->area2, $id);
            }
            
            $OrderTable             = new OrderTable($id, $orderlist);
            $result['ordertable']   = $OrderTable->byHtml("user");
            $result['ordertableall']= $OrderTable->byHtml("all");
            $OrderListProduct       = new OrderListProduct;
            $result['rank']         = $OrderListProduct->get_rank($id);
            if($User->username!='0'){
                $hadBuy = $OrderList->findone("o.product_id={$id} and u.ad_id={$User->id}",array('tablename'=>'orderlist o left join user u on o.user_id = u.id','fields'=>'count(DISTINCT user_id) as total,GROUP_CONCAT(DISTINCT user_id) as cid'));         
            }else{
                $hadBuy = $OrderList->findone("product_id={$id}",array('fields'=>'count(DISTINCT user_id) as total,GROUP_CONCAT(DISTINCT user_id) as cid'));
            }
            $cond = array();
            $cond[] = 'type=1';
            if($User->username!='0'){
                $cond[] = ' ad_id = "'.$User->id.'" ';
            }
            if($hadBuy['cid']){
                $cond[] = ' id not in ('.$hadBuy['cid'].') ';                
            }
            $condwhere = implode(' AND ', $cond);
            //print_r($cond);exit;
            $ulist = $User->find($condwhere, array("limit"=>10000, "fields"=>"name"));
            $result['hadBuy'] = $hadBuy['total'];
            $result['hadBuyavg'] = $hadBuy['total']?round(($order_user_num/$hadBuy['total']),1):0;
            $color_list     = $ProductColor->get_color_list($id);
            $color_list   = ProductsAttributeFactory::fetch($color_list, 'color', "color_id", "products_color");
            usort($color_list, function($a, $b){
                return $a['products_color']['rank'] > $b['products_color']['rank'] ? 1 : -1;
            });
            $result['color_list']   = $color_list;
            $result['unorderulist'] = $ulist;
        }

        Flight::display("ad/product.html", $result);
    }
}

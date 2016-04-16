<?php
class Control_custom {
    public static function _beforeCall($r, $id=0){
        Flight::validateUserHasLogin();
    }

    public static function Action_index($r){
        Flight::notFound();        
    }

    /*真我永恒报表开始*/
    public static function Action_meforever_report($r){
        $result     = FrontSetting::build();

        $User   =   new User();
        if($User->type==3){
            $options['limit']    =   1000;
            $zongdai_list   =   $User->get_zongdai_list($options);
    
            $result['zongdai_list'] =   $zongdai_list;
        }
        $result['control'] 	=	"meforever_report";
        Flight::display("custom/meforever/report.html",$result);
    }
    public static function Action_meforever_history($r){
    	$result     = FrontSetting::build();

        $result['control'] 	=	"history";
        Flight::display("custom/meforever/history.html",$result);
    }
    public static function Action_meforever_report_table($r){
    	$data           =   $r->query;
        $master_user_id =   $data->master_user_id;
        
        $limit          =   $data->limit ? $data->limit : 15;   //排名前15
        
        $User       =   new User;
        $UserSlave  =   new UserSlave;
        $OrderList  =   new OrderList;
        $Product    =   new Product();
        $skc_total  =   $Product->get_SKC();
        
        if($User->type==2){
            $master_user_id =   $User->id;
        }        
        if($master_user_id){
	        $slave_user_list    =   $UserSlave->get_slave_user_list($master_user_id);
	        //print_r($slave_user_list);exit;
	        $list               =   array();
	        foreach ($slave_user_list as $user){
	            $options            =   array();
	            $options['fields']  =   "user_id,product_id,product_color_id,count(distinct product_id) as sku,count(distinct product_id,product_color_id) as skc,sum(num) as num,sum(amount) as price,sum(discount_amount) as discount_price";
	            //$options['db_debug']    =   true;
	            
	            $user_id    =   $user['id'];
	            $list[$user_id]['total'] =  $OrderList->findone("user_id={$user_id}",$options);
	            
	            $list[$user_id]['info']['name'] =   $user['name'];
	            $list[$user_id]['info']['exp_price']     =   $user['exp_price'];
	            
	            $options['group']   =   "product_id,product_color_id";
	            $options['limit']   =   $limit;
	            $options['order']   =   "num DESC";
	            //$options['db_debug']=   true;
	            $info =  $OrderList->find("user_id={$user_id}",$options);
	            foreach ($info as $row){
	                $list[$user_id]['info']['num']  += $row['num'];
	                $list[$user_id]['info']['price']+= $row['price'];
	            }
	        } 

	        $options    		=   array();
	        $options['fields']  =   "user_id,area,sum(ship_num) as ship_num,sum(ship_price) as ship_price";
	        $options['group']   =   "user_id";
	        $options['key']     =   "user_id";
	        
	        $options['limit']   =   100000;
	        
	        $OrderListHis   =   new CustomMeforeverHistory;
	        $history_list   =   $OrderListHis->find("1",$options);

	        foreach($list as $key=>&$row){
	            $row['total']['skc_num']    =   sprintf("%.1f", $row['total']['skc']/$row['total']['sku']);
	            $row['total']['skc_perent'] =   sprintf("%.1f%%", $row['total']['skc']/$skc_total* 100);
	            $row['total']['skc_depth']  =   sprintf("%.1f", $row['total']['num']/$row['total']['skc']);
	            
	            $row['info']['num_perent']  =   sprintf("%.1f%%", $row['info']['num']/$row['total']['num'] * 100);
	            $row['info']['price_perent']=   sprintf("%.1f%%", $row['info']['price']/$row['total']['price'] * 100);
	            $row['info']['skc_depth']   =   sprintf("%.1f" , $row['info']['num']/$limit);         //SKC深度
	            $row['info']['exp_perent']  =   sprintf("%.1f%%", $row['total']['price']/$row['info']['exp_price'] * 100);
	            
	            $row['his']['area']         =   $history_list[$key]['area'];
	            $row['his']['ship_price']   =   $history_list[$key]['ship_price'];
	            if($history_list[$key]['area'])
	                $row['his']['area_price']   =   sprintf("%.1f" ,($history_list[$key]['ship_price']/$history_list[$key]['area']));
	            unset($row);
	        }
	        //print_r($list);exit;
	        $showlist       =   array();
	        $n  =   0;
	        foreach ($list as $row){
	            $showlist[$n++] =   $row;
	        }
	        
	        usort($showlist, function ($a,$b){
	            return $a['total']['num']<$b['total']['num'];
	        });
	        
	        //print_r($showlist);exit;
	        $result['list'] =   $showlist;
    	}
        Flight::display('custom/meforever/report_table.html', $result);
    }

    public static function Action_meforever_history_table($r){
    	$data           =   $r->query;
        $fliter_uid     =   $data->fliter_uid;
        $area1          =   $data->area1;
        $area2          =   $data->area2;
        
        $User   =   new User();
        switch ($User->type) {
            case 3  :
                if($User->username!="0"){
                    $condition[]  = "u.zd_id={$User->id}";
                }
                break;
            default :
                $condition[]   = "user_id={$User->id}";
                break;
        }
        if($area1)  $condition[]   = "u.area1={$area1}";
        if($area2)  $condition[]   = "u.area2={$area2}";
        if($fliter_uid) $condition[]    =   "user_id={$fliter_uid}";
        $where  =   implode(" AND ", $condition);
        
        $history    =   new CustomMeforeverHistory();
        $options    =   array();
        $options['tablename']=  "custom_meforever_history left join user as u on user_id=u.id";
        $options['fields']   =  "category_id,medium_id,wave_id,sum(ship_num) as ship_num,sum(ship_price) as ship_price,sum(ship_skc) as ship_skc,sum(sales_num) as sales_num,sum(sales_price) as sales_price,sum(sales_skc) as sales_skc,sum(order_num) as order_num,sum(order_price) as order_price,sum(order_skc) as order_skc,sum(stock_num) as stock_num,sum(stock_price) as stock_price";
        
        // $options['db_debug']    =   true;
        $total_list     =   $history->findone($where,$options);
        
        $list   =   array();
        $list['order']['num']       =   $total_list['order_num'];
        $list['order']['price']     =   $total_list['order_price'];
        $list['order']['skc']       =   $total_list['order_skc'];
        $list['ship']['num']        =   $total_list['ship_num'];
        $list['ship']['price']      =   $total_list['ship_price'];
        $list['ship']['skc']        =   $total_list['ship_skc'];
        $list['sales']['num']       =   $total_list['sales_num'];
        $list['sales']['price']     =   $total_list['sales_num'];
        $list['sales']['skc']       =   $total_list['sales_skc'];
        $list['stock']['num']       =   $total_list['stock_num'];
        $list['stock']['price']     =  $total_list['stock_price'];
        $list['num_perent']         =   sprintf("%.1f%%", $list['sales']['num']/$list['ship']['num'] * 100);
        $list['price_perent']       =   sprintf("%.1f%%", $list['sales']['price']/$list['ship']['price'] * 100);
        $list['skc_perent']         =   sprintf("%.1f%%", $list['sales']['skc']/$list['ship']['skc'] * 100);
        $list['stock_num_perent']   =   sprintf("%.1f%%", $list['stock']['num']/$list['order']['num'] * 100);
        $list['stock_price_perent'] =   sprintf("%.1f%%", $list['stock']['price']/$list['order']['price'] * 100);
        $options['group']   =   "category_id";
        $category_list  =   $history->find($where,$options);
        
        foreach($category_list as &$cate){
            $cate['ship_perent']    =   sprintf("%.1f%%", $cate['ship_num']/$total_list['ship_num'] * 100);
            $cate['sales_perent']   =   sprintf("%.1f%%", $cate['sales_num']/$total_list['sales_num'] * 100);
            $cate['num_perent']     =   sprintf("%.1f%%", $cate['sales_num']/$cate['ship_num'] * 100);
            $cate['skc_perent']     =   sprintf("%.1f%%", $cate['sales_skc']/$cate['ship_skc'] * 100);
            $cate['contribute']     =   sprintf("%.1f%%", $cate['sales_perent']/$cate['ship_perent'] * 100);
        }
        
        $options['group']   =   "medium_id ";
        $medium_list        =   $history->find($where,$options);
        
        foreach($medium_list as &$med){
            $med['ship_perent']    =   sprintf("%.1f%%", $med['ship_num']/$total_list['ship_num'] * 100);
            $med['sales_perent']   =   sprintf("%.1f%%", $med['sales_num']/$total_list['sales_num'] * 100);
            $med['num_perent']     =   sprintf("%.1f%%", $med['sales_num']/$med['ship_num'] * 100);
            $med['skc_perent']     =   sprintf("%.1f%%", $med['sales_skc']/$med['ship_skc'] * 100);
            $med['contribute']     =   sprintf("%.1f%%", $med['sales_perent']/$med['ship_perent'] * 100);
        }

        $options['group']   =   "wave_id ";
        $wave_list        =   $history->find($where,$options);
        
        foreach($wave_list as &$wav){
            $wav['ship_perent']    =   sprintf("%.1f%%", $wav['ship_num']/$total_list['ship_num'] * 100);
            $wav['sales_perent']   =   sprintf("%.1f%%", $wav['sales_num']/$total_list['sales_num'] * 100);
            $wav['num_perent']     =   sprintf("%.1f%%", $wav['sales_num']/$wav['ship_num'] * 100);
            $wav['skc_perent']     =   sprintf("%.1f%%", $wav['sales_skc']/$wav['ship_skc'] * 100);
            $wav['contribute']     =   sprintf("%.1f%%", $wav['sales_perent']/$wav['ship_perent'] * 100);
        }
        
        $result['list']             =   $list;
        $result['category_list']    =   $category_list;
        $result['medium_list']      =   $medium_list;
        $result['wave_list']        =   $wave_list;
        Flight::display('custom/meforever/history_table.html',$result);
    }
    /*真我永恒结束*/

    /*国人服饰*/
    /*合同打印，系列字段*/
    public static function Action_guoren_contract($r,$username){
        $OrderList  =   new OrderList;
        $options    =   array();
        $options['tablename']   = " products_attr as pa 
                                    left join product as p on pa.keyword_id=p.series_id
                                    left join user as u on u.username='{$username}'
                                    left join orderlist as o on p.id = o.product_id and o.user_id=u.id";
        $options['fields']      = " pa.keyword_id,sum(o.discount_amount) as price";
        $options['group']       = " pa.keyword_id ";
        $options['order']       = " pa.rank";

        $where          =   "pa.field='series'";

        $list           = $OrderList->find($where,$options);
        $total_price    = 0;
        foreach ($list as $row) {
            $total_price += $row['price'];
        }

        $result['list']         =   $list;
        $result['total_price']  =   $total_price;

        $company            =   new Company;
        $result['fairname'] =   $company->fairname;

        $User       = new User;
        $userinfo   = $User->findone("username='{$username}'",array("fields"=>"name"));
        $result['name']     =   $userinfo['name'];    
        Flight::display('custom/guoren/contract.html',$result);
    }
    /* 国人服饰结束 */

    /*辛德*/
    /*合同打印，波段字段*/
    public static function Action_xinde_contract($r,$username){
        $OrderList  =   new OrderList;
        $options    =   array();

        $User       =   new User;
        $userinfo   =   $User->findone("username='{$username}'");
        if($userinfo['type']==2){
            $options['tablename']   =   " products_attr as pa 
                                          left join product as p on pa.keyword_id=p.wave_id
                                          left join user as u on u.username='{$username}'
                                          left join orderlist as o on p.id=o.product_id and o.zd_user_id=u.id";
            $options['fields']      = " pa.keyword_id,sum(o.zd_discount_amount) as price,sum(o.num) as num,p.date_market";
        }else{
            $options['tablename']   =   " products_attr as pa 
                                          left join product as p on pa.keyword_id=p.wave_id
                                          left join user as u on u.username='{$username}'
                                          left join orderlist as o on p.id=o.product_id and o.user_id=u.id";
            $options['fields']      = " pa.keyword_id,sum(o.discount_amount) as price,sum(o.num) as num,p.date_market";
        
        }
        $options['group']       = " pa.keyword_id";
        // $options['db_debug']    =   true;
        $options['order']       = " pa.rank";

        $where  =   "pa.field='wave'";

        $list   =   $OrderList->find($where,$options);
        $result['list']     =   $list;

        $total  =   array();
        $fushi  =   array();
        $shipin =   array();
        $i      =   0;
        $len    =   count($list);
        foreach($list as $row){
            $total['price'] += $row['price'];
            $total['num']   += $row['num'];
            if(++$i<$len){
                $fushi['price']+= $row['price'];
                $fushi['num']  += $row['num'];
            }else{
                $shipin['price']= $row['price'];
                $shipin['num']  = $row['num'];
            }
        }
        $result['total']    =   $total;
        $result['fushi']    =   $fushi;
        $result['shipin']   =   $shipin;

        $User       = new User;
        $userinfo   = $User->findone("username='{$username}'",array("fields"=>"name"));
        $result['name']     =   $userinfo['name'];    
        Flight::display('custom/xinde/contract.html',$result);
    }
}

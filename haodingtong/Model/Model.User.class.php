<?php

class User Extends BaseClass {
    public function __construct($id=null){
        $this->setFactory('user');
        if(is_numeric($id)){
            $this->setAttribute($this->findone("id={$id}"));
        }elseif(( $user = SESSION::get("user") ) && $user['id'] > 0){
            $this->setAttribute($user);
        }
    }

    private static $_instances = array();
    public static function getInstance ($user_id) {
        $instance = STATIC::$_instances[$user_id];
        if(!$instance) {
            $instance = new User($user_id);
            STATIC::$_instances[$user_id]   = $instance;
        }
        return $instance;
    }

    public static function find_agent ($user_slave_id) {
        $UserSlave  = new UserSlave;
        $info       = $UserSlave->findone("user_slave_id={$user_slave_id}");
        $user_id    = $info['user_id'];
        if($user_id) {
            return User::getInstance($user_id);
        }else{
            return null;
        }
    }

    public function refresh_login_userinfo() {
        if($this->id) {
            $u  = $this->findone("id={$this->id}");
            SESSION::set("user", $u);
        }
    }

    public function create_user($data){
        foreach($data as $key => $val){
            $target[$key]   = $val;
        }
        $user   = $this->create($target);
        $user->insert();
        return $user->getData();
    }

    public function get_exp_info($params=array()){
        $area1      = $params['area1'];
        $area2      = $params['area2'];
        $master_uid = $params['master_uid'];
        $is_lock    = $params['is_lock'];
        $ad_id   = $params['ad_id'];
        $search_user    = trim($params['search_user']);
        $order_status     = $params['order_status'];
        if($master_uid)  return $this->get_exp_info_zongdai($master_uid);
        if($area1)  $condition[]    = "u.area1={$area1}";
        if($area2)  $condition[]    = "u.area2={$area2}";
        if(is_numeric($is_lock)){
            $condition[]    = "u.is_lock={$is_lock}";
        }
        if($ad_id){
            $condition[]    = "u.ad_id={$ad_id}";
        }
        if($search_user){
            $qt     = addslashes($search_user);
            $condition[]        = "(u.name LIKE '%{$qt}%' OR u.username='{$qt}')";
        }
        if($order_status)       $condition[]    =  $order_status=='all'? "u.order_status>=1" : "u.order_status = {$order_status}";
        $condition[]    = "u.type=1";
        $condition[]	= "(u.mid>0 or us.id is null)";
        $where      = implode(" AND ", $condition);
        $options['fields']  = "SUM(u.exp_num) as exp_num, SUM(u.exp_price) as exp_price";
        $options['tablename']   = "user as u left join user_slave as us on u.id=us.user_slave_id";
        //$options['db_debug'] = true;
        $info   = $this->findone($where, $options);
        return $info;
    }
    
    public function get_exp_info2($params=array()){
        $area1      = $params['area1'];
        $area2      = $params['area2'];
        if($params['zongdai']){
            $master_uid = $params['zongdai'];
        }else{
            $master_uid = $params['master_uid'];
        }
        //$master_uid = $params['zongdai'];
        $is_lock    = $params['is_lock'];
        $property    = $params['property'];
        $ad_id   = $params['ad_id'];
        $search_user    = trim($params['search_user']);
        $order_status     = $params['order_status'];
        if($master_uid){
            $UserSlave = new UserSlave();
            $user_slave = $UserSlave->get_slave_user_id($master_uid);
            $condition[] = "u.id in (".($user_slave?$user_slave:0).")";
        }
        if($area1)  $condition[]    = "u.area1={$area1}";
        if($area2)  $condition[]    = "u.area2={$area2}";
        if(is_numeric($is_lock)){
            $condition[]    = "u.is_lock={$is_lock}";
        }
        if(is_numeric($property)){
            $condition[]    = "u.property={$property}";
        }
        if($ad_id){
            $condition[]    = "u.ad_id={$ad_id}";
        }
        if($search_user){
            $qt     = addslashes($search_user);
            $condition[]        = "(u.name LIKE '%{$qt}%' OR u.username='{$qt}')";
        }
        if($order_status)       $condition[]    =  $order_status=='all'? "u.order_status>=1" : "u.order_status = {$order_status}";
        $condition[]    = "u.type=1";
        //$condition[]	= "(u.mid>0 or us.id is null)";
        $where      = implode(" AND ", $condition);
        $options['fields']  = "SUM(u.exp_num) as exp_num, SUM(u.exp_price) as exp_price";
        $options['tablename']   = "user as u left join user_slave as us on u.id=us.user_slave_id";
        //$options['db_debug'] = true;
        $info   = $this->findone($where, $options);
        return $info;
    }
    
    public function get_exp_info_zongdai($master_uid){
    	return $this->get_slave_exp_info($master_uid);
        $options['tablename']   = "user_slave as us left join user as u on us.user_slave_id=u.id";
        $options['fields']      = "SUM(u.exp_num) as exp_num, SUM(u.exp_price) as exp_price";
        $condition[]            = "us.user_id={$master_uid}";
        $where  = implode(" AND ", $condition);
        return $this->findone($where, $options);
    }
    
    public function get_print_info($params=array()){
        $OrderList  = new OrderList;
        $info = array();
        $ad_id = $params['ad_id'];
        $mid = $params['mid'];
        if($mid){
            $UserSlave = new UserSlave();
            $user_slave = $UserSlave->get_slave_user_id($mid);
            $condition[] = "u.id in (".($user_slave?$user_slave:0).") ";
        }
        //$area1      = $params['area1'];
        //$area2      = $params['area2'];
        //if($area1)  $condition[]    = "u.area1={$area1}";
        //if($area2)  $condition[]    = "u.area2={$area2}";
        if($ad_id) $condition[]    = "u.ad_id={$ad_id}";
        $condition[]    = "u.type=1";       
        $where  = implode(' AND ', $condition);
        $options['tablename'] = 'user u';
        $options['fields'] = 'count(u.id) as total';
        //$options['db_debug'] = true;
        $totalget = $this->find($where, $options);
        $total = $totalget[0]['total'];
        $condition2 = $condition;
        $condition2[] = 'u.is_lock=1';
        $where2  = implode(' AND ', $condition2);
        $totallockget = $this->find($where2, $options);
        $totallock = $totallockget[0]['total'];
        $info = array('total'=>$total,'totallock'=>$totallock,'totalunlock'=>($total-$totallock));
        $options['tablename'] = 'orderlist o left join user u  on o.user_id=u.id ';
        $options['fields'] = 'sum(num) as tnum , sum(amount) as tamount ';
        $lockinfo = $this->find($where2, $options);
        $info['locknum'] = $lockinfo[0]['tnum'];
        $info['lockprice'] = $lockinfo[0]['tamount'];
        $where3  = implode(' AND ', $condition);
        $options['fields'] = " u.exp_num, u.exp_price , sum(o.num) as num , ".$OrderList->DISCOUNT_CONDITION." as discount_price";
        $options['group'] = 'u.id';
        $options['limit'] = '10000';
        $options['having'] = ' ( discount_price >= exp_price AND u.exp_price >0 AND u.exp_num = 0  ) or ( num >= exp_num AND u.exp_num >0 AND u.exp_price = 0 ) or ( 
                u.exp_price >0 AND u.exp_num >0 AND discount_price >= exp_price  AND num >= exp_num )';
        $finishInfo = $this->find($where3, $options);
        $info['finished'] = sizeof($finishInfo);
        $info['unfinished'] = $total-sizeof($finishInfo);
        return $info;
    }

    public function get_exp_list($params=array(), $options=array()){
        $area1      = $params['area1'];
        $area2      = $params['area2'];
        $fliter_uid = $params['fliter_uid'];
        $category_id = $params['category_id'];
        $brand_id   = $params['brand_id'];
        $season_id  = $params['season_id'];
        $nannvzhuan_id  = $params['nannvzhuan_id'];
        $is_finished  = $params['is_finished'];
        $is_lock    = $params['is_lock'];
        $OrderList  = new OrderList;
        $options['tablename']   = "user as u left join orderlist as o on u.id=o.user_id left join product as p on o.product_id=p.id left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id";
        if($options['table_more']){
            $options['tablename'].=' '.$options['table_more'];
        }
        $options['fields']      = "u.name, u.username, u.exp_num, u.exp_price, u.is_lock,u.id, sum(o.num) as num, sum(o.num * p.price) as price, ".$OrderList->DISCOUNT_CONDITION." as discount_price";
        if($options['fields_more']){
            $options['fields'].=' , '.$options['fields_more'];
        }
        $options['group']       = "u.id";
        $condition[]    = "u.type=1";
        $condition[]    = "(p.status=1 or o.id is null)";
        if($area1)  $condition[]    = "u.area1={$area1}";
        if($area2)  $condition[]    = "u.area2={$area2}";
        if($fliter_uid) $condition[]    = "u.id={$fliter_uid}";
        if($category_id) $condition[]   = "p.category_id={$category_id}";
        if($brand_id)   $condition[]    = "p.brand_id={$brand_id}";
        if($season_id)  $condition[]    = "p.season_id={$season_id}";
        if($nannvzhuan_id)  $condition[]    = "p.nannvzhuan_id={$nannvzhuan_id}";
        if(isset($is_lock)&&is_numeric($is_lock)){
            $condition[]    = "u.is_lock=".$is_lock;
        }
        if($is_finished==1){
            $options['having'] = ' ( discount_price >= exp_price AND u.exp_price >0 AND u.exp_num = 0  ) or ( num >= exp_num AND u.exp_num >0 AND u.exp_price = 0 ) or ( 
                u.exp_price >0 AND u.exp_num >0 AND discount_price >= exp_price  AND num >= exp_num )';
        }elseif($is_finished==2){
            $options['having'] = ' ( (discount_price < exp_price or discount_price is null ) AND u.exp_price >0   ) or ( (num < exp_num or num is null) AND u.exp_num >0  ) or ( 
                u.exp_price = 0 AND u.exp_num = 0  ) ';
        }
        //$options['db_debug']    = true;
        $where  = implode(' AND ', $condition);
        return $this->find($where, $options);
    }
    
    public function get_exp_list_print($params=array(), $options=array()){
        $area1      = $params['area1'];
        $area2      = $params['area2'];
        $zongdai    = $params['zongdai'];
        $fliter_uid = $params['fliter_uid'];
        $category_id = $params['category_id'];
        $brand_id   = $params['brand_id'];
        $season_id  = $params['season_id'];
        $nannvzhuan_id  = $params['nannvzhuan_id'];
        $is_finished  = $params['is_finished'];
        $property  = $params['property'];
        $is_lock    = $params['is_lock'];
        $order_status = $params['order_status'];
        $sname = $params['search_user'];
        $ad_id = $params['ad_id'];
        $mid = $params['mid'];
        $OrderList  = new OrderList;
        $discount_price = $OrderList->DISCOUNT_CONDITION;
        $options['tablename']   = "user as u left join orderlist as o on u.id=o.user_id left join product as p on o.product_id=p.id ";
        if($options['table_more']){
            $options['tablename'].=' '.$options['table_more'];
        }
        $options['fields']      = "u.order_status, u.name, u.username, u.exp_num, u.exp_price, u.is_lock,u.id, sum(o.num) as num, sum(o.num * p.price) as price , ".$discount_price." as  discount_price , (".$discount_price."/exp_price) as price_percent";
        if($options['fields_more']){
            $options['fields'].=' , '.$options['fields_more'];
        }
        $options['group']       = "u.id";
        
        switch($options['order']){
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
        if($ad_id){
            $condition[]    = "u.ad_id={$ad_id}";
        }
        if($mid){
            $condition[]    = "us.user_id={$mid}";
        }
        $condition[]    = "u.type=1";
        $condition[]    = "(p.status=1 or o.id is null)";
        if($order_status){
            $condition[] = $order_status=='all' ? "u.order_status >= 1 " : "u.order_status = {$order_status} ";
        }
        if($area1)  $condition[]    = "u.area1={$area1}";
        if($area2)  $condition[]    = "u.area2={$area2}";
        if($zongdai)$condition[]    = "us.user_id={$zongdai}";
        if($fliter_uid) $condition[]    = "u.id={$fliter_uid}";
        if($category_id) $condition[]   = "p.category_id={$category_id}";
        if($brand_id)   $condition[]    = "p.brand_id={$brand_id}";
        if($season_id)  $condition[]    = "p.season_id={$season_id}";
        if($nannvzhuan_id)  $condition[]    = "p.nannvzhuan_id={$nannvzhuan_id}";
        if(isset($is_lock)&&is_numeric($is_lock)){
            $condition[]    = "u.is_lock=".$is_lock;
        }
        if(is_numeric($property)){
            $condition[]  = "u.property=".$property;
        }
        if($sname){
            $condition[] = "( u.username = '{$sname}' or u.name like  '%{$sname}%' )";
        }
        if($is_finished==1){
            $options['having'] = ' ( discount_price >= exp_price AND u.exp_price >0 AND u.exp_num = 0  ) or ( num >= exp_num AND u.exp_num >0 AND u.exp_price = 0 ) or (
                u.exp_price >0 AND u.exp_num >0 AND discount_price >= exp_price  AND num >= exp_num )';
        }elseif($is_finished==2){
            $options['having'] = ' ( (discount_price < exp_price or discount_price is null ) AND u.exp_price >0   ) or ( (num < exp_num or num is null) AND u.exp_num >0  ) or (
                u.exp_price = 0 AND u.exp_num = 0  ) ';
        }
        //$options['db_debug']    = true;
        $where  = implode(' AND ', $condition);
        return $this->find($where, $options);
    }
    
    public function get_exp_group_list($params=array(), $options=array()){
        $area1          = $params['area1'];
        $area2          = $params['area2'];

        if($area2){
            $condition[]    = "u.area2={$area2}";
            $area       = 'area2';
            $group      = 'user_id';
            $fields     = "u.exp_num, u.username, u.exp_price, u.name, u.id as area";
            $options['fields']      = $fields;
            $options['tablename']   = "user as u";
            $options['limit']       = 1000;
            // $options['db_debug']    = true;
            $condition[]    = "u.type=1";
            $where      = implode(" AND ", $condition);
            $exp_list   = $this->find($where, $options);

            $OrderList  = new OrderList;
            $condition  = array();
            $options    = array();
            if($area1)  $condition['area1'] = $area1;
            if($area2)  $condition['area2'] = $area2;
            $options['group']   = "o.user_id";
            $options['key']     = 'user_id';
            $options['fields_more'] = "o.user_id";
            // $options['db_debug']    = true;
            $info   = $OrderList->getOrderAnalysisList($condition, $options);

        }else{
            $fields         = "SUM(u.exp_num) as exp_num, SUM(u.exp_price) as exp_price, l.name";
            if($area1){
                $condition[]    = "u.area1={$area1}";
                $area       = 'area2';
            }else{
                $area       = 'area1';
            }
            //$condition[] = "u.mid>0";
            $fields .= ",u.{$area} as area";
            $options['group']       = "u.{$area}";
            $options['fields']      = $fields;
            $options['tablename']   = "user as u left join location as l on u.{$area}=l.id";
            $options['limit']       = 1000;
            // $options['db_debug']    = true;
            $condition[]    = "u.type=1";
            $where      = implode(" AND ", $condition);
            $exp_list   = $this->find($where, $options);

            $OrderList  = new OrderList;
            $condition  = array();
            $options    = array();
            if($area1)  $condition['area1'] = $area1;
            if($area2)  $condition['area2'] = $area2;
            $options['group']   = "u.{$area}";
            $options['key']     = $area;
            $options['fields_more'] = "u.{$area}";
            // $options['db_debug']    = true;
            $info   = $OrderList->getOrderAnalysisList($condition, $options);
        }

        foreach($exp_list as &$e){
            $myorder    = $info[$e['area']];
            $e['num']   = $myorder['num'];
            $e['price']   = $myorder['price'];
            $e['discount_price']   = $myorder['discount_price'];
            $e['exp_num_percent']       = $myorder['num'] && $e['exp_num']      ? sprintf("%.2f%%", $myorder['num'] / $e['exp_num'] * 100)        : "0%";
            $e['exp_price_percent']     = $myorder['discount_price'] && $e['exp_price']  ? sprintf("%.2f%%", $myorder['discount_price'] / $e['exp_price'] * 100)    : "0%";
        }
        return $exp_list;
    }

    public function get_zongdai_exp_order_list($params=array(), $options=array()){
        $options['limit']       = 200;
        // $options['db_debug']    = true;
        $master_uid     = $params['master_uid'];
        $condition[]    = "u.type=2";
        if($master_uid){
            $options['tablename']   = "user_slave as us left join user as u on u.id=us.user_id left join user as usu on us.user_slave_id=usu.id left join orderlistuser as o on us.user_slave_id=o.user_id";
            $options['fields']      = "usu.name, usu.username, usu.is_lock, us.user_id, us.user_slave_id, usu.exp_num, usu.exp_price, o.num, o.price, o.discount_price";
            $condition[]            = "us.user_id={$master_uid}";
        }else{
            $ad_area1       = $params['area1'];
            $ad_area2       = $params['area2'];
            $options['tablename']   = "user_slave as us left join user as u on u.id=us.user_id left join user as usu on us.user_slave_id=usu.id left join orderlistuser as o on us.user_slave_id=o.user_id left join user as um on us.user_id=um.mid";
            $options['fields']      = "u.id, u.name, u.username, u.is_lock, us.user_id, SUM(usu.exp_num) as exp_num, um.exp_price, SUM(o.num) as num, SUM(o.price) as price, SUM(o.discount_price) as discount_price";
            $options['group']       = "us.user_id";
            if($ad_area1)   $condition[]        = "u.area1={$ad_area1}";
            if($ad_area2)   $condition[]        = "u.area2={$ad_area2}";
        }
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        $OrderList  = new OrderList;
        foreach($list as &$e){
            if(!$master_uid){
                $discount_info          = $OrderList->getSelfOrderinfo($e['id']);
                $e['discount_price']    = $discount_info['discount_price'];
            }
            $e['exp_num_percent']       = $e['num'] && $e['exp_num']      ? sprintf("%.2f%%", $e['num'] / $e['exp_num'] * 100)        : "0%";
            $e['exp_price_percent']     = $e['discount_price'] && $e['exp_price']  ? sprintf("%.2f%%", $e['discount_price'] / $e['exp_price'] * 100)    : "0%";
        }
        return $list;
    }

    public function get_zongdai_list($options=array()){
        $condition[]    = "type=2";
        $where  = implode(' AND ', $condition);
        return $this->find($where, $options);
    }

    public function get_dealer_list($cond=array(), $options=array()){
        $area1          = $cond['area1'];
        $area2          = $cond['area2'];
        $zongdai        = $cond['zongdai'];
        $condition[]    = "type=1";
        if($area1)  $condition[]    = "area1={$area1}";
        if($area2)  $condition[]    = "area2={$area2}";
        if($zongdai)$condition[]    = "id in (select user_slave_id from user_slave where user_id={$zongdai})";
        $options['fields']    = "id,name,username";
        $where  = implode(' AND ', $condition);
        return $this->find($where, $options);
    }
    
    public function get_zongdailist($cond=array(), $options=array()){
        $area1          = $cond['area1'];
        $area2          = $cond['area2'];
        if($area1)  $condition[]    = "area1={$area1}";
        if($area2)  $condition[]    = "area2={$area2}";
        $condition[]    = "type=2";
        $where  = implode(' AND ', $condition);
        return $this->find($where, $options);
    }

    public function get_slave_exp_info($user_id){
    	/*$condition[] = "type=1";
    	$condition[] = "mid={$user_id}";
    	$where = implode(' AND ', $condition);
    	$info = $this->findone($where);
    	return $info;*/
        $condition[]    = "u.type=1";
        $condition[]    = "us.user_id={$user_id}";
        $options['tablename']   = "user as u left join user_slave as us on u.id=us.user_slave_id";
        $options['fields']      = "SUM(u.exp_num) as exp_num, SUM(u.exp_price) as exp_price, SUM(u.exp_pnum) as exp_pnum";
        $where  = implode(" AND ", $condition);
        //$options['db_debug'] = true;
        $info   = $this->findone($where, $options);
        return $info;
    }
    public function get_ad_exp_info($user_id){        
        $condition[]    = "type=1";
        $condition[]    = "ad_id={$user_id}";
        $options['fields']      = "SUM(exp_num) as exp_num, SUM(exp_price) as exp_price, SUM(exp_pnum) as exp_pnum";
        $where  = implode(" AND ", $condition);
        //$options['db_debug'] = true;
        $info   = $this->findone($where, $options);
        return $info;
    }
	public function get_user_exp_list_ad($params=array(), $options=array()){
		$isArea 	= $params['isArea'];
		$isZongdai 	= $params['isZongdai'];
		$master_uid 	= $params['master_uid'];
		$area1 		= $params['area1'];
		$area2 		= $params['area2'];
		$uname 		= $params['uname'];
		$ad_id      = $params['ad_id'];
		if($uname){
                        $options['fields']      = "u.exp_price, u.exp_num, u.area1,u.area2,u.mid,u.id,IF(u.mid,um.name,u.name) as name,IF(u.mid,um.username,u.username) as username,IF(u.mid,um.id,u.id) as user_id, IF(u.mid,um.is_lock,u.is_lock) as is_lock";
                        $options['tablename']   = 'user as u left join user_slave as us on u.id=us.user_slave_id left join location as l on u.area2=l.id left join user as um on u.mid=um.id';
		//	$condition[]	 	= "(us.id is null or u.mid>0)";
			$condition[]		= "(u.username='{$uname}' or u.name like '%$uname%')";
		}elseif($isArea){
			if($area2){
				$options['fields']	= "u.exp_price, u.exp_num, u.area1,u.area2,u.mid,u.id,IF(u.mid,um.name,u.name) as name,IF(u.mid,um.username,u.username) as username,IF(u.mid,um.id,u.id) as user_id, IF(u.mid,um.is_lock,u.is_lock) as is_lock";
				$options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id left join location as l on u.area2=l.id left join user as um on u.mid=um.id';
                // $options['fields']  = "u.exp_price, u.exp_num, u.area1,u.area2,u.id,u.name,u.username,u.id as user_id, u.is_lock as is_lock";
                // $options['tablename']   = 'user as u left join location as l on u.area2=l.id';
                $condition[]        = "u.area2={$area2}";
                // $options['db_debug']    = true;
			}elseif($area1){
				$condition[]		= "u.area1={$area1}";
				$options['fields']	= "l.name, sum(u.exp_price) as exp_price, sum(u.exp_num) as exp_num,u.area1,u.area2";
				$options['group']	= 'u.area2';
				$options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id left join location as l on u.area2=l.id';
			}else{
				$options['fields']	= "l.name, sum(u.exp_price) as exp_price,sum(u.exp_num) as exp_num,u.area1";
				$options['group']	= 'u.area1';
				$options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id left join location as l on u.area1=l.id';
			}
			$condition[]	 	= "(us.id is null or u.mid>0)";
		}else{
			if($master_uid){
				$options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id';
				$options['fields']	= 'u.username, u.name, u.exp_price, u.exp_num, u.mid, u.id, u.is_lock, u.id as user_id';
				$condition[]	= "us.user_id={$master_uid}";
			}else{
				$options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id left join user as um on us.user_id=um.id';
				$options['fields']	= 'IF(u.mid,um.username,u.username) as username, IF(u.mid,um.name,u.name) as name, IF(u.mid,um.exp_price,u.exp_price) as exp_price, IF(u.mid,um.exp_num,u.exp_num) as exp_num, u.mid, u.id, IF(u.mid,um.is_lock,u.is_lock) as is_lock, IF(u.mid,um.id,u.id) as user_id';
                $options['order']   = "u.mid desc,u.username";
				$condition[]	= "(us.id is null or u.mid>0)";
			}
			if($area1) $condition[]	= "u.area1={$area1}";
			if($area2) $condition[]	= "u.area2={$area2}";
		}
		$options['limit']	= 2000;
		if(!$options['order'])   $options['order']	= "u.username asc";
		//$options['group']	= 'u.mid';
		//$options['db_debug'] 	= true;
		$condition[]	 	= "u.type=1";
		if($ad_id){
		    $condition[]	 	= "u.ad_id={$ad_id}";
		}
		$where 	= implode(' AND ', $condition);
		return $this->find($where, $options);
	}
	public function get_user_exp_list_ad2($params=array(), $options=array()){
	    $isArea 	= $params['isArea'];
	    $isZongdai 	= $params['isZongdai'];
	    $master_uid 	= $params['master_uid'];
	    $area1 		= $params['area1'];
	    $area2 		= $params['area2'];
	    $uname 		= $params['uname'];
	    if($uname){
	        $options['fields']      = "u.exp_price, u.exp_num, u.area1,u.area2,u.mid,u.id,IF(u.mid,um.name,u.name) as name,IF(u.mid,um.username,u.username) as username,IF(u.mid,um.id,u.id) as user_id, IF(u.mid,um.is_lock,u.is_lock) as is_lock";
	        $options['tablename']   = 'user as u left join user_slave as us on u.id=us.user_slave_id left join location as l on u.area2=l.id left join user as um on u.mid=um.id';
	        //	$condition[]	 	= "(us.id is null or u.mid>0)";
	        $condition[]		= "(u.username='{$uname}' or u.name like '%$uname%')";
	    }elseif($isArea){
	        if($area2){
	            $options['fields']	= "u.exp_price, u.exp_num, u.area1,u.area2,u.mid,u.id,IF(u.mid,um.name,u.name) as name,IF(u.mid,um.username,u.username) as username,IF(u.mid,um.id,u.id) as user_id, IF(u.mid,um.is_lock,u.is_lock) as is_lock";
	            $options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id left join location as l on u.area2=l.id left join user as um on u.mid=um.id';
	            // $options['fields']  = "u.exp_price, u.exp_num, u.area1,u.area2,u.id,u.name,u.username,u.id as user_id, u.is_lock as is_lock";
	            // $options['tablename']   = 'user as u left join location as l on u.area2=l.id';
	            $condition[]        = "u.area2={$area2}";
	            // $options['db_debug']    = true;
	        }elseif($area1){
	            $condition[]		= "u.area1={$area1}";
	            $options['fields']	= "l.name, sum(u.exp_price) as exp_price, sum(u.exp_num) as exp_num,u.area1,u.area2";
	            $options['group']	= 'u.area2';
	            $options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id left join location as l on u.area2=l.id';
	        }else{
	            $options['fields']	= "l.name, sum(u.exp_price) as exp_price,sum(u.exp_num) as exp_num,u.area1";
	            $options['group']	= 'u.area1';
	            $options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id left join location as l on u.area1=l.id';
	        }
	        $condition[]	 	= "(us.id is null or u.mid>0)";
	    }else{
	        if($master_uid){
	            $options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id';
	            $options['fields']	= 'u.username, u.name, u.exp_price, u.exp_num, u.mid, u.id, u.is_lock, u.id as user_id';
	            $condition[]	= "us.user_id={$master_uid}";
	        }else{
	            $options['tablename'] 	= 'user as u left join user_slave as us on u.id=us.user_slave_id left join user as um on us.user_id=um.id';
	            $options['fields']	= 'IF(u.mid,um.username,u.username) as username, IF(u.mid,um.name,u.name) as name, u.exp_price, u.exp_num, u.mid, u.id, IF(u.mid,um.is_lock,u.is_lock) as is_lock, IF(u.mid,um.id,u.id) as user_id';
	            $options['order']   = "u.mid desc,u.username";
	            $condition[]	= "(us.id is null or u.mid>0)";
	        }
	        if($area1) $condition[]	= "u.area1={$area1}";
	        if($area2) $condition[]	= "u.area2={$area2}";
	    }
	    $options['limit']	= 2000;
	    if(!$options['order'])   $options['order']	= "u.username asc";
	    //$options['group']	= 'u.mid';
	    //$options['db_debug'] 	= true;
	    $condition[]	 	= "u.type=1";
	    $where 	= implode(' AND ', $condition);
	    return $this->find($where, $options);
	}
	public function get_user_exp_list_ad3($params=array(), $options=array()){
	    $uname 		= $params['uname'];
	    $ad_id      = $params['ad_id'];
	    if($uname){	       
	        $condition[]		= "(u.username='{$uname}' or u.name like '%$uname%')";
	    }
	    
	    $options['tablename'] 	= 'user as u ';
	    $options['fields']	= 'u.username, u.name, u.exp_price, u.exp_num,  u.id, u.id as user_id';
	     
	    $options['limit']	= 2000;
	    if(!$options['order'])   $options['order']	= "u.username asc";
	    //$options['group']	= 'u.mid';
	    //$options['db_debug'] 	= true;
	    $condition[]	 	= "u.type=1";
	    if($ad_id){
	        $condition[]	 	= "u.ad_id={$ad_id}";
	    }
	    $where 	= implode(' AND ', $condition);
	    return $this->find($where, $options);
	}
    public function get_exp_list_count($params=array()){
        $exp    = $this->get_exp_info($params);
        //print_r($exp);
        $OrderList      = new OrderList;
        $ord    = $OrderList->getOrderAnalysisCount($params);
        return array('exp' => $exp, 'ord' => $ord);
    }
    
    public function get_exp_list_count2($params=array()){
        $exp    = $this->get_exp_info2($params);
        $OrderList      = new OrderList;
        $ord    = $OrderList->getOrderAnalysisCount($params);
        return array('exp' => $exp,'ord' => $ord);
    }

    public function get_ad_list($params=array(), $options=array()){
        $options['group']   = "ad";
        $options['fields']  = "ad";
        $options['limit']   = 1000;
        $condition[]    = "type=1";
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }

    public function has_permission_brand ($permission_brand_id) {
        $permission_brand_list = $this->get_permission_brand();
        foreach($permission_brand_list as $brand_id){
            // echo $brand_id, '-', $permission_brand_id, "<br>";
            if($permission_brand_id == $brand_id){
                return false;
            }
        }
        return true;
    }

    public function get_permission_brand () {
        if($this->permission_brand){
            return explode(',', $this->permission_brand);
        }else{
            return array();
        }
    }

    public function add_permission_brand ($permission_brand_id) {
        $permission_brand_list = $this->get_permission_brand();
        $new_permission_brand_list = array();
        foreach($permission_brand_list as $permission_brand){
            if($permission_brand && $permission_brand_id != $permission_brand){
                $new_permission_brand_list[] = $permission_brand;
            }
        }
        $this->permission_brand = implode(',', $new_permission_brand_list);
        $this->save();
    }

    public function del_permission_brand ($permission_brand_id) {
        $permission_brand_list = $this->get_permission_brand();
        if(!in_array($permission_brand_id, $permission_brand_list)){
            $permission_brand_list[]    = $permission_brand_id;
        }
        $this->permission_brand = implode(',', $permission_brand_list);
        $this->save();
    }

    public function has_permission_isspot ($permission_isspot) {
        $permission_isspot_list = $this->get_permission_isspot();
        foreach($permission_isspot_list as $isspot){
            if($permission_isspot == $isspot){
                return false;
            }
        }
        return true;
    }

    public function get_permission_isspot () {
        if($this->permission_isspot){
            return explode(',', $this->permission_isspot);
        }else{
            return array();
        }
    }

    public function add_permission_isspot ($permission_isspot) {
        $permission_isspot_list = $this->get_permission_isspot();
        $new_permission_isspot_list = array();
        foreach($permission_isspot_list as $permission_isspot_id){
            if($permission_isspot_id && $permission_isspot != $permission_isspot_id){
                $new_permission_isspot_list[] = $permission_isspot_id;
            }
        }
        $this->permission_isspot = implode(',', $new_permission_isspot_list);
        $this->save();
    }

    public function del_permission_isspot ($permission_isspot) {
        $permission_isspot_list = $this->get_permission_isspot();
        if(!in_array($permission_isspot, $permission_isspot_list)){
            $permission_isspot_list[]    = $permission_isspot;
        }
        $this->permission_isspot = implode(',', $permission_isspot_list);
        $this->save();
    }

    public function getZongDaiUserList($area,$fields="*"){
      $userlist =  $this->find(' area2="'.$area.'" AND type=1  ',array('fields'=>$fields,'limit'=>100));
      return $userlist;
    }
    
    public function getADUserList($uid,$fields="*"){
        $userlist =  $this->find(' ad_id="'.$uid.'" AND type=1  ',array('fields'=>$fields,'limit'=>1000));
        return $userlist;
    }

    public function get_user_product_discount(User $user, Product $product){
        $category_id    = $product->category_id;
        $brand_id       = $product->brand_id;
        $UserDiscount   = new UserDiscount;
        $category_discount  = $UserDiscount->get_discount($user->id,'category', $category_id);
        if(!$category_discount){
            $category_discount = $UserDiscount->get_discount($user->id,'brand', $brand_id);
        }
        return $category_discount ? $category_discount : $user->discount;
    }

    public function getUserInfoById($id){
        static $userInfo ;
        if(!$id){
            return null;
        }
        if(isset($userInfo[$id])){
            return $userInfo[$id];
        }else{
           $uinfo = $this->findone('id="'.$id.'"');
           $userInfo[$id] = $uinfo;
           return $uinfo;
        }
    }
    
    /*获取用户列表*/
    public function get_user_list($options=array()){
        $condition[]    = "type!=0";
        $where  = implode(' AND ', $condition);
        return $this->find($where, $options);
    }
    
    public function getLsatInfo($id,$fields='*'){
        $user_info = array();
        if($id){
            $user_info = $this->findone("id={$id}",array('fields'=>$fields,'limit'=>1));
        }
        return $user_info;
    }
    
    public function get_user_order_list_ad($params=array(), $options=array()){
       
        $options['tablename'] 	= 'user u left join orderlist o on u.id = o.user_id left join user_slave usl on u.id = usl.user_slave_id';
        $options['fields']	= 'u.id,u.username,u.name,u.type,u.order_status, IF(u.type=2,(select sum(o.num) from orderlist o , user_slave us where o.user_id = us.user_slave_id and us.user_id = u.id),sum(o.num)) as num,IF(u.type=2,(select sum(o.amount) from orderlist o , user_slave us where o.user_id = us.user_slave_id and us.user_id = u.id),sum(o.amount)) as price';
        if(!$options['order']) $options['order']	= "num desc";
        //$options['group']	= 'u.mid';
        //$options['db_debug'] 	= true;
        $condition[]	 	= "(u.type = 1 or u.type=2)";
        $condition[]	 	= "usl.user_id is null";
        $options['group']	= 'u.id';
        $where 	= implode(' AND ', $condition);
        return $this->find($where, $options);
    }
    
    public function get_order_list_count($params=array()){
        $OrderList      = new OrderList;
        $options = array();
        $list = $OrderList->findone('',array('limit'=>1,'fields'=>'sum(num) as num,sum(amount) as price'));
        return array('ord' => $list);
    }
    
    public function get_ad_user_id($ad_id){
        $options['fields']	= "id";
        $options['limit']	= "10000";
        $options['key'] = 'id';
        $info 	= $this->find("ad_id={$ad_id} AND type=1 ", $options);
        return implode(',', array_keys($info));
    }
    
    public function get_exp_list_print_new($params=array(), $options=array()){
        $area1      = $params['area1'];
        $area2      = $params['area2'];
        $zongdai    = $params['zongdai'];
        $fliter_uid = $params['fliter_uid'];
        $category_id = $params['category_id'];
        $brand_id   = $params['brand_id'];
        $season_id  = $params['season_id'];
        $nannvzhuan_id  = $params['nannvzhuan_id'];
        $is_finished  = $params['is_finished'];
        $property  = $params['property'];
        $is_lock    = $params['is_lock'];
        $order_status = $params['order_status'];
        $sname = $params['search_user'];
        $ad_id = $params['ad_id'];
        $mid = $params['mid'];
        $OrderList  = new OrderList;
        $discount_price = $OrderList->DISCOUNT_CONDITION;
        $options['tablename']   = "user as u left join orderlist as o on u.id=o.user_id left join product as p on o.product_id=p.id ";
        if($options['table_more']){
            $options['tablename'].=' '.$options['table_more'];
        }
        $options['fields']      = "u.order_status, u.name, u.username, u.exp_num, u.exp_price, u.is_lock,u.id, sum(o.num) as num, sum(o.num * p.price) as price , ".$discount_price." as  discount_price , (".$discount_price."/exp_price) as price_percent";
        if($options['fields_more']){
            $options['fields'].=' , '.$options['fields_more'];
        }
        $options['group']       = "u.id";
    
        switch($options['order']){
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
        if($ad_id){
            $condition[]    = "u.ad_id={$ad_id}";
        }
        if($mid){
            $condition[]    = "us.user_id={$mid}";
        }
        $condition[]    = "u.type=1";
        $condition[]    = "(p.status=1 or o.id is null)";
        if($order_status){
            $condition[] = $order_status=='all' ? "u.order_status >= 1 " : "u.order_status = {$order_status} ";
        }
        if($area1)  $condition[]    = "u.area1={$area1}";
        if($area2)  $condition[]    = "u.area2={$area2}";
        if($zongdai)$condition[]    = "us.user_id={$zongdai}";
        if($fliter_uid) $condition[]    = "u.id={$fliter_uid}";
        if($category_id) $condition[]   = "p.category_id={$category_id}";
        if($brand_id)   $condition[]    = "p.brand_id={$brand_id}";
        if($season_id)  $condition[]    = "p.season_id={$season_id}";
        if($nannvzhuan_id)  $condition[]    = "p.nannvzhuan_id={$nannvzhuan_id}";
        if(isset($is_lock)&&is_numeric($is_lock)){
            $condition[]    = "u.is_lock=".$is_lock;
        }
        if(is_numeric($property)){
            $condition[]  = "u.property=".$property;
        }
        if($sname){
            $condition[] = "( u.username = '{$sname}' or u.name like  '%{$sname}%' )";
        }
        if($is_finished==1){
            $options['having'] = ' ( discount_price >= exp_price AND u.exp_price >0 AND u.exp_num = 0  ) or ( num >= exp_num AND u.exp_num >0 AND u.exp_price = 0 ) or (
                u.exp_price >0 AND u.exp_num >0 AND discount_price >= exp_price  AND num >= exp_num )';
        }elseif($is_finished==2){
            $options['having'] = ' ( (discount_price < exp_price or discount_price is null ) AND u.exp_price >0   ) or ( (num < exp_num or num is null) AND u.exp_num >0  ) or (
                u.exp_price = 0 AND u.exp_num = 0  ) ';
        }
        //$options['db_debug']    = true;
        $where  = implode(' AND ', $condition);
        return $this->find($where, $options);
    }
     
    public function get_user_mulit_list () {
        $mulit_name = $this->mulit_name;
        $user_id    = $user_id;
        if($mulit_name) {
            $options['limit']   = 100;
            $options['order']   = "id";
            $list   = $this->find("mulit_name='{$mulit_name}' and type=1", $options);
        }else{
            $list   = array();
        }
        return $list;
    }
}





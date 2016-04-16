<?php

class Control_dealer {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $limit          = 20;
        $User           = new User;
        $Location       = new Location;
        $callback       = function($id) use ($Location){
            return $Location->getCurrent($id);
        };
        $Cache          = new Cache($callback);

        $condition[]    = "type in (1,2)";
        $data           = $r->query;
        $q              = $data->q;
        $is_lock        = $data->is_lock;
        $area1          = $data->area1;
        $area2          = $data->area2;
        $property       = $data->property;
        $agent_id       = $data->agent_id;
//        $type           = $data->type;
        $type           = 1;
        if($q){
            $qt         = addslashes($q);
            $condition[]    = "(name LIKE '%{$qt}%' OR username LIKE '%{$qt}%')";
        }
        if($is_lock)    $condition[]    = "is_lock=1";
        if($type)       $condition[]    = "type={$type}";
        if(is_numeric($property))    $condition[]    = "property={$property}";
        if($area1){
            $condition[]    = "area1={$area1}";
            $result['area'] = $area1;
        }
        if($area2){
            $condition[]    = "area2={$area2}";
            $result['area'] = $area2;
        }
        if($agent_id) {
            $condition[]    = "(id in (select user_slave_id from user_slave where user_id={$agent_id}) or id={$agent_id})";
            $result['agent_id'] = $agent_id;
        }
        $where  = implode(' AND ', $condition);
        $list   = $User->find($where, array("page"=>$r->query->p, "limit"=>$limit, "order"=>"id desc"));
        foreach($list as &$row){
            $row['area1']    = $Cache->get($row['area1'], array($row['area1']));
            $row['area2']    = $Cache->get($row['area2'], array($row['area2']));
            if($row['type'] == 1) {
                $agent      = User::find_agent ($row['id']);
                if($agent) {
                    $row['agentname']   = $agent->name;
                }
            }else{
                $row['agentname']   = $row['name'];
            }
        }
        $total  = $User->getCount($where);

        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $r->query, $total, $limit);
        $result['q']    = $q;

        $query  = array();
        foreach($r->query as $key => $val){
            if($key != "indicator"){
                $query[]    = "{$key}=" . urlencode($val);
            }
        }
        $result['indicator_url']    = implode("&", $query);
        $result['indicator']        = $r->query->indicator;
        $result['is_lock']          = $is_lock;

        $Factory    = new ProductsAttributeFactory('user_level');
        $result['user_level_list']  = $Factory->getAllList();
        $p_Factory  = new ProductsAttributeFactory('property');
        $result['property_list']    = $p_Factory->getAllList();

        $result['zongdai_list']     = $User->get_zongdai_list(array("limit"=>1000));
        $result['property']         = $property;
        $result['agent_list']       = $result['zongdai_list'];
        $result['type']             = $type;

        Flight::display('dealer/index.html', $result);
    }

    public static function Action_general_agents($r)
    {
        Flight::validateEditorHasLogin();

        $limit    = 20;
        $User     = new User;
        $Location = new Location;
        $callback = function ($id) use ($Location) {
            return $Location->getCurrent($id);
        };
        $Cache    = new Cache($callback);

        $condition[] = "type in (1,2)";
        $data        = $r->query;
        $q           = $data->q;
        $is_lock     = $data->is_lock;
        $area1       = $data->area1;
        $area2       = $data->area2;
        $property    = $data->property;
        $agent_id    = $data->agent_id;
        $type        = 2;
//		$type        = $data->type;
        if ($q) {
            $qt          = addslashes($q);
            $condition[] = "(name LIKE '%{$qt}%' OR username LIKE '%{$qt}%')";
        }
        if ($is_lock) $condition[] = "is_lock=1";
        if ($type) $condition[] = "type={$type}";
        if (is_numeric($property)) $condition[] = "property={$property}";
        if ($area1) {
            $condition[]    = "area1={$area1}";
            $result['area'] = $area1;
        }
        if ($area2) {
            $condition[]    = "area2={$area2}";
            $result['area'] = $area2;
        }
        if ($agent_id) {
            $condition[]        = "(id in (select user_slave_id from user_slave where user_id={$agent_id}) or id={$agent_id})";
            $result['agent_id'] = $agent_id;
        }
        $where = implode(' AND ', $condition);
        $list  = $User->find($where, array("page" => $r->query->p, "limit" => $limit, "order" => "id desc"));
        foreach ($list as &$row) {
            $row['area1'] = $Cache->get($row['area1'], array($row['area1']));
            $row['area2'] = $Cache->get($row['area2'], array($row['area2']));
            if ($row['type'] == 1) {
                $agent = User::find_agent($row['id']);
                if ($agent) {
                    $row['agentname'] = $agent->name;
                }
            } else {
                $row['agentname'] = $row['name'];
            }
        }
        $total = $User->getCount($where);

        $result['list']     = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $r->query, $total, $limit);
        $result['q']        = $q;

        $query = array();
        foreach ($r->query as $key => $val) {
            if ($key != "indicator") {
                $query[] = "{$key}=" . urlencode($val);
            }
        }
        $result['indicator_url'] = implode("&", $query);
        $result['indicator']     = $r->query->indicator;
        $result['is_lock']       = $is_lock;

        $Factory                   = new ProductsAttributeFactory('user_level');
        $result['user_level_list'] = $Factory->getAllList();
        $p_Factory                 = new ProductsAttributeFactory('property');
        $result['property_list']   = $p_Factory->getAllList();

        $result['zongdai_list'] = $User->get_zongdai_list(array("limit" => 1000));
        $result['property']     = $property;
        $result['agent_list']   = $result['zongdai_list'];
        $result['type']         = $type;

        // 设置顶部的导航链接
        $result['control'] = 'dealer';

        Flight::display('dealer/general_agents.html', $result);
    }

    public static function Action_user($r, $id){
        Flight::validateEditorHasLogin();

        $User   = new User($id);
        //合并user_slave_tree
        //$UserSlaveTree	= new UserSlaveTree;
        $UserSlave      = new UserSlave;
        $result['user'] = $User->getAttribute();
        if($result['user']['ad_id']){
        	$ad_user = $User->findone("id={$result['user']['ad_id']}");
        	$result['user']['ad_name']	= $ad_user['username'];
        }
        //$parent_user = $UserSlaveTree->findone("user_slave_id={$id}");
        $parent_user = $UserSlave->findone("user_slave_id={$id}");
        if($parent_user){
        	//$parent_id = $parent_user['parent_user_id'];
            $parent_id = $parent_user['user_id'];
        	$parent_info = $User->findone("id={$parent_id}");
        	$result['user']['parentid']=$parent_info['username'];
        }

        Flight::json($result);
    }

    public static function Action_add($r){
        Flight::validateEditorHasLogin();

        $User   = new User;
        //$UserSlaveTree	= new UserSlaveTree;
        $UserSlave  = new UserSlave;
        $data   = $r->data;
        $user_id  = $data->id;
        $username = $data->username;
        $parentid = $data->parentid;
        $ad_name  = $data->ad_name;

        $curuser = $User->findone("username='{$username}' AND id!={$user_id}");
        if(empty($data->username)){
        	$result['valid']	= false;
        	$result['message']  = "客户账号不能为空！";
        	return Flight::json($result);
        }
        if($curuser){
        	$result['valid']	= false;
        	$result['message']  = "客户账号:".$username."已存在！";
        	return Flight::json($result);
        }
        if($parentid){
        	$parent_user = $User->findone("username='{$parentid}' AND type=2");
        	if(!$parent_user){
        		$result['valid']	= false;
        		$result['message']  = "所属上级：".$parentid."不存在！";
        		return Flight::json($result);
        	}
            $parent_user_id = $parent_user['id'];
        }
        if($ad_name){
        	$ad_user = $User->findone("username='{$ad_name}' AND type=3");
        	if(!$ad_user){
        		$result['valid']	= false;
        		$result['message']  = "分管AD：".$ad_name."不存在！";
        		return Flight::json($result);
        	}
            $ad_user_id     = $ad_user['id'];
        }

        $form       = new Form($r->data, 'user');
        $result     = $form->run('insert');
        if($result['valid']){
            $userData   = $result['target'];
            $id         = $data->id;
            if($data->is_stock && $parent_user_id) {   // 总代备单账号
                $userData['mid']    = $parent_user_id;
            }else{
                $userData['mid']    = 0;
            }
            if($ad_user_id) {   // 所属AD
                $userData['ad_id']  = $ad_user_id;
            }
            if($id) {
                $User->update($userData, "id={$id}");
                ProductOrder::refresh_user($user_id);
            }else{
                $authUser   = AuthUser::getInstance();
                $auth       = $authUser->auth();
                if($auth) {
                    return Flight::json($auth);
                }
                $id     = $User->create($userData)->insert();
            }
            if($parent_user_id) {
                $UserSlave->create_slave($parent_user_id,$id);
            }
            $exp_num    =   $userData['exp_num'];
            $exp_price  =   $userData['exp_price'];
            $type   =   $userData['type'];
            $UserIndicator  =   new UserIndicator();
            $UserIndicator->set_indicator_user($id, $type, $exp_num, $exp_price);
            $UserIndicator->refresh_indicator();
            $result['message']  = "保存成功";
        }
        Flight::json($result);
    }

    public static function Action_slave($r){
        Flight::validateEditorHasLogin();

        $user_id    = $r->query->user_id;
        if(is_numeric($user_id)){
            $user       = new User($user_id);
            if($user->type != 2){
                exit;
            }
            $result['user'] = $user->getAttribute();
            $UserSlave  = new UserSlave;
            $list       = $UserSlave->find("user_id={$user_id} and user_slave_id>0", array("limit"=>1000));
            $list       = Flight::listFetch($list, "user", "user_slave_id", "id");
            $result['list'] = $list;
        }

        Flight::display('dealer/slave.html', $result);
    }

    public static function Action_slave_user_list($r){
        Flight::validateEditorHasLogin();

        $q          = trim($r->data->q);
        $area1      = $r->data->area1;
        $area2      = $r->data->area2;
        $filter     = $r->data->filter;
        if($area1 || $area2 || $q){
            $condition  = array();
            if($q){
                $qt     = addslashes($q);
                $condition[]    = "name LIKE '%{$qt}%'";
            }
            if($area1){
                $condition[]    = "area1={$area1}";
            }
            if($area2){
                $condition[]    = "area2={$area2}";
            }
            if($filter){
                $condition[]    = "id not in ({$filter})";
            }
            $condition[]    = "type=1";
            $where  = implode(' AND ', $condition);
            $User   = new User;
            $list   = $User->find($where, array("limit"=>10000));
        }
        $result['list'] = $list;

        Flight::display("dealer/slave_user_list.html", $result);
    }

    public static function Action_set_slave($r){
        Flight::validateEditorHasLogin();

        $user_id        = $r->data->user_id;
        $user_slave_id  = $r->data->user_slave_id;
        $status         = $r->data->status;
        $UserSlave      = new UserSlave;
        if($status == 1){
            $UserSlave->create_slave($user_id, $user_slave_id);
        }else{
            $UserSlave->delete("user_id=$user_id AND user_slave_id=$user_slave_id");
        }
        ProductOrder::refresh_user($user_slave_id);

        Flight::json(array("valid"=>true));
    }
    
    public static function Action_set_slave_list($r){//全选框下线
        Flight::validateEditorHasLogin();
        $user_list      = $r->data->user_list;
        $UserSlave      = new UserSlave;
        foreach ($user_list as $user){
            $UserSlave->create_slave($user['user_id'], $user['user_slave_id']);
            ProductOrder::refresh_user($user['user_slave_id']);
        }
    }

    public static function Action_print($r) {
        Flight::validateEditorHasLogin();

        $User       = new User;
        $list       = $User->find("type=1", array("limit"=>10000));
        $result['list'] = $list;

        Flight::display("dealer/print.html", $result);
    }

    public static function Action_exp($r, $id) {
        Flight::validateEditorHasLogin();

        $data   = $r->query;
        $t      = $data->t;
        $User   = new User;
        $UserExpComplete    = new UserExpComplete;
        $Factory        = new ProductsAttributeFactory($t);
        $u      = $User->findone("id={$id}");
        $list   = $Factory->getAllList();
        $condition['user_id']   = $id;
        $condition['field']     = $t;
        $exp    = $UserExpComplete->get_exp_complete_list($condition, $options=array('key'=>'keyword_id'));
        foreach($list as &$row){
            $row['exp'] = $exp[$row['keyword_id']];
        }
        $result['id']   = $id;
        $result['t']    = $t;
        $result['u']    = $u;
        $result['list'] = $list;

        Flight::display("dealer/exp.html", $result);
    }


    public static function Action_set_exp_complete($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $user_id    = $data->user_id;
        $field      = $data->field;
        $keyword_id = $data->keyword_id;
        $UserExpComplete    = new UserExpComplete;
        $result     = array();
        if(is_numeric($data->exp_num)){
            $UserExpComplete->set_exp($user_id, $field, $keyword_id, 'exp_num', $data->exp_num);
            $result['message']  = "订数指标设置完成";
        }
        if(is_numeric($data->exp_pnum)){
            $UserExpComplete->set_exp($user_id, $field, $keyword_id, 'exp_pnum', $data->exp_pnum);
            $result['message']  = "款量指标设置完成";
        }
        if(is_numeric($data->exp_skc)){
            $UserExpComplete->set_exp($user_id, $field, $keyword_id, 'exp_skc', $data->exp_skc);
            $result['message']  = "款色指标设置完成";
        }
        if(is_numeric($data->exp_num)){
            $UserExpComplete->set_exp($user_id, $field, $keyword_id, 'exp_num', $data->exp_num);
            $result['message']  = "订数指标设置完成";
        }
        if(is_numeric($data->exp_price)){
            $UserExpComplete->set_exp($user_id, $field, $keyword_id, 'exp_price', $data->exp_price);
            $result['message']  = "金额指标设置完成";
        }
        if(!$result['message']){
            $result['message']  = "设置失败";
        }

        Flight::json($result);
    }


    public static function Action_discount($r, $id) {
        Flight::validateEditorHasLogin();

        $data   = $r->query;
        $t      = $data->t ? $data->t : 'category';
        
        $User   = new User;
        $UserDiscount   = new UserDiscount;
        $Factory        = new ProductsAttributeFactory($t);
        $u      = $User->findone("id={$id}");
        $list   = $Factory->getAllList();
        $condition['user_id']   = $id;
        $discount    = $UserDiscount->get_discount_list($id,$t, $options=array('key'=>'category_id'));
        foreach($list as &$row){
            $row['exp'] = $discount[$row['keyword_id']];
        }
        $result['id']   = $id;
        $result['t']    = $t;
        $result['u']    = $u;
        $result['list'] = $list;

        Flight::display("dealer/discount.html", $result);
    }
    
    public static function Action_get_user_list($r){
        Flight::validateEditorHasLogin();
        $user_id    = $r->data->user_id;
        $User       = new User($user_id);
        $UserSlave  = new UserSlave();
        if ($User->type==2){
            $result['list'] = $UserSlave->find("user_id={$user_id}",array("limit"=>10000,"fields"=>"user_slave_id as id"));
        }else{
            $result['list'] = $User->find("type=1",array("limit"=>10000,"fields"=>"id"));
        }
        Flight::json($result);
    }
    
    public static function Action_set_discount($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $user_id    = $data->user_id;
        $field      = $data->field;
        $keyword_id = $data->keyword_id;
        $UserDiscount    = new UserDiscount;
        $UserProdDiscount    = new UserProdDiscount;
        $result     = array();

        $UserDiscount->set_discount($user_id,$field, $keyword_id, $data->value);
        $result['message']  = "折扣设置完成";

        Flight::json($result);
    }

    public static function Action_permission_brand ($r, $uid) {
        Flight::validateEditorHasLogin();

        $User       = new User($uid);
        $Factory    = new ProductsAttributeFactory('brand');
        $brand_list = $Factory->getAllList();
        foreach($brand_list as &$brand){
            $brand['has_permission_brand']  = $User->has_permission_brand($brand['keyword_id']);
        }
        $result['list'] = $brand_list;
        $result['user'] = $User->getAttribute();

        Flight::display("dealer/permission_brand.html", $result);
    }

    public static function Action_permission_brand_status ($r, $uid) {
        Flight::validateEditorHasLogin();

        $data       = $r->query;
        $brand_id   = $data->brand_id;
        $User       = new User($uid);
        if($User->has_permission_brand($brand_id)){
            $User->del_permission_brand($brand_id);
        }else{
            $User->add_permission_brand($brand_id);
        }

        Flight::redirect($r->referrer);
    }

    //款号折扣
    public static function Action_khdiscount($r, $id) {
        Flight::validateEditorHasLogin();

        $data   = $r->query;
        $User   = new User;
        $UserProdDiscount   = new UserProdDiscount;
        $u      = $User->findone("id={$id}");
        $condition['user_id']   = $id;
        $list    = $UserProdDiscount->get_discount_list($id, $options=array('limit' => 100));

        $result['id']   = $id;
        $result['t']    = 'kuanhao';
        $result['u']    = $u;
        $result['list'] = $list;

        Flight::display("dealer/khdiscount.html", $result);
    }

    //款号折扣--添加
    public static function Action_khdiscountadd($r){
        Flight::validateEditorHasLogin();
        $data       		= $r->data;
        $kuanhao       	= $data->kuanhao;
        $discount     	= $data->discount;
        $user_id 				= $data->user_id;

        if($kuanhao && $discount){
            $Product   = new Product;
            $ProductInfo      = $Product->findone("kuanhao='{$kuanhao}'");

            $UserProdDiscount   = new UserProdDiscount;
            if($ProductInfo){
                $UserProdDiscountInfo = $UserProdDiscount->get_discount($user_id,$kuanhao);

                if($UserProdDiscountInfo){
                    $record_id = $UserProdDiscountInfo['id'];
                    $UserProdDiscount->update(array("kuanhao_discount" => $discount), "id={$record_id}");
                }else{
                    $category_id = $ProductInfo['category_id'];
                    $UserDiscount   = new UserDiscount;
                    $category_discount = $UserDiscount->get_discount($user_id,$category_id);
                    $UserProdDiscount->set_discount($user_id, $kuanhao, $discount, $category_id, $category_discount);
                }
            }else{
                SESSION::message("款号:{$kuanhao}不存在!");
            }
        }else{
            SESSION::message("参数错误:{$name}:{$table}");
        }

        Flight::redirect($r->referrer);
    }
    //款号折扣--删除
    public static function Action_khdiscountdel($r, $id=0){
        Flight::validateEditorHasLogin();
        if(is_numeric($id)){
            $UserProdDiscount   = new UserProdDiscount;
            $UserProdDiscount->delete("id={$id}");
            SESSION::message("删除成功");
        }
        Flight::redirect($r->referrer);
    }
    //款号折扣--修改
    public static function Action_khdiscountedit($r){
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $id         = $data->id;
        $kuanhao       = $data->kuanhao;
        $discount       = $data->discount;
        if($id){
            $UserProdDiscount   = new UserProdDiscount;
            $UserProdDiscount->update(array("kuanhao_discount" => $discount), "id={$id}");
        }

        Flight::redirect($r->referrer);
    }

    // public static function Action_auth ($r, $user_id) {
    //     Flight::validateEditorHasLogin();

    //     $data       = $r->query;
    //     $auth       = $data->auth;
    //     $User   = new User;
    //     $User->update(array("auth"=>$auth), "id={$user_id}");

    //     Flight::redirect($r->referrer);
    // }

    public static function Action_auth ($r) {
        Flight::validateEditorHasLogin();
        
        $data       = $r->query;
        $user_id    = $data->user_id;
        $auth       = $data->auth;
        $limit      = Flight::get("limit");

        $User       = new User;
        $count      = $User->findone("type=1 and auth=1",array("fields"=>"count(*) as number"));
        if($limit && $auth && $count['number'] >= $limit){
            $result['message']  = "授权失败,已达到限制终端数".$limit;
            $result['valid']    = false;
        }else{
            $User->update(array("auth"=>$auth), "id={$user_id}");
            $result['message']  = "授权成功";
            $result['valid']    = true;
        }
        Flight::json($result);
    }
    
    public static function Action_user_select ($r) {
        $data       =   $r->query;
        $list_type  =   $data->list_type ? $data->list_type : 1;
        $User       =   new User;
        $result['list_type']    =   $list_type;
        $result['zd_list']  =   $User->find("type=2",array("limit"=>100));
        Flight::display('dealer/user_select.html', $result);
    }

    public static function Action_user_list($r){
        $data       =   $r->data;
        $q          =   $data->q;
        $area1      =   $data->area1;
        $area2      =   $data->area2;
        $zd_id      =   $data->zd_id;
        $list_type  =   $data->list_type ? $data->list_type : 1;
        $limit      =   $data->limit ? $data->limit : 10000;
        
        if($q){
            $qt         = addslashes($q);
            $condition[]    = "(name LIKE '%{$qt}%' OR username LIKE '%{$qt}%')";
        }
        if($area1){
            $condition[]    = "area1={$area1}";
            $result['area'] = $area1;
        }
        if($area2){
            $condition[]    = "area2={$area2}";
            $result['area'] = $area2;
        }
        if($list_type==1){
            $condition[]    =   "type=1";
            if($zd_id) {
                $condition[]    = "(id in (select user_slave_id from user_slave where user_id={$zd_id}))";
            }
        }else{
            $condition[]    =   "type=2";
            if($zd_id){
                $condition[]    =   "id={$zd_id}";
            }
        }

        $where  = implode(" AND ", $condition);
        
        $User   = new User;
        $user_list   = $User->find($where,array("fields"=>"id,name,username","page"=>$p, "limit"=>$limit, "order"=>"id asc"));
        $count = count($user_list);
        $list = array();
        $i = 0;
        foreach ($user_list as $value) {
            if($i<$count)
                $list[($i++/40)][] = $value;
        }
        $result['list']      = $list;
        Flight::display('dealer/user_list.html', $result);
    }
    
    public static function Action_refresh_user($r) {
        Flight::validateEditorHasLogin();
        
        $user_id    =   $r->data->user_id;
        ProductOrder::refresh_user($user_id);
    
        $result['message']  = "用户折扣缓存更新完成";

        Flight::json($result);
    }

    public static function Action_permission_isspot ($r, $uid) {
        Flight::validateEditorHasLogin();

        $User       = new User($uid);
        
        $isspot_list = array();
        $isspot_list[] = array("value"=>1,"name"=>"期货");
        $isspot_list[] = array("value"=>2,"name"=>"现货");

        foreach($isspot_list as $key=>&$isspot){
            $isspot['has_permission_isspot']  = $User->has_permission_isspot($isspot['value']);
        }
        $result['list'] = $isspot_list;
        $result['user'] = $User->getAttribute();

        Flight::display("dealer/permission_isspot.html", $result);
    }

    public static function Action_permission_isspot_status ($r, $uid) {
        Flight::validateEditorHasLogin();

        $data       = $r->query;
        $isspot     = $data->isspot;
        $User       = new User($uid);
        if($User->has_permission_isspot($isspot)){
            $User->del_permission_isspot($isspot);
        }else{
            $User->add_permission_isspot($isspot);
        }

        Flight::redirect($r->referrer);
    }
}
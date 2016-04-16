<?php

class Control_ad {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $User           = new User;
        $Location       = new Location;
        $callback       = function($id) use ($Location){
            return $Location->getCurrent($id);
        };
        $Cache          = new Cache($callback);

        $condition[]    = "type in (3)";
        $q              = $r->query->q;
        if($q){
            $qt         = addslashes($q);
            $condition[]    = "(name LIKE '%{$qt}%' OR username LIKE '%{$qt}%')";
        }
        $where  = implode(' AND ', $condition);
        $list   = $User->find($where, array("page"=>$r->query->p));
        foreach($list as &$row){
            $areaid         = $row['area2'] ? $row['area2']  : $row['area1'];
            $row['area']    = $Cache->get($areaid, array($areaid));
        }
        $total  = $User->getCount($where);

        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $r->query, $total);
        $result['q']    = $q;

        $query  = array();
        foreach($r->query as $key => $val){
            if($key != "indicator"){
                $query[]    = "{$key}=" . urlencode($val);
            }
        }
        $result['indicator_url']    = implode("&", $query);
        $result['indicator']        = $r->query->indicator;

        $Factory    = new ProductsAttributeFactory('user_level');
        $result['user_level_list'] = $Factory->getAllList();
        $result['control'] = 'dealer';
        //Flight::set('Control','dealer');
        Flight::display('ad/index.html', $result);
    }

    public static function Action_user($r, $id){
        Flight::validateEditorHasLogin();

        $User   = new User($id);
        $result['user'] = $User->getAttribute();

        Flight::json($result);
    }

    public static function Action_add($r){
        Flight::validateEditorHasLogin();

        $User   = new User;
        $data   = $r->data;
        if(isset($data->username) && $data->password && $data->name){
            if($data->id){
                $User->update($data, "id={$data->id}");
            }else{
                $User->create_user($data);
            }
        }

        Flight::redirect($r->referrer);
    }
    
    public static function Action_ad_user_list($r){
        Flight::validateEditorHasLogin();
        $aid = $r->data->aid;
        $condition  = array();
        $condition[]    = "(type=1 or type=2)";
        $where  = implode(' AND ', $condition);
        $User   = new User;
        $list   = $User->find($where, array("limit"=>10000));
        $list_check = array();
        $list_uncheck = array();
        foreach($list as $val){
            if($val['ad_id']!=0&&$val['ad_id']==$aid){
                $list_check[]=$val;
            }else{
                $list_uncheck[]=$val;
            }
        }
        $result['list_check'] = $list_check;
        $result['list_uncheck'] = $list_uncheck;
        Flight::display("ad/ad_user_list.html", $result);
    }
    
    public static function Action_ad_add($r){
        Flight::validateEditorHasLogin();
    
        $User   = new User;
        $data   = $r->data;
        if(isset($data->username) && $data->password && $data->name){
            $userlist = implode(',', $data['userlist']);
            $slavelist = implode(',', $data['slavelist']);
            unset($data['userlist']);
            unset($data['slavelist']);
            unset($data['area1']);
            if($data->id){
                $User->update($data, "id={$data->id}");
                $aid = $data->id;
            }else{
                $userInfo = $User->create_user($data) ;
                $aid = $userInfo['id'];
            }
            if($aid){
                if($data->id){
                    // $User->update(array('ad_id'=>0),' ad_id = "'.$aid.'" ');
                }
                if($userlist){
                    $User->update(array('ad_id'=>$aid),' id in ('.$userlist.') ');
                }
                if($slavelist){
                    $User->update(array('ad_id'=>$aid),' id in (select user_slave_id from user_slave where user_id in ('.$slavelist.') ) ');
                    $User->update(array('ad_id'=>$aid),' id in ('.$slavelist.') ');
                }
            }
        }
    
        Flight::redirect($r->referrer);
    }
    
    public static function Action_slave($r){
        Flight::validateEditorHasLogin();
    
        $ad_id    = $r->query->ad_id;
        if(is_numeric($ad_id)){
            $user       = new User($ad_id);
            if($user->type != 3){
                exit;
            }
            $result['user'] = $user->getAttribute();
            $list   =   $user->find("ad_id={$ad_id} and type=1",array("limit"=>1000));
            $result['list'] = $list;
            $result['zongdai_list']     = $user->get_zongdai_list(array("limit"=>100));
        }
        Flight::display('ad/slave.html', $result);
    }
    
    public static function Action_slave_user_list($r){
        Flight::validateEditorHasLogin();
        
        $q          = trim($r->data->q);
        $area1      = $r->data->area1;
        $area2      = $r->data->area2;
        $filter     = $r->data->filter;
        $zongdai_id = $r->data->zongdai_id;
        if($area1 || $area2 || $q || $zongdai_id){
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
            if($zongdai_id){
                $condition[]    = "(id in (select user_slave_id from user_slave where user_id={$zongdai_id}) or id={$zongdai_id})";
            }
            $condition[]    = "type=1";
            $where  = implode(' AND ', $condition);
            $User   = new User;
            $list   = $User->find($where, array("limit"=>10000));
        }
        $result['list'] = $list;
    
        Flight::display("ad/slave_user_list.html", $result);
    }
    
    public static function Action_set_slave($r){
        Flight::validateEditorHasLogin();
    
        $ad_id          = $r->data->ad_id;
        $user_slave_id  = $r->data->user_slave_id;
        $status         = $r->data->status;
        $User           = new User;
        if($status == 1){
            $User->update(array("ad_id"=>$ad_id), "id={$user_slave_id}");
        }else{
            $User->update(array("ad_id"=>0), "id={$user_slave_id}");
        }
    
        Flight::json(array("valid"=>true));
    }
    
    public static function Action_set_slave_list($r){//全选框下线
        Flight::validateEditorHasLogin();
        $user_list      = $r->data->user_list;
        $User           = new User;
        foreach ($user_list as $row){
            $User->update(array("ad_id"=>$row['ad_id']), "id={$row['user_slave_id']}");
            $userinfo = $User->findone("id={$row['user_slave_id']}",array("fields"=>"id,mid"));
            if($userinfo['mid']){
                $User->update(array("ad_id"=>$row['ad_id']), "id={$userinfo['mid']}");
            }
        }
    }

}
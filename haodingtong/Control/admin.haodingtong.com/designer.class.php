<?php

class Control_designer {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $User           = new User;
        $Location       = new Location;
        $callback       = function($id) use ($Location){
            return $Location->getCurrent($id);
        };
        $Cache          = new Cache($callback);

        $condition[]    = "type in (9)";
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

        $Factory    = new ProductsAttributeFactory('designer');
        $result['designer_list'] = $Factory->getAllList();
        $result['control'] = 'dealer';
        //Flight::set('Control','dealer');
        Flight::display('designer/index.html', $result);
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

    public static function Action_designer_add($r){
        Flight::validateEditorHasLogin();
    
        $User   = new User;
        $data   = $r->data;
        if(isset($data->username) && $data->password && $data->name){
            if($data->id){
                $User->update($data, "id={$data->id}");
            }else{
                $User->create_user($data) ;
            }
        }
        Flight::redirect($r->referrer);
    }
}
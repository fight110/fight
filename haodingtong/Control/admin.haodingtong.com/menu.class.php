<?php
class Control_menu {

    public static function Action_control($r){
        Flight::validateEditorHasLogin();
        $menu_type = $r->query->type;
        $result = array();
        if($menu_type){
            $html = '';
            $mcontrol = new MenuControl();
            $userMenu = $mcontrol->getUserMenuByType($menu_type,1);
            foreach($userMenu as $uv){
                $html.='<h2 class="menu_cate_title">'.$uv[0]['cname'].'</h2>';
                $html.='<ul class="menu_list_ul">';
                foreach($uv as $uvv){                 
                    $html.='<li><input type="checkbox" name="menuId" value="'.$uvv['id'].'" '.($uvv['is_open']?'checked="checked"':'').'>'.$uvv['mname'].'</li>';
                }
                $html.='</ul>';
            }
            $result['html'] = $html;
        }
        Flight::json($result);
    }

    public static function Action_save($r){
        Flight::validateEditorHasLogin();
        $select = $r->data->sel;
        $notselect = $r->data->notSel;
        $mcontrol = new MenuControl();
        if(sizeof($select)){
            $selectStr = implode(',',$select );
            $mcontrol->update(array('is_open'=>1), ' id in ('.$selectStr.') ');
        }
        
        if(sizeof($notselect)){
            $notselectStr = implode(',',$notselect );
            $mcontrol->update(array('is_open'=>0), ' id in ('.$notselectStr.') ');
        }
        $result['success'] = 1;
        Flight::json($result);
    }

    public static function Action_index ($r, $utype=1) {
        Flight::validateEditorHasLogin();

        $result['utype']    = $utype;
        $result['control']  = 'menu';
        
        Flight::display("menu/index.html", $result);
    }

    public static function Action_mlist ($r) {
        Flight::validateEditorHasLogin();

        $utype = $r->query->utype;
        $Menulist   = new Menulist;
        $mlist      = $Menulist->get_mlist($utype);
        $result['list']     = $mlist;
        $result['utype']    = $utype;

        Flight::display("menu/mlist.html", $result);
    }

    public static function Action_add ($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $menu['id']         = $data->id;
        $menu['pid']        = $data->pid;
        $menu['utype']      = $data->utype;
        $menu['name']       = $data->name;
        $menu['link']       = $data->link;
        $menu['rank']       = $data->rank;
        $menu['tagname']    = $data->tagname;
        $menu['status']     = $data->status;

        $Menulist   = new Menulist;
        $id         = $Menulist->create($menu)->insert(true);
        $result['error']    = 0;

        Flight::json($result);
    }

    public static function Action_menuinfo ($r, $id) {
        Flight::validateEditorHasLogin();

        $Menulist   = new Menulist;
        $menuinfo   = $Menulist->findone("id={$id}");
        $result['menu'] = $menuinfo;

        Flight::json($result);
    }

    public static function Action_set_menu_active ($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $utype      = $data->utype;
        $mid        = $data->mid;
        $Menulist   = new Menulist;
        $mids       = implode(",", $mid);
        if(!$mids) {
            $mids = "0";
        }
        $Menulist->update(array("status"=>0), "utype={$utype} AND id not in ($mids)");
        $Menulist->update(array("status"=>1), "utype={$utype} AND id in ($mids)");

        Flight::redirect($r->referrer);
    }
}

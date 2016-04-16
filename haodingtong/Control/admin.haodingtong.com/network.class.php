<?php
class Control_network {

    public static function _beforeCall($r, $id=0){
        $User   = new User;
        if($User->type != 0){
            Flight::redirect("/");
            return false;
        }
    }

    public static function Action_index($r,$id=0){
        Flight::validateEditorHasLogin();
        $data       = $r->query;
        $p          = $data->p;
        $limit      = $data->limit  ? $data->limit : 20;
        $q          = trim($data->q);
        $timeout    = $data->timeout ? $data->timeout : 5;        

        $options['page']    = $p;
        $options['limit']   = $limit;
        $options['count']   = true;
        $UserSession        = new UserSession;
        $params             = array();
        $params['update_time']  = date("Y-m-d H:i:s", time() - 60 * $timeout);
        if($q) {
            $params['q']    = $q;
        }
        $list   = $UserSession->get_session_list($params, $options);
        $total  = $UserSession->get_count_total();

        $result['list']         = $list;
        $result['onlineNum']    = $onlineNum;
        $result['deviceNum']    = $total;
        $result['pagelist']     = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['q']            = $q;
        $result['timeout']      = $timeout;
        
        Flight::display('network/index.html', $result);

    }

    public static function Action_logout ($r, $session_id) {
        Flight::validateEditorHasLogin();
        
        $user_session   = UserSession::getInstance($session_id);
        $user_session->kick("帐号被管理员踢出");
        
        Flight::redirect($r->referrer);
    }

    public static function Action_auth_logout($r) {
        Flight::validateEditorHasLogin();

        $options['limit']       = 10000;
        // $options['db_debug']    = true;
        $condition[]    = "user_id in (select id from user where type in (1,2))";
        $where          = implode(" AND ", $condition);
        $UserSession    = new UserSession;
        $list   = $UserSession->find($where, $options);
        foreach($list as $row) {
            $user_session   = UserSession::getInstance($row['session_id']);
            $user_session->kick("帐号被管理员踢出");
        }
        
        Flight::redirect($r->referrer);
    }
}

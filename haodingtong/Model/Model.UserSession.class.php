<?php

class UserSession Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_session');
    }

    private static $_instance = array();
    public static function getInstance ($session_id=null) {
        if($session_id == null) {
            $session_id     = SESSION::getSID();
        }
        $instance = STATIC::$_instance[$session_id];
        if(!$instance) {
            $instance       = new UserSession;
            $session        = $instance->findone("session_id='{$session_id}'");
            if(!$session['id']) {
                $session    = array();
                $session["session_id"]  = $session_id;
                $session["create_time"] = date("Y-m-d H:i:s");
                $session["domain"]      = $_SERVER['HTTP_HOST'];
                $session['id']  = $instance->create($session)->insert();
            }
            $instance->setAttribute($session);
            STATIC::$_instance[$session_id] = $instance;
        }
        return $instance;
    }
    public function refresh () {
        $this->update_time = date("Y-m-d H:i:s");
        $this->save();
    }
    public function login ($user_id) {
        $this->user_id  = $user_id;
        $this->user_session_list_id     = $this->create_login($user_id);
        $this->login_num += 1;
        $this->refresh();
    }
    public function logout () {
        $user_session_list_id   = $this->user_session_list_id;
        $UserSessionList        = new UserSessionList;
        $UserSessionList->update(array("logout_time"=>date("Y-m-d H:i:s")), "id={$user_session_list_id}");
        $this->user_id          = 0;
        $this->refresh();
    }
    public function kick_others () {
        $user_id        = $this->user_id;
        $session_id     = $this->session_id;
        $message        = "帐号在其他设备登入";
        $this->update(array("user_id"=>0, "message"=>$message), "user_id={$user_id} AND session_id<>'{$session_id}'");
    }
    public function kick ($message) {
        $this->message  = $message ? $message : "";
        $this->logout();
    }

    private function create_login ($user_id) {
        $data['user_session_id']    = $this->id;
        $data['user_id']            = $user_id;
        $data['login_time']         = date("Y-m-d H:i:s");
        $data['ip_address']         = Flight::IP();
        $data['useragent']          = $_SERVER['HTTP_USER_AGENT'];
        $UserSessionList            = new UserSessionList;
        $id     = $UserSessionList->create($data)->insert();
        return $id;
    }

    public function get_session_list ($params=array(), $options=array()) {
        $options['tablename']   = "user_session as us left join user_session_list as l on us.user_session_list_id=l.id left join user as u on us.user_id=u.id";
        $options['fields']      = "us.*, u.username, u.name, u.auth, l.login_time, l.logout_time";
        $update_time    = $params['update_time'];
        if($update_time) {
            $condition[]    = "us.update_time>'{$update_time}'";
        }
        $q              = $params['q'];
        if($q) {
            $condition[]    = "(u.name like '%{$q}%' or u.username like '%{$q}%')";
        }
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }

    public function check_has_logined ($username) {
        $condition[]    = "user_id in (select id from user where username='{$username}')";
        $where  = implode(" AND ", $condition);
        $total  = $this->getCount($where);
        return $total;
    }



}





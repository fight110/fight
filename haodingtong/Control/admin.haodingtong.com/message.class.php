<?php

class Control_message {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $Message          = new Message;

        $q              = $r->query->q;
        $condition      = array();
        if($q){
            $qt         = addslashes($q);
            $condition[]    = "(title LIKE '%{$qt}%' OR message LIKE '%{$qt}%')";
        }
        $where  = implode(' AND ', $condition);
        $list   = $Message->find($where, array("page"=>$r->query->p));
        $total  = $Message->getCount($where);

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

        Flight::display('message/index.html', $result);
    }

    public static function Action_add($r){
        Flight::validateEditorHasLogin();

		$User 		= new User;
        $Message  	= new Message;
        $UserSms	= new UserSms;
        $data   	= $r->data;
        $uids 		= $data->uid;
        $title 		= $data->title;
        $message	= $data->message;
        if($data->title && $data->message){
            if($data->id){
                $Message->update(array('title'=>$title,'message'=>$message), "id={$data->id}");
            }else{
                $message_id = $Message->create_message($User->id,$User->username,$title,$message);
                //uids-为空则发送给所有客户，否则只发送当前用户
                if(empty($uids)){
                	$uid_list = $User->get_user_list(array('limit'=>1000));
					foreach($uid_list as $key => $val){
						$user_id = $val['id'];
						$sms_id = $UserSms->create_sms($user_id,$message_id);
					}
                } else {
					$uid_list = explode(",",$uids);
					foreach($uid_list as $key => $val){
						$user_id = $val;
						$user_info = $User->findone("username='{$user_id}'");
						if(!empty($user_info)) $UserSms->create_sms($user_info['id'],$message_id);
					}
                }
            }
        }

        Flight::redirect($r->referrer);
    }

	public static function Action_info($r, $id){
		Flight::validateEditorHasLogin();

        $Message   = new Message($id);
        $result['message'] = $Message->getAttribute();

        Flight::json($result);
	}

	public static function Action_delete($r, $id){
        Flight::validateEditorHasLogin();

        if(is_numeric($id)){
            $Message    = new Message;
            $Message->delete("id={$id}");
            //删除该消息关联的用户
            $UserSms 		= new UserSms;
            $UserSms->delete("mid={$id}");
            SESSION::message("删除成功");
        }

        Flight::redirect($r->referrer);
    }

}
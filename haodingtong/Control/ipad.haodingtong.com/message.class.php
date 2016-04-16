<?php

class Control_message {
    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $result['control']  = "message";

        Flight::display('message/index.html', $result);
    }

    public static function Action_mymessage($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $limit      = $data->limit  ? $data->limit  : 10;
        $p          = $data->p      ? $data->p      : 1;

		$User 		= new User;
        $UserSms    = new UserSms;
        $options    = array();
        $options['limit']   = $limit;
        $options['page']    = $p;
        $list       = $UserSms->get_my_messagelist($options, $User->id);

        $result['list']     = $list;
        $result['start']    = ($p - 1) * $limit;

        Flight::display("message/mymessage.html", $result);
    }

	public static function Action_view($r,$id){
		Flight::validateUserHasLogin();

		$result     = FrontSetting::build();
        $result['control']  = "message";

		$UserSms   = new UserSms;
		if($id){
        	$result['messagedetail']    = $UserSms->get_message_detail($id);

			$UserSms->update(array('status'=>1), "id={$id}");

			Flight::display('message/detail.html', $result);
		} else {
			Flight::redirect($r->referrer);
		}
	}

	public static function Action_delete($r, $id){
		Flight::validateUserHasLogin();

        if(is_numeric($id)){
            $UserSms 		= new UserSms;
            $UserSms->delete("id={$id}");
            SESSION::message("删除成功");
        }

        Flight::redirect($r->referrer);
	}
}
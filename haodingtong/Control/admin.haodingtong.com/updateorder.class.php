<?php 
class Control_updateorder {
	public static function Action_index($r){
		$User = new User;
		$OrderListUser = new OrderListUser;
		$user_list= $User->find("type=1",array("fields"=>"id","limit"=>10000));
		foreach ($user_list as $user) {
			$OrderListUser->refresh($user['id']);
		}
		echo "ok";exit;
	}

	public static function Action_agent($r){
		$User = new User;
		$OrderListAgent = new OrderListAgent;
		$user_list= $User->find("type=2",array("fields"=>"id","limit"=>10000));
		foreach ($user_list as $user) {
			$OrderListAgent->refresh_agent($user['id']);
		}
		echo "ok";exit;
	}
}
<?php

class Control_indicator {
    public static function Action_user($r, $user_id){
        Flight::validateEditorHasLogin();

        $UserIndicator 	= new UserIndicator;
        $list 	= $UserIndicator->get_user_indicator_list_admin($user_id);
        $result['list']		= $list;

        // $userIndicator  = UserIndicator::getInstance($user_id);
        // $userIndicator->refresh();

        $result['user_id']	= $user_id;
        $result['control']	= "dealer";

        Flight::display('indicator/user.html', $result);
    }

    public static function Action_add ($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->query;
        $k          = $data->k;
        $user_id    = $data->user_id;

        $UserIndicator  = new UserIndicator;
        if($k) {
            $Factory    = new ProductsAttributeFactory($k);
            $attr_list  = $Factory->getAllList();
            foreach($attr_list as &$attr) {
                $field              = "{$k}_id";
                $keyword_id         = $attr['keyword_id'];
                $attr['indicator']  = $UserIndicator->get_indicator($user_id, $field, $keyword_id);
            }
            $result['attr_list']    = $attr_list;
        }else {
            $result['indicator']    = $UserIndicator->get_indicator($user_id);
        }

        Flight::display("indicator/add.html", $result);
    }

    public static function Action_adding ($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $indicator['field']         = $data->field;
        $indicator['keyword_id']    = $data->keyword_id;
        $indicator['user_id']       = $data->user_id;
        $indicator['exp_pnum']      = $data->exp_pnum;
        $indicator['exp_skc']       = $data->exp_skc;
        $indicator['exp_num']       = $data->exp_num;
        $indicator['exp_amount']    = $data->exp_amount;

        $User           = new User;
        $UserIndicator  = new UserIndicator;
        $user           = $User->findone("id={$indicator['user_id']}");
        $indicator['type']  = $user['type'] == 3 && $user['username'] == "0" ? 4 : $user['type'];
        $UserIndicator->create($indicator)->insert(true);

        $result['error']    = 0;

        Flight::json($result);
    }

    public static function Action_edit ($r) {
    	Flight::validateEditorHasLogin();

    	$data 		= $r->data;
    	$id 		= $data->id;
    	$indicator['user_id']	= $data->user_id;
    	$indicator['exp_pnum'] 	= $data->exp_pnum;
    	$indicator['exp_skc'] 	= $data->exp_skc;
    	$indicator['exp_num'] 	= $data->exp_num;
    	$indicator['exp_amount'] = $data->exp_amount;

    	$UserIndicator 	= new UserIndicator;
    	if($id) {
    		$UserIndicator->update($indicator, "id={$id}");
    		$result['message']	= "更新成功";
    	}else{
    		$id 	= $UserIndicator->create($indicator)->insert();
    		if($id) {
    			$result['message']	= "添加成功";
    		}else{
    			$result['error']	= 1;
    			$result['message']	= "添加失败";
    		}
    	}

    	Flight::json($result);
    }

    public static function Action_indicator ($r, $id) {
    	Flight::validateEditorHasLogin();

    	$UserIndicator 	= new UserIndicator;
    	$indicator 		= $UserIndicator->findone("id={$id}");
    	$result['indicator']	= $indicator;

    	Flight::json($result);
    }

    public static function Action_set_status ($r, $id) {
    	Flight::validateEditorHasLogin();

    	$data 		= $r->query;
    	$status 	= $data->status;
    	$UserIndicator 	= new UserIndicator;
    	$UserIndicator->update(array("status"=>$status), "id={$id}");

    	Flight::redirect($r->referrer);
    }


}
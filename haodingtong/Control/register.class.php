<?php
class Control_register {
    public static function Action_index($r){
        $Company            = new Company;
        $result['company']  = $Company->getData();
        Flight::display('register/index.html', $result);
    }

    public static function Action_message($r){
        $data = $r->data;
        $name = $data->name;
        $phone= $data->phone;
        if($name && $phone){
            $UserRegister   = new UserRegister;
            $message        = $UserRegister->vaild($phone);
            if($message==""){
                $UserRegister->create_register($name,$phone);
                $message = "成功提交申请,请等待审核";
            }
        }else{
            $message = "请输入姓名和手机号";
        }
        $result['message'] = $message;
        Flight::json($result);
    }

    public static function Action_examine($r){
        Flight::validateUserHasLogin();
        $result['control'] = "examine";
        Flight::display('register/examine.html',$result);
    }

    public static function Action_examine_table($r){
        $data   = $r->query;
        $limit  = $data->limit ? $data->limit : 20 ;
        $p      = $data->p ? $data->p : 1;
        $status = $data->status;

        $options    = array();
        $options['limit']   = $limit;
        $options['page']    = $p;
        if(isset($status)) $condition[] = "status={$status}";
        $where  = implode(" AND ", $condition);
        
        $UserRegister   = new UserRegister;
        $list   = $UserRegister->find($where,$options);

        $result['list'] = $list;
        $result['start']= ($p-1) * $limit + 1;
        $result['p']    = $p;
        Flight::display('register/examine_table.html',$result);
    }

    public static function Action_set_status($r,$id){
        $UserRegister = new UserRegister;
        $UserRegister->update(array("status"=>1),"id={$id}");
        $result['vaild']    = true;
        $result['message']  = "修改成功";
        Flight::json($result);
    }
}
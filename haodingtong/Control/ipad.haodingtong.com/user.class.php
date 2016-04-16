<?php

class Control_user {
    public static function Action_dealerlist($r){
        $User   = new User;
        $result = array();
        $data   = $r->query;
        $area1  = $data->area1;
        $area2  = $data->area2;
        $zongdai= $data->zongdai;
        if($User->type == 0 || $User->type ==3){
            $condition  = array();
            $condition['area1'] = $area1;
            $condition['area2'] = $area2;
            $condition['zongdai']=$zongdai;
            $options['limit']   = 1000;
            $list   = $User->get_dealer_list($condition, $options);
            $result['list'] = $list;
        }
        Flight::json($result);
    }

    public static function Action_zongdailist($r){//根据区域获取总代
        $User   = new User;
        $result = array();
        $data   = $r->query;
        $area1  = $data->area1;
        $area2  = $data->area2;
        if($User->type == 0 || $User->type ==3){
            $condition  = array();
            $condition['area1'] = $area1;
            $condition['area2'] = $area2;
            $options['limit']   = 1000;
            $list   = $User->get_zongdailist($condition, $options);
            $result['list'] = $list;
        }
        Flight::json($result);
    }
    
    public static function Action_lock_order($r, $user_id=null){
        Flight::validateUserHasLogin();
        $User   = new User;
        $company = new Company();
        $companyData = $company->getData();
        if($user_id === null){
            $User->update(array('is_lock'=>1), "id={$User->id}");
            $u      = SESSION::get('user');
            $u['is_lock']   = 1;
            SESSION::set('user', $u);
        }elseif(is_numeric($user_id)){
            $rlog = new ReviewCancelLog();
            $lock   = $r->query->lock;
            if(!is_numeric($lock)){
                $lock   = 1;
            }
            $updateInfo = array('is_lock'=>$lock);
            $userinfo = $User->findone(' id = "'.$user_id.'" ');
            if($lock==1){
                $rlog->delete(' user_id="'.$user_id.'" ');
                if($companyData['check_order']){
                    $updateInfo['order_status'] = 3;
                }
                if($userinfo['type']==2){
                    $UserSlave  = new UserSlave;
                    $slave_id   = $UserSlave->get_slave_user_id($userinfo['id']);
                }
            }else{
                if($userinfo['id']){
                    if($userinfo['type']==2){
                        $UserSlave  = new UserSlave;
                        $slave_id   = $UserSlave->get_slave_user_id($userinfo['id']);
                    }else{
                        $slave_id = $userinfo['id'];
                    }
                    $orderUser = new OrderListUser();
                    $orderUserInfo = $orderUser->findone(' user_id in ('.($slave_id?$slave_id:0).') ',array('fields'=>'sum(num) as all_num,sum(price) as all_price,sum(discount_price) as all_discount_price'));
                    $reviewData = array();
                    $reviewData['user_id'] = $user_id;
                    $reviewData['num'] = $orderUserInfo['all_num'];
                    $reviewData['price'] = $orderUserInfo['all_price'];
                    $reviewData['discount_price'] = $orderUserInfo['all_discount_price'];
                    $reviewData['dtime'] = date('Y-m-d H:i:s');
                    $rlog->create($reviewData)->insert(true);
                    if($companyData['check_order']){
                        $updateInfo['order_status'] = 2;
                    }
                }
            }
            $User->update($updateInfo, "id={$user_id}");
            if($userinfo['type']==2){
                $User->update($updateInfo,"id in ({$slave_id})");
            }
        }

        Flight::redirect($r->referrer);
    }
    
    public static function Action_refuse_order($r){
        Flight::validateUserHasLogin();
        $data = $r->data;
        $uid = $data->uid;
        $result['error'] = 'true';
        $status = 0;     
        $user = new User();
        if($uid){
            $updateInfo = array();
            $userinfo = $user->findone(' id = "'.$uid.'" ');
            if($userinfo['is_lock']){
                $rlog = new ReviewCancelLog();
                $updateInfo['is_lock'] = 0;
                if($userinfo['type']==2){
                    $UserSlave  = new UserSlave;
                    $slave_id   = $UserSlave->get_slave_user_id($userinfo['id']);
                }else{
                    $slave_id = $userinfo['id'];
                }
                $orderUser = new OrderListUser();
                $orderUserInfo = $orderUser->findone(' user_id in ('.($slave_id?$slave_id:0).') ',array('fields'=>'sum(num) as all_num,sum(price) as all_price,sum(discount_price) as all_discount_price'));
                $reviewData = array();
                $reviewData['user_id'] = $uid;
                $reviewData['num'] = $orderUserInfo['all_num'];
                $reviewData['price'] = $orderUserInfo['all_price'];
                $reviewData['discount_price'] = $orderUserInfo['all_discount_price'];
                $reviewData['dtime'] = date('Y-m-d H:i:s');
                $rlog->create($reviewData)->insert(true);
            }
            $updateInfo['order_status'] = 2;
            $user->update($updateInfo, ' id = "'.$uid.'"  ');           
            $result['error'] = 'false';
        }
        Flight::json($result);
    }
}
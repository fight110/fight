<?php

class FrontSetting {

    public static function build(){
        $result             = array();
        $Company            = new Company;
        $Product            = new Product;
        $User               = new User;
        $CompanyStatus      = new CompanyStatus;
        $OrderList          = new OrderList;
        $UserSms			= new UserSms;
        $UserIndicator  =   new UserIndicator();
        $result['company']  = $Company->getData();
        $result['company']['status']    = $CompanyStatus->getStatus();
        $result['unreadsms'] = $UserSms->getMySmsNum($User->id);
        if($user_id = $User->id){
            $result['user'] = $User->getAttribute();
            switch ($User->type) {
                case 0  :   //管理员
                    $menu_type = 5;
                    break;
                case 1  :   //店仓
                    $indicator  =   $UserIndicator->get_indicator($user_id);
                    if($indicator){
                        $orderinfo['num']   =   $indicator['ord_num'];
                        $orderinfo['price'] =   $indicator['ord_amount'];
                        $orderinfo['discount_price'] =   $indicator['ord_discount_amount'];
                        if($indicator['exp_num']){
                            $orderinfo['percent_exp_num']   = sprintf("%d%%", $indicator['ord_num']/$indicator['exp_num'] * 100);
                        }
                        if($indicator['exp_amount']){
                            $orderinfo['percent_exp_price'] = sprintf("%d%%", $orderinfo['discount_price']/$indicator['exp_amount'] * 100);
                        }
                    }else{
                        list($rank, $orderinfo) = $OrderList->getRank($user_id);
                        if($result['user']['exp_num']){
                            $orderinfo['percent_exp_num']   = sprintf("%d%%", $orderinfo['num']/$result['user']['exp_num'] * 100);
                        }
                        if($result['user']['exp_price']){
                            $orderinfo['percent_exp_price'] = sprintf("%d%%", $orderinfo['discount_price']/$result['user']['exp_price'] * 100);
                        }
                    }

                    $result['orderinfo']    = $orderinfo;
//                     $orderinfo['allrank'] = $rank;
//                     $result['productCountAll']  = $Product->getCount("");
//                     $orderinfo['order_delete_num']    = $Product->getMydeleteNum($user_id);
//                     $orderinfo['all_delete_num']      = $Product->getCount("status=0");
//                     $orderinfo['unordered_num']   = $result['productCountAll'] - $orderinfo['pnum'] - $orderinfo['all_delete_num'];
//                     $wrong_order    = $Company->wrong_order;
//                     $orderinfo['wrong_num']       = $OrderList->getCount("num>={$wrong_order} AND user_id={$user_id}");
//                     $result['orderinfo']    = $orderinfo;

//                     $userTarget = new UserTarget();
//                     $userTargetOrder = $userTarget->getUserTargetOrder($user_id,'p.season_id');
//                     $result['userTargetOrder']    = $userTargetOrder;
                    
                    //是否精简版
//                     $result['user']['is_lite']  = SESSION::get(STRING_HDT_LITE);
                    $result['user']['needLastInfo'] = $User->getLsatInfo($user_id,'is_lock,order_status');
                    $menu_type = 1;
//                     if(strstr($_SERVER['REQUEST_URI'], 'dealer1/groupdetailnew')||strstr($_SERVER['REQUEST_URI'], 'dealer1/display_new')){
//                         $result['showSearchInput'] = 1;
//                     }
                    break;
                case 2  :   //总代
                    $UserSlave  = new UserSlave;
                    $OrderListAgent = new OrderListAgent;
                    $result['slave_list']   = $UserSlave->get_slave_user_list($user_id);
                    $indicator  =   $UserIndicator->get_indicator($user_id);
                    $order      =   $OrderList->findone("zd_user_id={$user_id}",array("fields"=>"sum(zd_discount_amount) as discount_price"));
                    $agent_orderlist = $OrderListAgent->findone("user_id={$user_id}",array("fields"=>"sum(num) as num,sum(amount) as amount"));
                    if($indicator){
                        $orderinfo['num']   =   $agent_orderlist['num'];
                        $orderinfo['price'] =   $agent_orderlist['amount'];
                        $orderinfo['discount_price'] =   $order['discount_price'];
                        if($indicator['exp_num']){
                            $orderinfo['percent_exp_num']   = sprintf("%d%%", $orderinfo['num']/$indicator['exp_num'] * 100);
                        }
                        if($indicator['exp_amount']){
                            $orderinfo['percent_exp_price'] = sprintf("%d%%", $orderinfo['discount_price']/$indicator['exp_amount'] * 100);
                        }
                    }else{
                        
                        //$orderinfo              = $OrderList->getSelfOrderinfo($user_id);
                        $orderinfo              = $OrderList->getADSelfOrderinfo($user_id);
                        //$exp_info               = $User->get_slave_exp_info($user_id);
                        //$result['user']['exp_num']      = $exp_info['exp_num'];
                        //$result['user']['exp_price']    = $exp_info['exp_price'];
                        if($result['user']['exp_num']){
                            $orderinfo['percent_exp_num']   = sprintf("%d%%", $orderinfo['num']/$result['user']['exp_num'] * 100);
                        }
                        if($result['user']['exp_price']){
                            $orderinfo['percent_exp_price'] = sprintf("%d%%", $orderinfo['discount_price']/$result['user']['exp_price'] * 100);
                        }
                    }
                    $result['orderinfo']    = $orderinfo;
                    $menu_type = 2;
                    $result['user']['needLastInfo'] = $User->getLsatInfo($user_id,'is_lock,order_status');
                    break;
                case 3  :   //区域经理
                    $menu_type = 3;
                    if($result['user']['username']!='0'){
                        $userlist=$User->getADUserList($result['user']['id'],'id,name');
                        $result['userlist']=$userlist;
                        $menu_type = 4;
                    }
                    break;
                case 9:
                    $menu_type=9;
                    break;
                default : 1;
            }
        }
        if($menu_type){
            // $mcontrol = new MenuControl();
            // $result['userMenu'] = $mcontrol->getUserMenuByType($menu_type);
            $Menulist   = new Menulist;
            $result['menulist'] = $Menulist->get_mlist($menu_type);
        }
        return $result;
    }


    public static function build_iphone(){
        $result     = array();

        $result['ProductInfo']  = SESSION::cache("iphoneProductInfo", function(){
            $Product    = new Product;
            $total      = $Product->getCount("");
            $info['total']  = $total;
            return $info;
        }, 60);

        return $result;
    }

}





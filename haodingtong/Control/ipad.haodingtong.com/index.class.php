<?php
class Control_index {
    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $User   = new User;
        switch ($User->type) {
            case 1 :
                $OrderList = new OrderList;
                $OrderList->refresh_index_user($User->id);
                $User->refresh_login_userinfo();
                $Company = new Company();
                $companyData = $Company->getData();
                if($companyData['new_display']==1){
                    Flight::redirect("/dealer1/display_new");
                }elseif ($companyData['group_order']==1) {
                    Flight::redirect("/pushorder/group_order");
                }elseif ($companyData['isspot']==1){
                    Flight::redirect("/dealer1/isspot");
                }else{
                    Flight::redirect("/dealer1");
                }
                
                break;
            case 2 :
                Flight::redirect("/dealer2/exp_print_new2");
                break;
            case 3 :
                //if($User->username!='0'){
                    //Flight::redirect("/ad/exp3");
                //}else{
                    Flight::redirect("/ad/exp_print_new2");
                //}
                
                break;
            case 9:
                Flight::redirect("/designer");
                break;
            // case 0 :
            //     Flight::redirect("/dealer1");
            //     break;
            default : Flight::redirect("/login");
        }
    }

    public static function Action_detail($r, $id){
        Flight::validateUserHasLogin();

        $User   = new User;
        switch ($User->type) {
            case 1 :
                Flight::redirect("/dealer1/detail/{$id}");
                break;
            case 2 :
                $mid    = $User->id;
                $u  = $User->findone("mid={$mid}");
                if($u['id']){
                    SESSION::set("user", $u);
                }
                Flight::redirect("/dealer1/detail/{$id}");
                break;
            case 3 :
                Flight::redirect("/ad/detail/{$id}");
                break;
            default : Flight::redirect("/login");
        }
    }

    public static function Action_search($r){
        Flight::validateUserHasLogin();
        $data       = $r->query;
        $id         = $data->q;
        $bianhao    = $data->bianhao;
        $f          = $data->f;
        $search_f   = $data->search_f;
        $Company    = new Company;
        $company_search_f = $Company->search_f;
        $controlName = (($search_f == "group_ad"||$search_f == "display_ad")?'ad':'dealer1');
        if($search_f == "group"||$search_f == "group_ad"){
            if($company_search_f){
                $ProductGroup   = new ProductGroup;
                $group          = $ProductGroup->findone("dp_num={$id}");
                if($group['id']){
                        Flight::redirect("/".$controlName."/groupdetail/{$group['id']}");
                }else{
                        Flight::redirect("/".$controlName."/group");
                }
            }else{
                $ProductGroupMember   = new ProductGroupMember;
                $options['tablename'] = "product_group_member as pgm 
                                        left join product as p on pgm.product_id=p.id 
                                        left join product_group as pg on pg.id=pgm.group_id";
                $options['fields'] = "pg.id";
                $where = "p.kuanhao={$id} or p.id in (select product_id from product_color where skc_id={$id})";
                $info  = $ProductGroupMember->findone($where,$options);
                if($info['id']){
                        Flight::redirect("/".$controlName."/groupdetail/{$info['id']}");
                }else{
                        Flight::redirect("/".$controlName."/group");
                }
            }
            return;
        }elseif($search_f == "display"||$search_f == "display_ad"){
            if($company_search_f){
                $ProductDisplay = new ProductDisplay;
                $display        = $ProductDisplay->findone("bianhao={$id}");
                if($display['id']){
                    Flight::redirect("/".$controlName."/displaydetail/{$display['id']}");
                }else{
                    Flight::redirect("/".$controlName."/display");
                }
            }else{
                $ProductDisplayMemberColor = new ProductDisplayMemberColor;
                $options = array();
                $options['tablename'] = "product_display_member_color as pdmc 
                                        left join product as p on pdmc.product_id=p.id
                                        left join product_display as pd on pdmc.display_id=pd.id";
                $options['fields'] = "pd.id";
                $where = "p.kuanhao={$id} or p.id in (select product_id from product_color where skc_id={$id})";
                $info  = $ProductDisplayMemberColor->findone($where,$options);

                if($info['id']){
                    Flight::redirect("/".$controlName."/displaydetail/{$info['id']}");
                }else{
                    Flight::redirect("/".$controlName."/display");
                }
            }
            return;
        }elseif($search_f == "group_display"){
            /*$ProductGroup   = new ProductGroup;
             $group          = $ProductGroup->findone("dp_num={$id}");
             if($group['id']){
             $gtp=new GroupToDisplay();
             $gtpInfo=$gtp->findone(' group_id ="'.$group['id'].'" ' );
             if(!$gtpInfo['display_id']){
             Flight::redirect("/".$controlName."/display_new");
             }*/
             Flight::redirect("/dealer1/display_new?q={$id}");
            /*}else{
             Flight::redirect("/".$controlName."/display_new");
            }*/
            return;           
        }elseif($search_f == "display_ad_new"){
            $ProductDisplay = new ProductDisplay;
            $display        = $ProductDisplay->findone("bianhao={$id}");
            if($display['id']){
                Flight::redirect("/ad/displaydetailnew/{$display['id']}");
            }else{
                Flight::redirect("/ad/display_new");
            }
            return;
        }
        $Product    = new Product();
        $User       = new User;
        if(is_numeric($id)){
            $ProductColor   = new ProductColor;
            $pc     = $ProductColor->get_by_skc_id($id);
            if($pc['product_id']){
                $product_id     = $pc['product_id'];
            }else{
                $plist = $Product->find("bianhao='{$id}' or kuanhao='{$id}' or id in (select product_id from product_color where skc_id='{$id}')",
                                    array("fields"=>"id as product_id"));
                //$ProductColor   = new ProductColor;
                //$plist = $ProductColor->find("skc_id like '{$id}%'");
                $plength    = count($plist);
                if($plength > 0){
                    if($plength == 1){
                        $product_id = $plist[0]['product_id'];
                    }else{
                        if($User->type == 3){
                            Flight::redirect("/ad?q={$id}");
                        }else{
                            Flight::redirect("/dealer1?q={$id}");
                        }
                        return;
                    }
                }
            }
        }elseif(is_numeric($bianhao)){
            switch ($f) {
                case "up"   :
                    $eq     = "<";
                    break;
                case "down" :
                    $eq     = ">";
                    break;
                default     :
                    $eq     = "=";
            }
            $product    = $Product->findone("bianhao{$eq}'{$bianhao}'", array("order"=>"bianhao ASC"));
            $product_id = $product['id'];
        }else{
            $id         = strtoupper($id);
            // $product    = $Product->findone("kuanhao='{$id}'");
            $product    = $Product->findone("bianhao='{$id}' or kuanhao='{$id}' or id in (select product_id from product_color where skc_id='{$id}')");
            $product_id     = $product['id'];
        }

        if($product_id){
            if($User->type == 1) {
                Flight::redirect("/dealer1?q={$id}");
            }elseif($User->type ==2){
                Flight::redirect("/dealer2?q={$id}");
            }else{
                // Flight::redirect("/index/detail/{$product_id}");
                Flight::redirect("/ad?q={$id}");
            }
        }else{
            SESSION::message("未找到该款");
            if($User->type == 1) {
                Flight::redirect("/dealer1");
            }elseif($User->type ==2){
                Flight::redirect("/dealer2");
            }else{
                Flight::redirect("/ad");
            }
        }
    }

    public static function Action_test($r){
        // $OrderList  = new OrderList;
        // for($i = 2000; $i < 3000; $i++){
        //     $OrderList->copy_order1(4, $i);
        // }
    }

    public static function Action_is_haodingtong_alive($r){
        $callback   = $r->query->callback;
        $User       = new User;
        $result     = array();
        if($User->id){
            $result['uid']  = $User->id;
        }
        $json   = json_encode($result);
        if($callback){
            echo $callback . "({$json})";
        }else{
            echo "{$json}";
        }
    }

    public static function Action_gotod2($r){
        Flight::validateUserHasLogin();
        $User   = new User;
        if($mid = $User->mid){
            $zongdai    = $User->findone("id={$mid}");
            if($zongdai['id']){
                SESSION::set("user", $zongdai);
                Flight::redirect("/index");
                exit;
            }
        }
        Flight::redirect($r->referrer);
    }

    public static function Action_gotod1($r){
        Flight::validateUserHasLogin();
        $User   = new User;
        if(2 == $User->type){
            $zongdai    = $User->findone("mid={$User->id}");
            if($zongdai['id']){
                SESSION::set("user", $zongdai);
                Flight::redirect("/index");
                exit;
            }
        }
        Flight::redirect("/index");
    }

	public static function Action_refuser($r){
		$OrderList 	= new OrderList;
		$OrderList->refresh_index_user();
//		$OrderList->refresh_index_product();
        $OrderListProductColor  = new OrderListProductColor;
        $OrderListProductColor->refresh_all();
		echo "ok";
        // $User   = new User;
        // $ulist  = $User->find("type=1 and id>729", array("limit"=>1000));
        // foreach($ulist as $user){
        //     echo "{$user['id']} {$user['name']}<br>\n";
        //     ProductOrder::refresh_user($user['id']);
        // }
	}

    

}

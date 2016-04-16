<?php
class Control_display {
    public static function Action_index($r){
        
        $data = $r->data;
        if($data->update){
            $group_bianhao = $data->group_bianhao;
            $display_bianhao = $data->display_bianhao;
            $skc_id = $data->skc_id;
            
            if(!$display_bianhao){
                echo '请输入陈列编号!';exit;
            }
            
            if(!$group_bianhao){
                echo '请输入搭配编号!';exit;
            }
           
            if(!$skc_id){
                echo '请输入圆牌号!';exit;
            }
            
            $pd = new ProductDisplay();
            $pg = new ProductGroup();
            $pc = new ProductColor();
            $od = new OrderListDetail();
            $gtd = new GroupToDisplay();
            $pgm    = new ProductGroupMember();
            $pdm = new ProductDisplayMember();
            $pdmc = new ProductDisplayMemberColor();
            
            $pdinfo = $pd->findone('bianhao="'.$display_bianhao.'"');
            if(!sizeof($pdinfo)){
                echo '你输入的陈列不存在!';exit;
            }
            
            $pginfo = $pg->findone('dp_num="'.$group_bianhao.'"');
            if(!sizeof($pginfo)){
                echo '你输入的搭配不存在!';exit;
            }
            
            $pcinfo = $pc->findone('skc_id="'.$skc_id.'"');
            if(!sizeof($pcinfo)){
                echo '你输入的圆牌号不存在!';exit;
            }
            
            $product_id = $pcinfo['product_id'];
            $color_id = $pcinfo['product_id'];
            $group_id = $pginfo['id'];
            $display_id = $pdinfo['id'];
            
            $odinfo = $od->find('o.product_id = "'.$product_id.'" AND o.product_color_id = "'.$color_id.'"',array('limit'=>100,'group'=>'display_id,group_id','tablename'=>' orderlistdetail o left join product_display pd on o.display'));
            if(sizeof($odinfo)){
                
                Flight::display('display/choose.html', $result);
            }else{
                $gtd->createGroupToDisplay($group_id,$display_id);
                $pgm->create_member($group_id, $product_id,$color_id);
                $pdm->create_member($display_id, $product_id);
                $pdmc->create_color($display_id, $product_id, $color_id);
                echo '更新成功！';exit;
            }
            
        }
        Flight::display('display/index.html', $result);
    }

    public static function Action_get_type2_list($r){
        $data   = $r->query;
        $pd_type    = $data->pd_type;

        $ProductDisplay = new ProductDisplay;
        $list = $ProductDisplay->find("pd_type={$pd_type}",array("fields"=>"pd_type2","group"=>"pd_type2"));
        $result['list'] = $list;
    
        Flight::display("display/type_list.html", $result);
    }
    
    public static function Action_display_next($r){
        $display_id     = $r->query->display_id;
        $f              = $r->query->f;
        switch ($f) {
            case 'pre':
                $condition[]      = "id < {$display_id}";
                $options['order'] = "id DESC";
                $message          = "已经是第一组了";
                break;
            case 'next':
                $condition[]      = "id > {$display_id}";
                $options['order'] = "id ASC";
                $message          = "已经是最后一组了";
                break;
            default:
                $condition[]      = "id={$display_id}";
                $message          = "未找到该款";
                break;
        }
        $ProductDisplay = new ProductDisplay;
        $where          = implode(" AND ", $condition);
        $product_display= $ProductDisplay->findone($where,$options);
        if($product_display['id']){
            $valid   = true;
        }else{
            $valid   = false;
        }
        $result['valid']    = $product_display['id'] ? true : false;
        $result['message']  = $message;
        $result['id']       = $product_display['id'];

        Flight::json($result);
    }
    
    public static function Action_display_by_bianhao($r){
        $bianhao                = $r->query->bianhao;
        $ProductDisplay         = new ProductDisplay;
        $product_display        = $ProductDisplay->findone("bianhao='{$bianhao}'");
        $result['display_id']   = $product_display['id'];
        Flight::json($result);
    }
}
<?php

class Control_group {
    
    public static function Action_member_list($r) {
        Flight::validateUserHasLogin();

        $data   = $r->query;
        $group_id   = $data->group_id;

        $User                   = new User;
        $Product                = new Product;
        $ProductColor           = new ProductColor;
        $ProductGroupMember     = new ProductGroupMember;
        $OrderList              = new OrderList;
        $list   = $ProductGroupMember->get_member_list($group_id);
        $options['limit']   = 100;
        $options['key']     = "product_color_id";
        $options['group']   = "product_id,product_color_id";
        $options['fields']  = "product_id,product_color_id,sum(num) as num";
        $user_id            = $User->id;
        foreach($list as &$row) {
            $product_id     = $row['product_id'];
            $product        = $Product->findone("id={$product_id}");
            $row['product'] = $product;
            $color_list     = $ProductColor->get_color_list($product_id);
            $where          = "user_id={$user_id} AND product_id={$product_id} AND num>0";
            $orderinfo      = $OrderList->find($where, $options);
            foreach($color_list as &$color) {
                $product_color_id   = $color['color_id'];
                if($color['status']){
                    $num                = $orderinfo[$product_color_id]['num'];
                    $color['num']       = $num;
                }
            }
            $row['color_list']      = $color_list;
        }
        $result['list'] = $list;

        Flight::display("group/member_list.html", $result);
    }
}
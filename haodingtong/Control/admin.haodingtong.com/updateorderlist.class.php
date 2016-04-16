<?php

class Control_updateorderlist {
    public static function Action_update($r, $username){
        $User       = new User;
        $user       = $User->findone("username='{$username}'");
        if($user['id']) {
            $options['limit']   = 100000;
            $OrderList  = new OrderList; 
            $list   = $OrderList->find("user_id={$user['id']}", $options);
            foreach($list as $row) {
                $user_id    = $row['user_id'];
                $product_id = $row['product_id'];
                $product_color_id   = $row['product_color_id'];
                $product_size_id    = $row['product_size_id'];
                $num        = $row['num'];
                ProductOrder::add($user_id, $product_id, $product_color_id, $product_size_id, $num);
            }
            ProductOrder::run();
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_list ($r) {
        $User   = new User;
        $list   = $User->find("type=1", array("limit"=>10000, "order"=>"id"));

        echo "<meta charset='utf-8'><table>";
        foreach($list as $u) {
            echo "<tr><td>{$u['username']}</td><td>{$u['name']}</td><td><a href='/updateorderlist/update/{$u['username']}'>更新</a></td></tr>";
        }
        echo "</table>";
    }

    
}
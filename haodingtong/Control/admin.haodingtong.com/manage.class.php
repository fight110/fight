<?php

class Control_manage {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();


        Flight::display('manage/index.html', $result);
    }

    public static function Action_modify($r){
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $uname      = $data->uname;
        $bianhao    = $data->bianhao;
        $color      = $data->color;
        $new_color  = $data->new_color;
        $size       = $data->size;
        $new_size   = $data->new_size;
        $OrderList  = new OrderList;
        $User       = new User;

        // 清空帐号订单
        if($uname){
            $u     = $User->findone("username='{$uname}'");
            if($user_id = $u['id']){
                $list   = $OrderList->find("user_id={$user_id}", array("limit"=>10000));
                foreach($list as $row){
                    ProductOrder::add($user_id, $row['product_id'], $row['product_color_id'], $row['product_size_id'], 0);
                }
                ProductOrder::run();
                // $OrderList->remove_order($user_id);
                SESSION::message("删除订单成功");
             }else{
                SESSION::message(sprintf("用户名%s不存在", $uname));
             }
        }

        // 删除商品属性
        if($bianhao){
            $Product    = new Product;
            $product    = $Product->findone("bianhao='{$bianhao}'");
            if($product_id  = $product['id']){
                $Keywords   = new Keywords;
                // 删除颜色
                if($color){
                    $color_id   = $Keywords->getKeywordId($color);
                    if($new_color){
                        $new_color_id   = $Keywords->getKeywordId($new_color);
                        $OrderList->change_color($product_id, $color_id, $new_color_id);
                        $ProductColor   = new ProductColor;
                        $ProductColor->update(array("color_id"=>$new_color_id), "product_id='{$product_id}' and color_id='{$color_id}'");
                        SESSION::message("替换颜色成功");
                    }else{
                        $OrderList->remove_order(0, $product_id, $color_id, 0);
                        $ProductColor   = new ProductColor;
                        $ProductColor->remove_color($product_id, $color_id);
                        SESSION::message("删除颜色成功");
                    }
                }

                // 删除尺码
                if($size){
                    $size_id    = $Keywords->getKeywordId($size);
                    $OrderList->remove_order(0, $product_id, 0, $size_id);
                    $ProductSize    = new ProductSize;
                    $ProductSize->remove_size($product_id, $size_id);
                    SESSION::message("删除尺码成功");
                }
            }else{
                SESSION::message(sprintf("商品编号%s不存在", $bianhao));
            }
            
        }

        Flight::redirect($r->referrer);
    }


}

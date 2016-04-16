<?php

class OrderListBak Extends BaseClass {
    public function __construct(){
        $this->setFactory('orderlistbak');
    }

    public function bak ($product_id, $color_id, $status) {
        if($status){
            $table_from     = "orderlistbak";
            $table_to       = "orderlist";
        }else{
            $table_from     = "orderlist";
            $table_to       = "orderlistbak";
        }
        $sql1   = "insert into {$table_to} select * from {$table_from} where product_id={$product_id} and product_color_id={$color_id}";
        $sql2   = "delete from {$table_from} where product_id={$product_id} and product_color_id={$color_id}";
        $this->dbh->query($sql1);
        $this->dbh->query($sql2);
    }

}


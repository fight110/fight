<?php

class Control_index {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();
        
        Flight::redirect("/product"); 
    }

    public static function Action_refuser($r){
		$OrderList 	= new OrderList;
		$OrderList->refresh_index_user();
//		$OrderList->refresh_index_product();
        $OrderListProductColor  = new OrderListProductColor;
        $OrderListProductColor->refresh_all();
        ProductOrder::refresh_product_price_change();
		echo "ok";
	}

}
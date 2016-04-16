<?php

class OrderListLog{
    public function __construct(){
        
    }

    public static function add ($user_id, $product_id, $color_id, $size_id, $num) {
    	$ip 		= Flight::IP();
    	$time 		= time();
    	$filename 	= DOCUMENT_ROOT . "tmpl/log_" . date("Ymd") . ".txt";
    	$string 	= sprintf("%s\t%s\t%s\t%s\t%s\t%s\t%s\r\n", $user_id, $product_id, $color_id, $size_id, $num, $ip, $time);
    	file_put_contents($filename, $string, FILE_APPEND);
    }

}





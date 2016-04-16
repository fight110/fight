<?php

function smarty_modifier_price_cn($string, $format="%.1f")
{
	// if(is_string($string)){
	// 	$string = sprintf("%.2f", $string);
	// }
 //    if(abs($string) >= 10000){
 //        $result     = sprintf("{$format}万", $string / 10000);
 //    }else{
 //        $result     = $string;
 //    }
	$result = number_format($string);
    return $result;
}
<?php

function smarty_modifier_hdt_pcimage($string, $product_id=0, $product_color_id=0)
{
	if($product_id && $product_color_id) {
		$pcinfo 	= ProductImage::getProductColorImage($product_id, $product_color_id);
		if($pcinfo && $pcinfo['image']) {
			$result = $pcinfo['image'];
		}
	}
	if(!$result) {
		$result = $string ? $string : 'tmpl/default.jpg';
	}
	return $result;
}
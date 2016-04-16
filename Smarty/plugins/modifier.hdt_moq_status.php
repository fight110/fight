<?php

function smarty_modifier_hdt_moq_status($string, $product_id, $color_id)
{
	$ProductColorMoq   = new ProductColorMoq();
    $status 	= $ProductColorMoq->get_status($product_id, $color_id);
	$result 	=	$status ? '<font color="green">可投产</font>' : '<font color="red">不投产</font>';
	return $result;
}
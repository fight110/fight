<?php

function smarty_modifier_hdt_menu($string, $utype)
{
	$Menulist   = new Menulist;
    $result 	= $Menulist->get_mlist($utype);
	return $result;
}
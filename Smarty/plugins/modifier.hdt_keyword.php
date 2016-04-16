<?php

function smarty_modifier_hdt_keyword($string)
{
	return Keywords::cache_get(array($string));
}
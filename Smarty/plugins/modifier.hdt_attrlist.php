<?php

function smarty_modifier_hdt_attrlist($string, $field, $defaultText='', $element='option')
{
    $data       = array();
	$Factory	= new ProductsAttributeFactory($field);
    $params     = array();
    if($field == "brand") {
        $User       = new User;
        if($User->type == 1) {
            $params['permission']   = $User->permission_brand;
        }
    }
    $list 		= $Factory->getAllList($params);
    switch ($element) {
        case 'checkbox'     :
            foreach($list as $row) {
                $keyword_id     = $row['keyword_id'];
                $name           = $row['keywords']['name'];
                // if($string == $keyword_id){
                //     $data[] = "<input type='checkbox' name='{$field}_id' value='{$keyword_id}' checked='checked'>{$name} ";
                // }else{
                    $data[] = "<input type='checkbox' name='{$field}_id[]' value='{$keyword_id}'>{$name} ";
                // }
            }
            break;
        case 'fancybox':          
            if(sizeof($list)==1&&$list[0]['keywords']['name']==''){
                $data = array();
            }else{
            $data[] = '<div class="selectItem"><div class="selectKey">'.$defaultText.':</div><div class="selectValue limitHeight"><ul data-name="'.$field.'_id" '.(((isset($_GET[$field.'_id'])&&$_GET[$field.'_id']!=='')?' data-checked="'.$_GET[$field.'_id'].'" ':'')).'>';
            foreach($list as $row) {
                $keyword_id     = $row['keyword_id'];
                $name           = $row['keywords']['name'];
                
                $data[] = '<li data-value="'.$keyword_id.'">'.$name.'</li>';
            }
            $data[] = '</ul></div><div class="selectAction"><span class="viewMore">更多</span></div></div>';
            }
            break;
        default :
            $data[] = "<option value=''>{$defaultText}</option>";
            foreach($list as $row){
                $keyword_id     = $row['keyword_id'];
                $name           = $row['keywords']['name'];
                if($string == $keyword_id){
                    $data[] = "<option value='{$keyword_id}' selected='selected'>{$name}</option>";
                }else{
                    $data[] = "<option value='{$keyword_id}'>{$name}</option>";
                }
            }
    }
    

    $result 	= implode('', $data);

    return $result;
}
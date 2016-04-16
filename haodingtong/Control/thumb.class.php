<?php

class Control_thumb{
    public static function Action_index($r){
        $params     = explode('/', $r->url, 4);
        $size       = $params[2];
        if(is_numeric( strpos($params[3], 'tmpl') )){
            $filename   = $params[3];
        }else{
            $filename   = "/img/" . $params[3];
        }
        $_GET['src']    = $filename;
        if(is_numeric($size)){
            $_GET['w']  = $size;
        }
        $_SERVER ['QUERY_STRING'] = $r->url;
        require DOCUMENT_ROOT. 'lib/TimThumb/timthumb_v2.php';
        timthumb_v2::start($r->url);
    }
}
<?php

Flight::map('boot', function($model, $action, $id=""){
    $request    = Flight::request();
    if(!$action)    $action     = "index";
    if($request->query->debug)  Flight::set('debug', true);
    $ControlName    = "Control_{$model}";
    $ActionName     = "Action_{$action}";
    if(!class_exists($ControlName)){
        $smarty     = Flight::view();
        $static     = DOCUMENT_ROOT . "templates/".PROJECT_NAME."/" .PROJECT_HTTP_HOST. "/html/{$model}";
        if(file_exists($static)){
            Flight::set('Control', 'static');
            Flight::display("html/{$model}");
        }else{
            Flight::notFound();
        }
        exit;
    }
    if(is_callable($ControlName."::".$ActionName)){
        $arguments  = array($request);
        if(isset($id)) $arguments[]    = $id;
        $beforeCallFunc     = "{$ControlName}::_beforeCall";
        if(is_callable($beforeCallFunc)){
            $beforeCallResult   = call_user_func_array(array($ControlName, '_beforeCall'), $arguments);
        }
        if(false !== $beforeCallResult) {
            Flight::set('Control', $model);
            Flight::set('Action', $action);
            call_user_func_array(array($ControlName, $ActionName), $arguments);
        }
    }
    else{
        Flight::notFound();
    }
});
Flight::before('start', function(&$params, &$output){
    SESSION::start();//24 * 3600 * 5
    $HOST_CONFIG['admin.hao.com']            = "admin.haodingtong.com";
    $HOST_CONFIG['ipad.hao.com']       = "ipad.haodingtong.com";
    $HOST_CONFIG['masteradmin.haodingtong.com'] = "admin.haodingtong.com";
    $HOST_CONFIG['testadmin.haodingtong.com']   = "admin.haodingtong.com";
    if($HOST_PATH = $HOST_CONFIG[ $_SERVER['HTTP_HOST'] ]){
        define("PROJECT_HTTP_HOST", $HOST_PATH);
    }else{
        define("PROJECT_HTTP_HOST", "ipad.haodingtong.com");
    }
});
Flight::before('boot', function(&$params, &$output){
    // 定时记录一次更新时间
    SESSION::cache("refresh_session_time", function() {
        $User           = new User;
        $user_session   = UserSession::getInstance();
        if($User->id > 0 && $User->type == 1 && $user_session->user_id == 0) {
            $message    = $user_session->message;
            if($message){
                SESSION::message($message);
                $user_session->message = "";
                $user_session->refresh();
            }
            SESSION::set("user", null);
            Flight::redirect("/");
            exit;
        }
        $user_session->refresh();
    }, 20);
    // Auth::is_auth();
});

// Flight::before('start', function(&$params, &$output){
//     $runtime    = new Runtime;
//     $runtime->start();
// });
// Flight::after('display', function(&$params, &$output){
//     $runtime    = new Runtime;
//     $runtime->end();
// });

function route($a="index", $b="index", $c=""){
    if(!$a) $a  = "index";
    Flight::boot($a, $b, $c);
}


Flight::map('display', function($template, $data=array()){
    $smarty     = Flight::view();
    $control    = Flight::get('Control');
    $action     = Flight::get('Action');
    $message    = SESSION::getmessage();
    $keyword    = include DOCUMENT_ROOT . "haodingtong/Config/keyword.conf.php";
    $user       = new User;
    if($user->id){
        $smarty->assign('currentUser', $user->getAttribute());
    }
    $smarty->assign('control',  $control);
    $smarty->assign('action',   $action);
    $smarty->assign('show',     $data);
    $smarty->assign('message',  $message);
    $smarty->assign('keyword',  $keyword);
    $smarty->display($template);
});
Flight::map('fetch', function($template, $data=array()){
    $smarty     = Flight::view();
    $user       = new User;
    if($user->id){
        $smarty->assign('currentUser', $user->getAttribute());
    }
    $smarty->assign('show', $data);
    return $smarty->fetch($template);
});

Flight::map('validateUserHasLogin', function(){
    $user               = SESSION::get('user');
    if(!is_array($user)){
        $user_session   = UserSession::getInstance();
        if($user_session->user_id) {
            $User       = new User;
            $user       = $User->findone("id={$user_session->user_id}");
            SESSION::set("user", $user);
        }else{
            $r              = Flight::request();
            if($returl      = $r->data->returl){}
            elseif($returl  = $r->query->returl){}
            else{
                $returl     = 'http://'. $_SERVER['HTTP_HOST'] . $r->url;
            }
            Flight::redirect('http://'.$_SERVER['HTTP_HOST'] ."/login?returl=" . urlencode($returl));
            exit;
        }
    }
});
Flight::map('validateEditorHasLogin', function(){
    $editor             = SESSION::get('editor');
    if(!is_array($editor)){
        $r              = Flight::request();
        if($returl      = $r->data->returl){}
        elseif($returl  = $r->query->returl){}
        else{
            $returl     = 'http://'.$_SERVER['HTTP_HOST'] . $r->url;
        }
        Flight::redirect("http://{$_SERVER['HTTP_HOST']}/login?returl=" . urlencode($returl));
        exit;
    }else{
        $mcontrol = new MenuControl();
        $_SESSION['userMenu'] = $mcontrol->getUserMenuByType(5);
    }

});
Flight::map('IP', function(){
    return ip2long(Flight::request()->ip);
});
/*
    @list           array
    @tablename      string      数据表名
    @list_column    string      对应list内部每一行元素的key eg: article.category  => $list[0]['article']['category']
    @table_column   string
    @where_string   string      eg : 'status=1'
    @as_string      string      eg : 'name'
*/
Flight::map('listFetch', function($list, $tablename, $list_column, $table_column='id', $where_string='', $as_string=false){
    $total      = count($list);
    if(!$total) return $list;

    $hash       = array();
    foreach($list as $key => $row){
        $column     = getValueFromMultiArray($row, $list_column);
        if($column !== null){
            $hash[$column][]   = $key;
        }
    }
    $values     = implode(',', array_keys($hash));
    if(!$values)    return $list;
    $factory    = DataFactory::getInstance($tablename);
    $condition[]    = "{$table_column} in ({$values})";
    if($where_string)    $condition[]    = $where_string;
    $where      = implode(' AND ', $condition);
    $newlist    = $factory->saver->getList($where, array('limit' => $total));
    if(false===$as_string){
        $as_string = $tablename;
    }
    foreach($newlist as $item){
        $keylist    = $hash[$item[$table_column]];
        foreach($keylist as $key){
            $list[$key][$as_string] = $item;
        }
    }

    return $list;
});
function getValueFromMultiArray($array, $string){
    $result     = $array;
    $explode    = explode('.', $string);
    foreach($explode as $key){
        if(!is_array($result))                  return null;
        if(!array_key_exists($key, $result))    return null;

        $result     = $result[$key];
    }
    return $result;
}





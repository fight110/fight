<?php

class Control_domain {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();
        $Domain    = new Domain;
        $result['domain_list']  = $Domain->get_list();
        Flight::display('domain/index.html', $result);
    }

    public static function Action_set_status($r,$id){
        Flight::validateEditorHasLogin();
        $data   = $r->query;
        $status = $data->status;
        $Domain = new Domain;
        $Domain->set_status($id,$status);
        Flight::redirect($r->referrer);
    }

    public static function Action_domain_html($r){
        $data = $r->query;
        $id   = $data->id;
        $Domain = new Domain;
        $result['info'] = $Domain->findone("id={$id}");
        Flight::display('domain/domain.html',$result);
    }

    public static function Action_add($r){
        $data   = $r->query;
        $domain = $data->domain;
        $name   = $data->name;
        $id     = $data->id;
        if($domain && $name){
            $Domain = new Domain;
            if($id){
                $Domain->edit_domain($id,$domain,$name);
            }else{
                $Domain->create_domain($domain,$name);
            }
            $result['valid'] = true;
            $result['message'] = "保存成功";
        }else{
            $result['message'] = "保存失败";
        }
        Flight::json($result);
    }

}
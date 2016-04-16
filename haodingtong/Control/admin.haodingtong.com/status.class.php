<?php

class Control_status {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $CompanyStatus  = new CompanyStatus;
        $result['list'] = $CompanyStatus->getList();
        $result['status']   = $CompanyStatus->getStatus();

        Flight::display('status/index.html', $result);
    }

    public static function Action_add($r){
        Flight::validateEditorHasLogin();

        $CompanyStatus  = new CompanyStatus;
        $id             = $r->data->id;
        $content        = $r->data->content;
        if($content){
            if(is_numeric($id)){
                $CompanyStatus->edit($id, $content);
            }else{
                $CompanyStatus->add($content);
            }
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_delete($r, $id){
        Flight::validateEditorHasLogin();

        if(is_numeric($id)){
            $CompanyStatus = new CompanyStatus;
            $CompanyStatus->delete($id);
        }
        Flight::redirect($r->referrer);
    }

    public static function Action_setstatus($r){
        Flight::validateEditorHasLogin();

        $content    = $r->data->content;
        if($content){
            $CompanyStatus  = new CompanyStatus;
            $CompanyStatus->setStatus($content);
        }else{
            $message    = "设置失败";
        }

        if($message){
            $result     = array('valid'=>false, 'message'=>$message);
        }else{
            $result     = array('valid'=>true);
        }
        Flight::json($result);
    }

}
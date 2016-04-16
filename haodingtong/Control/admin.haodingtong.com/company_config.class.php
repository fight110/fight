<?php

class Control_company_config {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();
        $Company    = new Company;
        $result['company']  = $Company->getData();
        Flight::display('company_config/index.html', $result);
    }

    public static function Action_put($r){
        Flight::validateEditorHasLogin();
        $Company    = new Company;
        foreach($r->data as $key => $val){
            $Company->$key  = $val;
        }
        SESSION::message("保存成功");
        Flight::redirect($r->referrer);
    }

    public static function Action_setshow($r){
        Flight::validateEditorHasLogin();
        $Company    = new Company;
        $Company->show_id   = $r->data->show_id;
        Flight::json(array('valid'=>true));
    }

    public static function Action_get_classes($r){
        $Factory        =   new ProductsAttributeFactory('classes');
        $result['list'] =   $Factory->getAllList();
        Flight::json($result);
    }

}
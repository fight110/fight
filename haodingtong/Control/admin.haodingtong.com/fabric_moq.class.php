<?php

class Control_fabric_moq {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $Factory    = new ProductsAttributeFactory('fabric');
        $list       = $Factory->getAllList();
        $result['list'] = $list;

        $id     = $r->query->id;
        if(!$id){
            $id     = $list[0]['keyword_id'];
        }
        if($id){
            $Product    = new Product;
            $moqlist    = $Product->getFabricColorList($id);
            $result['moqlist'] = $moqlist;
        }else{
            Flight::redirect("/keyword/?t=fabric");exit;
        }

        $result['id']   = $id;

        Flight::display('fabric_moq/index.html', $result);
    }

    public static function Action_edit($r){
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $fabric_id  = $data->fabric_id;
        $color_id   = $data->color_id;
        $val        = $data->val;
        if($fabric_id && $color_id){
            $FabricMoq  = new FabricMoq;
            $FabricMoq->setMoq($fabric_id, $color_id, $val);
            $result = array('error'=>0);
        }else{
            $result = array('error'=>1, 'errmsg'=>'参数错误');
        }
        Flight::json($result);
    }


}
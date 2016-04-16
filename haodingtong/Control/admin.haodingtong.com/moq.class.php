<?php

class Control_moq {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $Factory    = new ProductsAttributeFactory('user_level');
        $list       = $Factory->getAllList();
        $result['list'] = $list;

        $id     = $r->query->id;
        if(!$id){
            $id     = $list[0]['keyword_id'];
        }
        if($id){
            $Moq    = new Moq;
            $moqlist    = $Moq->find("keyword_id={$id}", array("limit"=>1000));
            $moqlist    = Flight::listFetch($moqlist, 'product', 'product_id', 'id');
            $result['moqlist']  = $moqlist;
        }else{
            Flight::redirect("/keyword/?t=user_level");exit;
        }

        $result['id']   = $id;

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();

        Flight::display('moq/index.html', $result);
    }


    public static function Action_product_list($r){
        Flight::validateEditorHasLogin();

        $q          = trim($r->data->q);
        $filter     = $r->data->filter;
        $condition  = array();
        if($q){
            $qt     = addslashes($q);
            $condition[]    = "(name LIKE '%{$qt}%' or bianhao='{$qt}')";
        }
        if($filter){
            $condition[]    = "id not in ($filter)";
        }
        $where      = implode(' AND ', $condition);
        $Product    = new Product;
        $list       = $Product->find($where, array("limit"=>20));
        $result['list'] = $list;

        Flight::display("moq/product_list.html", $result);
    }

    public static function Action_set($r){
        Flight::validateEditorHasLogin();

        $Moq    = new Moq;
        $mid    = $Moq->create_moq($r->data);

        if($mid){
            $result     = array('valid' => true);
        }else{
            $result     = array('valid' => false, 'message' => '创建失败');
        }
        Flight::json($result);
    }

    public static function Action_delete($r, $id){
        Flight::validateEditorHasLogin();

        if(is_numeric($id)){
            $Moq    = new Moq;
            $Moq->delete("id={$id}");
            SESSION::message("删除成功");
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_batch($r){
        Flight::validateEditorHasLogin();

        $data   = $r->query;
        $batch_add  = $data->batch_add;
        $batch_del  = $data->batch_del;
        $minimum    = $data->minimum;
        $keyword_id = $data->keyword_id;
        $q          = $data->q;
        $keys   = array('category_id', 'style_id', 'series_id', 'wave_id');
        $condition  = array();
        $options    = array();
        $options['limit']   = 10000;
        $options['fields']  = 'id';
        foreach($keys as $key){
            if($val = $data->$key){
                $condition[]    = "{$key}=" . $val;
            }
        }
        if($q){
            $condition[]    = "(bianhao='{$q}' or name LIKE '%{$q}%')";
        }
        $where  = implode(' AND ', $condition);
        $Product    = new Product;
        $Moq        = new Moq;
        $list   = $Product->find($where, $options);

        if($keyword_id && $batch_add){
            foreach($list as $row){
                $Moq->create_moq(array('keyword_id'=>$keyword_id, 'minimum'=>$minimum, 'product_id'=>$row['id']));
            }
        }

        if($keyword_id && $batch_del){
            $ids    = array();
            foreach($list as $row){
                $ids[]  = $row['id'];
            }
            $ids_string = implode(',', $ids);
            if($ids_string){
                $Moq->delete("keyword_id={$keyword_id} AND product_id in ($ids_string)");
            }
        }

        Flight::redirect($r->referrer);
    }


}
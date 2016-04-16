<?php

class Control_index {
    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build_iphone();

        $User       = new User;
        $OrderList  = new OrderList;
        $Product    = new Product;
        $result['user']     = $User->getAttribute();

        if($User->type == 1){
            list($rank, $orderinfo) = $OrderList->getRank($User->id);
            $result['orderinfo']    = $orderinfo;

            $delete_num     = $Product->getCount("status=0");

            $result['ProductInfo']['delete_num']    = $delete_num;
            $result['orderinfo']['order_delete_num']    = $Product->getMydeleteNum($User->id);
            $result['ProductInfo']['unorder_num']   = $result['ProductInfo']['total']  - $orderinfo['pnum'] - $result['orderinfo']['order_delete_num'];
            

            $Factory    = new ProductsAttributeFactory('style');
            $result['style_list']       = $Factory->getAllList();
            $Factory    = new ProductsAttributeFactory('series');
            $result['series_list']      = $Factory->getAllList();
            $Factory    = new ProductsAttributeFactory('wave');
            $result['wave_list']        = $Factory->getAllList();
            $Factory    = new ProductsAttributeFactory('category');
            $result['category_list']    = $Factory->getAllList();
        }

        

        Flight::display("index.html", $result);
    }

    public static function Action_product($r){
        Flight::validateUserHasLogin();

        $q              = $r->query->q;
        $result['q']    = $q;
        if($q){
            $Product    = new Product;
            $where      = "bianhao={$q}";
            $list       = $Product->find($where, array());
            if(count($list) == 1){
                $product_id     = $list[0]['id'];
                Flight::redirect("/index/detail/{$product_id}");
                return;
            }
        }

        $User       = new User;
        $Keywords   = new Keywords;
        $OrderList  = new OrderList;
        $result['user']     = $User->getAttribute();
        list($rank, $orderinfo) = $OrderList->getRank($User->id);
        $result['orderinfo']    = $orderinfo;

        $keys   = array('ordered', 'style_id', 'category_id', 'series_id', 'classes_id', 'wave_id', 'q');
        foreach($keys as $key){
            $result[$key]   = $r->query->$key;
            if($r->query->$key){
                if($key == "ordered"){
                    switch ($r->query->$key){
                        case 'on'   :
                            $name   = "已订款";
                            break;
                        case 'off'  :
                            $name   = "未订款";
                            break;
                        case 'unactive' :
                            $name   = "删除款";
                            break;
                        default : 1;
                    }
                }elseif($key == "q"){
                    $name   = $r->query->q;
                }else{
                    $name   = $Keywords->getName_File($r->query->$key);
                }
                $result['name'] = $name;
            }
        }

        Flight::display("index/product.html", $result);
    }

    public static function Action_group($r){
        Flight::validateUserHasLogin();

        $User       = new User;
        $Keywords   = new Keywords;
        $OrderList  = new OrderList;
        $result['user']     = $User->getAttribute();
        list($rank, $orderinfo) = $OrderList->getRank($User->id);
        $result['orderinfo']    = $orderinfo;

        Flight::display("index/group.html", $result);
    }

    public static function Action_gdetail($r, $id){
        Flight::validateUserHasLogin();

        $ProductGroup       = new ProductGroup($id);
        $ProductGroupImage  = new ProductGroupImage;
        if($ProductGroup->id){
            $result['group']    = $ProductGroup->getAttribute();
            $result['imagelist']    = $ProductGroupImage->find("group_id={$id}", array("limit"=>100));
        }

        Flight::display("index/gdetail.html", $result);
    }

    public static function Action_detail($r, $id){
        Flight::validateUserHasLogin();

        $Product    = new Product($id);
        if($Product->id){
            $result['product']  = $Product->getAttribute();

            $Keywords   = new Keywords;
            $keys   = array("style", "category", "classes", "series", "price_band", "wave");
            foreach($keys as $key){
                $result['product'][$key]    = $Keywords->getName_File($result['product']["{$key}_id"]);
            }


            $ProductColor   = new ProductColor;
            $color_list     = $ProductColor->find("product_id={$id}", array());
            foreach($color_list as &$color_row){
                $color_row['name']  = $Keywords->getName_File($color_row['color_id']);
            }
            $color_list   = ProductsAttributeFactory::fetch($color_list, 'color', "color_id", "products_color");
            usort($color_list, function($a, $b){
                return $a['products_color']['rank'] > $b['products_color']['rank'] ? 1 : -1;
            });
            $result['color_list']   = $color_list;

            $ProductSize    = new ProductSize;
            $size_list      = $ProductSize->find("product_id={$id}", array());
            foreach($size_list as &$size_row){
                $size_row['name']   = $Keywords->getName_File($size_row['size_id']);
            }
            $size_list   = ProductsAttributeFactory::fetch($size_list, 'size', "size_id", "products_size");
            usort($size_list, function($a, $b){
                return $a['products_size']['rank'] > $b['products_size']['rank'] ? 1 : -1;
            });
            $result['size_list']    = $size_list;

            $ProductComment         = new ProductComment;
            $result['scoreinfo']    = $ProductComment->getAvgScore($id);

            $ProductImage           = new ProductImage;
            $result['imagelist']    = $ProductImage->find("product_id={$id}", array());

            $OrderList      = new OrderList;
            $User           = new User;
            $condition      = array();
            $options        = array();
            $options['key']     = "product_id";
            $options['status']  = false;
            $options['fields_more'] = "o.product_id";
            $condition['product_id']    = $id;
            $order_all  = $OrderList->getOrderAnalysisList($condition, $options);
            $result['order_all']    = $order_all[$id];
            $condition['user_id']   = $User->id;
            $order_user = $OrderList->getOrderAnalysisList($condition, $options);
            $result['order_user']   = $order_user[$id];
            $order_user_num     = $result['order_user']['num'];
            if($order_user_num){
                $result['rank']     = $OrderList->getOrderProductRank($id, $order_user_num);
            }

            if($User->user_level){
                $Moq    = new Moq;
                $moq    = $Moq->findone("product_id={$id} AND keyword_id={$User->user_level}");
                $result['moq']  = $moq;
            }

            if(isset($r->query->is_show)){
                $result['control']  = 'show';
                $result['show_id']  = $r->query->show_id;
                $result['is_show']  = $r->query->is_show;
            }
        }

        Flight::display("index/detail.html", $result);
    }

    public static function Action_show($r){
        Flight::validateUserHasLogin();

        $Company    = new Company;
        $show_id    = $Company->show_id;
        $is_show    = "on";
        if(!$show_id){
            $Product = new Product;
            $product = $Product->findone("status=1", array("order"=>"bianhao"));
            $show_id = $product['id'];
            $is_show = "off";
        }
        if($show_id){
            Flight::redirect("/index/detail/{$show_id}?is_show={$is_show}&show_id={$show_id}");
        }else{
            Flight::redirect("/index/product");
        }
    }

    public static function Action_get_show_id($r){
        Flight::validateUserHasLogin();
        $Company    = new Company;
        $result['show_id']  = $Company->show_id;
        Flight::json($result);
    }


}
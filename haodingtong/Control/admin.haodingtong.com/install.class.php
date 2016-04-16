<?php

define("DIR_HDT_INSTALL", 			DOCUMENT_ROOT. "tmpl/");
define("FILE_HDT_TABLES_SQL", 		'haodingtong.tables.sql');
define("FILE_HDT_DATA_SQL",         'haodingtong.data.sql');
define("FILE_HDT_IMPORT_INI",       'import.ini');
define("FILE_HDT_PRODUCT_DETAIL", 	'product.xls');
define("FILE_HDT_PRODUCT_GROUP",  	'product.group.xls');
define("FILE_HDT_PRODUCT_DISPLAY", 	'product.display.xls');
define("FILE_HDT_PRODUCT_DISPLAY_NEW",  'product.display.new.xls');
define("FILE_HDT_PRODUCT_MOQ", 		'product.moq.xls');
define("FILE_HDT_USER_DEALER", 		'user.xls');
define("FILE_HDT_USER_ZONGDAI", 	'user.zongdai.xls');
define("FILE_HDT_USER_RULE",        'user.rule.xls');
define("FILE_HDT_USER_DISCOUNT",    'user.discount.xls');
define("FILE_HDT_USER_TARGET",      'user.target.xls');

class Control_install {
    public static function Action_index($r){
    	$filenames 	= array(
    		FILE_HDT_TABLES_SQL		=> array('status'=>0),
    		FILE_HDT_PRODUCT_DETAIL	=> array('status'=>0),
			FILE_HDT_PRODUCT_GROUP 	=> array('status'=>0),
			FILE_HDT_PRODUCT_DISPLAY=> array('status'=>0),
            FILE_HDT_PRODUCT_DISPLAY_NEW=> array('status'=>0),
			FILE_HDT_PRODUCT_MOQ 	=> array('status'=>0),
			FILE_HDT_USER_DEALER	=> array('status'=>0),
			FILE_HDT_USER_ZONGDAI 	=> array('status'=>0),
            FILE_HDT_USER_RULE      => array('status'=>0),
            FILE_HDT_USER_TARGET    => array('status'=>0),
            FILE_HDT_USER_DISCOUNT  => array('status'=>0)
    	);

    	if($handle = opendir(DIR_HDT_INSTALL)){
    		while(false !== ($entry = readdir($handle))){
    			if($entry != "." && $entry != ".."){
    				$filename 	= iconv("GBK", "UTF-8", $entry);
    				if(array_key_exists($filename, $filenames)){
    					$filenames[$filename]['status']	= 1;
    				}
    			}
    		}
    		$result['files']	= $filenames;
    	}
    	Flight::display("install/index.html", $result);
    }

    public static function Action_install_sql($r){
    	$filename 	= DIR_HDT_INSTALL. FILE_HDT_TABLES_SQL;
    	$result 	= array();
    	if(file_exists($filename)){
    		$dbh 	= Flight::DatabaseMaster();
            $sql1   = sprintf("DROP DATABASE %s;CREATE DATABASE %s;USE %s;", HDT_MYSQL_DBNAME, HDT_MYSQL_DBNAME, HDT_MYSQL_DBNAME);
    		$sql2 	= file_get_contents($filename);
    		$dbh->query($sql1);
            $dbh->query($sql2);
    		$result['errorinfo']	= $dbh->errorinfo();
            $filename   = DIR_HDT_INSTALL. FILE_HDT_DATA_SQL;
            if(file_exists($filename)){
                $sql3   = file_get_contents($filename);
                $dbh->query($sql3);
            }
            $file   = DOCUMENT_ROOT . 'haodingtong/Config/Data/Keywords';
            if(is_file($file)){
                unlink($file);
            }
            system("rm -rf ". DOCUMENT_ROOT . "/thumb/*");
    	}else{
    		$result['error']	= 1;
    		$result['errmsg']	= sprintf("文件[%s]不存在", FILE_HDT_TABLES_SQL);
    	}
    	Flight::json($result);
    }

    private static function get_image_filename($dir, $name, $extends){
        if(is_string($extends)){
            $extends    = explode(',', $extends);
        }

        foreach($extends as $extend){
            $filename   = "{$dir}/{$name}.{$extend}";
            if(file_exists($filename)){
                return array($filename, $extend);
            }
        }
        return false;
    }

    private static function get_image_filename_new($dir, $skc_id, $extends){
        if(is_string($extends)){
            $extends    = explode(',', $extends);
        }

        foreach($extends as $extend){
            $filename   = "{$dir}/{$skc_id}.{$extend}";
            if(file_exists(DOCUMENT_ROOT . $filename)){
                return $filename;
            }
        }
        return false;
    }


    public static function Action_install_product($r){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_PRODUCT_DETAIL);
        //0, 编号  款号  货号  款名  价格  价格带 季节  系列  大类  波段  小类  款别  设计师 主题  卖点  尺码  颜色
        //'bianhao', 'kuanhao', 'huohao', 'name', 'price', 'price_band', 'season', 'series', 'category', 'wave', 'classes', 'style', 'designer', 'theme', 'content', 'size', 'color'
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }
        $IMAGE_EXTENDS  = $ini['IMAGE_EXTEND']  ? $ini['IMAGE_EXTEND']  : 'jpg';
        $IMAGE_KUANHAO  = $ini['IMAGE_KUANHAO'];// ? $ini['IMAGE_KUANHAO'] : 'kuanhao';
        $result     = array('error'=>0);
        $Keywords   = new Keywords;
        $Product    = new Product;
        $ProductSize        = new ProductSize;
        $ProductColor       = new ProductColor;
        $ProductImage       = new ProductImage;
        $Keywords_callback  = function($k, $field=false) use ($Keywords){
            $kid    = $Keywords->getKeywordId($k);
            if($kid){
                $Factory    = new ProductsAttributeFactory($field);
                $Factory->createItemByKid($kid);
            }
            return $kid;
        };
        $Keywords_cache_get     = Cache::setCacheCallback($Keywords_callback);
        $hash = array();
        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            while($row = $Excel->nextRow()){
                $product    = array();
                $rid        = 1;
                $product['bianhao'] = $row[$rid++];
                $product['kuanhao'] = $row[$rid++];
                $product['huohao']  = $row[$rid++];
                $product['name']    = $row[$rid++];
                $product['price']   = $row[$rid++];
                $product['price_purchase']   = $row[$rid++];
                $price_band         = $row[$rid++];
                if($price_band == "默认"){
                    $price_yu       = $product['price'] % 100;
                    $price_start    = $product['price'] - $price_yu;
                    $price_end      = $price_start + 99;
                    $price_band     = "{$price_start}-{$price_end}";
                }
                $product['price_band_id']   = $Keywords_cache_get(array($price_band, 'price_band'));
                $product['season_id']       = $Keywords_cache_get(array($row[$rid++], 'season'));
                $product['series_id']       = $Keywords_cache_get(array($row[$rid++], 'series'));
                $product['category_id']     = $Keywords_cache_get(array($row[$rid++], 'category'));
                $product['wave_id']         = $Keywords_cache_get(array($row[$rid++], 'wave'));
                $product['classes_id']      = $Keywords_cache_get(array($row[$rid++], 'classes'));
                $product['style_id']        = $Keywords_cache_get(array($row[$rid++], 'style'));
                $product['designer']        = $row[$rid++];
                $product['theme_id']        = $Keywords_cache_get(array($row[$rid++], 'theme'));
                $product['brand_id']        = $Keywords_cache_get(array($row[$rid++], 'brand'));
                $product['sxz_id']          = $Keywords_cache_get(array($row[$rid++], 'sxz'));
                $product['nannvzhuan_id']   = $Keywords_cache_get(array($row[$rid++], 'nannvzhuan'));
                $product['neiwaida_id']     = $Keywords_cache_get(array($row[$rid++], 'neiwaida'));
                $product['changduankuan_id']    = $Keywords_cache_get(array($row[$rid++], 'changduankuan'));
                // $product['fabric_id']    = $Keywords_cache_get(array($row[$rid++], 'fabric'));
                $product['content']         = $row[$rid++];
                $product['date_market']     = $row[$rid++];
                $product['proportion_list'] = $row[$rid++];
                if($product['proportion_list']){
                    $product['is_proportion']   = 1;
                }
                $pinfo = $hash[$product['kuanhao']];
                $sizestring = $row[$rid++];
                if(!$pinfo){
                	$product_id     = $Product->create($product)->insert();
                	$pinfo = $hash[$product['kuanhao']] = array('product_id'=>$product_id);
                	$size_list  = explode(';', $sizestring);
                    foreach($size_list as $size){
                        $size   = trim($size);
                        if($size){
                            $size_id    = $Keywords_cache_get(array($size, 'size'));
                            $ProductSize->create_size($product_id, $size_id);
                        }
                    }
                    if($IMAGE_KUANHAO){
                        $image_name_format  = $product[$IMAGE_KUANHAO];
                        if($image_path  = STATIC::get_image_filename_new("tmpl/images/chanpin", $image_name_format, $IMAGE_EXTENDS)){
                            $ProductImage->create_image($product_id, $image_path);
                            if(!$pinfo['defaultimage']){
                                $Product->update(array("defaultimage"=>$image_path), "id={$product_id}");
                                $pinfo['defaultimage'] = $image_path;
                            }
                        }
                        $nn = 0;
                        while($nn++ < 10){
                            $image_name_format  = $product[$IMAGE_KUANHAO] . "-" . $nn;
                            if($image_path  = STATIC::get_image_filename_new("tmpl/images/chanpin", $image_name_format, $IMAGE_EXTENDS)){
                                $ProductImage->create_image($product_id, $image_path);
                                if(!$pinfo['defaultimage']){
                                    $Product->update(array("defaultimage"=>$image_path), "id={$product_id}");
                                    $pinfo['defaultimage'] = $image_path;
                                }
                            }
                        }
                    }

                }
				$product_id = $pinfo['product_id'];
                if($product_id){
                    $color = $row[$rid++];
                    $color_code_string = $row[$rid++];
                    $skc_id = $row[$rid++];
                    $color_list     = explode(";", $color);
                    $color_code_list = explode(";", $color_code_string);
                    $color_num      = 0;
                    foreach($color_list as $color_string){
                        $color_string  = trim($color_string);
                        if($color_string){
                            $color_id   = $Keywords_cache_get(array($color_string, 'color'));
                            $color_code = $color_code_list[$color_num++];
                            $ProductColor->create_color($product_id, $color_id, $skc_id, $color_code);
                        }
                    }
                    if(!$IMAGE_KUANHAO){
                        $image_name_format  = $skc_id;
                        if($image_path  = STATIC::get_image_filename_new("tmpl/images/chanpin", $image_name_format, $IMAGE_EXTENDS)){
                            $ProductImage->create_image($product_id, $image_path);
                            if(!$pinfo['defaultimage']){
                                $Product->update(array("defaultimage"=>$image_path), "id={$product_id}");
                                $pinfo['defaultimage'] = $image_path;
                            }
                        }
                    }
                }
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_PRODUCT_DETAIL);
        }
        Flight::json($result);
    }

    public static function Action_install_product_group($r){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_PRODUCT_GROUP);
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }
        $IMAGE_EXTENDS  = $ini['IMAGE_EXTEND']  ? $ini['IMAGE_EXTEND']  : 'jpg';
        $GROUP_KUANHAO  = $ini['GROUP_KUANHAO'];// ? $ini['GROUP_KUANHAO'] : 'kuanhao';
        $result     = array('error'=>0);
        $Product        = new Product;
        $ProductGroup   = new ProductGroup;
        $ProductColor 	= new ProductColor;
        $ProductGroupMember     = new ProductGroupMember;
        $ProductGroupImage      = new ProductGroupImage;
        $Product_cache_get      = Cache::setCacheCallback(function($kuanhao) use ($Product, $GROUP_KUANHAO){
            $product    = $Product->findone("{$GROUP_KUANHAO}='{$kuanhao}'");
            return $product['id'];
        });

        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            $hash = array();
            while($row = $Excel->nextRow()){
                $rid                = 1;
                $group_dp_num       = $row[$rid++];
                $group_name         = $row[$rid++];
                if(!$group_name) $group_name = "搭配" . $group_name;
                $ginfo = $hash[$group_dp_num];
                if(!$ginfo){
                	$group_id           = $ProductGroup->create_group($group_dp_num, $group_name, '');
                	$ginfo = $hash[$group_dp_num] = array('group_id' => $group_id);
                    if($image_path  = STATIC::get_image_filename_new("tmpl/images/dapei", $group_dp_num, $IMAGE_EXTENDS)){
                        $ProductGroupImage->create_image($group_id, $image_path);
                        $ProductGroup->update(array("defaultimage"=>$image_path), "id={$group_id}");
                    }
                }
                $group_id = $ginfo['group_id'];
                if($group_id){
                    while($skc_id = $row[$rid++]){
                        $skc_id_list    = explode(";", $skc_id);
                        foreach($skc_id_list as $SKC_ID) {
                            if($SKC_ID){
                                if($GROUP_KUANHAO){
                                    $product_id = $Product_cache_get($SKC_ID);
                                    $color_id   = 0;
                                }else{
                                    $pcinfo = $ProductColor->get_by_skc_id($SKC_ID);
                                    $product_id = $pcinfo['product_id'];
                                    $color_id   = $pcinfo['color_id'];
                                }
                                $ProductGroupMember->create_member($group_id, $product_id, $color_id);
                            }
                        }
                    }

                }
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_PRODUCT_GROUP);
        }
        Flight::json($result);
    }

    public static function Action_install_product_display($r){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_PRODUCT_DISPLAY);
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }
        $IMAGE_EXTENDS      = $ini['IMAGE_EXTEND']  ? $ini['IMAGE_EXTEND']  : 'jpg';
        $DISPLAY_KUANHAO    = $ini['DISPLAY_KUANHAO'];//   ? $ini['DISPLAY_KUANHAO']   : 'bianhao';
        $result     = array('error'=>0);
        $Product        = new Product;
        $ProductColor   = new ProductColor;
        $Keywords       = new Keywords;
        $ProductDisplay             = new ProductDisplay;
        $ProductDisplayMember       = new ProductDisplayMember;
        $ProductDisplayMemberColor  = new ProductDisplayMemberColor;
        $ProductDisplayImage        = new ProductDisplayImage;
        $ProductGroup               = new ProductGroup;
        $ProductGroupMenber         = new ProductGroupMember();
        $Product_cache_get          = Cache::setCacheCallback(function($bianhao) use ($Product, $DISPLAY_KUANHAO){
            $product    = $Product->findone("{$DISPLAY_KUANHAO}='{$bianhao}'");
            return $product['id'];
        });
        $Keywords_cache_get         = Cache::setCacheCallback(function($k) use ($Keywords){
            return $Keywords->getKeywordId($k);
        });

        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            $hash = array();
            while($row = $Excel->nextRow()){
                $rid                = 1;
                $Display_dp_num       = $row[$rid++];
                $Display_name         = $row[$rid++];
                $skc_id               = $row[$rid++];
                if(!$Display_name) $Display_name = "陈列" . $Display_dp_num;
                $dinfo = $hash[$Display_dp_num];
                if(!$dinfo){
                	$Display_id           = $ProductDisplay->create_Display($Display_dp_num, $Display_name, '');
                	$dinfo = $hash[$Display_dp_num] = array('Display_id' => $Display_id);
                    if($image_path  = STATIC::get_image_filename_new("tmpl/images/chenlie", $Display_dp_num, $IMAGE_EXTENDS)){
                        $ProductDisplayImage->create_image($Display_id, $image_path);
                        $ProductDisplay->update(array("defaultimage"=>$image_path), "id={$Display_id}");
                    }
                }
                $Display_id = $dinfo['Display_id'];
                if($Display_id){
                    $skc_id_list = explode(";", $skc_id);
                    $skc_id_list = array_unique($skc_id_list);
                    foreach($skc_id_list as $SKC_ID) {
                        if($DISPLAY_KUANHAO){
                            $product_id = $Product_cache_get($SKC_ID);
                            $ProductDisplayMember->create_member($Display_id, $product_id);
                            $color_list = $ProductColor->get_color_list($product_id);
                            foreach($color_list as $color){
                                $color_id = $color['color_id'];
                                $ProductDisplayMemberColor->create_color($Display_id, $product_id, $color_id);
                            }
                        }else{
                            // $group = $ProductGroup->findone("dp_num={$SKC_ID}");
                            // if($group['id']){
                            //     $members = $ProductGroupMenber->getGroupMember($group['id']);
                            //     foreach($members as $member){
                            //         $product_id = $member['product_id'];
                            //         $color_id   = $member['color_id'];
                            //         $ProductDisplayMember->create_member($Display_id, $product_id);
                            //         $ProductDisplayMemberColor->create_color($Display_id, $product_id, $color_id);
                            //     }
                            // }
                            // continue;
                            $pcinfo = $ProductColor->get_by_skc_id($SKC_ID);
                            $product_id = $pcinfo['product_id'];
                            $color_id   = $pcinfo['color_id'];
                            if($product_id){
                                $ProductDisplayMember->create_member($Display_id, $product_id);
                                $ProductDisplayMemberColor->create_color($Display_id, $product_id, $color_id);
                            }

                        }
                    }
                }
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_PRODUCT_DISPLAY);
        }
        Flight::json($result);
    }

    public static function Action_install_product_display_new($r){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_PRODUCT_DISPLAY_NEW);
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }
        $result     = array('error'=>0);
        $Keywords   = new Keywords;
        $Product    = new Product;
        $ProductGroup   = new ProductGroup;
        $ProductDisplay  = new ProductDisplay;
        $ProductDisplayImage = new ProductDisplayImage;
        $GroupToDisplay     = new GroupToDisplay;
        $Keywords_cache_get     = Cache::setCacheCallback(function($k, $field=false) use ($Keywords){
            $kid    = $Keywords->getKeywordId($k);
            if($kid && $field){
                $Factory    = new ProductsAttributeFactory($field);
                $Factory->createItemByKid($kid);
            }
            return $kid;
        });
        $Product_cache_get          = Cache::setCacheCallback(function($bianhao) use ($Product, $MOQ_KUANHAO){
            $product    = $Product->findone("{$MOQ_KUANHAO}='{$bianhao}'");
            return $product['id'];
        });
        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            while($row = $Excel->nextRow()){
                if(!is_numeric($row[1])){
                    continue;
                }
                $rid            = 1;
                $Display_dp_num       = $row[$rid++];
                $Display_name         = $row[$rid++];
                if(!$Display_name) $Display_name = "陈列" . $Display_dp_num;
                $dinfo = $hash[$Display_dp_num];
                if(!$dinfo){
                    $Display           = $ProductDisplay->findone("bianhao={$Display_dp_num}");
                    $Display_id        = $Display['id'];
                }
                while($Display_id && $dp_num_string = $row[$rid++]){
                    $dp_num_list = explode(";", $dp_num_string);
                    foreach($dp_num_list as $dp_num){
                        if($dp_num){
                            $group   = $ProductGroup->findone("dp_num='{$dp_num}'");
                            if($group['id']){
                                $GroupToDisplay->createGroupToDisplay($group['id'], $Display_id);
                            }
                        }
                    }
                }
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_PRODUCT_DISPLAY_NEW);
        }
        Flight::json($result);
    }

    public static function Action_install_product_moq($r){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_PRODUCT_MOQ);
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }
        $MOQ_KUANHAO    = $ini['MOQ_KUANHAO']  ? $ini['MOQ_KUANHAO']  : 'bianhao';
        $result     = array('error'=>0);
        $Keywords   = new Keywords;
        $Product    = new Product;
        $Moq        = new Moq;
        $Keywords_cache_get     = Cache::setCacheCallback(function($k, $field=false) use ($Keywords){
            $kid    = $Keywords->getKeywordId($k);
            if($kid && $field){
                $Factory    = new ProductsAttributeFactory($field);
                $Factory->createItemByKid($kid);
            }
            return $kid;
        });
        $Product_cache_get          = Cache::setCacheCallback(function($bianhao) use ($Product, $MOQ_KUANHAO){
            $product    = $Product->findone("{$MOQ_KUANHAO}='{$bianhao}'");
            return $product['id'];
        });
        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            while($row = $Excel->nextRow()){
                if(!is_numeric($row[1])){
                    continue;
                }
                $rid            = 1;
                $keyword_id     = $Keywords_cache_get(array($row[$rid++], 'user_level'));
                $product_bianhaos   = explode(';', $row[$rid++]);
                $minimum        = $row[$rid++];
                foreach($product_bianhaos as $product_bianhao){
                    $product_id     = $Product_cache_get(array($product_bianhao));
                    $Moq->create_moq(array('keyword_id'=>$keyword_id, 'product_id'=>$product_id, 'minimum'=>$minimum));
                }
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_USER_DEALER);
        }
        Flight::json($result);
    }

    public static function Action_install_user_dealer($r){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_USER_DEALER);
        $result     = array('error'=>0);
        $User       = new User;
        $Keywords   = new Keywords;
        $Location   = new Location;
        $Keywords_callback  = function($k, $field=false) use ($Keywords){
            $kid    = $Keywords->getKeywordId($k);
            if($kid){
                $Factory    = new ProductsAttributeFactory($field);
                $Factory->createItemByKid($kid);
            }
            return $kid;
        };
        $Keywords_cache_get     = Cache::setCacheCallback($Keywords_callback);
        $Location_cache_get     = Cache::setCacheCallback(function($pid, $area) use ($Location){
            $node   = $Location->addNode($pid, $area);
            return $node->id;
        });

        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            while($row = $Excel->nextRow()){
                if(!is_numeric($row[1])){
                    continue;
                }
                $rid            = 1;
                $user           = array();
                $user['type']       = 1;
                $user['username']   = $row[$rid++];
                $user['password']   = $row[$rid++];
                $user['name']       = $row[$rid++];
                $user['exp_price']  = $row[$rid++];
                $user['exp_num']    = $row[$rid++];
                $user['user_level'] = $Keywords_cache_get(array($row[$rid++], 'user_level'));
                $user['area1']      = $Location_cache_get(array(0, $row[$rid++]));
                $user['area2']      = $Location_cache_get(array($user['area1'], $row[$rid++]));
                $discount   = $row[$rid++];
                if($discount>1){
                    $user['discount']   = $discount / 100;
                }elseif(!$discount){
                    $user['discount']   = 1;
                }else{
                    $user['discount']   = $discount;
                }
                $discount_type  = $row[$rid++];
                $user['discount_type']  = $discount_type ? 1 : 0;
                $brands = $row[$rid++];
                if($brands){
                    $brand_list = explode(";", $brands);
                    $brand_id_list = array();
                    foreach($brand_list as $brand){
                        $brand_id = $Keywords_callback($brand, 'brand');
                        $brand_id_list[]    = $brand_id;
                    }
                    $permission_brand = implode(",", $brand_id_list);
                    $user['permission_brand'] = $permission_brand;
                }
                $User->create_user($user);
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_USER_DEALER);
        }
        Flight::json($result);
    }

    public static function Action_install_user_zongdai($r){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_USER_ZONGDAI);
        $result     = array('error'=>0);
        $User       = new User;
        $UserSlave  = new UserSlave;
        $Keywords   = new Keywords;
        $Location   = new Location;
        $Keywords_callback  = function($k, $field=false) use ($Keywords){
            $kid    = $Keywords->getKeywordId($k);
            if($kid){
                $Factory    = new ProductsAttributeFactory($field);
                $Factory->createItemByKid($kid);
            }
            return $kid;
        };
        $Keywords_cache_get     = Cache::setCacheCallback($Keywords_callback);
        $Location_cache_get     = Cache::setCacheCallback(function($pid, $area) use ($Location){
            $id     = $Location->getIdByName($area);
            if(!$id){
                $node   = $Location->addNode($pid, $area);
                $id     = $node->id;
            }
            return $id;
        });
        $User_cahce_get         = Cache::setCacheCallback(function($username) use ($User){
            $user   = $User->findone("username='". addslashes($username) ."'");
            return $user['id'];
        });

        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            while($row = $Excel->nextRow()){
                if(!is_numeric($row[1])){
                    continue;
                }
                $rid            = 1;
                $user           = array();
                $user['type']       = 2;
                $user['username']   = $row[$rid++];
                $user['password']   = $row[$rid++];
                $user['name']       = $row[$rid++];
                $user['area1']      = $Location_cache_get(array(0, $row[$rid++]));
                $user['area2']      = $Location_cache_get(array($user['area1'], $row[$rid++]));
                $discount_type      = $row[$rid++];
                $user['discount_type']       = $discount_type ? 1 : 0;
                $u = $User->create_user($user);
                $user_id    = $u['id'];
                if($user_id){
                    $slave_uids = $row[$rid++];
                    $main_username  = $row[$rid++];
                    if($slave_uids){
                        $slave_list     = explode(';', $slave_uids);
                        foreach($slave_list as $slave_username){
                            $user_slave_id  = $User_cahce_get(array($slave_username));
                            $UserSlave->create_slave($user_id, $user_slave_id);
                        }
                    }
                    if($main_username){
                        $ret = $User->update(array('mid'=>$user_id), "username='{$main_username}'");
                        if(!$ret){
                            $result['error']    = 1;
                            $result['errmsg']   .= "<br>总代{$user['username']}绑定帐号{$main_username}找不到";
                        }
                    }else{
                        $result['error']    = 1;
                        $result['errmsg']   .= "<br>总代{$user['username']}绑定帐号不能为空";
                    }
                }
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_USER_ZONGDAI);
        }
        Flight::json($result);
    }

    public static function Action_output($r){
        $Product    = new Product;
        $Keywords   = new Keywords;
        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $Keywords_cache_get     = Cache::setCacheCallback(function($kid) use ($Keywords){
            return $Keywords->getKeywordName($kid);
        });
        $list       = $Product->find("1", array("order"=>"bianhao asc", "limit"=>1000));
        foreach($list as &$row){
            $row['color_list']  = $ProductColor->get_color_list($row['id']);
            $row['size_list']   = $ProductSize->get_size_list($row['id']);
        }
        $result['list'] = $list;

        // $excel_name     = "product.xls";
        // $Response   = Flight::response();
        // $Response->header("Content-type","application/vnd.ms-excel");
        // $Response->header("Content-Disposition", "attachment; filename={$excel_name}");
        Flight::display("install/product.detail.tpl", $result);
    }


    public static function Action_install_rule($r){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_USER_RULE);
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }

        if(file_exists($file_excel)){
            $User   = new User;
            $Rule   = new Rule;
            $Keywords   = new Keywords;
            $OrderListHistory   = new OrderListHistory;
            $Excel  = new ExcelReader($file_excel);
            $hash   = array();
            $num_row    = 0;
            $history_name   = array();
            while($row = $Excel->nextRow()){
                $num_row++;
                $rid            = 1;
                $name           = $row[$rid++];
                $series         = $row[$rid++];
                $category       = $row[$rid++];
                $classes        = $row[$rid++];
                $wave           = $row[$rid++];
                $kuanhao        = $row[$rid++];
                $price_band     = $row[$rid++];
                $nannvzhuan     = $row[$rid++];
                $season         = $row[$rid++];
                $color          = $row[$rid++];
                $num            = $row[$rid++];
                $price          = $row[$rid++];
                if(!$history_name[$name]){
                    $ret_history = $OrderListHistory->createOrderListHistory($name, $series, $category, $classes, $wave, $kuanhao, $price_band, $price, $color, $num, $nannvzhuan, $season);
                    if($ret_history === false){
                        $result['error']    = 1;
                        $result['errmsg']   .= sprintf("<br>\n历史数据用户名%s不存在, 第%d行", $name, $num_row);
                        $history_name[$name]    = 1;
                    }
                }
            }
            // $fields = array('series', 'category', 'classes', 'wave', 'price_band', 'color');
            // foreach($hash as $name => $data){
            //     $u  = $User->findone("name='".addslashes($name)."'");
            //     $user_id = $u['id'];
            //     if($user_id){
            //         foreach($fields as $field){
            //             $data_field     = $data[$field];
            //             $mydata         = array();
            //             foreach($data_field as $kname => $num){
            //                 $kid    = $Keywords->getKeywordId($kname);
            //                 $mydata[$kid]   = $num;
            //             }
            //             $Rule->createUserRule($user_id, "{$name}{$field}指引", $field, $mydata);
            //         }
            //     }else{
            //         $result['error']    = 1;
            //         $result['errmsg']   .= sprintf("<br>\n用户名%s不存在", $name);
            //     }
            // }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_USER_RULE);
        }
        Flight::json($result);
    }

    public static function Action_qsdata($r){
    		$file_excel = DIR_HDT_INSTALL . 'qsdata.xls';
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }

        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            $hash   = array();
            $Budget = new Budget;
            $User 	= new User;
            $Keyword = new Keywords;
            $Rule 	= new Rule;
            $ruledata = array();
            $lastuname = '';
            $lastuid = 0;
            while($row = $Excel->nextRow()){
            	$name = $row[1];
            	$category = $row[3] . $row[2];
            	$data1 = $row[4];
            	$data2 = $row[5];
            	if($lastuname && $lastuname != $name){
            		$Rule->createUserRule($lastuid, "{$name}rule", 'category', $ruledata);
            		$ruledata = array();
            	}
            	$user  = $User->findone("name='$name'");
            	$user_id = $user['id'];
            	$keyword_id = $Keyword->getKeywordId($category);
            	$Budget->addBudget($user_id, 'category', $keyword_id, $data2 * 100);

            	$ruledata[$keyword_id] = $data1 * 100;
            	$lastuid = $user_id;
            	$lastuname = $name;
            }
          }
    }

    public static function Action_install_user_exp($r){
        $file_excel = DIR_HDT_INSTALL . 'user.exp.xls';
        $result     = array('error'=>0);
        $User       = new User;
        $Keywords   = new Keywords;
        $Keywords_callback  = function($k, $field=false) use ($Keywords){
            $kid    = $Keywords->getKeywordId($k);
            if($kid){
                $Factory    = new ProductsAttributeFactory($field);
                $Factory->createItemByKid($kid);
            }
            return $kid;
        };
        $Keywords_cache_get     = Cache::setCacheCallback($Keywords_callback);

        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            $UserExpComplete    = new UserExpComplete;
            while($row = $Excel->nextRow()){
                $rid            = 1;
                $name           = trim($row[$rid++]);
                $category       = trim($row[$rid++]);
                $pnum           = trim($row[$rid++]);
                $skc            = trim($row[$rid++]);
                $num            = trim($row[$rid++]);
                $price          = trim($row[$rid++]);
                $field          = trim($row[$rid++]);
                if(!$field) $field = 'category';
                $user           = $User->findone("name='{$name}'");
                $user_id        = $user['id'];
                $keyword_id     = $Keywords_cache_get($category);
                if($price){
                    $exp_name       = 'exp_price';
                    $UserExpComplete->set_exp($user_id, $field, $keyword_id, $exp_name, $price);
                    echo implode("\t", array($user_id, $field, $keyword_id, $exp_name, $price)), "\n";
                }
                if($pnum){
                    $exp_name       = 'exp_pnum';
                    $UserExpComplete->set_exp($user_id, $field, $keyword_id, $exp_name, $pnum);
                    echo implode("\t", array($user_id, $field, $keyword_id, $exp_name, $pnum)), "\n";
                }
                if($skc){
                    $exp_name       = 'exp_skc';
                    $UserExpComplete->set_exp($user_id, $field, $keyword_id, $exp_name, $skc);
                    echo implode("\t", array($user_id, $field, $keyword_id, $exp_name, $skc)), "\n";
                }
                if($num){
                    $exp_name       = 'exp_num';
                    $UserExpComplete->set_exp($user_id, $field, $keyword_id, $exp_name, $num);
                    echo implode("\t", array($user_id, $field, $keyword_id, $exp_name, $num)), "\n";
                }
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_USER_DEALER);
        }
        Flight::json($result);
    }


    public static function Action_install_discount($r){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_USER_DISCOUNT);
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }

        $result     = array();
        if(file_exists($file_excel)){
            $User   = new User;
            $Keywords   = new Keywords;
            $UserDiscount   = new UserDiscount;
            $Excel  = new ExcelReader($file_excel);
            $hash   = array();
            $num_row    = 0;
            $history_name   = array();
            while($row = $Excel->nextRow()){
                $num_row++;
                $rid            = 1;
                $name           = trim($row[$rid++]);
                $category       = trim($row[$rid++]);
                $discount       = trim($row[$rid++]);
                $u              = $User->findone("name='".addslashes($name)."'");
                if($u['id']){
                    $keyword_id = $Keywords->getKeywordId($category);
                    $UserDiscount->set_discount($u['id'], $keyword_id, $discount);
                    if($u['mid']){
                        $UserDiscount->set_discount($u['mid'], $keyword_id, $discount);
                    }
                }else{
                    $result['error']    = 1;
                    $result['errmsg']   .= sprintf("<br>\n用户名%s不存在, 第%d行", $name, $num_row);
                }
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_USER_RULE);
        }
        Flight::json($result);
    }

    public static function Action_install_user_target($r){
        $file_excel = DIR_HDT_INSTALL . 'user.target.xls';
        $result     = array('error'=>0);
        $User       = new User;

        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            $UserTarget     = new UserTarget;
            while($row = $Excel->nextRow()){
                $rid            = 1;
                $name           = trim($row[$rid++]);
                $category       = trim($row[$rid++]);
                $num            = trim($row[$rid++]);
                $price          = trim($row[$rid++]);
                $user           = $User->findone("name='{$name}'");
                $user_id        = $user['id'];
                if($user_id){
                    $keyword_id = Keywords::cache_get_id($category);
                    $UserTarget->create(array("user_id"=>$user_id, "other_id"=>$keyword_id, "target_num"=>$num, "target_price"=>$price))->insert();
                }
            }
        }else{
            $result['error']    = 1;
            $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_USER_DEALER);
        }
        Flight::json($result);
    }


    public function check_product_group(){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_PRODUCT_GROUP);
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }
        $IMAGE_EXTENDS  = $ini['IMAGE_EXTEND']  ? $ini['IMAGE_EXTEND']  : 'jpg';
        $GROUP_KUANHAO  = $ini['GROUP_KUANHAO'];
        $Product        = new Product;
        $ProductGroup   = new ProductGroup;
        $ProductColor 	= new ProductColor;
        $ProductGroupMember     = new ProductGroupMember;
        $ProductGroupImage      = new ProductGroupImage;
        $Product_cache_get      = Cache::setCacheCallback(function($kuanhao) use ($Product, $GROUP_KUANHAO){
            $product    = $Product->findone("{$GROUP_KUANHAO}='{$kuanhao}'");
            return $product['id'];
        });
        $error = array();
        $line_num = 0;
        $hashGroup=array();
        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            while($row = $Excel->nextRow()){
                $line_num++;
                $group_dp_num       = $row[1];
                $group_name         = $row[2];
                $skc_id         = $row[3];
    
                if(!$group_dp_num){
                    $error[]    = '第'.$line_num.'行搭配编号不存在!';
                }
                if(!$skc_id){
                    $error[]    = '第'.$line_num.'行搭配产品信息不存在!';
                }
                if($group_dp_num){
                    if(!isset($hashGroup[$group_dp_num])){
                        $hashGroup[$group_dp_num]=$line_num;
                    }else{
                        $error[] = '第'.$line_num.'行搭配编号与第'.$hashGroup[$group_dp_num].'行重复！';
                    }
                    $pginfo = $ProductGroup->findone('dp_num="'.$group_dp_num.'" ');
                    $group_id = $pginfo['id'];
                    if(sizeof($pginfo)){
                        if(!$pginfo['name']){
                            $error[]    = '第'.$line_num.'行搭配名字为空!';
                        }
                        if($skc_id){
                            $skc_id_list    = explode(";", $skc_id);
                            foreach($skc_id_list as $SKC_ID) {
                                if($SKC_ID){
                                    if($GROUP_KUANHAO){
                                        $product_id = $Product_cache_get($SKC_ID);
                                        $color_id   = 0;
                                        if(!$product_id){
                                            $error[]    = '第'.$line_num.'行搭配产品信息错误:'.$SKC_ID;
                                        }
                                    }else{
                                        $pcinfo = $ProductColor->get_by_skc_id($SKC_ID);
                                        $product_id = $pcinfo['product_id'];
                                        $color_id   = $pcinfo['color_id'];
                                        if(!$product_id||!$color_id){
                                            $error[]    = '第'.$line_num.'行搭配产品信息错误:'.$SKC_ID;
                                        }
                                    }
                                    if($product_id){
    
                                        $checkGroup = $ProductGroupMember->findone(' group_id = "'.$group_id.'" AND product_id ="'.$product_id.'" AND color_id = "'.$color_id.'" ');
                                        if(!sizeof($checkGroup)){
                                            $error[]    = '第'.$line_num.'行搭配产品信息没有导入:'.$SKC_ID;
                                        }
    
                                    }
                                    //$ProductGroupMember->create_member($group_id, $product_id, $color_id);
                                }
                            }
                        }
                    }else{
                        $error[]    = '第'.$line_num.'行搭配信息没有导入!';
                    }
                }
            }
        }else{
            $error[]    = '搭配表不存在!';
        }
        return $error;
    }
    
    public function check_product_display(){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_PRODUCT_DISPLAY);
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }
        $IMAGE_EXTENDS      = $ini['IMAGE_EXTEND']  ? $ini['IMAGE_EXTEND']  : 'jpg';
        $DISPLAY_KUANHAO    = $ini['DISPLAY_KUANHAO'];
        $Product        = new Product;
        $ProductColor   = new ProductColor;
        $Keywords       = new Keywords;
        $ProductDisplay             = new ProductDisplay;
        $ProductDisplayMember       = new ProductDisplayMember;
        $ProductDisplayMemberColor  = new ProductDisplayMemberColor;
        $ProductDisplayImage        = new ProductDisplayImage;
        $ProductGroup               = new ProductGroup;
        $ProductGroupMenber         = new ProductGroupMember();
        $Product_cache_get          = Cache::setCacheCallback(function($bianhao) use ($Product, $DISPLAY_KUANHAO){
            $product    = $Product->findone("{$DISPLAY_KUANHAO}='{$bianhao}'");
            return $product['id'];
        });
        $Keywords_cache_get         = Cache::setCacheCallback(function($k) use ($Keywords){
            return $Keywords->getKeywordId($k);
        });
        $error = array();
        $line_num = 0;
        $hashDisplay = array();
        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            while($row = $Excel->nextRow()){
                $line_num++;
                $bianhao       = $row[1];
                $display_name         = $row[2];
                $skc_id         = $row[3];
                if(!$bianhao){
                    $error[]    = '第'.$line_num.'行陈列编号不存在!';
                }
                if(!$skc_id){
                    $error[]    = '第'.$line_num.'行陈列产品信息不存在!';
                }
                if($bianhao){
                    if(!isset($hashDisplay[$bianhao])){
                        $hashDisplay[$bianhao]=$line_num;
                    }else{
                        $error[] = '第'.$line_num.'行搭配编号与第'.$hashDisplay[$bianhao].'行重复！';
                    }
                    $pginfo = $ProductDisplay->findone('bianhao="'.$bianhao.'" ');
                    $display_id = $pginfo['id'];
                    if(sizeof($pginfo)){
                        if(!$pginfo['name']){
                            $error[]    = '第'.$line_num.'行陈列名字为空!';
                        }
                        if($skc_id){
                            $skc_id_list    = explode(";", $skc_id);
                            foreach($skc_id_list as $SKC_ID) {
                                if($SKC_ID){
                                    if($DISPLAY_KUANHAO){
                                        $product_id = $Product_cache_get($SKC_ID);
                                        if(!$product_id){
                                            $error[]    = '第'.$line_num.'行陈列产品信息错误:'.$SKC_ID;
                                        }else{
                                            $color_list = $ProductColor->get_color_list($product_id);
                                            foreach($color_list as $color){
                                                $color_id = $color['color_id'];
                                                $checkDisplayColor = $ProductDisplayMemberColor->findone(' display_id = "'.$display_id.'" AND product_id ="'.$product_id.'" AND keyword_id = "'.$color_id.'" ');
                                                if(!sizeof($checkDisplayColor)){
                                                    $error[]    = '第'.$line_num.'行陈列产品信息没有导入:'.$SKC_ID;
                                                }
                                            }
                                        }
                                    }else{
                                        $pcinfo = $ProductColor->get_by_skc_id($SKC_ID);
                                        $product_id = $pcinfo['product_id'];
                                        $color_id   = $pcinfo['color_id'];
                                        if(!$product_id||!$color_id){
                                            $error[]    = '第'.$line_num.'行陈列产品信息错误:'.$SKC_ID;
                                        }else{
                                            $checkDisplayColor = $ProductDisplayMemberColor->findone(' display_id = "'.$display_id.'" AND product_id ="'.$product_id.'" AND keyword_id = "'.$color_id.'" ');
                                            if(!sizeof($checkDisplayColor)){
                                                $error[]    = '第'.$line_num.'行陈列产品信息没有导入:'.$SKC_ID;
                                            }
                                        }
                                    }
                                    if($product_id){
                                        $checkDisplay = $ProductDisplayMember->findone(' display_id = "'.$display_id.'" AND product_id ="'.$product_id.'" ');
                                        if(!sizeof($checkDisplay)){
                                            $error[]    = '第'.$line_num.'行陈列产品信息没有导入';
                                        }
    
    
                                    }
                                    //$ProductGroupMember->create_member($group_id, $product_id, $color_id);
                                }
                            }
                        }
                    }else{
                        $error[]    = '第'.$line_num.'行陈列信息没有导入!';
                    }
                }
            }
        }else{
            $error[]    = '陈列表不存在!';
        }
        return $error;
    }
    
    public function check_product(){
        $file_excel = DIR_HDT_INSTALL . iconv("UTF-8", "GBK", FILE_HDT_PRODUCT_DETAIL);
        $file_ini   = DIR_HDT_INSTALL . FILE_HDT_IMPORT_INI;
        if(file_exists($file_ini)){
            $ini    = json_decode(file_get_contents($file_ini), true);
        }
        $IMAGE_EXTENDS  = $ini['IMAGE_EXTEND']  ? $ini['IMAGE_EXTEND']  : 'jpg';
        $IMAGE_KUANHAO  = $ini['IMAGE_KUANHAO'];
        $Keywords   = new Keywords;
        $Product    = new Product;
        $ProductSize        = new ProductSize;
        $ProductColor       = new ProductColor;
        $ProductImage       = new ProductImage;
        $error = array();
        $line_num = 0;
        $hashProduct=array();
        $hashKuanhao=array();
        $hashSizeColor = array();
        $checkProduct = function($info,$field,$data,$tips,&$array=array(),$type=1,$need=1)use($Keywords){
            static $karray;
            $from = $info['info'][$field];
            if($need==1){
                if(!$from){
                    $array[] = sprintf($tips.'数据导入为空！','('.$info['info']['kuanhao'].')');
                    return ;
                }
                if(!$data){
                    $array[] = sprintf($tips.'表格信息为空！','('.$info['info']['kuanhao'].')');
                    return ;
                }
            }
            switch ($type){
                case 1:
                    $data = $data;
                    break;
                case 2:
                    if(isset($karray[$field][$data])){
                        $data = $karray[$field][$data];
                    }else{
                        $kid = $Keywords->findone('name="'.$data.'"');
                        $karray[$field][$data] = $kid['id'];
                        $data = $kid['id'];
                    }
                    break;
            }
            if($from!=$data){
                if(isset($info['rnum'])&&$info['rnum']){
                    $array[] = '第'.$info['rnum'].'行和'.'第'.$info['fnum'].'行款号重复('.$info['info']['kuanhao'].'), '.sprintf($tips.'不一致','与第'.$info['fnum'].'行');
                }else{
                    $array[] = sprintf($tips.'错误','('.$info['info']['kuanhao'].')');
                }          
                return ;
            }
        };
        if(file_exists($file_excel)){
            $Excel  = new ExcelReader($file_excel);
            while($row = $Excel->nextRow()){
                $line_num++;
                if($line_num<=1)continue;
                $bianhao = $row[1];
                $kuanhao = $row[2];
                if(!$bianhao||!$kuanhao){
                    $error[]    = '第'.$line_num.'行货品编号或款号不存在!';
                }else{
                    if(!isset($hashProduct[$bianhao])){
                        $hashProduct[$bianhao]=$line_num;
                    }else{
                        $error[] = '第'.$line_num.'行('.$kuanhao.')货品编号与第'.$hashProduct[$bianhao].'行重复！';
                    }
                    if(!isset($hashKuanhao[$kuanhao])){
                        $hashKuanhao[$kuanhao]['id'] = $bianhao;
                        $hashKuanhao[$kuanhao]['info'] = $Product->findone('bianhao ="'.$bianhao.'" ');
                        $hashKuanhao[$kuanhao]['fnum'] = $line_num;
                    }else{
                        $hashKuanhao[$kuanhao]['rnum'] = $line_num;
                    }
                    $pinfo = $hashKuanhao[$kuanhao];
    
                    if(sizeof($pinfo['info'])){
                        $checkProduct($pinfo,'kuanhao',$row[2],'第'.$line_num.'行%s款号',$error);
                        $checkProduct($pinfo,'huohao',$row[3],'第'.$line_num.'行%s货号',$error);
                        $checkProduct($pinfo,'name',$row[4],'第'.$line_num.'行%s款名',$error);
                        $checkProduct($pinfo,'price',$row[5],'第'.$line_num.'行%s价格',$error,1);
                        $checkProduct($pinfo,'price_purchase',$row[6],'第'.$line_num.'行%s买断价',$error,1,0);
                        $price_band = $row[7];
                        if($price_band == "默认"){
                            $price_yu       = $row[5] % 100;
                            $price_start    = $row[5] - $price_yu;
                            $price_end      = $price_start + 99;
                            $price_band     = "{$price_start}-{$price_end}";
                        }
                        $checkProduct($pinfo,'price_band_id',$price_band,'第'.$line_num.'行%s价格带',$error,2);
                        $checkProduct($pinfo,'season_id',$row[8],'第'.$line_num.'行%s季节',$error,2);
                        $checkProduct($pinfo,'series_id',$row[9],'第'.$line_num.'行%s系列',$error,2);
                        $checkProduct($pinfo,'category_id',$row[10],'第'.$line_num.'行%s大类',$error,2);
                        $checkProduct($pinfo,'wave_id',$row[11],'第'.$line_num.'行%s波段',$error,2);
                        $checkProduct($pinfo,'classes_id',$row[12],'第'.$line_num.'行%s小类',$error,2,0);
                        $checkProduct($pinfo,'style_id',$row[13],'第'.$line_num.'行%s风格',$error,2);
                        $checkProduct($pinfo,'designer',$row[14],'第'.$line_num.'行%s设计师',$error,1,0);
                        $checkProduct($pinfo,'theme_id',$row[15],'第'.$line_num.'行%s主题',$error,2,0);
                        $checkProduct($pinfo,'brand_id',$row[16],'第'.$line_num.'行%s品牌',$error,2,0);
                        $checkProduct($pinfo,'sxz_id',$row[17],'第'.$line_num.'行%s上下装',$error,2,0);
                        $checkProduct($pinfo,'nannvzhuan_id',$row[18],'第'.$line_num.'行%s男女装',$error,2,0);
                        $checkProduct($pinfo,'neiwaida_id',$row[19],'第'.$line_num.'行%s内外搭',$error,2,0);
                        $checkProduct($pinfo,'changduankuan_id',$row[20],'第'.$line_num.'行%s长短款',$error,2,0);
                        $checkProduct($pinfo,'content',$row[21],'第'.$line_num.'行%s商品详情',$error,1,0);
                        $checkProduct($pinfo,'date_market',$row[22],'第'.$line_num.'行%s上市时间',$error,1,0);
                        $product_id = $pinfo['info']['id'];
                        $sizestring = $row[24];
                        if(!$sizestring){
                            $error[]    = '第'.$line_num.'行('.$pinfo['info']['kuanhao'].')尺寸表格信息为空!';
                        }else{
                            $size_list  = explode(';', $sizestring);
                            foreach($size_list as $size){
                                $size   = trim($size);
                                if($size){
                                    if(!isset($hashSizeColor[$size])){
                                        $keyId = $Keywords->findone('name="'.$size.'"');
                                        if(!sizeof($keyId)){
                                            $error[]    = '第'.$line_num.'行('.$pinfo['info']['kuanhao'].')尺寸信息:'.$size.',错误!';
                                        }else{
                                            $hashSizeColor[$size] = $keyId['id'];
                                        }
                                    }
                                    if($hashSizeColor[$size]){
                                        $size_id = $hashSizeColor[$size];
                                        $check_product_size = $ProductSize->findone('product_id="'.$product_id.'" AND size_id="'.$size_id.'" ');
                                        if(!sizeof($check_product_size)){
                                            $error[]    = '第'.$line_num.'行('.$pinfo['info']['kuanhao'].')尺寸信息:'.$size.',没有导入!';
                                        }
                                    }
                                }
                            }
                        }
                        $color = $row[25];
                        $color_code_string = $row[26];
                        $skc_id = $row[27];
                        $color_list     = explode(";", $color);
                        $color_code_list = explode(";", $color_code_string);
                        $color_num      = 0;
                        if($color&&$color_code_string&&$skc_id){
                            foreach($color_list as $color_string){
                                $color_string  = trim($color_string);
                                if($color_string){
                                    if(!isset($hashSizeColor[$color_string])){
                                        $keyId = $Keywords->findone('name="'.$color_string.'"');
                                        if(!sizeof($keyId)){
                                            $error[]    = '第'.$line_num.'行('.$pinfo['info']['kuanhao'].')颜色信息:'.$color_string.',错误!';
                                        }else{
                                            $hashSizeColor[$color_string] = $keyId['id'];
                                        }
                                    }
                                    if($hashSizeColor[$color_string]){
                                        $color_id = $hashSizeColor[$color_string];
                                        $color_code = $color_code_list[$color_num++];
                                        $check_product_color = $ProductColor->findone('product_id="'.$product_id.'" AND color_id="'.$color_id.'" AND skc_id = "'.$skc_id.'" AND color_code="'.$color_code.'" ');
                                        if(!sizeof($check_product_color)){
                                            $error[]    = '第'.$line_num.'行('.$pinfo['info']['kuanhao'].')颜色或颜色编码或圆牌号错误!';
                                        }
                                    }
                                    $color_code = $color_code_list[$color_num++];
                                }
                            }
                        }else{
                            $error[]    = '第'.$line_num.'行('.$pinfo['info']['kuanhao'].')颜色或颜色编码或者圆牌号为空!';
                        }
                    }else{
                        $error[]    = '第'.$line_num.'行货品没有导入!';
                    }
                }
            }
        }else{
            $error[]    = '产品表不存在!';
        }
        return $error;
    }

    public static function Action_install_product_stock ($r) {
        $file_excel = DIR_HDT_INSTALL . 'product.stock.xls';      
        $result     = array('error'=>0);    
            if(file_exists($file_excel)){
                $Excel  = new ExcelReader($file_excel);
                $line_num = 0;
                $hashProduct = array();
                $Product = new Product;
                $ProductStock   = new ProductStock;
                while($row = $Excel->nextRow()){                 
                    $line_num++;
                    if($line_num<=1)continue;
                    $kuanhao    = $row[1];
                    $color      = $row[2];
                    $size       = $row[3];
                    $total_num  = $row[4];
                    if(isset($hashProduct[$kuanhao])){
                        $product_id = $hashProduct[$kuanhao];
                    }else{
                        $product    = $Product->findone('kuanhao="'.$kuanhao.'"');
                        $product_id = $product['id'];
                        $hashProduct[$kuanhao] = $product_id;
                    }
                    $color_id   = Keywords::cache_get_id($color);
                    $size_id    = Keywords::cache_get_id($size);
                    if($product_id && $color_id && $size_id){
                        $ProductStock->create_stock($product_id, $color_id, $size_id, $total_num);
                        $Product->update(array("isspot"=>2), "id={$product_id}");
                    }
                }
            }else{
                $result['error']    = 1;
                $result['errmsg']   = sprintf("文件[%s]不存在", FILE_HDT_PRODUCT_USER_LEVEL);
            }
            Flight::json($result);
    }
}
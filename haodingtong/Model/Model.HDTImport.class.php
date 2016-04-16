<?php
define("DIR_HDT_INSTALL",           DOCUMENT_ROOT. "haodingtong/Config/");
define("FILE_HDT_TABLES_SQL",       'haodingtong.tables.sql');
define("FILE_HDT_DATA_SQL",         'haodingtong.data.sql');
define("FILE_HDT_IMPORT_INI",       'import.ini');

class HDTImport {
    public function __construct(){
        $this->_keywords = array();
    }

    public function run ($method, $params=array()) {
        if(method_exists($this, $method)){
            return call_user_func_array(array($this, $method), $params);
        }
    }

    public function clear_orderlist () {
        $sqls[] = "TRUNCATE TABLE orderlist";
        $sqls[] = "TRUNCATE TABLE orderlistbak";
        $sqls[] = "TRUNCATE TABLE orderlistdetail";
        $sqls[] = "TRUNCATE TABLE orderlistproduct";
        $sqls[] = "TRUNCATE TABLE orderlistproductcolor";
        $sqls[] = "TRUNCATE TABLE orderlistuser";
        $sqls[] = "TRUNCATE TABLE orderlistuserproductcolor";
        $sqls[] = "TRUNCATE TABLE orderlist_proportion";
        $sqls[] = "TRUNCATE TABLE orderlist_agent";
        $sqls[] = "TRUNCATE TABLE orderlist_area";
        $sqls[] = "TRUNCATE TABLE custom_meforever_history";
        $sqls[] = "UPDATE user_indicator SET ord_pnum=0,ord_num=0,ord_skc=0,ord_amount=0,ord_discount_amount=0";
        $dbh    = Flight::DatabaseMaster();
        foreach($sqls as $sql){
            $dbh->query($sql);
        }
    }

    public function clear_product () {
        $sqls[] = "TRUNCATE TABLE group_to_display";
        $sqls[] = "TRUNCATE TABLE product";
        $sqls[] = "TRUNCATE TABLE products_attr";
        $sqls[] = "TRUNCATE TABLE products_attr_group";
        $sqls[] = "TRUNCATE TABLE product_color";
        $sqls[] = "TRUNCATE TABLE product_comment";
        $sqls[] = "TRUNCATE TABLE product_display";
        $sqls[] = "TRUNCATE TABLE product_display_image";
        $sqls[] = "TRUNCATE TABLE product_display_member";
        $sqls[] = "TRUNCATE TABLE product_display_member_color";
        $sqls[] = "TRUNCATE TABLE product_gift";
        $sqls[] = "TRUNCATE TABLE product_group";
        $sqls[] = "TRUNCATE TABLE product_group_image";
        $sqls[] = "TRUNCATE TABLE product_group_member";
        $sqls[] = "TRUNCATE TABLE product_image";
        $sqls[] = "TRUNCATE TABLE product_perferential";
        $sqls[] = "TRUNCATE TABLE product_show";
        $sqls[] = "TRUNCATE TABLE product_size";
        $sqls[] = "TRUNCATE TABLE product_stock";
        $sqls[] = "TRUNCATE TABLE product_user_level";
        $sqls[] = "TRUNCATE TABLE product_proportion";
        $sqls[] = "TRUNCATE TABLE product_color_moq";
        $sqls[] = "TRUNCATE TABLE products_color_group";
        $sqls[] = "TRUNCATE TABLE user_guide";
        $dbh    = Flight::DatabaseMaster();
        foreach($sqls as $sql){
            $dbh->query($sql);
        }
        self::clear_orderlist();
    }

    public function clear_user () {
        $sqls[] = "DELETE FROM user where id>3";
        $sqls[] = "TRUNCATE TABLE user_slave";
        $sqls[] = "TRUNCATE TABLE user_slave_tree";
        $sqls[] = "TRUNCATE TABLE location";
        $sqls[] = "DELETE FROM user_indicator where id > 1";
        $sqls[] = "TRUNCATE TABLE user_discount";
        $sqls[] = "TRUNCATE TABLE user_size_history";
        $sqls[] = "TRUNCATE TABLE user_guide";
        $dbh    = Flight::DatabaseMaster();
        foreach($sqls as $sql){
            $dbh->query($sql);
        }
        self::clear_orderlist();
    }

    public function clear_cache(){
        Rrmdir::rrmdir(DOCUMENT_ROOT . "/cache/");
        Rrmdir::rrmdir(DOCUMENT_ROOT . "/thumb/");
    }


    public function clear_all () {
        $filename   = DIR_HDT_INSTALL. FILE_HDT_TABLES_SQL;
        $result     = array();
        if(file_exists($filename)){
            $dbh    = Flight::DatabaseMaster();
            $sql1   = sprintf("DROP DATABASE %s;CREATE DATABASE %s;USE %s;", HDT_MYSQL_DBNAME, HDT_MYSQL_DBNAME, HDT_MYSQL_DBNAME);
            $sql2   = file_get_contents($filename);
            $dbh->query($sql1);
            $dbh->query($sql2);
            $result['errorinfo']    = $dbh->errorinfo();
            $filename   = DIR_HDT_INSTALL. FILE_HDT_DATA_SQL;
            if(file_exists($filename)){
                $sql3   = file_get_contents($filename);
                $dbh->query($sql3);
            }
            $file   = DOCUMENT_ROOT . 'haodingtong/Config/Data/Keywords';
            if(is_file($file)){
                unlink($file);
            }
            Rrmdir::rrmdir(DOCUMENT_ROOT . "/thumb/");
            // system("rm -rf ". DOCUMENT_ROOT . "/thumb/*");
        }
    }

    public function product ($r) {
        $files          = $r->files;
        $imgstring      = $r->data->imgstring;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];

            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }

            $Product    = new Product;
            $Excel      = new ExcelReader($filetmpl);
            $i          = 0;
            $result     = array("modify_pnum"=>0, "insert_pnum"=>0, "modify_cnum"=>0, "insert_cnum"=>0, "insert_image_num"=>0);
            while($row  = $Excel->nextRow()){
                if($r->data->passfirst && $i++ == 0){
                    continue;
                }
                $product    = $this->make_product($row);
                $kuanhao    = $product->attr('kuanhao');
                $pinfo      = $Product->findone("kuanhao='{$kuanhao}'");
                if($pinfo['id']){
                    if($product->check_change($pinfo)){
                        $Product->update($product->attrs(), "id={$pinfo['id']}");
                        $result['modify_pnum']  += 1;
                    }
                    $product->attr("id",    $pinfo['id']);
                    if($r->data->checksize){
                        $product->check_size();
                    }
                }else{
                    $product_id     = $Product->create($product->attrs())->insert();
                    $product->attr("id",    $product_id);
                    $product->check_size();
                    $result['insert_pnum'] += 1;
                }
                $check_color_info   = $product->check_color();
                /* $result[$check_color_info]  += 1; */
                $result['insert_cnum'] += $check_color_info['insert_cnum'];//颜色分号分隔修改
                $result['modify_cnum'] += $check_color_info['modify_cnum'];//更新款色数
                $check_image_list   = $product->check_image($imgstring);
                $check_image_num    = count($check_image_list);
                $result['insert_image_num'] += $check_image_num;
                if(!$pinfo['defaultimage'] && $check_image_num){
                    $product->set_default_image($check_image_list[0]);
                }
            }

            $message    = "新增款数:{$result['insert_pnum']}, 新增款色数:{$result['insert_cnum']},更新款色数:{$result['modify_cnum']}, 新导入图片数:{$result['insert_image_num']}";
            return $this->error($message);
        }
    }

    public function product_group ($r) {
        $files          = $r->files;
        $unitkey        = $r->data->unitkey;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];

            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }

            $message_list   = array();
            $ProductGroup   = new ProductGroup;
            $Excel          = new ExcelReader($filetmpl);
            $i              = 0;
            $insert_gnum    = 0;
            $insert_pnum    = 0;
            while($row  = $Excel->nextRow()){
                if($r->data->passfirst && $i++ == 0){
                    continue;
                }
                $group  = $this->make_product_group($row);
                $dp_num = $group->attr('dp_num');
                $ginfo  = $ProductGroup->findone("dp_num='{$dp_num}'");
                if($ginfo['id']){
                    $group->attr('id',  $ginfo['id']);
                }else{
                    $group_id   = $ProductGroup->create($group->attrs())->insert();
                    $group->attr("id",  $group_id);
                    $insert_gnum    += 1;
                }
                $group->check_unit($unitkey);
                $image_list     = $group->check_image($unitkey);
                $error_list     = $group->error_list;
                if(count($error_list)){
                    $message_list[]    = implode(",", $error_list);
                }
                $image_list_count   = count($image_list);
                $insert_pnum        += $image_list_count;
                if($image_list_count && !$ginfo['defaultimage']){
                    $group->set_default_image($image_list[0]);
                }
            }
            $message_list[]    = "新增搭配数:{$insert_gnum},新增图片:{$insert_pnum}";
            if(count($message_list)){
                $message .=  implode(",", $message_list);
            }
            return $this->error($message);
        }
    }


    public function product_display ($r) {
        $files          = $r->files;
        $unitkey        = $r->data->unitkey;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];

            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }

            $message_list   = array();
            $ProductDisplay = new ProductDisplay;
            $Excel          = new ExcelReader($filetmpl);
            $i              = 0;
            $insert_dnum    = 0;
            $insert_pnum    = 0;
            while($row  = $Excel->nextRow()){
                if($r->data->passfirst && $i++ == 0){
                    continue;
                }
                $display    = $this->make_product_display($row);
                $bianhao    = $display->attr('bianhao');
                $dinfo      = $ProductDisplay->findone("bianhao='{$bianhao}'");
                if($dinfo['id']){
                    $display->attr('id',  $dinfo['id']);
                }else{
                    $display_id     = $ProductDisplay->create($display->attrs())->insert();
                    $display->attr("id",  $display_id);
                    $insert_dnum    += 1;
                }
                $display->check_unit($unitkey);
                $image_list     = $display->check_image($unitkey);
                $error_list     = $display->error_list;
                if(count($error_list)){
                    $message_list[]    = implode("<br>", $error_list);
                }
                $image_list_count   = count($image_list);
                $insert_pnum        += $image_list_count;
                if($image_list_count && !$dinfo['defaultimage']){
                    $display->set_default_image($image_list[0]);
                }
            }
            $message_list[]    = "新增陈列数:{$insert_dnum},新增图片:{$insert_pnum}";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);
        }
    }

    public function user ($r) {
        $files          = $r->files;
        $unitkey        = $r->data->unitkey;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];

            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }

            $User           = new User;
            $UserSlave      = new UserSlave;
            $UserIndicator  =   new UserIndicator();
            //$UserSlaveTree	= new UserSlaveTree;
            $Excel          = new ExcelReader($filetmpl);
            $i              = 0;
            $insert_unum    = 0;
            $update_unum    = 0;
            while($row  = $Excel->nextRow()){
                if($r->data->passfirst && $i++ == 0){
                    continue;
                }

                $user       = $this->make_user($row);
                $username   = $user->attr('username');
                $uinfo      = $User->findone("username='{$username}'");
                if($uinfo['id']){
                    $user->attr('id',  $uinfo['id']);
                    $user_id    = $uinfo['id'];
                    $User->create($user->attrs())->insert(true);
                    $update_unum    += 1;
                }else{
                    if($user->attr('type') == 1) {
                        $authUser   = AuthUser::getInstance();
                        $auth       = $authUser->auth();
                        if($auth) {
                            $message = "执行行数:{$i}, 新增用户数:{$insert_unum}, 更新用户数:{$update_unum}, 错误提示[" . $auth['message']. "]";
                            return $this->error($message);
                        }
                    }
                    $user_id    = $User->create($user->attrs())->insert(true);
                    $user->attr("id",  $user_id);
                    $insert_unum    += 1;
                }
				//所属上级
                $zongdai_name   = $user->zongdai_name;
                if($zongdai_name){
                	$zongdai	= $User->findone("username='{$zongdai_name}'");
                	if($zongdai['id']){
                        $UserSlave->create_slave($zongdai['id'], $user->attr('id'));
                		$user->attr('mid',$zongdai['id']);        //增加总代备单属性
                		$User->update(array("mid"=>$zongdai['id']), "id={$user_id} AND is_stock=1");
                	}
                }
				//分管AD
                $ad_name 	= $user->ad_name;
                if($ad_name){
                	$ad    = $User->findone("username='{$ad_name}' and type=3");
                	if($ad['id']){
                		$User->update(array("ad_id"=>$ad['id']), "id={$user_id}");
                	}
                }
                
                $exp_num    =   $user->attr('exp_num');
                $exp_price  =   $user->attr('exp_price');
                $type   =   $user->attr('type');
                $UserIndicator->set_indicator_user($user_id, $type, $exp_num, $exp_price);
            }
            $UserIndicator->refresh_indicator();
            $User->update(array("auth"=>1),"type <> 1");
            $message = "总行数:{$i}, 新增用户数:{$insert_unum}, 更新用户数:{$update_unum}";
            return $this->error($message);
        }
    }


    public function orderlist ($r) {
        $files          = $r->files;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
    
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list   = array();
            $Excel          = new ExcelReader($filetmpl);
            $i              = 0;
            $done           = 0;
            $fail           = 0;
            while($row  = $Excel->nextRow()){
                if($r->data->passfirst && $i++ == 0){
                    continue;
                }

                $orderlist  = $this->make_orderlist($row);
                if(count($orderlist->error_list)) {
                    foreach($orderlist->error_list as $error) {
                        $message_list[] = '第'.$i.'行 错误 '. $error;
                    }
                    $fail++;
                }else{
                    $user = $orderlist->attr('user');
                    $user_id    = $user['id'];
                    $product = $orderlist->attr('product');
                    $product_id = $product['id'];
                    $product_color_id = $orderlist->attr('product_color_id');
                    $size_list  = $orderlist->attr('size_list');
                    foreach($size_list as $size) {
                        $product_size_id    = $size['size']['size_id'];
                        $num    = $size['num'];
                        ProductOrder::add($user_id, $product_id, $product_color_id, $product_size_id, $num);
                    }
                    $done++;
                }
            }
            ProductOrder::run();
            $message_list[] = "更新结束！成功执行{$done}行，失败{$fail}行";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);
        }
    }

    public function orderlist_two($r){
        $files          = $r->files;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
    
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list   = array();
            $Excel          = new ExcelReader($filetmpl);
            $i              = 0;
            $done           = 0;
            $fail           = 0;
            while($row  = $Excel->nextRow()){
                if($i++ == 0){
                    $titles     = $row;
                    continue;
                }
                $orderlist_two  = new HDTImportOrderlistTwo($row, $titles);
                $error_list     = $orderlist_two->run();
                if(count($error_list)){
                    foreach($error_list as $error) {
                        $message_list[] = "第{$i}行错误:" . $error;
                    }
                    $fail++;
                }else{
                    $product_id         = $orderlist_two->attr('product_id');
                    $product_color_id   = $orderlist_two->attr('color_id');
                    $product_size_id    = $orderlist_two->attr('size_id');
                    $user_id_list       = $orderlist_two->attr('user_id_list');
                    $num_list           = $orderlist_two->attr('num_list');
                    foreach ($user_id_list as $key=>$user_id) {
                        if($user_id){
                            ProductOrder::add($user_id, $product_id, $product_color_id, $product_size_id, $num_list[$key]);
                        }
                    }
                    $done++;
                }
            }
            ProductOrder::run();
            $message_list[] = "更新结束！成功执行{$done}行，失败{$fail}行";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);
        }
    }


    public function exp_complete ($r) {
        $files          = $r->files;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
    
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list       = array();
            $Excel              = new ExcelReader($filetmpl);
            $i                  = 0;
            while($row  = $Excel->nextRow()){
                if($i++ == 0){
                    $titles     = $row;
                    continue;
                }

                $exp_complete   = new HDTImportExpComplete($row, $titles);
                $error_list     = $exp_complete->run();
                if(count($error_list)){
                    foreach($error_list as $error) {
                        $message_list[] = "第{$i}行错误:" . $error;
                    }
                }
            }
            $message_list[] = "更新结束！";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);
        }
    }

    public function user_indicator ($r) {
        $files          = $r->files;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
    
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list       = array();
            $Excel              = new ExcelReader($filetmpl);
            $i                  = 0;
            while($row  = $Excel->nextRow()){
                if($i++ == 0){
                    $titles     = $row;
                    continue;
                }
    
                $user_indicator = new HDTImportUserIndicator($row, $titles);
                $error_list     = $user_indicator->run();
                if(count($error_list)){
                    foreach($error_list as $error) {
                        $message_list[] = "第{$i}行错误:" . $error;
                    }
                }
            }
            $message_list[] = "更新结束！";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);
        }
    }

    private function make_orderlist ($row) {
        $orderlist  = new HDTImportOrderlist($row);
        $user       = $orderlist->attr('user');
        $product    = $orderlist->attr('product');
        $product_color_id = $orderlist->attr('product_color_id');
        $size_list  = $orderlist->attr('size_list');
        $user_id    = $user['id'];
        $product_id = $product['id'];
        if(!$user_id) {
            $orderlist->error("用户不存在");
        }
        if(!$product_id) {
            $orderlist->error("款号不存在");
        }
        if(!$product_color_id) {
            $orderlist->error("颜色不存在");
        }
        return $orderlist;
    }

    public function product_stock ($r) {
        $files          = $r->files;
        $unitkey        = $r->data->unitkey;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];

            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }

            $message_list   = array();
            $Product        = new Product();
            $ProductStock   = new ProductStock();
            $Excel          = new ExcelReader($filetmpl);
            $i              = 0;
            $insert_num     = 0;
            while($row  = $Excel->nextRow()){
                if($r->data->passfirst && $i++ == 0){
                    continue;
                }

                $product_stock   = $this->make_product_stock($row);

                $kuanhao    = $product_stock->kuanhao;
                $color      = $product_stock->color;
                $size       = $product_stock->size;
                $total_num   = $product_stock->totalnum;

                $product    = $Product->findone("kuanhao='{$kuanhao}'");

                if($product['id']){
                    $color_id   = Keywords::cache_get_id($color);
                    $size_id    = Keywords::cache_get_id($size);

                    $ProductStock->create_stock($product['id'], $color_id, $size_id, $total_num);
                    $insert_num++;
                }else{
                    $message_list[]    = implode("<br>", "{$kuanhao}不存在");
                }
            }
            
            $message_list[]    = "新增数量:{$insert_num}";
            $message    =   implode(",", $message_list);
            return $this->error($message);
        }
    }
    
    public function product_color_moq ($r) {
        $files          = $r->files;
        $unitkey        = $r->data->unitkey;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
    
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list   = array();
            $Product        = new Product();
            $ProductColorMoq= new ProductColorMoq();
            $Excel          = new ExcelReader($filetmpl);
            $i              = 0;
            $insert_num     = 0;
            while($row  = $Excel->nextRow()){
                if($r->data->passfirst && $i++ == 0){
                    continue;
                }
    
                $product_color_moq   = $this->make_product_color_moq($row);
    
                $kuanhao    = $product_color_moq->kuanhao;
                $color      = $product_color_moq->color;
                $num        = $product_color_moq->num;
    
                $product    = $Product->findone("kuanhao='{$kuanhao}'");
    
                if($product['id']){
                    $color_id   = Keywords::cache_get_id($color);
    
                    $ProductColorMoq->create_moq($product['id'], $color_id, $num);
                    $insert_num++;
                }else{
                    $message_list[]    = implode("<br>", "{$kuanhao}不存在");
                }
            }
    
            $message_list[]    = "新增数量:{$insert_num}";
            $message    =   implode(",", $message_list);
            return $this->error($message);
        }
    }
    
    public function user_discount ($r) {
        $files          = $r->files;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
    
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list       = array();
            $Excel              = new ExcelReader($filetmpl);
            $i                  = 0;
            while($row  = $Excel->nextRow()){
                if($i++ == 0){
                    $titles     = $row;
                    continue;
                }
    
                $user_discount = new HDTImportUserDiscount($row, $titles);
                $error_list     = $user_discount->run();
                if(count($error_list)){
                    foreach($error_list as $error) {
                        $message_list[] = "第{$i}行错误:" . $error;
                    }
                }
            }
            $message_list[] = "更新结束！";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);
        }
    }
    
    public function user_size_history ($r) {
        $files          = $r->files;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
    
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list       = array();
            $Excel              = new ExcelReader($filetmpl);
            $i                  = 0;
            $succ               = 0;
            $fail               = 0;
            while($row  = $Excel->nextRow()){
                if($i++ == 0){
                    $titles     = $row;
                    continue;
                }
    
                $user_size_history = new HDTImportUserSizeHistory($row, $titles);
                $error_list     = $user_size_history->run();
                if(count($error_list)){
                    foreach($error_list as $error) {
                        $fail++;
                        $message_list[] = "第{$i}行错误:" . $error;
                    }
                }
            }
            $succ   =   $i-$fail;
            $message_list[] = "更新结束！数据共{$i},成功{$succ}，失败{$fail}";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);
        }
    }

    public function product_size_mininum($r){
         $files          = $r->files;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list       = array();
            $Excel              = new ExcelReader($filetmpl);
            $i                  = 0;
            $succ               = 0;
            $fail               = 0;
            while($row  = $Excel->nextRow()){
                if($i++ == 0){
                    continue;
                }
                $product_size_mininum = new HDTImportProductSizeMininum($row);
                $error_list = $product_size_mininum->run();
                if(count($error_list)){
                    foreach($error_list as $error) {
                        $fail++;
                        $message_list[] = "第{$i}行错误:" . $error;
                    }
                }
            }
            $succ   =   $i-$fail;
            $message_list[] = "更新结束！数据共{$i},成功{$succ}，失败{$fail}";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);

        }
    }

    public function product_color_group($r){
        $files          = $r->files;
        $unitkey        = $r->data->unitkey;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
    
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list   = array();
            $Excel          = new ExcelReader($filetmpl);
            $i              = 0;
            $insert_dnum    = 0;
            $insert_pnum    = 0;
            $ProductsAttrColor  = new ProductsAttributeFactory('color');
            $attr_list    = $ProductsAttrColor->getAllList(array('cache_time'=>0));
            foreach ($attr_list as $row){
                $tmp_list[$row['keyword_id']] = $row['keywords']['name'];
            }
    
            //print_r($tmp_list);exit;
    
            while($row  = $Excel->nextRow()){
                if($r->data->passfirst && $i++ == 0){
                    continue;
                }
                $color_group    = new HDTImportProductColorGroup($row);
    
                $ProductsAttr   = new ProductsAttributeFactory('color_group');
                $ProductsColorGroup = new ProductsColorGroup;
                $ProductsAttrGroup  = new ProductsAttrGroup;
    
                $group_id       = $ProductsAttr->createItem($color_group->attr('color_group'));
                $rgb            = $color_group->attr('rgb');
                $color_list     = $color_group->attr('color_list');
    
                $ProductsColorGroup->createItem($group_id, $rgb);
                foreach ($color_list as $color){
                    $color_id = array_search($color, $tmp_list);
                    if($color_id){
                        $list[]=$color_id;
                        $ProductsAttrGroup->add_attr_group($color_id, $group_id);
                    }
                }
                $insert_num +=  1;
            }
            $message    = "新增色系数:{$insert_num}";
            return $this->error($message);
        }
    }

     public function user_guide($r){
        $files          = $r->files;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list       = array();
            $Excel              = new ExcelReader($filetmpl);
            $i                  = 0;
            $succ               = 0;
            $fail               = 0;
            while($row  = $Excel->nextRow()){
                if($i++ == 0){
                    continue;
                }
                $user_guide = new HDTImportUserGuide($row);
                $error_list = $user_guide->run();
                if(count($error_list)){
                    foreach($error_list as $error) {
                        $fail++;
                        $message_list[] = "第{$i}行错误:" . $error;
                    }
                }
            }
            $succ   =   $i-$fail;
            $message_list[] = "更新结束！数据共{$i},成功{$succ}，失败{$fail}";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);

        }
    }

    public function custom_meforever_history($r){
        $files          = $r->files;
        $filetypelist   = array("xls");
        foreach($files as $file){
            $filename   = $file['name'];
            $namelist   = explode(".", $filename);
            $filetype   = $namelist[count($namelist) - 1];
            $filetmpl   = $file['tmp_name'];
            if(!in_array($filetype, $filetypelist)){
                return $this->error("上传文件格式必须为" . implode(",", $filetypelist) . "为后缀");
            }
    
            $message_list       = array();
            $Excel              = new ExcelReader($filetmpl);
            $i                  = 0;
            $succ               = 0;
            $fail               = 0;
            while($row  = $Excel->nextRow()){
                if($i++ == 0){
                    continue;
                }
                $custom_meforever_history = new HDTImportCustomMeforever($row);
                $error_list = $custom_meforever_history->run();
                if(count($error_list)){
                    foreach($error_list as $error) {
                        $fail++;
                        // $message_list[] = "第{$i}行错误:" . $error;
                    }
                }
            }
            $succ   =   $i-$fail;
            $message_list[] = "更新结束！数据共{$i},成功{$succ}，失败{$fail}";
            if(count($message_list)){
                $message .=  implode("<br>", $message_list);
            }
            return $this->error($message);
        }
    }

    private function make_product ($row) {
        return new HDTImportProduct($row);
    }

    private function make_product_group ($row) {
        return new HDTImportProductGroup($row);
    }

    private function make_product_display ($row) {
        return new HDTImportProductDisplay($row);
    }
    
    private function make_user ($row) {
        return new HDTImportUser($row);
    }
    
    private function make_product_stock($row){
        return new HDTImportProductStock($row);
    }
    
    private function make_product_color_moq($row){
        return new HDTImportProductColorMoq($row);
    }
    public function error ($message) {
        return array("error"=>1, "message"=>$message);
    }
}





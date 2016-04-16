<?php

class Control_data {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        Flight::display('data/index.html', $result);
    }

    public static function Action_index_bak($r){
        Flight::validateEditorHasLogin();

        Flight::display('data/index_bak.html', $result);
    }

    public static function Action_index_detail ($r) {
        Flight::validateEditorHasLogin();

        Flight::display("data/index_detail.html", $result);
    }

    public static function Action_index_detail_user ($r, $user_id) {
        Flight::validateEditorHasLogin();

        $dir        = DOCUMENT_ROOT . "tmpl/output/detail/";
        $filename   = $dir ."{$user_id}.txt";
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        if(is_file($filename)){
            unlink($filename);
        }
        $dbh    = Flight::DatabaseMaster();
        $data       = $r->query;
        $all_product =  $data->all_product;
        if($all_product) {
            $tablename  = "product as p left join product_color as pc on pc.product_id=p.id left join user as u on u.id={$user_id}
            left join user_slave as us on u.id=us.user_slave_id left join user as um on us.user_id=um.id
            left join location as a1 on u.area1=a1.id left join location as a2 on u.area2=a2.id";
            $where  = "pc.status<>0";
            $sql    = "SELECT a1.name as a1name,a2.name as a2name, um.name as mname, u.name as uname, u.username, pc.skc_id, pc.color_code, p.kuanhao, p.bianhao, p.name, p.category_id,p.wave_id,p.classes_id, p.price, p.size_group_id,
            u.id as user_id, pc.product_id, pc.color_id as product_color_id, 
            (SELECT SUM( o.num ) FROM orderlist as o WHERE o.user_id=u.id and o.product_id=pc.product_id and o.product_color_id=pc.color_id) as num,
            (SELECT SUM( o.num * p.price ) FROM orderlist as o WHERE o.user_id=u.id and o.product_id=pc.product_id and o.product_color_id=pc.color_id) as sum_price";
            $sql    .= " FROM {$tablename} ";
            $sql    .= " WHERE {$where} ORDER BY p.id ASC";
        }else{
            $tablename  = "orderlist AS o LEFT JOIN product AS p ON o.product_id = p.id left join user as u on o.user_id=u.id
            left join user_slave as us on u.id=us.user_slave_id left join user as um on us.user_id=um.id
            left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id
            left join location as a1 on u.area1=a1.id left join location as a2 on u.area2=a2.id";
            $where      = "pc.status<>0 and o.user_id={$user_id}";
            $sql    = "SELECT a1.name as a1name,a2.name as a2name, um.name as mname, u.name as uname, u.username, pc.skc_id, pc.color_code, p.kuanhao, p.bianhao, p.name, p.category_id,p.wave_id,p.classes_id, p.price, p.size_group_id,
             o.user_id, o.product_id, o.product_color_id, SUM( o.num ) as num, SUM(o.num*p.price) as sum_price";
            $sql    .= " FROM {$tablename}";
            $sql    .= " WHERE {$where} GROUP BY o.user_id, o.product_id, o.product_color_id ORDER BY u.id, p.id ASC";
        }
        // echo $sql;exit;
        $sth    = $dbh->prepare($sql);
        $sth->execute();
        $tablename  = "orderlist AS o LEFT JOIN product AS p ON o.product_id = p.id left join user as u on o.user_id=u.id
            left join user_slave as us on u.id=us.user_slave_id left join user as um on us.user_id=um.id
            left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id
            left join location as a1 on u.area1=a1.id left join location as a2 on u.area2=a2.id";
        $where      = "pc.status<>0 and o.user_id={$user_id}";
        $content    = array();
        while($row  = $sth->fetch()){
            $data   = array();
            $data[] = $row['a1name'];
            $data[] = $row['a2name'];
            $data[] = $row['mname'];
            $data[] = $row['uname'];
            $data[] = $row['username'];
            $data[] = $row['kuanhao'];
            $data[] = $row['bianhao'];
            $data[] = $row['skc_id'];
            $data[] = $row['name'];
            $data[] = Keywords::cache_get($row['product_color_id']);
            $data[] = $row['color_code']."\t";
            $data[] = Keywords::cache_get($row['wave_id']);
            $data[] = Keywords::cache_get($row['category_id']);
            $data[] = Keywords::cache_get($row['classes_id']);
            $data[] = $row['num'];
            $data[] = $row['price'];
            $data[] = $row['sum_price'];
            $size_hash  = array();
            $sql    = "SELECT SUM(o.num) as num, o.product_size_id FROM {$tablename} "
                    . "WHERE {$where} AND o.user_id={$row['user_id']} AND o.product_id={$row['product_id']} AND o.product_color_id={$row['product_color_id']} "
                    . "GROUP BY o.user_id, o.product_id, o.product_color_id, o.product_size_id";
            $sth1   = $dbh->prepare($sql);
            $sth1->execute();
            while($info = $sth1->fetch()){
                $size_hash[$info['product_size_id']]    = $info['num'];
            }
            $size_group = SizeGroup::getInstance($row['size_group_id']);
            $size_list  = $size_group->get_size_list();
            foreach($size_list as $size_row){
                $num    = $size_hash[$size_row['size_id']];
                $data[] = $num;
            }
            
            $content[]  = implode(",", $data);
        }
        if($content){
            file_put_contents($filename, implode("\n", $content));
        } 

        $result['error']    = 0;
        Flight::json($result);
    }

    public static function Action_index_detail_user_sku ($r, $user_id) {
        Flight::validateEditorHasLogin();
    
        $dir        = DOCUMENT_ROOT . "tmpl/output/detail/";
        $filename   = $dir ."{$user_id}.txt";
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        if(is_file($filename)){
            unlink($filename);
        }
        $dbh    = Flight::DatabaseMaster();
        $data       = $r->query;
        $all_product =  $data->all_product;
        if($all_product) {          
            $tablename  = "product as p left join product_color as pc on p.id=pc.product_id left join product_size as ps on p.id=ps.product_id
            left join user as u on u.id={$user_id}
            left join user_slave as us on u.id=us.user_slave_id left join user as um on us.user_id=um.id
            left join location as a1 on u.area1=a1.id left join location as a2 on u.area2=a2.id
            left join orderlist as o on u.id=o.user_id and p.id=o.product_id and ps.size_id=o.product_size_id and o.product_color_id=pc.color_id
            left join products_attr as pa on ps.size_id=pa.keyword_id  and pa.field = 'size'";
            $where  = "pc.status<>0";
            $sql    = "SELECT a1.name as a1name,a2.name as a2name,um.name as mname,u.name as uname,u.username, p.id,p.kuanhao, p.bianhao,pc.skc_id, p.name,
            pc.color_id as product_color_id,pc.color_code,p.category_id,p.wave_id,p.classes_id,p.price,ps.size_id as product_size_id,o.num, o.amount as sum_price";
            $sql    .= " FROM {$tablename} ";
            $sql    .= " WHERE {$where} ORDER BY p.id,pc.color_id,pa.rank ASC";
        }else{
            $tablename  = "orderlist AS o LEFT JOIN product AS p ON o.product_id = p.id left join user as u on o.user_id=u.id
                left join user_slave as us on u.id=us.user_slave_id left join user as um on us.user_id=um.id
                left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id
                left join location as a1 on u.area1=a1.id left join location as a2 on u.area2=a2.id
                left join products_attr as pa on o.product_size_id=pa.keyword_id  and pa.field = 'size'";
            $where      = "pc.status<>0 and o.user_id={$user_id}";
            $sql    = "SELECT a1.name as a1name,a2.name as a2name,um.name as mname,u.name as uname,u.username, p.id,p.kuanhao, p.bianhao,pc.skc_id, p.name,
                        pc.color_id as product_color_id,pc.color_code,p.category_id,p.wave_id,p.classes_id,p.price,o.product_size_id,o.num, o.amount as sum_price";
            $sql    .= " FROM {$tablename}";
                $sql    .= " WHERE {$where} ORDER BY u.id, p.id,pc.color_id,pa.rank ASC";
        }
        //echo $sql;exit;
                $sth    = $dbh->prepare($sql);
                $sth->execute();
            $content    = array();
            while($row  = $sth->fetch()){
            $data   = array();
                $data[] = $row['a1name'];
                $data[] = $row['a2name'];
                $data[] = $row['mname'];
                $data[] = $row['uname'];
                $data[] = $row['username'];
                $data[] = $row['kuanhao'];
                $data[] = $row['bianhao'];
                $data[] = $row['skc_id'];
                $data[] = $row['name'];
                $data[] = Keywords::cache_get($row['product_color_id']);
                $data[] = $row['color_code']."\t";
                $data[] = Keywords::cache_get($row['wave_id']);
                $data[] = Keywords::cache_get($row['category_id']);
                $data[] = Keywords::cache_get($row['classes_id']);
                $data[] = $row['price'];
                $data[] = Keywords::cache_get($row['product_size_id']);
                $data[] = $row['num'];
                $data[] = $row['sum_price'];
    
                $content[]  = implode(",", $data);
                }
                if($content){
                file_put_contents($filename, implode("\n", $content));
                }
    
                    $result['error']    = 0;
                    Flight::json($result);
    }
    
    public static function Action_index_detail_user_count ($r, $username=null) {
        $dir        = DOCUMENT_ROOT . "tmpl/output/detail/";

        $User   = new User;
        $condition[]    = "type=1";
        if($username) {
            $user   = $User->findone("username='{$username}'");
            $user_id    = $user['id'];
            $user_type  = $user['type'];
            if($user_type == 1) {
                $condition[]    = "id={$user_id}";
                $user_name      = "[{$username}]{$user['name']}";
            }
            if($user_type == 2) {
                $condition[]    = "id in (select user_slave_id from user_slave where user_id={$user_id})";
                $user_name      = "[{$username}]{$user['name']}";
            }
        }
        
        $titles         = array("大区", "地区","省代", "客户", "帐号", "款号", "编号", "圆牌号", "款名", "颜色", "色号", "波段", "大类", "小类");
        $titles[]       = "合计";
        $titles[]       = "单价";
        $titles[]       = "总价";

        $Keywords       = new Keywords;
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);

        $excel_name     = sprintf("%s%s客户订单数据横向表", $fairname, $user_name);
        // $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));
        $Response       = Flight::response();
        $Response->header("Content-type","application/vnd.ms-excel;");
        $Response->header("Content-Disposition", "attachment; filename={$excel_name}.csv");
        $size_group_list    = SizeGroup::getAllInstance();
        foreach($size_group_list as $size_group) {
            $size_list  = $size_group->get_size_list();
            $new_titles = $titles;
            foreach($size_list as $size) {
                $new_titles[]   = Keywords::cache_get($size['size_id'])."\t";
            }
            echo iconv("utf-8",'gbk', implode(",", $new_titles)), "\n";
        }

        $dbh    = Flight::DatabaseMaster();
        
        $where  = implode(" AND ", $condition);
        $list   = $User->find($where, array("limit"=>10000, "fields"=>"id"));
        foreach($list as $row) {
            $filename   = $dir . "{$row['id']}.txt";
            if(is_file($filename)) {
                $content    = iconv("utf-8",'gbk', file_get_contents($filename));
                echo $content, "\r\n";
            }
        }
    }
    
    public static function Action_index_detail_user_count_sku ($r, $username=null) {
        $dir        = DOCUMENT_ROOT . "tmpl/output/detail/";
    
        $User   = new User;
        $condition[]    = "type=1";
        if($username) {
            $user   = $User->findone("username='{$username}'");
            $user_id    = $user['id'];
            $user_type  = $user['type'];
            if($user_type == 1) {
                $condition[]    = "id={$user_id}";
                $user_name      = "[{$username}]{$user['name']}";
            }
            if($user_type == 2) {
                $condition[]    = "id in (select user_slave_id from user_slave where user_id={$user_id})";
                $user_name      = "[{$username}]{$user['name']}";
            }
        }
    
        $titles         = array("大区", "地区","省代", "客户", "帐号", "款号", "编号", "圆牌号", "款名", "颜色", "色号", "波段", "大类", "小类");
        $titles[]       = "单价";
        $titles[]       = "尺码";
        $titles[]       = "数量";
        $titles[]       = "总价";
    
        $Keywords       = new Keywords;
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
    
        $excel_name     = sprintf("%s%s客户订单数据竖向表", $fairname, $user_name);
        // $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));
        $Response       = Flight::response();
        $Response->header("Content-type","application/vnd.ms-excel;");
        $Response->header("Content-Disposition", "attachment; filename={$excel_name}.csv");
        echo iconv("utf-8",'gbk', implode(",", $titles)), "\n";
    
        $where  = implode(" AND ", $condition);
        $list   = $User->find($where, array("limit"=>10000, "fields"=>"id"));
        foreach($list as $row) {
            $filename   = $dir . "{$row['id']}.txt";
            if(is_file($filename)) {
                $content    = iconv("utf-8",'gbk', file_get_contents($filename));
                echo $content, "\r\n";
            }
        }
    }

    public static function Action_ulist ($r, $username=null) {
        $User   = new User;
        $condition[]    = "type=1";
        if($username) {
            $user   = $User->findone("username='{$username}'");
            $user_id    = $user['id'];
            $user_type  = $user['type'];
            if($user_type == 1) {
                $condition[]    = "id={$user_id}";
            }
            if($user_type == 2) {
                $condition[]    = "id in (select user_slave_id from user_slave where user_id={$user_id})";
            }
        }
        $where  = implode(" AND ", $condition);
        $list   = $User->find($where, array("limit"=>10000, "fields"=>"id,name"));
        $result['list'] = $list;
        Flight::json($result);
    }
    
    public static function Action_total_detail_user ($r){ //横向表汇总订单

        $data       = $r->query;
        $all_product= $data->all_product;
        $username   = $data->username ? $data->username : 0;
        $User       = new User;
        $user       = $User->findone("username='{$username}'");
        $user_id    = $user['id'];
        $user_type  = $user['type'];
        
        $condition  = array();
        $condition[]= "pc.status<>0";
        
        $fields = "pc.skc_id, pc.color_code, p.kuanhao, p.bianhao, p.name, p.category_id,p.wave_id,p.classes_id, p.price, p.size_group_id,
                   pc.product_id, pc.color_id as product_color_id";   
        $order  = "p.bianhao ASC";
        $group   = "GROUP BY p.id, pc.color_id";
        
        if($all_product){
            $tablename = "product as p left join product_color as pc on pc.product_id=p.id 
                          left join orderlist as o on p.id=o.product_id and pc.color_id=o.product_color_id";
            switch ($user_type) {
                case 1:
                    $fields_more = " ,(SELECT SUM( o.num ) FROM orderlist as o WHERE o.user_id={$user_id} and o.product_id=pc.product_id and o.product_color_id=pc.color_id) as num,
                        (SELECT SUM( o.num * p.price ) FROM orderlist as o WHERE o.user_id={$user_id} and o.product_id=pc.product_id and o.product_color_id=pc.color_id) as sum_price";
                    break;
                case 2:
                    $fields_more = " ,(SELECT SUM( o.num ) FROM orderlist as o WHERE o.zd_user_id={$user_id} and o.product_id=pc.product_id and o.product_color_id=pc.color_id) as num,
                            (SELECT SUM( o.num * p.price ) FROM orderlist as o WHERE o.zd_user_id={$user_id} and o.product_id=pc.product_id and o.product_color_id=pc.color_id) as sum_price";
                    break;
                default:
                    $fields_more = ",SUM( o.num ) as num, SUM(o.num*p.price) as sum_price";
                    break;
            }
        }else{
            $tablename = "orderlist AS o LEFT JOIN product AS p ON o.product_id = p.id
                          left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id";
            $fields_more = ",SUM( o.num ) as num, SUM(o.num*p.price) as sum_price";
            switch ($user_type) {
                case 1:
                    $condition[] = " o.user_id={$user_id} ";
                    break;
                case 2:
                    $condition[] = " o.zd_user_id={$user_id} ";
                    break;
                default:
                    break;
            }
        }
        $where = implode(' AND ', $condition);
        $sql   = "SELECT {$fields} {$fields_more} FROM {$tablename} WHERE {$where} {$group} ORDER BY {$order}";
        
        //echo $sql;exit;
        Flight::validateEditorHasLogin();
        
        $dbh    = Flight::DatabaseMaster();
        // echo $sql;exit;
        $sth = $dbh->prepare($sql);
        $sth->execute();
        $tablename2 = "orderlist AS o LEFT JOIN product AS p ON o.product_id = p.id
            left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id";
        
        $titles         = array("款号", "编号", "圆牌号", "款名", "颜色", "色号", "波段", "大类", "小类");
        $titles[]       = "合计";
        $titles[]       = "单价";
        $titles[]       = "总价";
        
        $Keywords       = new Keywords;
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
        
        $excel_name     = sprintf("%s[%s]%s汇总订单数据横向表", $fairname,$user['username'], $user['name']);
        // $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));
        $Response       = Flight::response();
        $Response->header("Content-type","application/vnd.ms-excel;");
        $Response->header("Content-Disposition", "attachment; filename={$excel_name}.csv");
        $size_group_list    = SizeGroup::getAllInstance();
        foreach($size_group_list as $size_group) {
            $size_list  = $size_group->get_size_list();
            $new_titles = $titles;
            foreach($size_list as $size) {
                $new_titles[]   = Keywords::cache_get($size['size_id']);
            }
            echo iconv("utf-8",'gbk', implode(",", $new_titles)), "\n";
        }
        
        while ($row = $sth->fetch()) {
            $data = array();
            $data[] = $row['kuanhao'];
            $data[] = $row['bianhao'];
            $data[] = $row['skc_id'];
            $data[] = $row['name'];
            $data[] = Keywords::cache_get($row['product_color_id']);
            $data[] = $row['color_code']."\t";
            $data[] = Keywords::cache_get($row['wave_id']);
            $data[] = Keywords::cache_get($row['category_id']);
            $data[] = Keywords::cache_get($row['classes_id']);
            $data[] = $row['num'];
            $data[] = $row['price'];
            $data[] = $row['sum_price'];
            $size_hash = array();
            $sql = "SELECT SUM(o.num) as num, o.product_size_id FROM {$tablename2} " . "WHERE {$where} AND o.product_id={$row['product_id']} AND o.product_color_id={$row['product_color_id']} " . "GROUP BY  o.product_id, o.product_color_id, o.product_size_id";
            $sth1 = $dbh->prepare($sql);
            $sth1->execute();
            while ($info = $sth1->fetch()) {
                $size_hash[$info['product_size_id']] = $info['num'];
            }
            $size_group = SizeGroup::getInstance($row['size_group_id']);
            $size_list = $size_group->get_size_list();
            foreach ($size_list as $size_row) {
                $num = $size_hash[$size_row['size_id']];
                $data[] = $num;
            }
            echo iconv("utf-8",'gbk', implode(",", $data)), "\n";
        }
    }
    
    public static function Action_total_detail_user_sku($r){ //纵向表汇总订单
        $data       = $r->query;
        $all_product= $data->all_product;
        $username   = $data->username ? $data->username : 0;
        $User       = new User;
        $user       = $User->findone("username='{$username}'");
        $user_id    = $user['id'];
        $user_type  = $user['type'];
        
        $condition  = array();
        $condition[]= "pc.status<>0";
        
        $fields = "p.id,p.kuanhao, p.bianhao,pc.skc_id, p.name,pc.color_id as product_color_id,pc.color_code,
            p.category_id,p.wave_id,p.classes_id,p.price,sum(o.num) as num, sum(o.amount) as sum_price";   
        $order  = "p.bianhao,pc.color_id,pa.rank ASC";
        if($all_product){
            $group   = "GROUP BY p.id, pc.color_id,ps.size_id";
            $tablename  = "product as p 
                    left join product_color as pc on p.id=pc.product_id 
                    left join product_size as ps on p.id=ps.product_id
                    left join products_attr as pa on ps.size_id=pa.keyword_id  and pa.field = 'size'";
            $fields_more= ",ps.size_id as product_size_id";
            switch ($user_type){
                case 1:
                    $tablename_more =   " left join orderlist as o on o.user_id={$user_id} and p.id=o.product_id and ps.size_id=o.product_size_id ";
                    break;
                case 2:
                    $tablename_more =   " left join orderlist as o on o.zd_user_id={$user_id} and p.id=o.product_id and ps.size_id=o.product_size_id ";
                    break;
                default:
                    $tablename_more = " left join orderlist as o on p.id=o.product_id and ps.size_id=o.product_size_id ";
                    break;
            }
        }else{
            $group   = "GROUP BY p.id, pc.color_id,o.product_size_id";
            $tablename = "orderlist AS o 
                left join product as p on o.product_id=p.id 
                left join product_color as pc on o.product_color_id=pc.color_id and o.product_id=pc.product_id 
                left join products_attr as pa on o.product_size_id=pa.keyword_id  and pa.field = 'size'";
            $fields_more= ",o.product_size_id as product_size_id";
            switch ($user_type){
                case 1:
                    $condition[] = "o.user_id={$user_id}";
                    break;
                case 2:
                    $condition[] = "o.zd_user_id={$user_id}";
                    break;
                default:
                    break;
            }
        }

        $where = implode(' AND ', $condition);
        $sql   = "SELECT {$fields} {$fields_more} FROM {$tablename} {$tablename_more} WHERE {$where} {$group} ORDER BY {$order}";
        
        Flight::validateEditorHasLogin();
        
        $dbh    = Flight::DatabaseMaster();
        //echo $sql;exit;
        $sth    = $dbh->prepare($sql);
        $sth->execute();
        
        $titles         = array("款号", "编号", "圆牌号", "款名", "颜色", "色号", "波段", "大类", "小类");
        $titles[]       = "单价";
        $titles[]       = "总价";
        $titles[]       = "尺码";
        $titles[]       = "数量";
        
        $Keywords       = new Keywords;
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
        
        $excel_name     = sprintf("%s[%s]%s汇总订单数据纵向表", $fairname,$user['username'], $user['name']);
        // $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));
        $Response       = Flight::response();
        $Response->header("Content-type","application/vnd.ms-excel;");
        $Response->header("Content-Disposition", "attachment; filename={$excel_name}.csv");
        
        echo iconv("utf-8",'gbk', implode(",", $titles)), "\n";
        
        while ($row = $sth->fetch()) {
            $data = array();
            $data[] = $row['kuanhao'];
            $data[] = $row['bianhao'];
            $data[] = $row['skc_id'];
            $data[] = $row['name'];
            $data[] = Keywords::cache_get($row['product_color_id']);
            $data[] = $row['color_code']."\t";
            $data[] = Keywords::cache_get($row['wave_id']);
            $data[] = Keywords::cache_get($row['category_id']);
            $data[] = Keywords::cache_get($row['classes_id']);
            $data[] = $row['price'];
            $data[] = $row['sum_price'];
            $data[] = Keywords::cache_get($row['product_size_id'])."\t";
            $data[] = $row['num'];
            
            echo iconv("utf-8",'gbk', implode(",", $data)), "\n";
        }
    }

    public static function Action_category($r){
        Flight::validateEditorHasLogin();

        $Factory        = new ProductsAttributeFactory('size');
        $data           = $r->data;
        if($data->category_id){
            $KEY_NAME   = "大类";
            $KEY_ID     = "category_id";
        }elseif($data->classes_id){
            $KEY_NAME   = "小类";
            $KEY_ID     = "classes_id";
        }elseif($data->series_id){
            $KEY_NAME   = "系列";
            $KEY_ID     = "series_id";
        }
        if(!$KEY_ID) exit;
        $titles         = array($KEY_NAME, "款号", "编号", "款名", "图片");
        $titles[]       = "单价";
        $titles[]       = "订数";
        $titles[]       = "总价";
        $titles[]       = "客户数";
        $titles[]       = "颜色";
        $titles[]       = "数量";

        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);

        $excel_name     = sprintf("%s%s排名数据", $fairname, $KEY_NAME);
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));

        $dbh        =   Flight::DatabaseMaster();
        $fields     = "p.id,p.classes_id,p.category_id,p.series_id,p.kuanhao, p.bianhao, p.name, p.price,p.defaultimage, GROUP_CONCAT(DISTINCT pc.color_id) as color_ids,sum( o.num ) as num, sum(o.num*p.price) as sum_price, count(DISTINCT o.user_id) as unum";
        $tablename  = "product as p left join product_color as pc on p.id=pc.product_id left join orderlist as o on pc.product_id=o.product_id and pc.color_id=o.product_color_id";
        $group      = "p.id";
        $order      = $KEY_ID.",num desc";
        $where      = "p.status <> 0";
        $sql        = "SELECT {$fields} FROM {$tablename} WHERE {$where} GROUP BY {$group} ORDER BY {$order}";
        $sth        = $dbh->prepare($sql);
        $sth->execute();
        $total_num      = 0;
        $total_price    = 0;
        $sheet          = $ExcelWriter->getActiveSheet();
        $line           = 2;
        while($row  = $sth->fetch()){
            $color_ids      =   explode(",", $row['color_ids']);
            $color_list     =   array();
            $color_num      =  0;
            foreach ($color_ids as $color_id) {
                $color_list[$color_id]['name']  =   Keywords::cache_get(array($color_id));
                $sql2       = "SELECT sum(o.num) as num FROM orderlist as o WHERE o.product_id={$row['id']} AND o.product_color_id={$color_id} ";
                $sth2       =   $dbh->prepare($sql2);
                $sth2->execute();
                $colororder = $sth2->fetch(); 
                $color_list[$color_id]['num']   =   $colororder['num'];
                $color_num++;
            }
            usort($color_list, function($a,$b){
                return $a['num'] < $b['num'];
            });

            $merge_line = $line+$color_num-1;

            $sheet->mergeCells("A{$line}:A{$merge_line}");
            $sheet->mergeCells("B{$line}:B{$merge_line}");
            $sheet->mergeCells("C{$line}:C{$merge_line}");
            $sheet->mergeCells("D{$line}:D{$merge_line}");
            $sheet->mergeCells("E{$line}:E{$merge_line}");
            $sheet->mergeCells("F{$line}:F{$merge_line}");
            $sheet->mergeCells("G{$line}:G{$merge_line}");
            $sheet->mergeCells("H{$line}:H{$merge_line}");
            $sheet->mergeCells("I{$line}:I{$merge_line}");

            $sheet->SetCellValue("A{$line}",Keywords::cache_get(array($row[$KEY_ID])));
            $sheet->SetCellValue("B{$line}",$row['kuanhao']);
            $sheet->SetCellValue("C{$line}",$row['bianhao']);
            $sheet->SetCellValue("D{$line}",$row['name']);
            $ExcelWriter->setCellImage("E{$line}",DOCUMENT_ROOT . "thumb/75/{$row['defaultimage']}");
            $sheet->SetCellValue("F{$line}",$row['price']);
            $sheet->SetCellValue("G{$line}",$row['num']>>0);
            $sheet->SetCellValue("H{$line}",$row['sum_price']>>0);
            $sheet->SetCellValue("I{$line}",$row['unum']);

            $color_line  =   $line;
            foreach ($color_list as $color) {
                $sheet->SetCellValue("J{$color_line}",$color['name']);
                $sheet->SetCellValue("K{$color_line}",$color['num']);
                $color_line++;
            }

            $sheet->getRowDimension($line)->setRowHeight(80);

            $line           += $color_num;
            $total_num      += $row['num'];
            $total_price    += $row['sum_price'];
        }
        $line++;
        $sheet->SetCellValue("A{$line}","总计");
        $line++;
        $sheet->SetCellValue("A{$line}","订数");
        $sheet->SetCellValue("A{$line}",$total_num);
        $line++;
        $sheet->SetCellValue("A{$line}","金额");
        $sheet->SetCellValue("A{$line}",$total_price);
    }

    public static function Action_company($r){
        Flight::validateEditorHasLogin();

        $Factory        = new ProductsAttributeFactory('size');
        $size_list_all  = $Factory->getAllList();
        $titles         = array("款号", "款名", "图片", "颜色");
        foreach($size_list_all as $size_row){
            $titles[]   = $size_row['keywords']['name'];
        }
        $titles[]       = "合计";
        $titles[]       = "单价";
        $titles[]       = "总价";
        $titles[]       = "客户数";

        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);

        $excel_name     = sprintf("%s总订单数据-款色明细表", $fairname);
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));

        $dbh    = Flight::DatabaseMaster();
        $tablename  = "orderlist AS o LEFT JOIN product AS p ON o.product_id = p.id";
        $where  = "p.status<>0";
        $sql    = "SELECT p.kuanhao, p.name, p.price, p.defaultimage, o.product_id, o.product_color_id, sum( o.num ) as num, sum(o.num*p.price) as sum_price, count(DISTINCT o.user_id) as unum";
        $sql    .= " FROM {$tablename}";
        $sql    .= " WHERE {$where} GROUP BY o.product_id, o.product_color_id ORDER BY p.bianhao ASC";
        $sth    = $dbh->prepare($sql);
        $sth->execute();

        $total_num      = 0;
        $total_price    = 0;

        while($row  = $sth->fetch()){
            $data   = array();
            $data[] = $row['kuanhao'];
            $data[] = $row['name'];
            $data[] = array('type'=>'Image', 'data'=>DOCUMENT_ROOT . "thumb/210/{$row['defaultimage']}");
            $data[] = Keywords::cache_get(array($row['product_color_id']));
            $size_hash  = array();
            $sql    = "SELECT SUM(o.num) AS num, o.product_size_id FROM {$tablename} "
                    . "WHERE {$where} AND o.product_id={$row['product_id']} AND o.product_color_id={$row['product_color_id']} "
                    . "GROUP BY o.product_id, o.product_color_id, o.product_size_id";
            $sth1   = $dbh->prepare($sql);
            $sth1->execute();
            while($info = $sth1->fetch()){
                $size_hash[$info['product_size_id']]    = $info['num'];
            }
            foreach($size_list_all as $size_row){
                $num    = $size_hash[$size_row['keyword_id']];
                $data[] = $num;
            }
            $data[] = $row['num'];
            $data[] = $row['price'];
            $data[] = $row['sum_price'];
            $data[] = $row['unum'];
            $ExcelWriter->row($data, array('height'=>210));

            $total_num      += $row['num'];
            $total_price    += $row['sum_price'];
        }

        $ExcelWriter->row(array("", ""));
        $ExcelWriter->row(array("", ""));

        $ExcelWriter->row(array("总计", ""));
        $ExcelWriter->row(array("订数", $total_num));
        $ExcelWriter->row(array("金额", $total_price));
    }

    public static function Action_company_group($r){
        Flight::validateEditorHasLogin();

        $Factory        = new ProductsAttributeFactory('size');
        $size_list_all  = $Factory->getAllList();
        $titles         = array("款号", "款名", "图片");
        foreach($size_list_all as $size_row){
            $titles[]   = $size_row['keywords']['name'];
        }
        $titles[]       = "合计";
        $titles[]       = "单价";
        $titles[]       = "总价";
        $titles[]       = "客户数";

        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);

        $excel_name     = sprintf("%s汇总数据-单款明细表", $fairname);
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));

        $dbh    = Flight::DatabaseMaster();
        $tablename  = "orderlist AS o LEFT JOIN product AS p ON o.product_id = p.id";
        $where      = "p.status<>0";
        $sql    = "SELECT p.kuanhao, p.name, p.price, p.defaultimage, o.product_id, sum( o.num ) as num, sum(o.num*p.price) as sum_price, count(DISTINCT o.user_id) as unum";
        $sql    .= " FROM {$tablename}";
        $sql    .= " WHERE {$where} GROUP BY o.product_id ORDER BY num DESC";
        $sth    = $dbh->prepare($sql);
        $sth->execute();

        $total_num      = 0;
        $total_price    = 0;

        while($row  = $sth->fetch()){
            $data   = array();
            $data[] = $row['kuanhao'];
            $data[] = $row['name'];
            $data[] = array("type"=>"Image", "data"=>DOCUMENT_ROOT. "thumb/210/{$row['defaultimage']}");
            $size_hash  = array();
            $sql    = "SELECT SUM(o.num) AS num, o.product_size_id FROM {$tablename} "
                    . "WHERE {$where} AND o.product_id={$row['product_id']} "
                    . "GROUP BY o.product_id, o.product_size_id";
            $sth1   = $dbh->prepare($sql);
            $sth1->execute();
            while($info = $sth1->fetch()){
                $size_hash[$info['product_size_id']]    = $info['num'];
            }
            foreach($size_list_all as $size_row){
                $num    = $size_hash[$size_row['keyword_id']];
                $data[] = $num;
            }
            $data[] = $row['num'];
            $data[] = $row['price'];
            $data[] = $row['sum_price'];
            $data[] = $row['unum'];
            $ExcelWriter->row($data, array("height"=>210));

            $total_num      += $row['num'];
            $total_price    += $row['sum_price'];
        }

        $ExcelWriter->row(array("", ""));
        $ExcelWriter->row(array("", ""));

        $ExcelWriter->row(array("总计", ""));
        $ExcelWriter->row(array("订数", $total_num));
        $ExcelWriter->row(array("金额", $total_price));

    }

    public static function Action_detail($r){

        $Factory        = new ProductsAttributeFactory('size');
        $size_list_all  = $Factory->getAllList();
        //$titles         = array("所属", "客户","款号", "款名", "颜色", "圆盘号");
        $titles         = array("客户","款号", "款名", "颜色", "颜色代码");
        foreach($size_list_all as $size_row){
            $titles[]   = $size_row['keywords']['name'];
        }
        $titles[]       = "合计";
        $titles[]       = "单价";
        $titles[]       = "总价";
        $titles[]       = "折后";
    $un = $r->data->un;

        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);

        $excel_name     = sprintf("%s-%s客户订单数据", $un, $fairname);
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));

        $dbh    = Flight::DatabaseMaster();
        $tablename  = "orderlist AS o LEFT JOIN product AS p ON o.product_id = p.id left join user as u on o.user_id=u.id left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id left join location as l on u.area2=l.id left join user_slave as us on o.user_id=us.user_slave_id";
        //$where      = "p.status =1 and u.username like '$un%'"; 
    $condition[]    = "status<>0";
    if($un) $condition[]    = $un;
        $where      = implode(" AND ", $condition); 
        $sql    = "SELECT l.name as lname,u.name as uname, p.kuanhao, p.name, p.price, o.user_id, o.product_id, o.product_color_id, SUM( o.num ) as num, SUM(o.num*p.price) as sum_price, pc.skc_id , SUM(o.num*p.price* IF(p.category_id=0, 0.35, u.discount)) as discount_price";
        $sql    .= " FROM {$tablename}";
        $sql    .= " WHERE {$where} GROUP BY o.user_id, o.product_id, o.product_color_id ORDER BY u.id, p.bianhao ASC";
        $sth    = $dbh->prepare($sql);
        $sth->execute();

        while($row  = $sth->fetch()){
            $data   = array();
            //$data[] = $row['lname'];
            $data[] = $row['uname'];
            $data[] = $row['kuanhao'];
            $data[] = $row['name'];
        $color = Keywords::cache_get(array($row['product_color_id']));
        $data[] = $color;
            $size_hash  = array();
        $sql    = "SELECT o.num, o.product_size_id FROM {$tablename} "
                    . "WHERE {$where} AND o.user_id={$row['user_id']} AND o.product_id={$row['product_id']} AND o.product_color_id={$row['product_color_id']} "
                    . "GROUP BY o.user_id, o.product_id, o.product_color_id, o.product_size_id";
            $sth1   = $dbh->prepare($sql);
            $sth1->execute();
            while($info = $sth1->fetch()){
                $size_hash[$info['product_size_id']]    = $info['num'];
            }
            foreach($size_list_all as $size_row){
                $num    = $size_hash[$size_row['keyword_id']];
                $data[] = $num;
            }
            $data[] = $row['num'];
            $data[] = $row['price'];
            $data[] = $row['sum_price'];
            $data[] = $row['discount_price'];
            $ExcelWriter->row($data);
        unset($data);
        unset($size_hash);
        }
    }

    public static function Action_detail_zongdai($r){
        Flight::validateEditorHasLogin();

        $Factory        = new ProductsAttributeFactory('size');
        $size_list_all  = $Factory->getAllList();
        $titles         = array("客户","款号", "款名", "颜色", "圆盘号");
        $titles         = array("客户","款号", "编号", "圆牌号", "款名", "颜色", "波段", "大类", "小类");
        foreach($size_list_all as $size_row){
            $titles[]   = $size_row['keywords']['name'];
        }
        $titles[]       = "合计";
        $titles[]       = "单价";
        $titles[]       = "总价";

        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);

        $excel_name     = sprintf("%s总代订单数据", $fairname);
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));

        $dbh    = Flight::DatabaseMaster();
        $tablename  = "orderlist AS o LEFT JOIN product AS p ON o.product_id = p.id left join user_slave as us on o.user_id=us.user_slave_id left join user as u on us.user_id=u.id left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id";
        $where      = "p.status<>0";
        $sql    = "SELECT u.name as uname, p.kuanhao, p.name, p.price, p.bianhao, p.category_id,p.wave_id,p.classes_id, us.user_id, o.product_id, o.product_color_id, SUM( o.num ) as num, SUM(o.num*p.price) as sum_price,pc.skc_id";
        $sql    .= " FROM {$tablename}";
        $sql    .= " WHERE {$where} AND u.type=2 GROUP BY us.user_id, o.product_id, o.product_color_id ORDER BY u.id, p.bianhao ASC";
        $sth    = $dbh->prepare($sql);
        $sth->execute();

        while($row  = $sth->fetch()){
            $data   = array();
            $data[] = $row['uname'];
            $data[] = $row['kuanhao'];
            $data[] = $row['bianhao'];
            $data[] = $row['skc_id'];
            $data[] = $row['name'];
            $data[] = Keywords::cache_get(array($row['product_color_id']));
            $data[] = Keywords::cache_get(array($row['wave_id']));
            $data[] = Keywords::cache_get(array($row['category_id']));
            $data[] = Keywords::cache_get(array($row['classes_id']));
            $size_hash  = array();
            $sql    = "SELECT SUM(o.num) as num, o.product_size_id FROM {$tablename} "
                    . "WHERE {$where} AND us.user_id={$row['user_id']} AND o.product_id={$row['product_id']} AND o.product_color_id={$row['product_color_id']} "
                    . "GROUP BY o.product_id, o.product_color_id, o.product_size_id";
            $sth1   = $dbh->prepare($sql);
            $sth1->execute();
            while($info = $sth1->fetch()){
                $size_hash[$info['product_size_id']]    = $info['num'];
            }
            foreach($size_list_all as $size_row){
                $num    = $size_hash[$size_row['keyword_id']];
                $data[] = $num;
            }
            $data[] = $row['num'];
            $data[] = $row['price'];
            $data[] = $row['sum_price'];
            $ExcelWriter->row($data);
        }
    }



    public static function Action_detail_yiyou($r){
        Flight::validateEditorHasLogin();

        $User       = new User;
        $Company    = new Company;
        $userlist   = $User->find("type=1", array("limit"=>10000));
        $Factory        = new ProductsAttributeFactory('size');
        $size_group_list    = $Factory->get_group_list();
        foreach($size_group_list as $s){
            $tmpl_array[$s['group_id']][]   = $s['keyword_id'];
        }

        $newSizeList    = array();
        $newSizeHash    = array();
        $newSizeLength  = 0;
        foreach($tmpl_array as $group_id => $ary){
            for($i = 0, $len = count($ary); $i < $len; $i++){
                $size_id    = $ary[$i];
                $newSizeList[$i][]  = $size_id;
                $newSizeHash[$size_id]  = $i;
            }
            if($len > $newSizeLength){
                $newSizeLength = $len;
            }
        }


        $dbh        = Flight::DatabaseMaster();
        $d          = sprintf('/tmpl/excel/%s', date('YmdHis'));
        $dir        = sprintf('%s%s', DOCUMENT_ROOT, $d);
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        $files      = array();
        $excel_name     = "依友格式单店数据";
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array());
        foreach($userlist as $u){
            $user_id    = $u['id'];
            $sql    = "SELECT p.kuanhao, o.product_color_id, pc.color_code, GROUP_CONCAT( o.product_size_id, ':', o.num ) as size_num";
            $sql   .= " FROM orderlist as o LEFT JOIN product as p on o.product_id=p.id LEFT JOIN product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id";
            $sql   .= " WHERE p.status<>0 AND o.user_id=$user_id GROUP BY o.product_id, o.product_color_id ORDER BY p.bianhao ASC";
            $sth    = $dbh->prepare($sql);
            $sth->execute();

            $linecount      = 0;
            while($row  = $sth->fetch()){
                $linecount++;
                $data   = array();
                $data[] = $u['name'];
                $data[] = $row['kuanhao'];
                $data[] = "";
                $data[] = "[".$row['color_code']. "]";
                $data[] = Keywords::cache_get(array($row['product_color_id']));
                // $data[] = "";
                // $data[] = "";

                $F_list     = preg_split('/,|:/', $row['size_num']);
                $mysize_list    = array_pad(array(), $newSizeLength, '');
                for($i = 0, $len = count($F_list); $i < $len; $i += 2){
                    $size_id    = $F_list[$i];
                    $size_num   = $F_list[$i+1];
                    $size_hash_num      = $newSizeHash[$size_id];
                    $mysize_list[$size_hash_num]  += $size_num;
                }
                foreach($mysize_list as $mysize){
                    $data[]     = $mysize;
                }

                $ExcelWriter->row($data);
            }
            // unset($ExcelWriter);
            // $string = ob_get_contents();
            // $filename = iconv("UTF-8", "GBK", $excel_name). '.xls';
            // $fullname = $dir . '/' . $filename;
            // file_put_contents($fullname, $string);
            // if($linecount) $files[]    = $filename;
            // ob_end_clean();
        }

        // chdir($dir);
        // $zip_name = "依友格式".$Company->name . "单店数据.zip";
        // $shell  = "zip -rm {$zip_name} " . implode(' ', $files);
        // system($shell);
        // Flight::redirect($d ."/". $zip_name);
    }

    public static function Action_detail_baisheng($r){
        Flight::validateEditorHasLogin();

        $User       = new User;
        $Company    = new Company;
        $userlist   = $User->find("type=1", array("limit"=>10000));
        $Factory        = new ProductsAttributeFactory('size');
        $size_group_list    = $Factory->get_group_list();
        foreach($size_group_list as $s){
            $tmpl_array[$s['group_id']][]   = $s['keyword_id'];
        }

        $newSizeList    = array();
        $newSizeHash    = array();
        $newSizeLength  = 0;
        foreach($tmpl_array as $group_id => $ary){
            for($i = 0, $len = count($ary); $i < $len; $i++){
                $size_id    = $ary[$i];
                $newSizeList[$i][]  = $size_id;
                $newSizeHash[$size_id]  = $i;
            }
            if($len > $newSizeLength){
                $newSizeLength = $len;
            }
        }

        $dbh        = Flight::DatabaseMaster();
        $excel_name     = "客户数据-百盛格式";
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array());
        foreach($userlist as $u){
            $user_id    = $u['id'];
            $sql    = "SELECT p.kuanhao, p.name,p.price, o.product_color_id, o.product_size_id,o.num";
            $sql   .= " FROM orderlist as o LEFT JOIN product as p on o.product_id=p.id";
            $sql   .= " WHERE p.status<>0 AND o.user_id=$user_id ORDER BY p.bianhao ASC";
            $sth    = $dbh->prepare($sql);
            $sth->execute();

            $hash   = array();
            while($row  = $sth->fetch()){
                $kuanhao    = $row['kuanhao'];
                $color_id   = $row['product_color_id'];
                $size_id    = $row['product_size_id'];
                $size_hash_num      = $newSizeHash[$size_id];
                if(!$hash[$kuanhao][$color_id]){
                    $hash[$kuanhao][$color_id] = $row;
                    $hash[$kuanhao][$color_id]['mysize_list']   = array_pad(array(), $newSizeLength, '');
                }
                $hash[$kuanhao][$color_id]['mysize_list'][$size_hash_num]   += $row['num'];
                $hash[$kuanhao][$color_id]['n'] += $row['num'];
            }
            foreach($hash as $kuanhao => $r1) {
                foreach($r1 as $color_id => $r2) {
                    $data   = array();
                    $data[] = $u['name'];//用户名
                    $data[] = $r2['kuanhao'];//款号
                    $data[] = $r2['name'];//款名
                    $data[] = "";//单位
                    $data[] = "";//颜色代码
                    $data[] = Keywords::cache_get(array($r2['product_color_id']));//颜色
                    $data[] = "";//尺码档
                    foreach($r2['mysize_list'] as $num) {
                        $data[] = $num;
                    }
                    $data[] = $r2['n'];
                    $data[] = $r2['price'];
                    $ExcelWriter->row($data);
                }
            }
        }
    }

    public static function Action_detail_yiyou_zongdai($r){
        Flight::validateEditorHasLogin();

        $User       = new User;
        $Company    = new Company;
        $userlist   = $User->find("type=2", array("limit"=>10000));
        $Factory        = new ProductsAttributeFactory('size');
        $size_group_list    = $Factory->get_group_list();
        foreach($size_group_list as $s){
            $tmpl_array[$s['group_id']][]   = $s['keyword_id'];
        }

        $newSizeList    = array();
        $newSizeHash    = array();
        $newSizeLength  = 0;
        foreach($tmpl_array as $group_id => $ary){
            for($i = 0, $len = count($ary); $i < $len; $i++){
                $size_id    = $ary[$i];
                $newSizeList[$i][]  = $size_id;
                $newSizeHash[$size_id]  = $i;
            }
            if($len > $newSizeLength){
                $newSizeLength = $len;
            }
        }


        $dbh        = Flight::DatabaseMaster();
        $d          = sprintf('/tmpl/excel/%s', date('YmdHis'));
        $dir        = sprintf('%s%s', DOCUMENT_ROOT, $d);
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        $files      = array();
        $excel_name     = "依友格式总代数据";
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array());
        foreach($userlist as $u){
            $user_id    = $u['id'];
            $sql    = "SELECT p.kuanhao, pc.color_code, o.product_color_id, o.product_id";
            $sql   .= " FROM orderlist as o LEFT JOIN product as p on o.product_id=p.id left join user as u on u.id=o.user_id left join user_slave as us on u.id=us.user_slave_id left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id";
            $sql   .= " WHERE p.status<>0 AND us.user_id=$user_id GROUP BY o.product_id, o.product_color_id ORDER BY p.bianhao ASC";
            $sth    = $dbh->prepare($sql);
            $sth->execute();

            $linecount      = 0;
            while($row  = $sth->fetch()){
                $linecount++;
                $data   = array();
                $data[] = $u['name'];
                $data[] = $row['kuanhao'];
                $data[] = "";
                $data[] = "[". $row['color_code'] ."]";
                $data[] = Keywords::cache_get(array($row['product_color_id']));
                // $data[] = "";
                // $data[] = "";

                $sql    = "SELECT SUM(o.num) as num, o.product_size_id FROM orderlist as o LEFT JOIN product as p on o.product_id=p.id left join user as u on u.id=o.user_id left join user_slave as us on u.id=us.user_slave_id "
                        . "WHERE p.status=1 AND us.user_id={$user_id} AND o.product_id={$row['product_id']} AND o.product_color_id={$row['product_color_id']} "
                        . "GROUP BY o.product_id, o.product_color_id, o.product_size_id";
                $sth1   = $dbh->prepare($sql);
                $sth1->execute();
                $mysize_list    = array_pad(array(), $newSizeLength, '');
                while($info = $sth1->fetch()){
                    $size_id    = $info['product_size_id'];
                    $size_num   = $info['num'];
                    $size_hash_num      = $newSizeHash[$size_id];
                    $mysize_list[$size_hash_num]  += $size_num;
                }
                foreach($mysize_list as $mysize){
                    $data[]     = $mysize;
                }

                $ExcelWriter->row($data);
            }
            // unset($ExcelWriter);
            // $string = ob_get_contents();
            // $filename = iconv("UTF-8", "GBK", $excel_name). '.xls';
            // $fullname = $dir . '/' . $filename;
            // file_put_contents($fullname, $string);
            // if($linecount) $files[]    = $filename;
            // ob_end_clean();
        }

        // chdir($dir);
        // $zip_name = "依友格式".$Company->name . "总代数据.zip";
        // $shell  = "zip -rm {$zip_name} " . implode(' ', $files);
        // system($shell);
        // Flight::redirect($d ."/". $zip_name);
    }

    public static function Action_store($r){
        Flight::validateEditorHasLogin();

        $titles         = array("款号", "款名", "图片", "五星", "四星", "三星", "二星", "一星", "五星", "四星", "三星", "二星", "一星");

        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);

        $excel_name     = sprintf("%s收藏品款数据", $fairname);
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));

        $dbh    = Flight::DatabaseMaster();
        $tablename  = "user_product as up left join product as p on up.product_id=p.id left join user as u on up.user_id=u.id";
        $where      = "p.status<>0";
        $sql    = "SELECT ";
        $sql    .= "(SELECT COUNT(*) FROM {$tablename} WHERE {$where} AND up.rateval=1 AND up.product_id=product.id) as num1,";
        $sql    .= "(SELECT GROUP_CONCAT(u.name) FROM {$tablename} WHERE {$where} AND up.rateval=1 AND up.product_id=product.id) as name1,";

        $sql    .= "(SELECT COUNT(*) FROM {$tablename} WHERE {$where} AND up.rateval=2 AND up.product_id=product.id) as num2,";
        $sql    .= "(SELECT GROUP_CONCAT(u.name) FROM {$tablename} WHERE {$where} AND up.rateval=2 AND up.product_id=product.id) as name2,";

        $sql    .= "(SELECT COUNT(*) FROM {$tablename} WHERE {$where} AND up.rateval=3 AND up.product_id=product.id) as num3,";
        $sql    .= "(SELECT GROUP_CONCAT(u.name) FROM {$tablename} WHERE {$where} AND up.rateval=3 AND up.product_id=product.id) as name3,";

        $sql    .= "(SELECT COUNT(*) FROM {$tablename} WHERE {$where} AND up.rateval=4 AND up.product_id=product.id) as num4,";
        $sql    .= "(SELECT GROUP_CONCAT(u.name) FROM {$tablename} WHERE {$where} AND up.rateval=4 AND up.product_id=product.id) as name4,";

        $sql    .= "(SELECT COUNT(*) FROM {$tablename} WHERE {$where} AND up.rateval=5 AND up.product_id=product.id) as num5,";
        $sql    .= "(SELECT GROUP_CONCAT(u.name) FROM {$tablename} WHERE {$where} AND up.rateval=5 AND up.product_id=product.id) as name5,";

        $sql    .= "name,bianhao,kuanhao,defaultimage";
        $sql    .= " FROM product";
        $sql    .= " WHERE status=1 order by bianhao";
        $sth    = $dbh->prepare($sql);
        $sth->execute();

        while($row  = $sth->fetch()){
            $data   = array();
            $data[] = $row['kuanhao'];
            $data[] = $row['name'];
            if($row['defaultimage']){
                $img    = DOCUMENT_ROOT."thumb/210/{$row['defaultimage']}";
                $data[] = array('type'=>'Image', 'data'=>$img);
            }else{
                $data[] = '';
            }
            $data[] = $row['num5'];
            $data[] = $row['num4'];
            $data[] = $row['num3'];
            $data[] = $row['num2'];
            $data[] = $row['num1'];
            $data[] = $row['name5'];
            $data[] = $row['name4'];
            $data[] = $row['name3'];
            $data[] = $row['name2'];
            $data[] = $row['name1'];
            $ExcelWriter->row($data, array('height'=>210));
        }
    }

    public static function Action_order_book_1($r){
        Flight::validateEditorHasLogin();

        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);

        $excel_name     = sprintf("%s订货本", $fairname);

        $dbh    = Flight::DatabaseMaster();
        $fields         = "p.*, GROUP_CONCAT(pc.color_id) as color_ids";
        $tablename      = "product as p left join product_color as pc on p.id=pc.product_id";
        $where          = "p.status<>0";
        $group          = "pc.product_id";
        $order          = "p.bianhao asc";
        $sql    = "SELECT {$fields} FROM {$tablename} WHERE {$where} GROUP BY {$group} ORDER BY {$order}";
        $sth    = $dbh->prepare($sql);
        $sth->execute();

        $titles = array("款号", "款名", "波段", "大类", "图片", "单价", "颜色");
        $Factory        = new ProductsAttributeFactory('size');
        $size_list_all  = $Factory->getAllList();
        foreach($size_list_all as $size_row) {
            $titles[]   = $size_row['keywords']['name'];
        }
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));
        $num    = 2;
        $sheet  = $ExcelWriter->getActiveSheet();
        while($row  = $sth->fetch()){
            $color_ids      = explode(",", $row['color_ids']);
            $color_list     = array();
            foreach($color_ids as $color_id){
                $color_list[]   = Keywords::cache_get(array($color_id));
            }
            $lines  = count($color_list);
            if($lines < 4){
                $lines = 4;
            }
            if($lines){
                $KEY1   = $num;
                $KEY2   = $num + $lines - 1;
                $sheet->mergeCells("A{$KEY1}:A{$KEY2}");
                $sheet->mergeCells("B{$KEY1}:B{$KEY2}");
                $sheet->mergeCells("C{$KEY1}:C{$KEY2}");
                $sheet->mergeCells("D{$KEY1}:D{$KEY2}");
                $sheet->mergeCells("E{$KEY1}:E{$KEY2}");
                $sheet->mergeCells("F{$KEY1}:F{$KEY2}");
                $sheet->SetCellValue("A{$KEY1}", $row['kuanhao']);
                $sheet->SetCellValue("B{$KEY1}", $row['name']);
                $sheet->SetCellValue("C{$KEY1}", Keywords::cache_get(array($row['wave_id'])));
                $sheet->SetCellValue("D{$KEY1}", Keywords::cache_get(array($row['category_id'])));
                $ExcelWriter->setCellImage("E{$KEY1}", DOCUMENT_ROOT . "thumb/75/". $row['defaultimage']);
                $sheet->SetCellValue("F{$KEY1}", $row['price']);
                foreach($color_list as $color){
                    $sheet->SetCellValue("G{$KEY1}", $color);
                    $KEY1++;
                }
                $num += $lines;
            }
        }
        $sheet->getColumnDimension('E')->setWidth(12);
    }

    public static function Action_order_book_2($r){
        set_time_limit(0);
        @ini_set('memory_limit','1024M');

        Flight::validateEditorHasLogin();

        $Company        = new Company;
        $ProductSize    = new ProductSize;
        $fairname       = str_replace(' ', '', $Company->fairname);

        $excel_name     = sprintf("%s订单汇总单", $fairname);

        $dbh    = Flight::DatabaseMaster();
        $fields         = "p.*, GROUP_CONCAT(pc.color_id) as color_ids";
        $tablename      = "product as p left join product_color as pc on p.id=pc.product_id left join products_attr as pa on p.wave_id=pa.keyword_id and pa.field='wave'";
        $where          = "p.status<>0";
        $group          = "pc.product_id";
        $order          = "pa.rank,p.id asc";
        $sql    = "SELECT {$fields} FROM {$tablename} WHERE {$where} GROUP BY {$group} ORDER BY {$order}";
        $sth    = $dbh->prepare($sql);
        $sth->execute();

        $OrderList = new OrderList;
        $options = array();
        $options['fields']="product_id,product_color_id,product_size_id,sum(num) as num";
        $options['group'] ="product_id,product_color_id,product_size_id";
        $options['limit'] = 10000;
        // $options['debug'] = true;
        $pcsorderlist = $OrderList->find("1",$options);
        $pcs_hash    = array();
        foreach ($pcsorderlist as $pcs) {
            $pcs_hash[$pcs['product_id'].'_'.$pcs['product_color_id']."_".$pcs['product_size_id']] = $pcs['num'];
        }

        $OrderListProductColor = new OrderListProductColor;
        $options = array();
        $options['fields']="product_id,product_color_id,num";
        $options['limit'] = 10000;
        $pcorderlist = $OrderListProductColor->find("1",$options);
        $pc_hash    = array();
        foreach ($pcorderlist as $pc) {
            $pc_hash[$pc['product_id']."_".$pc['product_color_id']] = $pc['num'];
        }

        $OrderListProduct = new OrderListProduct;
        $options = array();
        $options['fields']="product_id,num";
        $options['key']   ="product_id";
        $options['limit'] = 10000;
        $p_hash = $OrderListProduct->find("1",$options);

        $ProductSize = new ProductSize;
        $options = array();
        $options['fields'] = "count(size_id) as size_length";
        $options['group']  = "product_id";
        $options['order']  = "size_length desc";
        $options['limit']  = 1;
        $size_length = $ProductSize->findone("1",$options);
        $max_size_length = $size_length['size_length'];

        $titles = array("款号", "款名", "波段", "大类", "图片", "单价", "颜色");
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));
        $num    = 2;
        $sheet  = $ExcelWriter->getActiveSheet();
        $size_key   = sprintf("%c%d",65 + 7 + $max_size_length, 1); //
        $sheet->SetCellValue($size_key, "合计");
        while($row  = $sth->fetch()){
            $color_ids      = explode(",", $row['color_ids']);
            $color_list     = array();
            foreach($color_ids as $color_id){
                $color_list[]   = Keywords::cache_get(array($color_id));
            }
            $lines  = count($color_list);
            if($lines < 2){
                $lines = 2;
            }
            $lines += 2;
            if($lines){
                $KEY1   = $num;
                $KEY2   = $num + $lines - 1;
                $sheet->mergeCells("A{$KEY1}:A{$KEY2}");
                $sheet->mergeCells("B{$KEY1}:B{$KEY2}");
                $sheet->mergeCells("C{$KEY1}:C{$KEY2}");
                $sheet->mergeCells("D{$KEY1}:D{$KEY2}");
                $sheet->mergeCells("E{$KEY1}:E{$KEY2}");
                $sheet->mergeCells("F{$KEY1}:F{$KEY2}");
                $sheet->SetCellValue("A{$KEY1}", $row['kuanhao']);
                $sheet->SetCellValue("B{$KEY1}", $row['name']);
                $sheet->SetCellValue("C{$KEY1}", Keywords::cache_get(array($row['wave_id'])));
                $sheet->SetCellValue("D{$KEY1}", Keywords::cache_get(array($row['category_id'])));
                $ExcelWriter->setCellImage("E{$KEY1}", DOCUMENT_ROOT . "thumb/75/". $row['defaultimage']);
                $sheet->SetCellValue("F{$KEY1}", $row['price']);
                $size_start_num     = 0;
                $size_list  = $ProductSize->get_size_list($row['id']);
                foreach($size_list as $size) {
                    $size_key   = sprintf("%c%d", 65 + 7 + $size_start_num++, $KEY1); //
                    $sheet->SetCellValue($size_key, $size['name']);
                }
                $KEY1++;
                foreach($color_list as $color){
                    $sheet->SetCellValue("G{$KEY1}", $color);
                    $KEY1++;
                }
                $sheet->SetCellValue("G{$KEY1}", "小计");
                $size_start_num = 0;
                foreach ($size_list as $size) {
                    $size_start_num++;
                    $col = $num+1;
                    $size_num = 0;
                    foreach ($color_ids as $color_id) {
                        $size_key   = sprintf("%c%d", 65 + 6 + $size_start_num, $col++); //
                        $sheet->SetCellValue($size_key, $pcs_hash[$row['id'].'_'.$color_id.'_'.$size['size_id']]);
                        $size_num += $pcs_hash[$row['id'].'_'.$color_id.'_'.$size['size_id']];
                    }
                    if($size_num) {
                        $size_key   = sprintf("%c%d", 65 + 6 + $size_start_num, $col++);
                        $sheet->SetCellValue($size_key, $size_num);
                    }
                }
                $col = $num+1;
                foreach ($color_ids as $color_id) {
                    $pc_num = $pc_hash[$row['id'].'_'.$color_id];
                    $size_key   = sprintf("%c%d", 65 + 7 + $max_size_length, $col++);
                    if($pc_num){
                        $sheet->SetCellValue($size_key, $pc_num);
                    }
                }
                $p_num = $p_hash[$row['id']]['num'];
                $size_key = sprintf("%c%d", 65 + 7 + $max_size_length, $col++);
                if($p_num){
                    $sheet->SetCellValue($size_key, $p_num);
                }
                $num += $lines;
            }
        }
        $sheet->getColumnDimension('E')->setWidth(12);
    }

    public static function Action_export ($r) {
        Flight::validateEditorHasLogin();

        $data   = $r->data;
        $sql    = $data->sql;
        $name   = $data->name;
        if($sql){
            $dbh    = Flight::DatabaseMaster();
            $sth    = $dbh->prepare($sql);
            $sth->execute();
            $i      = 0;
            $excel_name     = $name ? $name : '导出数据';
            $ExcelWriter    = new ExcelWriter($excel_name, $options=array());
            while($row  = $sth->fetch()){
                if($i++ == 0){
                    $titles = array_keys($row);
                    $ExcelWriter->row($titles);
                }
                $ExcelWriter->row(array_values($row));
            }
        }else{
            Flight::display("data/export.html", $result);
        }
    }

    public static function Action_export_sql($r) {
        $data   = $r->query;
        $key    = $data->key;
        if($key) {
            if(is_file($filename = DOCUMENT_ROOT . "haodingtong/Config/SQL/{$key}.txt")){
                echo file_get_contents($filename);
            }
        }
    }
    
    public static function Action_product_info($r){
        set_time_limit(0);
        @ini_set('memory_limit','1024M');
        
        Flight::validateEditorHasLogin();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        $dbh    = Flight::DatabaseMaster();
        //获取颜色
        $get_color_sql = 'SELECT color_id,color_code FROM product_color WHERE 1 GROUP BY color_id';
        $sth_color = $dbh->prepare($get_color_sql);
        $sth_color->execute();
        $sth_color_array = $fetch_result($sth_color);
        foreach($sth_color_array as $skey=>$sval){
            $sth_color_array[$skey]['color_name'] = Keywords::cache_get(array($sval['color_id']));
        }
        
        //获取大类
        $Factory_category        = new ProductsAttributeFactory('category');
        $category_group_list    = $Factory_category->getAllList();
        
        //获取尺寸
        $Factory_size        = new ProductsAttributeFactory('size');
        $size_group_list    = $Factory_size->getAllList();
        
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
        
        $excel_name     = sprintf("%s产品资料表", $fairname);
        $titles = array('色号','颜色','','大类','大类码','','尺码','尺码代码');   
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles,'PageLimit'=>'0'));
        
        //生成基础编码表
        foreach($sth_color_array as $scak=>$scav){
            $ExcelWriter->setCellVaule(0, $scak+2, $scav['color_code']);
            $ExcelWriter->setCellVaule(1, $scak+2, $scav['color_name']);
        }
        
        foreach($category_group_list as $cglk=>$cglv){
            $ExcelWriter->setCellVaule(3, $cglk+2, $cglv['keywords']['name']);
            $ExcelWriter->setCellVaule(4, $cglk+2, $cglv['keyword_id']);
        }
        
        foreach($size_group_list as $sglk=>$sglv){
            $ExcelWriter->setCellVaule(6, $sglk+2, $sglv['keywords']['name']);
            $ExcelWriter->setCellVaule(7, $sglk+2, $sglv['keyword_id']);
        }
               
        $ExcelWriter->setTitle('基础编码表');
        //基础编码表部分结束
        //开始产品资料表
        $ExcelWriter->newSheet(1,'产品资料表');
        $titles = array('款状态','编号*','圆牌号*','款号*','货号','款名*','价格*','买断价','颜色*','色号*','款色起订量','必订款','主推款','尺码组','尺码*','固定配比','按箱订货','连码起订','价格带','季节','系列*','大类*','波段*','中类','小类','款别','主题','品牌','上下装','面料','上货时间','卖点','性别','内外搭','设计师','长短款','版型','轮廓','价格1','价格2','自定义1','自定义2','自定义3','自定义4','自定义5','期现货(1为期货,2为现货)','爆款');
        $ExcelWriter->row($titles);
        
        $get_product_sql = 'select pc.product_id, pc.color_id, pc.skc_id, pc.color_code,pc.is_need as pc_need,pc.main_push_id,pc.status as pc_status,pc.mininum as pc_mininum, p.* from product_color pc , product p where pc.product_id = p.id order by  p.id ';
        $sth_product = $dbh->prepare($get_product_sql);
        $sth_product->execute();
        $sth_product_array = $fetch_result($sth_product);
        //print_r($sth_product_array);
        $product_size = new ProductSize();      
        foreach($sth_product_array as $spak=>$spav){
            $colnum = 0;
            // $pc_status = $spav['pc_status'] == 1;
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['pc_status']  ? "正常款" : "删除款");
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['bianhao']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['skc_id']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['kuanhao']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['huohao']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['name']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['price']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['price_purchase']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['color_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['color_code']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['pc_mininum']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['pc_need']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get($spav['main_push_id']));
            $psize = $product_size->get_size_list_str($spav['product_id']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $psize['group']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $psize['str']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['proportion_list']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['basenum']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['order_start_num']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get($spav['price_band_id']));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['season_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['series_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['category_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['wave_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['medium_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['classes_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['style_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['theme_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['brand_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['sxz_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['fabric_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['date_market']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['content']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['nannvzhuan_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['neiwaida_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['designer_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['changduankuan_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['edition_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['contour_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['price_1']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['price_2']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['df1_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['df2_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['df3_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['df4_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, Keywords::cache_get(array($spav['df5_id'])));
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['isspot']);
            $ExcelWriter->setCellVaule($colnum++, $spak+2, $spav['hot']);
        }
        
        $ExcelWriter->newSheet(2,'搭配资料表');   
        //new ProductGroupMember(); 
        //搭配资料表
        $get_group_sql = 'SELECT id,dp_num,name,dp_type,dp_type2 FROM product_group WHERE 1 order by id';
        $sth_group = $dbh->prepare($get_group_sql);
        $sth_group->execute();
        $sth_group_array = $fetch_result($sth_group);
        $titles = array('搭配号','搭配名','波段','分组','成员');
        $ExcelWriter->row($titles);
        $productGroup = new ProductGroupMember();
        $productColor = new ProductColor();
        $product = new Product();
        foreach($sth_group_array as $sgak=>$sgav){
            $ExcelWriter->setCellVaule(0, $sgak+2, $sgav['dp_num']);
            $ExcelWriter->setCellVaule(1, $sgak+2, $sgav['name']);
            $ExcelWriter->setCellVaule(2, $sgak+2, Keywords::cache_get($sgav['dp_type']));
            $ExcelWriter->setCellVaule(3, $sgak+2, Keywords::cache_get($sgav['dp_type2']));
            $pglist = $productGroup->find('group_id="'.$sgav['id'].'"',array('limit'=>1000));
            $plist = '';
            
            foreach($pglist as $pglv){
                if($pglv['color_id']==0){
                    $pinfo = $product->findone('id="'.$pglv['product_id'].'"',array('fields'=>'bianhao'));
                    if(sizeof($pinfo)){
                        $plist.=$pinfo['bianhao'].';';
                    }
                }else{
                    $skc_get = $productColor->findone('product_id="'.$pglv['product_id'].'" AND color_id="'.$pglv['color_id'].'"');
                    if(sizeof($skc_get)){
                        $plist.=$skc_get['skc_id'].';';
                    }
                }
            }
            $plist = rtrim($plist,';');
            $ExcelWriter->setCellVaule(4, $sgak+2, $plist);
        }
        
        $ExcelWriter->newSheet(3,'陈列资料表');
        //陈列资料表
        $get_group_sql = 'SELECT id,bianhao,name,pd_type,pd_type2 FROM product_display WHERE 1 order by id';
        $sth_group = $dbh->prepare($get_group_sql);
        $sth_group->execute();
        $sth_group_array = $fetch_result($sth_group);
        $titles = array('陈列号','陈列名','波段','分组','说明');
        $ExcelWriter->row($titles);
        $productDisplay = new ProductDisplayMemberColor();
        $productColor = new ProductColor();
        foreach($sth_group_array as $sgak=>$sgav){
            $ExcelWriter->setCellVaule(0, $sgak+2, $sgav['bianhao']);
            $ExcelWriter->setCellVaule(1, $sgak+2, $sgav['name']);
            $ExcelWriter->setCellVaule(2, $sgak+2, Keywords::cache_get($sgav['pd_type']));
            $ExcelWriter->setCellVaule(3, $sgak+2, Keywords::cache_get($sgav['pd_type2']));
            $pglist = $productDisplay->find('display_id="'.$sgav['id'].'"',array('limit'=>1000));
            $plist = '';
        
            foreach($pglist as $pglv){
                    $skc_get = $productColor->findone('product_id="'.$pglv['product_id'].'" AND color_id="'.$pglv['keyword_id'].'"');
                    if(sizeof($skc_get)){
                        $plist.=$skc_get['skc_id'].';';
                    }
            }
            $plist = rtrim($plist,';');
            $ExcelWriter->setCellVaule(4, $sgak+2, $plist);
        }
        $ExcelWriter->refreshIndex();
    }
    
    public static function Action_customer_info($r){
        Flight::validateEditorHasLogin();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        $dbh    = Flight::DatabaseMaster();
        $get_user_sql = 'SELECT * from user where type=1 or type=2 order by id ';
        $sth_user = $dbh->prepare($get_user_sql);
        $sth_user->execute();
        $sth_user_array = $fetch_result($sth_user,'id');
        
        $get_slave_sql = 'SELECT * from user_slave  ';
        $sth_slave = $dbh->prepare($get_slave_sql);
        $sth_slave->execute();
        $sth_slave_array = $fetch_result($sth_slave,'user_slave_id');
        
        
        $get_location_sql = 'SELECT id,name from location  ';
        $sth_location = $dbh->prepare($get_location_sql);
        $sth_location->execute();
        $sth_location_array = $fetch_result($sth_location,'id');
        
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
        
        $excel_name     = sprintf("%s客户资料表", $fairname);
        $titles = array('客户上级*','客户区域*','客户账号*','客户名称*','客户密码*','客户折扣*','指标数量','指标金额','客户品牌','客户等级','客户类型','有效性');   
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles,'PageLimit'=>'0'));
        $suak = 0;
        foreach($sth_user_array as $suav){         
            $col = 0;
            $muser = '';
            if(isset($sth_slave_array[$suav['id']])){
                $muser = $sth_user_array[$sth_slave_array[$suav['id']]['user_id']]['name'];
            }     
            $ExcelWriter->setCellVaule($col++, $suak+2, $muser);
            $ExcelWriter->setCellVaule($col++, $suak+2, $sth_location_array[$suav['area2']]['name']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['username']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['name']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['password']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['discount']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['exp_num']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['exp_price']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['permission_brand']);
            $ExcelWriter->setCellVaule($col++, $suak+2, Keywords::cache_get(array($suav['user_level'])));           
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['discount_type']?'买断':'正常');
            $ExcelWriter->setCellVaule($col++, $suak+2, '');
            $suak++;
        }
    }
    
    public static function Action_order_info($r){
        set_time_limit(0);
        @ini_set('memory_limit','10024M');
        Flight::validateEditorHasLogin();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        $dbh    = Flight::DatabaseMaster();
        
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
        $Factory        = new ProductsAttributeFactory('size');
        $size_group_list    = $Factory->get_group_list();
        $excel_name     = sprintf("%s订单明细表", $fairname);
        $titles = array('客户上级*','客户区域*','客户账号*','客户折扣*','圆牌号*','编号*','款号*','货号','款名*','波段','系列','大类','小类','色号','颜色*','单价*','买断价','尺码组');
        foreach($size_group_list as $sg){
            $titles[]=Keywords::cache_get(array($sg['keyword_id']));
        }      
        $titles = array_merge($titles,array('合计','折后价','折前金额','折后金额','款别','设计师','主题','品牌','上下装','男女装','内外搭','长短款','上市时间'));
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles,'PageLimit'=>'0','execltype'=>'2007'));
        $OrderList      = new OrderList;
        $dbh    = Flight::DatabaseMaster();
        
        $get_user_sql = 'SELECT * from user where type=1 or type=2 order by id ';
        $sth_user = $dbh->prepare($get_user_sql);
        $sth_user->execute();
        $sth_user_array = $fetch_result($sth_user,'id');
        
        $get_slave_sql = 'SELECT * from user_slave  ';
        $sth_slave = $dbh->prepare($get_slave_sql);
        $sth_slave->execute();
        $sth_slave_array = $fetch_result($sth_slave,'user_slave_id');
               
        $get_location_sql = 'SELECT id,name from location  ';
        $sth_location = $dbh->prepare($get_location_sql);
        $sth_location->execute();
        $sth_location_array = $fetch_result($sth_location,'id');
        //print_r($OrderList->get_user_orderlist_info(4));exit;
        
        $get_att_group_sql = 'SELECT attr_id,group_id from products_attr_group';
        $sth_att_group = $dbh->prepare($get_att_group_sql);
        $sth_att_group->execute();
        $sth_att_group_array = $fetch_result($sth_att_group,'attr_id');
        
        
        
        $get_order_user_sql = 'SELECT user_id from  orderlist  group by user_id order by user_id';
        $sth_order_user = $dbh->prepare($get_order_user_sql);
        $sth_order_user->execute();
        $sth_order_user_array = $fetch_result($sth_order_user);
        $line = 0;
        foreach($sth_order_user_array as $souav){
            $userOrderInfo = $OrderList->get_user_orderlist_info_new($souav['user_id']);
            foreach($userOrderInfo as $uk=>$uv){
                $col = 0;
                $size_array = explode(',', $uv['F']);
                $size_order = array();
                foreach($size_array as $sz){
                    $sizesingle = explode(':', $sz);
                    $size_order[$sizesingle[0]] = $sizesingle[1];                  
                }
                $muser = '';
                if(isset($sth_slave_array[$souav['user_id']])){
                    $muser = $sth_user_array[$sth_slave_array[$souav['user_id']]['user_id']]['name'];
                }
                $ExcelWriter->setCellVaule($col++, $line+2, $muser);
                $ExcelWriter->setCellVaule($col++, $line+2, $sth_location_array[$sth_user_array[$souav['user_id']]['area2']]['name']);
                $ExcelWriter->setCellVaule($col++, $line+2, $sth_user_array[$souav['user_id']]['username']);
                $ExcelWriter->setCellVaule($col++, $line+2, $sth_user_array[$souav['user_id']]['discount']);
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['skc_id']);
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['bianhao']);
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['kuanhao']);
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['huohao']);
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['name']);
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['wave_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['series_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['category_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['classes_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['color_code']);
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['product_color_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['p_price']);
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['price_purchase']);
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($sth_att_group_array[$uv['product_size_id']]['group_id'])));
                $size_group_name = '';
                foreach($size_group_list as $sgv){
                    $ExcelWriter->setCellVaule($col++, $line+2, $size_order[$sgv['keyword_id']]);
                }                             
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['num']);
                $ExcelWriter->setCellVaule($col++, $line+2, round($uv['p_d_price'],2));
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['price']);
                $ExcelWriter->setCellVaule($col++, $line+2, round($uv['discount_price'],2));
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['style_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['designer']);
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['theme_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['brand_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['sxz_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['nannvzhuan_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['neiwaida_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($uv['changduankuan_id'])));
                $ExcelWriter->setCellVaule($col++, $line+2, $uv['date_market']);
                $line++;
            }                                         
        }

        $ExcelWriter->setTitle('尺码版');
        
        $ExcelWriter->newSheet(1,'条码版');
        $titles = array('客户上级*','客户区域*','客户账号*','客户折扣*','条码描述','条码*','单价','数量*','折后单价','折前金额','折后金额');
        $ExcelWriter->row($titles);
        $line2 = 0;
        foreach($sth_order_user_array as $souav2){
            $userOrderSku = $OrderList->get_user_orderlist_sku_info($souav2['user_id']);
            foreach($userOrderSku as $skuInfo){
                $col2 = 0;
                $muser = '';
                if(isset($sth_slave_array[$souav2['user_id']])){
                    $muser = $sth_user_array[$sth_slave_array[$souav2['user_id']]['user_id']]['name'];
                }
                $ExcelWriter->setCellVaule($col2++, $line2+2, $muser);
                $ExcelWriter->setCellVaule($col2++, $line2+2, $sth_location_array[$sth_user_array[$souav2['user_id']]['area2']]['name']);
                $ExcelWriter->setCellVaule($col2++, $line2+2, $sth_user_array[$souav2['user_id']]['username']);
                $ExcelWriter->setCellVaule($col2++, $line2+2, $sth_user_array[$souav2['user_id']]['discount']);
                $ExcelWriter->setCellVaule($col2++, $line2+2, $skuInfo['kuanhao'].','.Keywords::cache_get(array($skuInfo['product_color_id'])).','.Keywords::cache_get(array($skuInfo['product_size_id'])));
                $ExcelWriter->setCellVaule($col2++, $line2+2, $skuInfo['kuanhao'].'-'.$skuInfo['color_code'].'-'.$skuInfo['product_size_id']);
                $ExcelWriter->setCellVaule($col2++, $line2+2, $skuInfo['p_price']);
                $ExcelWriter->setCellVaule($col2++, $line2+2, $skuInfo['num']);
                $ExcelWriter->setCellVaule($col2++, $line2+2, round($skuInfo['p_d_price'],2));
                $ExcelWriter->setCellVaule($col2++, $line2+2, $skuInfo['price']);
                $ExcelWriter->setCellVaule($col2++, $line2+2, round($skuInfo['discount_price'],2));
                $line2++;
            }
        }
        
        $ExcelWriter->newSheet(2,'完成率');
        $titles = array('客户账号','订单数量','指标数量','折后订单金额','指标金额','数量完成率','金额完成率');
        $ExcelWriter->row($titles);
        
        $get_uorder_sql = 'select * from orderlistuser ';
        $sth_uorder = $dbh->prepare($get_uorder_sql);
        $sth_uorder->execute();
        $sth_uorder_array = $fetch_result($sth_uorder,'user_id');
        $UserSlave  = new UserSlave;
        $line3 = 0;
        foreach($sth_user_array as $souav3){
            if($souav3['type']==2){
                $slave_id   = $UserSlave->get_slave_user_id($souav3['id']);
                $sth_zongdai_array = array();
                if($sth_user_array){
                    $get_zongdai_sql = 'select sum(num) as num ,sum(price) as price ,sum(discount_price) as discount_price from orderlistuser where user_id in ('.$slave_id.') ';
                    $sth_zongdai = $dbh->prepare($get_zongdai_sql);
                    $sth_zongdai->execute();
                    $sth_zongdai_array = $fetch_result($sth_zongdai);
                }
                $num = $sth_zongdai_array[0]['num'];
                $price = $sth_zongdai_array[0]['price'];
                $discount_price = $sth_zongdai_array[0]['discount_price'];
            }else{
                $num = $sth_uorder_array[$souav3['id']]['num'];
                $price = $sth_uorder_array[$souav3['id']]['price'];
                $discount_price = $sth_uorder_array[$souav3['id']]['discount_price'];
            }
            $col3 = 0;
            $ExcelWriter->setCellVaule($col3++, $line3+2, $souav3['username']);
            $ExcelWriter->setCellVaule($col3++, $line3+2, $num);
            $ExcelWriter->setCellVaule($col3++, $line3+2, $souav3['exp_num']);
            $ExcelWriter->setCellVaule($col3++, $line3+2, $discount_price);
            $ExcelWriter->setCellVaule($col3++, $line3+2, $souav3['exp_price']);
            $ExcelWriter->setCellVaule($col3++, $line3+2, $souav3['exp_num']?((round($num/$souav3['exp_num'],4)*100).'%'):'');
            $ExcelWriter->setCellVaule($col3++, $line3+2, $souav3['exp_price']?((round($discount_price/$souav3['exp_price'],4)*100).'%'):'');
            $line3++;
        }
        $ExcelWriter->refreshIndex();
    }
    
    public static function Action_analysis_info($r,$username){
        set_time_limit(0);
        @ini_set('memory_limit','1024M');
        Flight::validateEditorHasLogin();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        $User   =    new User();
        $user   = $User->findone("username={$username}");
        $dbh    = Flight::DatabaseMaster();      
        $analysis_list = array(
            array('name'=>'大类分析','field'=>'category'),
            array('name'=>'小类分析','field'=>'classes'),
            array('name'=>'波段分析','field'=>'wave'),
            array('name'=>'款式分析','field'=>'style'),
            array('name'=>'单色分析','field'=>'color'),
            array('name'=>'价格带分析','field'=>'price_band'),
            array('name'=>'系列分析','field'=>'series'),
            array('name'=>'性别分析','field'=>'nannvzhuan'),
            array('name'=>'上下装分析','field'=>'sxz'),
            array('name'=>'品牌分析','field'=>'brand'),
            array('name'=>'季节分析','field'=>'season')
        );
        $Product        = new Product;
        $OrderList      = new OrderList;
        $name   =   $user["name"] ? '['.$user["name"].']' : "总";
        $excel_name = $name.'订单分析数据';
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array('PageLimit'=>'0','execltype'=>'2007'));
        foreach($analysis_list as $ak =>$al){
            $ExcelWriter->newSheet($ak,$al['name']);
            $ExcelWriter->setval('A1','子项目',array('fill'=>'yellow','align'=>'center'));
            $ExcelWriter->merge('B1:E1');
            $ExcelWriter->merge('F1:I1');
            $ExcelWriter->merge('J1:K1');
            $ExcelWriter->merge('L1:M1');
            $ExcelWriter->merge('A2:A3');
            $ExcelWriter->setval('B1','款量',array('fill'=>'yellow','align'=>'center'));
            $ExcelWriter->setval('F1','款色量',array('fill'=>'yellow','align'=>'center'));
            $ExcelWriter->setval('J1','订货量',array('fill'=>'yellow','align'=>'center'));
            $ExcelWriter->setval('L1','订货金额',array('fill'=>'yellow','align'=>'center'));
            $ExcelWriter->setval('A2','汇总',array('fill'=>'green','align'=>'center','valign'=>'center'));
            $ExcelWriter->setval('B2','开发',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('C2','已定',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('D2','差异',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('E2','占比',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('F2','开发',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('G2','已定',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('H2','宽度',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('I2','深度',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('J2','件数',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('K2','占比',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('L2','金额',array('fill'=>'green','align'=>'center'));
            $ExcelWriter->setval('M2','占比',array('fill'=>'green','align'=>'center'));
            $t = $al['field'];
            $limit = 1000;
            $options = array();
            $condition = array();
            switch ($t) {
                case 'color'    :
                    $tid    = "pc.color_id";
                    $options['group']   = "{$tid}";
                    break;
                default :
                    $tid    = "p.{$t}_id";
                    $options['group']   = "{$tid}";
            }
            $condition[]    = "p.status=1";
            $options['fields']  = "count(DISTINCT pc.product_id) as pnum, count(DISTINCT pc.product_id,pc.color_id) as skc, p.*,{$tid} as tid";
            $options['tablename']   = "product as p left join product_color as pc on p.id=pc.product_id";
            $options['limit']   = $limit;
            
            $where          = implode(" AND ", $condition);
            $list           = $Product->find($where, $options);
            $options['group']   = "";
            $productcount   = $Product->findone($where, $options);           
                $options        = array();
                $cond = array();
                if($user['type']==1){
                    $cond['user_id']= $user['id'];
                }elseif($user['type']==2){
                    $cond['master_uid']=$user['id'];
                }
                $options['key'] = "tid";
                $options['group']   = "{$tid}";
                $options['fields_more'] = "{$tid} as tid";
                if($t == "color"){
                    $options['tables_more'] = "left join product_color as pc on p.id=pc.product_id and o.product_color_id=pc.color_id";
                }
                $orderinfo      = $OrderList->getOrderAnalysisList($cond, $options);
                $options['group']   = "";
                $options['key']     = "";
                $ordercount     = $OrderList->getOrderAnalysisCount($cond, $options);
                foreach ($list as &$row) {
                    $tid            = $row['tid'];
                    $ord            = $orderinfo[$tid];
                    $row['percent_pnum']    = $row['pnum'] ? sprintf("%.1f%%", $ord['pnum'] / $row['pnum'] * 100) : '-';
                    $row['skc_width']       = $row['skc']  ? sprintf("%.1f%%", $ord['skc'] / $row['skc'] * 100) : '-';
                    if($ord['skc']){
                        $row['skc_depth']   = sprintf("%d", $ord['num'] / $ord['skc']);
                    }
                    $row['difference_num']  = $row['pnum'] - $ord['pnum'];
                    $row['order']   = $ord;
                    if($ordercount['num']){
                        $row['percent_num'] = sprintf("%.1f%%", $ord['num'] / $ordercount['num'] * 100);
                    }
                    if($ordercount['price']){
                        $row['percent_price'] = sprintf("%.1f%%", $ord['price'] / $ordercount['price'] * 100);
                    }
                }
                $list   = ProductsAttributeFactory::fetch($list, $t, 'tid', 'order_key');
                $callback   = function($a){ return $a['order_key']['rank']; };
                $orderAsc   = true;
                usort($list, function($a, $b) use ($callback, $orderAsc){
                    $rank_a     = $callback($a);
                    $rank_b     = $callback($b);
                    if($rank_a<1) return true;
                    if($rank_b<1) return false;
                    return $orderAsc ? $rank_a > $rank_b : $rank_a < $rank_b;
                });
            $difference_num = $productcount['pnum']-$ordercount['pnum'];
            $percent_pnum = $productcount['pnum'] ? sprintf("%.1f%%", $ordercount['pnum'] / $productcount['pnum'] * 100) : '';
            $skc_width = $productcount['skc']  ? sprintf("%.1f%%", $ordercount['skc'] / $productcount['skc'] * 100) : '';
            if($ordercount['skc']){
                $skc_depth = sprintf("%d", $ordercount['num'] / $ordercount['skc']);
            }else{
                $skc_depth = '';
            }
            $ExcelWriter->setval('B3',$productcount['pnum'],array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('C3',$ordercount['pnum'],array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('D3',$difference_num,array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('E3',$percent_pnum,array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('F3',$productcount['skc'],array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('G3',$ordercount['skc'],array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('H3',$skc_width,array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('I3',$skc_depth,array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('J3',$ordercount['num'],array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('K3','100%',array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('L3',$ordercount['price'],array('fill'=>'green','align'=>'center','color'=>'red'));
            $ExcelWriter->setval('M3','100%',array('fill'=>'green','align'=>'center','color'=>'red'));
            $line = 0;
            foreach($list as $linfo){
                $col = 0;
                $ExcelWriter->setCellVaule($col++, $line+4, Keywords::cache_get(array($linfo['tid'])));
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['pnum']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['order']['pnum']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['difference_num']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['percent_pnum']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['skc']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['order']['skc']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['skc_width']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['skc_depth']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['order']['num']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['percent_num']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['order']['price']);
                $ExcelWriter->setCellVaule($col++, $line+4, $linfo['percent_price']);                
                $line++;
            }
        }
        $ExcelWriter->refreshIndex();
    }
    public static function Action_a($r){
        set_time_limit(0);
        $dbh    = Flight::DatabaseMaster();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        //echo rand(1,200);
        $sql = 'SELECT id FROM `user` WHERE TYPE =1';
        $res = $dbh->prepare($sql);
        $res->execute();
    
        $u = $fetch_result($res);
    
        $sql = 'SELECT  id   FROM `product` WHERE status =1';
        $res = $dbh->prepare($sql);
        $res->execute();
        $p = $fetch_result($res);
    
        foreach($u as $uinfo){
            foreach($p as $pinfo){
                $sql = 'SELECT color_id FROM `product_color` where product_id = "'.$pinfo['id'].'" ';
                $res = $dbh->prepare($sql);
                $res->execute();
                $pc = $fetch_result($res);
                $sql = 'SELECT size_id FROM `product_size` WHERE product_id = "'.$pinfo['id'].'"';
                $res = $dbh->prepare($sql);
                $res->execute();
                $pz = $fetch_result($res);
                foreach($pc as $pcinfo){
                    foreach($pz as $pzinfo){
                        $insert = 'insert into orderlist (user_id,product_id,product_color_id,product_size_id,num,post_time,create_ip) values ('.$uinfo['id'].','.$pinfo['id'].','.$pcinfo['color_id'].','.$pzinfo['size_id'].','.rand(1,200).',"'.date('Y-m-d H:i:s').'",0) ;'."\n";
                        file_put_contents('aaa.txt', $insert,FILE_APPEND);
                    }
                }
    
            }
        }
    }
    
    public static function Action_b($r){
        set_time_limit(0);
        for($i=0;$i<=5000;$i++){
            file_put_contents('aa.txt', $i."\n",FILE_APPEND);
        }
    }


    public static function Action_export_all($r){
        Flight::validateEditorHasLogin();

        $Company        = new Company;
        $ProductSize    = new ProductSize;
        $fairname       = str_replace(' ', '', $Company->fairname);
        $limit          = $r->query->limit  ? $r->query->limit  : 40;
        $p              = $r->query->p      ? $r->query->p      : 1;

        $excel_name     = sprintf("%s横向表", $fairname);
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array());
        $sheet  = $ExcelWriter->getActiveSheet();

        $sheet->SetCellValue("A1", '编号');
        $sheet->SetCellValue("B1", '款号');
        $sheet->SetCellValue("C1", '款名');
        $sheet->SetCellValue("D1", '颜色');
        $sheet->SetCellValue("E1", '大类');
        $sheet->SetCellValue("F1", '小类');
        $sheet->SetCellValue("G1", '波段');
        $sheet->SetCellValue("H1", '图片');
        $sheet->SetCellValue("I1", '单价');

        $User           = new User;
        $Product        = new Product;
        $ProductColor   = new ProductColor;
        $OrderList      = new OrderList;
        $Factory        = new ProductsAttributeFactory('size');
        $size_list_all  = $Factory->getAllList();
        $size_count     = count($size_list_all);
        $ulist  = $User->find("type=1 and id in (select DISTINCT user_id from orderlist)", array("limit"=>$limit, "order"=>"id asc", "page"=>$p));
        $i      = 9;
        foreach($ulist as $u){
            $key    = STATIC::get_Excel_line($i);
            $sheet->SetCellValue("{$key}1", $u['name']);
            $i2     = $i - 1;
            foreach($size_list_all as $size){
                $i2++;
                $key    = STATIC::get_Excel_line($i2);
                $sheet->SetCellValue("{$key}2", $size['keywords']['name']);
            }
            $i2++;
            $key    = STATIC::get_Excel_line($i2);
            $sheet->SetCellValue("{$key}2", '合计');
            $i2++;
            $key    = STATIC::get_Excel_line($i2);
            $sheet->SetCellValue("{$key}2", '总价');

            $i  += $size_count + 2;
        }

        $plist  = $Product->find("status<>0", array("limit"=>10000));
        $i      = 3;
        foreach($plist as $product){
            $product_id = $product['id'];
            $pclist     = $ProductColor->get_color_list($product_id);
            foreach($pclist as $pc) {
                if($pc['status']){
                    $sheet->SetCellValue("A{$i}", $pc['skc_id']);
                    $sheet->SetCellValue("B{$i}", $product['kuanhao']);
                    $sheet->SetCellValue("C{$i}", $product['name']);
                    $sheet->SetCellValue("D{$i}", Keywords::cache_get($pc['color_id']));
                    $sheet->SetCellValue("E{$i}", Keywords::cache_get($product['category_id']));
                    $sheet->SetCellValue("F{$i}", Keywords::cache_get($product['classes_id']));
                    $sheet->SetCellValue("G{$i}", Keywords::cache_get($product['wave_id']));
                    $ExcelWriter->setCellImage("H{$i}", DOCUMENT_ROOT . "thumb/75/". $product['defaultimage']);
                    $sheet->SetCellValue("I{$i}", $product['price']);
                    $sheet->getRowDimension($i)->setRowHeight(75);

                    $i2     = 9;
                    foreach($ulist as $u){
                        $count      = 0;
                        $where  = "user_id={$u['id']} AND product_id={$product_id} AND product_color_id={$pc['color_id']}";
                        $order  = $OrderList->find($where, array("key"=>"product_size_id", "limit"=>100));
                        foreach($size_list_all as $size){
                            $key    = STATIC::get_Excel_line($i2);
                            $num    = $order[$size['keyword_id']]['num'];
                            $sheet->SetCellValue("{$key}{$i}", $num);
                            $count += $num;
                            $i2++;
                        }
                        $price      = $count * $product['price'];
                        $key    = STATIC::get_Excel_line($i2++);
                        $sheet->SetCellValue("{$key}{$i}", $count);
                        $key    = STATIC::get_Excel_line($i2++);
                        $sheet->SetCellValue("{$key}{$i}", $price);
                    }
                    $i++;

                }
            }
        }

    }

    public static function get_Excel_line ($i) {
        $n      = intval($i / 26);
        $l      = $i % 26;
        if($n){
            $k1 = sprintf("%c", 65 + $n - 1);
        }
        $key    = $k1 . sprintf("%c", 65 + $l);
        return $key;
    }
    
    
    public static function Action_order_info_yy($r){
        set_time_limit(0);
        @ini_set('memory_limit','1024M');
        Flight::validateEditorHasLogin();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        $dbh    = Flight::DatabaseMaster();
    
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
        $Factory        = new ProductsAttributeFactory('size');
        $excel_name     = sprintf("%s订单明细表", $fairname);
        $titles = array('代理商*','下属店铺*','款号*','颜色*','规格*','订单量*','订货价','吊牌价*');     
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles,'PageLimit'=>'0','execltype'=>'2007'));
        $OrderList      = new OrderList;
        $dbh    = Flight::DatabaseMaster();
    
        $get_user_sql = 'SELECT * from user where type=1 or type=2 order by id ';
        $sth_user = $dbh->prepare($get_user_sql);
        $sth_user->execute();
        $sth_user_array = $fetch_result($sth_user,'id');
    
        $get_slave_sql = 'SELECT * from user_slave  ';
        $sth_slave = $dbh->prepare($get_slave_sql);
        $sth_slave->execute();
        $sth_slave_array = $fetch_result($sth_slave,'user_slave_id');
                        
        $get_order_user_sql = 'SELECT * , p.kuanhao from  orderlist o left join product p on o.product_id = p.id  order by user_id , product_id , product_color_id , product_size_id';
        $sth_order_user = $dbh->prepare($get_order_user_sql);
        $sth_order_user->execute();
        $line = 0;
        while($row  = $sth_order_user->fetch()){
            $col = 0;
            $muser = '';
            if(isset($sth_slave_array[$row['user_id']])){
                $muser = $sth_user_array[$sth_slave_array[$row['user_id']]['user_id']]['name'];
            }
            $ExcelWriter->setCellVaule($col++, $line+2, $muser);
            $ExcelWriter->setCellVaule($col++, $line+2, $sth_user_array[$row['user_id']]['name']);
            $ExcelWriter->setCellVaule($col++, $line+2, $row['kuanhao']);
            $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($row['product_color_id'])));
            $ExcelWriter->setCellVaule($col++, $line+2, Keywords::cache_get(array($row['product_size_id'])));
            $ExcelWriter->setCellVaule($col++, $line+2, $row['num']);
            $ExcelWriter->setCellVaule($col++, $line+2, $row['discount_unit_price']);
            $ExcelWriter->setCellVaule($col++, $line+2, $row['unit_price']);
            $line++;
        }     
    }
    
    public static function Action_d($r){
        Flight::validateEditorHasLogin();
        $xml = new ExcelXml();
        $xml->createTable('table1');
        /*$xml->createRow();
        $xml->createCell('123');
        $xml->createRow();
        $xml->createCell('123');
        $xml->createCell('456',array('Index'=>4));
        $xml->createCell('123');
        $xml->createRow(array('Index'=>4));
        $xml->createCell('789');
        $xml->createRow();
        $xml->createCell('789');
        
        
        $xml->createTable('table2');
        $xml->createRow();
        $xml->createCell('123',array('MergeAcross'=>3,'MergeDown'=>4));*/
        $xml->setCell(1, 1, '钟江梁',array('StyleID'=>1));
        $xml->setCell(1, 2, 12);
        $xml->setCell(2, 1, 21);
        $xml->setCell(2, 4, 24);
        $xml->setCell(2, 5, 25);
        $xml->setCell(7, 1, 71);
        $xml->setCell(7, 1, 71);
        $xml->setCell(7, 2, 72);
        $xml->setCell(7, 6, 76);
        $xml->setCell(7, 7, 77);
        $xml->setCell(8, 1, 81);
        $xml->setCell(9, 1, 91,array('MergeAcross'=>2,'MergeDown'=>3));
        $xml->getTableXml('','a');
    }
    
    public static function Action_e($r){
        $t=microtime(true);
        $a = array();
        for($i=0;$i<9000000;$i++){
            $a[$i] = $i;
        }
        $t2=microtime(true);
        echo round(($t2-$t),4);
    }
    
    public static function Action_order_info_2($r){
        
        set_time_limit(0);
        @ini_set('memory_limit','1024M');
        $start = memory_get_usage();
        Flight::validateEditorHasLogin();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        $dbh    = Flight::DatabaseMaster();
    
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
        $Factory        = new ProductsAttributeFactory('size');
        $size_group_list    = $Factory->get_group_list();
        //print_r($size_group_list);
        $tmpl_array = array();
        foreach($size_group_list as $s){
            $tmpl_array[$s['group_id']][]   = $s['keyword_id'];
        }
        $newSizeList    = array();
        $newSizeHash    = array();
        $newSizeLength  = 0;
        foreach($tmpl_array as $group_id => $ary){
            $len    = count($ary);
            if($len > $newSizeLength){
                $newSizeLength = $len;
            }
        }
        
       
        foreach($tmpl_array as $group_id => $ary){
            for($i = 0; $i < $newSizeLength; $i++){
                $size_id    = $ary[$i];
                $newSizeList[$i][]  = $size_id;
                $newSizeHash[$size_id]  = $i;
            }
        }
        
        $excel_name     = sprintf("%s订单明细表", $fairname);
        $titles = array('客户上级*','客户区域*','客户账号*','客户名称','客户折扣*','圆牌号*','编号*','款号*','货号','款名*','波段','系列','大类','小类','色号','颜色*','主推款','单价*','买断价','尺码组');
        /*foreach($size_group_list as $sg){
            $titles[]=Keywords::cache_get(array($sg['keyword_id']));
        }*/
        $len1 = sizeof($titles);
        $optionArr = array();
        foreach($newSizeList as $nslk=>$nslv ){
            $optionArr[$len1+$nslk] = ' ss:StyleID="2" ';
            $kstr = '';
            $len = sizeof($nslv);
            foreach($nslv as $nlsvk => $nslvv){
                $tail = ($nlsvk === ($len-1)?'':'&#10;');
                $kstr.= ($nslvv?Keywords::cache_get(array($nslvv)):'').$tail;
            }
            $titles[] = $kstr;
        }
        $titles = array_merge($titles,array('合计','折后价','折前金额','折后金额','款别','设计师','主题','品牌','上下装','男女装','内外搭','长短款','上市时间'));
        //$ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles,'PageLimit'=>'0','execltype'=>'2007'));
        $OrderList      = new OrderList;
        $dbh    = Flight::DatabaseMaster();
    
        $get_user_sql = 'SELECT * from user where type=1 or type=2 order by id ';
        $sth_user = $dbh->prepare($get_user_sql);
        $sth_user->execute();
        $sth_user_array = $fetch_result($sth_user,'id');
    
        $get_slave_sql = 'SELECT * from user_slave  ';
        $sth_slave = $dbh->prepare($get_slave_sql);
        $sth_slave->execute();
        $sth_slave_array = $fetch_result($sth_slave,'user_slave_id');
         
        $get_location_sql = 'SELECT id,name from location  ';
        $sth_location = $dbh->prepare($get_location_sql);
        $sth_location->execute();
        $sth_location_array = $fetch_result($sth_location,'id');
        //print_r($OrderList->get_user_orderlist_info(4));exit;
    
        $get_att_group_sql = 'SELECT attr_id,group_id from products_attr_group';
        $sth_att_group = $dbh->prepare($get_att_group_sql);
        $sth_att_group->execute();
        $sth_att_group_array = $fetch_result($sth_att_group,'attr_id');
    
    
    
        $get_order_user_sql = 'SELECT user_id from  orderlist  group by user_id order by user_id';
        $sth_order_user = $dbh->prepare($get_order_user_sql);
        $sth_order_user->execute();
        $sth_order_user_array = $fetch_result($sth_order_user);
        $line = 1;
        $t1=microtime(true);
        $xml = new ExcelXml();
        //$xml->createTable('尺码版');
        $xml->createRowByArray(1,$titles,array(),$optionArr);
        $title_str = $xml->createRowsToStr();
        $xml->initTableRows();
        //$xml->getTableXml('','abc');
        $dirRoot = 'tmpl/export/';
        
        if(!is_dir($dirRoot)){
            mkdir($dirRoot, 0777, true);
        }
        
        $dirName = $dirRoot.str_replace('.', '_', microtime(true)).'_'.rand(10000,99999);
        mkdir($dirName);
        $file_array = array();
        //echo $dirName;exit;
        $count = 0;
        foreach($sth_order_user_array as $souav){
            /*$count++;
            if($count>10){
                continue;
            }*/
            $userOrderInfo = $OrderList->get_user_orderlist_info_new($souav['user_id']);
            //exit;
            foreach($userOrderInfo as $uk=>$uv){
                $col = 1;
                $size_array = explode(',', $uv['F']);
                $size_order = array();
                foreach($size_array as $sz){
                    $sizesingle = explode(':', $sz);
                    $size_order[$sizesingle[0]] = $sizesingle[1];
                }
                //print_r($size_order);exit;
                $muser = '';
                if(isset($sth_slave_array[$souav['user_id']])){
                    $muser = $sth_user_array[$sth_slave_array[$souav['user_id']]['user_id']]['name'];
                }
                $xml->setCell($line+1, $col++, $muser);
                $xml->setCell($line+1, $col++, $sth_location_array[$sth_user_array[$souav['user_id']]['area2']]['name']);
                $xml->setCell($line+1, $col++, $sth_user_array[$souav['user_id']]['username']);
                $xml->setCell($line+1, $col++, $sth_user_array[$souav['user_id']]['name']);
                $xml->setCell($line+1, $col++, $sth_user_array[$souav['user_id']]['discount']);
                $xml->setCell($line+1, $col++, $uv['skc_id']);
                $xml->setCell($line+1, $col++, $uv['bianhao']);
                $xml->setCell($line+1, $col++, $uv['kuanhao']);
                $xml->setCell($line+1, $col++, $uv['huohao']);
                $xml->setCell($line+1, $col++, $uv['name']);
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['wave_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['series_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['category_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['classes_id'])));
                $xml->setCell($line+1, $col++, $uv['color_code']);
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['product_color_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['main_push_id'])));
                $xml->setCell($line+1, $col++, $uv['p_price']);
                $xml->setCell($line+1, $col++, $uv['price_purchase']);
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($sth_att_group_array[$uv['product_size_id']]['group_id'])));
                //foreach($size_group_list as $sgv){
                   // $xml->setCell($line+1, $col++, $size_order[$sgv['keyword_id']]);
                //}
                //$newSizeHash
                //$newSizeList
                $iarray = array();
                $iarraynew = array();
                    foreach($size_order as $sok=>$sov){
                        $ikey = $newSizeHash[$sok];
                        $iarray[$ikey] = $sov;
                    }
                for($i = 0; $i < $newSizeLength; $i++){
                   if(!isset($iarray[$i])){
                       $iarraynew[$i] = '';
                   }else{
                       $iarraynew[$i] = $iarray[$i];
                   }
                }
                foreach($iarraynew as $iv){
                    $xml->setCell($line+1, $col++, $iv);
                }
                
                $xml->setCell($line+1, $col++, $uv['num']);
                $xml->setCell($line+1, $col++, round($uv['p_d_price'],2));
                $xml->setCell($line+1, $col++, $uv['price']);
                $xml->setCell($line+1, $col++, round($uv['discount_price'],2));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['style_id'])));
                $xml->setCell($line+1, $col++, $uv['designer']);
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['theme_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['brand_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['sxz_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['nannvzhuan_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['neiwaida_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['changduankuan_id'])));
                $xml->setCell($line+1, $col++, $uv['date_market']);
                $line++;
            }
            $text = $xml->createRowsToStr();
            $file_array[] = $souav['user_id'].'.xml';
            file_put_contents($dirName.'/'.$souav['user_id'].'.xml', $text);
            $xml->initTableRows();
        }
        //$last_str = $title_str;
        file_put_contents($dirName.'/all.xml', $title_str);
        foreach($file_array as $fval){
           file_put_contents($dirName.'/all.xml', file_get_contents($dirName.'/'.$fval),FILE_APPEND);
           unlink($dirName.'/'.$fval);
        }
        //file_put_contents('a.xml', $last_str);
        //$table_str_1 = $xml->createTableToStr(file_get_contents($dirName.'/all.xml'), '尺码版');
        //unlink($dirName.'/all.xml');
        
        /*$xml->initTableRows();
        $titles = array('客户上级*','客户区域*','客户账号*','客户折扣*','条码描述','条码*','单价','数量*','折后单价','折前金额','折后金额');
        $xml->createRowByArray(1,$titles);
        $title_str_2 = $xml->createRowsToStr();
        $xml->initTableRows();
        $file_array_2 = array();
        $line2 = 1;
        foreach($sth_order_user_array as $souav2){
            $userOrderSku = $OrderList->get_user_orderlist_sku_info($souav2['user_id']);
            foreach($userOrderSku as $skuInfo){
                $col2 = 1;
                $muser = '';
                if(isset($sth_slave_array[$souav2['user_id']])){
                    $muser = $sth_user_array[$sth_slave_array[$souav2['user_id']]['user_id']]['name'];
                }
                $xml->setCell($line2+1, $col2++, $muser);
                $xml->setCell($line2+1, $col2++, $sth_location_array[$sth_user_array[$souav2['user_id']]['area2']]['name']);
                $xml->setCell($line2+1, $col2++, $sth_user_array[$souav2['user_id']]['username']);
                $xml->setCell($line2+1, $col2++, $sth_user_array[$souav2['user_id']]['discount']);
                $xml->setCell($line2+1, $col2++, $skuInfo['kuanhao'].','.Keywords::cache_get(array($skuInfo['product_color_id'])).','.Keywords::cache_get(array($skuInfo['product_size_id'])));
                $xml->setCell($line2+1, $col2++, $skuInfo['kuanhao'].'-'.$skuInfo['color_code'].'-'.$skuInfo['product_size_id']);
                $xml->setCell($line2+1, $col2++, $skuInfo['p_price']);
                $xml->setCell($line2+1, $col2++, $skuInfo['num']);
                $xml->setCell($line2+1, $col2++, round($skuInfo['p_d_price'],2));
                $xml->setCell($line2+1, $col2++, $skuInfo['price']);
                $xml->setCell($line2+1, $col2++, round($skuInfo['discount_price'],2));
                $line2++;
            }
            $text = $xml->createRowsToStr();
            $file_array_2[] = '2_'.$souav2['user_id'].'.xml';
            file_put_contents($dirName.'/2_'.$souav2['user_id'].'.xml', $text);
            $xml->initTableRows();
        }
        
        file_put_contents($dirName.'/2_all.xml', $title_str_2);
        foreach($file_array_2 as $fval2){
            file_put_contents($dirName.'/2_all.xml', file_get_contents($dirName.'/'.$fval2),FILE_APPEND);
            unlink($dirName.'/'.$fval2);
        }
        //file_put_contents('a.xml', $last_str);
       // $table_str_2 = $xml->createTableToStr(file_get_contents($dirName.'/2_all.xml'), '条码版');
        //unlink($dirName.'/2_all.xml');
        
        
        $xml->initTableRows();
        $titles = array('客户账号','订单数量','指标数量','折后订单金额','指标金额','数量完成率','金额完成率');
        $xml->createRowByArray(1,$titles);
        //$title_str_3 = $xml->createRowsToStr();
        //$xml->initTableRows();
        $file_array_3 = array();
        $line3 = 1;
        $get_uorder_sql = 'select * from orderlistuser ';
        $sth_uorder = $dbh->prepare($get_uorder_sql);
        $sth_uorder->execute();
        $sth_uorder_array = $fetch_result($sth_uorder,'user_id');
        $UserSlave  = new UserSlave;
        foreach($sth_user_array as $souav3){
            if($souav3['type']==2){
                $slave_id   = $UserSlave->get_slave_user_id($souav3['id']);
                $sth_zongdai_array = array();
                if($sth_user_array){
                    $get_zongdai_sql = 'select sum(num) as num ,sum(price) as price ,sum(discount_price) as discount_price from orderlistuser where user_id in ('.$slave_id.') ';
                    $sth_zongdai = $dbh->prepare($get_zongdai_sql);
                    $sth_zongdai->execute();
                    $sth_zongdai_array = $fetch_result($sth_zongdai);
                }
                $num = $sth_zongdai_array[0]['num'];
                $price = $sth_zongdai_array[0]['price'];
                $discount_price = $sth_zongdai_array[0]['discount_price'];
            }else{
                $num = $sth_uorder_array[$souav3['id']]['num'];
                $price = $sth_uorder_array[$souav3['id']]['price'];
                $discount_price = $sth_uorder_array[$souav3['id']]['discount_price'];
            }
            $col3 = 1;
            $xml->setCell($line3+1, $col3++, $souav3['username']);
            $xml->setCell($line3+1, $col3++, $num);
            $xml->setCell($line3+1, $col3++, $souav3['exp_num']);
            $xml->setCell($line3+1, $col3++, $discount_price);
            $xml->setCell($line3+1, $col3++, $souav3['exp_price']);
            $xml->setCell($line3+1, $col3++, $souav3['exp_num']?((round($num/$souav3['exp_num'],4)*100).'%'):'');
            $xml->setCell($line3+1, $col3++, $souav3['exp_price']?((round($discount_price/$souav3['exp_price'],4)*100).'%'):'');
            $line3++;
        }
        
        $text_3 = $xml->createRowsToStr();
        $xml->initTableRows();
        $table_str_3 = $xml->createTableToStr($text_3, '完成率');
        $table_str_2 = $xml->createTableToStr(file_get_contents($dirName.'/2_all.xml'), '条码版');
        unlink($dirName.'/2_all.xml');*/
        $table_str_1 = $xml->createTableToStr(file_get_contents($dirName.'/all.xml'), '尺码版');
        unlink($dirName.'/all.xml');
        $table_str = $table_str_1.$table_str_2.$table_str_3;
        //$xml->getTableXml($file_array_2,$dirName.'/table.xml');
        //$t2=microtime(true);
        
       // echo '时间： '.round(($t2-$t1),4).'<br>';
        //$end = memory_get_usage();
        //echo '内存： '.(($end-$start)/1024).'<br>';
        if($xml->getTableXml($table_str,$dirName.'/table.xml',true,$excel_name)=='link'){
            $result['link'] = $dirName.'/table.xml';
            Flight::display('data/link.html', $result);
        }else{
            rmdir($dirName);
        }
        
        exit;
    }
    
    public static function Action_order_info_test($r){
        set_time_limit(0);
        @ini_set('memory_limit','10024M');
        $start = memory_get_usage();
        Flight::validateEditorHasLogin();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        $dbh    = Flight::DatabaseMaster();
    
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
        $Factory        = new ProductsAttributeFactory('size');
        $size_group_list    = $Factory->get_group_list();
        $excel_name     = sprintf("%s订单明细表", $fairname);
        $titles = array('客户上级*','客户区域*','客户账号*','客户折扣*','圆牌号*','编号*','款号*','货号','款名*','波段','系列','大类','小类','色号','颜色*','单价*','买断价','尺码组');
        foreach($size_group_list as $sg){
            $titles[]=Keywords::cache_get(array($sg['keyword_id']));
        }
        $titles = array_merge($titles,array('合计','折后价','折前金额','折后金额','款别','设计师','主题','品牌','上下装','男女装','内外搭','长短款','上市时间'));
        $OrderList      = new OrderList;
        $dbh    = Flight::DatabaseMaster();
    
        $get_user_sql = 'SELECT * from user where type=1 or type=2 order by id ';
        $sth_user = $dbh->prepare($get_user_sql);
        $sth_user->execute();
        $sth_user_array = $fetch_result($sth_user,'id');
    
        $get_slave_sql = 'SELECT * from user_slave  ';
        $sth_slave = $dbh->prepare($get_slave_sql);
        $sth_slave->execute();
        $sth_slave_array = $fetch_result($sth_slave,'user_slave_id');
         
        $get_location_sql = 'SELECT id,name from location  ';
        $sth_location = $dbh->prepare($get_location_sql);
        $sth_location->execute();
        $sth_location_array = $fetch_result($sth_location,'id');
    
        $get_att_group_sql = 'SELECT attr_id,group_id from products_attr_group';
        $sth_att_group = $dbh->prepare($get_att_group_sql);
        $sth_att_group->execute();
        $sth_att_group_array = $fetch_result($sth_att_group,'attr_id');
     
        $get_order_user_sql = 'SELECT user_id from  orderlist  group by user_id order by user_id';
        $sth_order_user = $dbh->prepare($get_order_user_sql);
        $sth_order_user->execute();
        $sth_order_user_array = $fetch_result($sth_order_user);
        $line = 1;
        $t1=microtime(true);
        $xml = new ExcelXml();
        $xml->createTable('尺码版');
        $xml->createRowByArray(1,$titles);
        $count = 1;
        foreach($sth_order_user_array as $souav){
            $count++;
            if($count>1000){
                continue;
            }
            $userOrderInfo = $OrderList->get_user_orderlist_info_new($souav['user_id']);
            foreach($userOrderInfo as $uk=>$uv){
                $col = 1;
                $size_array = explode(',', $uv['F']);
                $size_order = array();
                foreach($size_array as $sz){
                    $sizesingle = explode(':', $sz);
                    $size_order[$sizesingle[0]] = $sizesingle[1];
                }
                $muser = '';
                if(isset($sth_slave_array[$souav['user_id']])){
                    $muser = $sth_user_array[$sth_slave_array[$souav['user_id']]['user_id']]['name'];
                }
                $xml->setCell($line+1, $col++, $muser);
                $xml->setCell($line+1, $col++, $sth_location_array[$sth_user_array[$souav['user_id']]['area2']]['name']);
                $xml->setCell($line+1, $col++, $sth_user_array[$souav['user_id']]['username']);
                $xml->setCell($line+1, $col++, $sth_user_array[$souav['user_id']]['discount']);
                $xml->setCell($line+1, $col++, $uv['skc_id']);
                $xml->setCell($line+1, $col++, $uv['bianhao']);
                $xml->setCell($line+1, $col++, $uv['kuanhao']);
                $xml->setCell($line+1, $col++, $uv['huohao']);
                $xml->setCell($line+1, $col++, $uv['name']);
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['wave_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['series_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['category_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['classes_id'])));
                $xml->setCell($line+1, $col++, $uv['color_code']);
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['product_color_id'])));
                $xml->setCell($line+1, $col++, $uv['p_price']);
                $xml->setCell($line+1, $col++, $uv['price_purchase']);
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($sth_att_group_array[$uv['product_size_id']]['group_id'])));
                foreach($size_group_list as $sgv){
                    $xml->setCell($line+1, $col++, $size_order[$sgv['keyword_id']]);
                }
                $xml->setCell($line+1, $col++, $uv['num']);
                $xml->setCell($line+1, $col++, round($uv['p_d_price'],2));
                $xml->setCell($line+1, $col++, $uv['price']);
                $xml->setCell($line+1, $col++, round($uv['discount_price'],2));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['style_id'])));
                $xml->setCell($line+1, $col++, $uv['designer']);
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['theme_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['brand_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['sxz_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['nannvzhuan_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['neiwaida_id'])));
                $xml->setCell($line+1, $col++, Keywords::cache_get(array($uv['changduankuan_id'])));
                $xml->setCell($line+1, $col++, $uv['date_market']);
                $line++;
            }
        }
        $xml->getTableXml('','abc');
        $t2=microtime(true);
        echo '时间： '.round(($t2-$t1),4).'<br>';
        $end = memory_get_usage();
        echo '内存： '.(($end-$start)/1024).'<br>';
        exit;
    }
    
    /*产品评论表*/
    public static function Action_comment_info($r){
        Flight::validateEditorHasLogin();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        $dbh    = Flight::DatabaseMaster();
        $get_comment_sql = 'SELECT * from product_comment where 1 order by post_time desc ';
        $sth_comment = $dbh->prepare($get_comment_sql);
        $sth_comment->execute();
        $sth_comment_array = $fetch_result($sth_comment,'id');
    
        $get_user_sql = 'SELECT id,username,name from user  ';
        $sth_user = $dbh->prepare($get_user_sql);
        $sth_user->execute();
        $sth_user_array = $fetch_result($sth_user,'id');
    
    
        $get_product_sql = 'SELECT id,kuanhao,name from product  ';
        $sth_product = $dbh->prepare($get_product_sql);
        $sth_product->execute();
        $sth_product_array = $fetch_result($sth_product,'id');
    
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
    
        $excel_name     = sprintf("%s产品评论表", $fairname);
        $titles = array('用户名','用户名称*','产品款号*','产品名称*','产品评论*','评论时间*');
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles,'PageLimit'=>'0'));
        $suak = 0;
        foreach($sth_comment_array as $suav){
            $col = 0;
            $ExcelWriter->setCellVaule($col++, $suak+2, $sth_user_array[$suav['user_id']]['username']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $sth_user_array[$suav['user_id']]['name']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $sth_product_array[$suav['product_id']]['kuanhao']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $sth_product_array[$suav['product_id']]['name']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['content']);
            $ExcelWriter->setCellVaule($col++, $suak+2, $suav['post_time']);
            $ExcelWriter->setCellVaule($col++, $suak+2, '');
            $suak++;
        }
    }
    
    
    public static function Action_product_order($r){
        set_time_limit(0);
        @ini_set('memory_limit','1024M');
        $start = memory_get_usage();
        Flight::validateEditorHasLogin();
        $fetch_result = function($sth,$key){
            $result = array();
            if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
            }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
            }
            return $result;
        };
        $dbh    = Flight::DatabaseMaster();
    
        $Company        = new Company;
        $fairname       = str_replace(' ', '', $Company->fairname);
        
        $user_info_sql = ' select u.id,u.username,u.name,u.type from user  u  left join user_slave us on us.user_slave_id = u.id where (u.type=1 and us.user_id is null ) or (u.type=2) order by u.type desc , u.id ';
        $user_info  = $dbh->prepare($user_info_sql);
        $user_info->execute();
        $user_info_array = $fetch_result($user_info,'id');
                                                     
        $excel_name     = sprintf("%s产品订量表", $fairname);
        $titles = array('款号','品类','汇总');
        foreach($user_info_array as $uiav){
            $titles[]=$uiav['name'];
        }
        $OrderList      = new OrderList;
        
        $line = 1;
        $t1=microtime(true);
        $xml = new ExcelXml();
        $xml->createTable('table');
        $xml->createRowByArray(1,$titles);
        $dirRoot = 'tmpl/export/';
    
        if(!is_dir($dirRoot)){
            mkdir($dirRoot, 0777, true);
        }
        $dirName = $dirRoot.str_replace('.', '_', microtime(true)).'_'.rand(10000,99999);
        mkdir($dirName);
        $file_array = array();
        $count = 0;
        $get_product_order_sql = 'SELECT product_id, sum( num ) AS num FROM orderlist GROUP BY product_id ';
        $get_product_order = $dbh->prepare($get_product_order_sql);
        $get_product_order->execute();
        $get_product_order_array = $fetch_result($get_product_order,'product_id');
        
        $user_product = array();
        foreach($user_info_array as $uiak=>$uv){
            if($uv['type']==2){
                $get_order_sql = 'SELECT sum( o.num ) AS num, o.product_id FROM orderlist o LEFT JOIN user_slave us ON o.user_id = us.user_slave_id WHERE us.user_id = "'.$uv['id'].'" GROUP BY o.product_id';
                $get_order = $dbh->prepare($get_order_sql);
                $get_order->execute();
                $get_order_array = $fetch_result($get_order,'product_id');
                $user_product[$uv['id']] = $get_order_array;
            }else{
                $get_order_sql = 'SELECT sum( o.num ) AS num, o.product_id FROM orderlist o  WHERE o.user_id = "'.$uv['id'].'" GROUP BY o.product_id';
                $get_order = $dbh->prepare($get_order_sql);
                $get_order->execute();
                $get_order_array = $fetch_result($get_order,'product_id');
                $user_product[$uv['id']] = $get_order_array;
            }
        }        
        
        
        $product_info_sql = 'select id,kuanhao,category_id from product where status <> 0';
        $product_info = $dbh->prepare($product_info_sql);
        $product_info->execute();
        $product_info_array = $fetch_result($product_info,'id');
        
        
        foreach($product_info_array as $piak=>$piav){
            $col = 1;
            $xml->setCell($line+1, $col++, $piav['kuanhao']);
            $xml->setCell($line+1, $col++, Keywords::cache_get(array($piav['category_id'])));
            $xml->setCell($line+1, $col++, $get_product_order_array[$piak]['num']);
            foreach($user_info_array as $k=>$v){
                $xml->setCell($line+1, $col++, $user_product[$k][$piak]['num']);
            }
            $line++;
        }
                           
        $xml->getTableXml('',$dirName.'/'.$excel_name,true,$excel_name);
   
        rmdir($dirName);
        exit;
    }

    public static function Action_orderlist_proportion ($r) {
        $dbh    = Flight::DatabaseMaster();
        $sql    = "SELECT um.username as um_username, um.name as um_name, u.username, u.name, p.kuanhao, p.name AS pname, c.name AS cname, pc.skc_id, p.size_group_id, pp.proportion, o.xnum, o.num, pc.color_code
FROM orderlist_proportion AS o
LEFT JOIN user AS u ON u.id = o.user_id
LEFT JOIN user_slave as us on u.id=us.user_slave_id
LEFT JOIN user AS um on us.user_id=um.id
LEFT JOIN product AS p ON p.id = o.product_id
LEFT JOIN product_color AS pc ON o.product_id = pc.product_id
AND o.product_color_id = pc.color_id
LEFT JOIN product_proportion AS pp ON pp.id = o.proportion_id
LEFT JOIN keywords AS c ON c.id = o.product_color_id
WHERE p.status =1
AND pc.status =1
AND o.num >0";
        $sth    = $dbh->prepare($sql);
        $sth->execute();
        $Company        = new Company;
        $fairname       = $Company->fairname;
        $excel_name     = sprintf("%s配比订单数据", $fairname);
        $titles         = array("总代帐号", "总代名称", "客户账户", "客户名称", "款号", "款名", "圆牌", "颜色", "色号", "尺码组", "尺码", "配比", "箱数", "件数");
        // $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));
        $ExcelWriter    = new ExcelCsv($excel_name, $options=array("titles"=>$titles));
        while($row = $sth->fetch()) {
            $data       = array();
            $data[]     = $row['um_username'];
            $data[]     = $row['um_name'];
            $data[]     = $row['username'];
            $data[]     = $row['name'];
            $data[]     = $row['kuanhao'];
            $data[]     = $row['pname'];
            $data[]     = $row['skc_id'];
            $data[]     = $row['cname'];
            $data[]     = $row['color_code'];
            $size_group_id  = $row['size_group_id'];
            $data[]     = Keywords::cache_get($size_group_id);
            $size_string    = $size_string_hash[$size_group_id];
            if(!$size_string) {
                $size_group     = SizeGroup::getInstance($size_group_id);
                $size_list      = $size_group->get_size_list();
                $sizes          = array();
                foreach($size_list as $size) {
                    $sizes[]    = Keywords::cache_get($size['size_id']);
                }
                $size_string    = implode(";", $sizes);
                $size_string_hash[$size_group_id]   = $size_string;
            }
            $data[]     = $size_string;
            $data[]     = $row['proportion'];
            $data[]     = $row['xnum'];
            $data[]     = $row['num'];
            $ExcelWriter->row($data);
            unset($data);
        }
    }

    public static function Action_product_master_order($r){
        set_time_limit(0);
        @ini_set('memory_limit','1024M');
        $start = memory_get_usage();
        Flight::validateEditorHasLogin();

        $OrderList  =   new OrderList;
        $User       =   new User;
        $options['tablename']   =   "product_color as pc left join product as p on pc.product_id=p.id left join orderlistproductcolor as o on pc.product_id=o.product_id and pc.color_id=o.product_color_id";
        $options['fields']      =   "pc.product_id,pc.color_id,pc.main_push_id,pc.skc_id,pc.color_code,p.*,o.num,o.price as amount";
        $options['limit']       =   10000;
        // $options['db_debug']    =   true;
        $where  =    " pc.status <> 0";
        $orderlistproductcolor  =    $OrderList->find($where,$options);  
        $zongdai_list   =   $User->find("type=2",array("fields"=>"id,name","limit"=>1000));
        $options        =   array();
        $options['fields']  =   "zd_user_id,product_id,product_color_id,sum(num) as num,sum(amount) as price";
        $options['group']   =   "product_id,product_color_id,zd_user_id";
        $options['limit']       =   1000000;
        //$options['db_debug']=   true;
        $where  = "1";
        $orderlist      =   $OrderList->find($where,$options);
        foreach ($orderlist as $key => $value) {
            $list[$value['product_id']."_pc_".$value['product_color_id']."_zd_".$value['zd_user_id']] = $value;
        }
        //print_r($list);exit;
        $titles2 = array('季节','波段','上市时间','系列','主题','款号','款名','性别','主推款','上下装',
                        '大类','中类','颜色','色号','价格带','尺寸带','单价','总量','金额');
        $len = sizeof($titles2);
        for($i=0;$i<$len;$i++){
            $titles1[] = " ";
        }
        foreach ($zongdai_list as $value) {
            $titles1[]=$value['name'];
            $titles1[]=" ";
            $titles2[]="数量";
            $titles2[]="金额";
        }
        $optionArr = array();
        $t1=microtime(true);
        $xml = new ExcelXml();
        $xml->createRowByArray(1,$titles1,array(),$optionArr);
        $xml->createRowByArray(2,$titles2,array(),$optionArr);
        $title_str = $xml->createRowsToStr();
        $xml->initTableRows();
        $dirRoot = 'tmpl/export/';
        
        if(!is_dir($dirRoot)){
            mkdir($dirRoot, 0777, true);
        }
        $dirName = $dirRoot.str_replace('.', '_', microtime(true)).'_'.rand(10000,99999);
        mkdir($dirName);
        $file_array = array();
        $line = 2;
        foreach($orderlistproductcolor as $order){
            $col = 1;
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['season_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['wave_id']));
            $xml->setCell($line+1, $col++, $order['date_market']);
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['series_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['theme_id']));
            $xml->setCell($line+1, $col++, $order['kuanhao']);
            $xml->setCell($line+1, $col++, $order['name']);
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['nannvzhuan_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['main_push_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['sxz_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['category_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['medium_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['color_id']));
            $xml->setCell($line+1, $col++, $order['color_code']);
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['price_band_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($order['size_group_id']));
            $xml->setCell($line+1, $col++, $order['price']);
            $xml->setCell($line+1, $col++, $order['num']);
            $xml->setCell($line+1, $col++, $order['amount']);
            foreach ($zongdai_list as $value) {
                $xml->setCell($line+1, $col++, $list[$order['product_id']."_pc_".$order['color_id']."_zd_".$value['id']]['num']);
                $xml->setCell($line+1, $col++, $list[$order['product_id']."_pc_".$order['color_id']."_zd_".$value['id']]['price']);
            }
            $line++;
            $text = $xml->createRowsToStr();
            $file_array[] = $order['product_id'].'_'.$order['color_id'].'.xml';
            file_put_contents($dirName.'/'.$order['product_id'].'_'.$order['color_id'].'.xml', $text);
            $xml->initTableRows();
        }
        file_put_contents($dirName.'/all.xml', $title_str);
        foreach($file_array as $fval){
           file_put_contents($dirName.'/all.xml', file_get_contents($dirName.'/'.$fval),FILE_APPEND);
           unlink($dirName.'/'.$fval);
        }
        $table_str_1 = $xml->createTableToStr(file_get_contents($dirName.'/all.xml'), '全国渠道汇总订单');
        unlink($dirName.'/all.xml');
        $table_str = $table_str_1.$table_str_2.$table_str_3;
        if($xml->getTableXml($table_str,$dirName.'/table.xml',true,"全国渠道汇总订单")=='link'){
            $result['link'] = $dirName.'/table.xml';
            Flight::display('data/link.html', $result);
        }else{
            rmdir($dirName);
        }
        exit;
    }

    public static function Action_product_dealer_order($r){
        set_time_limit(0);
        @ini_set('memory_limit','1024M');
        $start = memory_get_usage();
        Flight::validateEditorHasLogin();

        $OrderList  =   new OrderList;
        $User       =   new User;
        $dealer_list=   $User->find("type=1",array("fields"=>"id,name,username","limit"=>100,"order"=>"id"));
        $options    = array();
        $options['tablename']   = "product as p left join product_color as pc on p.id=pc.product_id left join product_size as ps on p.id=ps.product_id left join products_attr as pa on ps.size_id=pa.keyword_id and pa.field='size'";
        $options['fields']      = "p.*,pc.product_id,pc.color_id,pc.skc_id,ps.size_id";
        $options['order']       = "p.id,pc.color_id,pa.rank";
        $options['limit']       = 10000;
        // $options['db_debug']=   true;
        $where                  = "pc.status<>0";
        $product_list           = $OrderList->find($where,$options);

        $titles2 = array('款号','款名','大类','小类','单价','颜色','尺码');
        $len = sizeof($titles2);
        for($i=0;$i<$len;$i++){
            $titles1[] = " ";
        }
        foreach ($dealer_list as $row) {
            $titles1[]=$row['name'];
            $titles1[]=" ";
            $titles2[]="数量";
            $titles2[]="金额";
        }
        $optionArr = array();

        $xml = new ExcelXml();
        $xml->createRowByArray(1,$titles1,array(),$optionArr);
        $xml->createRowByArray(2,$titles2,array(),$optionArr);
        $title_str = $xml->createRowsToStr();
        $xml->initTableRows();
        $dirRoot = 'tmpl/export/';
        
        if(!is_dir($dirRoot)){
            mkdir($dirRoot, 0777, true);
        }
        $dirName = $dirRoot.str_replace('.', '_', microtime(true)).'_'.rand(10000,99999);
        mkdir($dirName);
        $file_array = array();
        $line = 2;
        foreach($product_list as $row){
            $col = 1;
            $xml->setCell($line+1, $col++, $row['kuanhao']);
            $xml->setCell($line+1, $col++, $row['name']);
            $xml->setCell($line+1, $col++, Keywords::cache_get($row['category_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($row['classes_id']));
            $xml->setCell($line+1, $col++, $row['price']);
            $xml->setCell($line+1, $col++, Keywords::cache_get($row['color_id']));
            $xml->setCell($line+1, $col++, Keywords::cache_get($row['size_id']));
            foreach ($dealer_list as $value) {
                $order  =   $OrderList->findone("user_id={$value['id']} and product_id={$row['product_id']} and product_color_id={$row['color_id']} and product_size_id={$row['size_id']}",array("fields"=>"num,amount"));
                $xml->setCell($line+1, $col++, $order['num']);
                $xml->setCell($line+1, $col++, $order['amount']);
            }
            $line++;
            $text = $xml->createRowsToStr();
            $file_array[] = $row['product_id'].'_'.$row['color_id']."_".$row['size_id'].'.xml';
            file_put_contents($dirName.'/'.$row['product_id'].'_'.$row['color_id']."_".$row['size_id'].'.xml', $text);
            $xml->initTableRows();
        }
        file_put_contents($dirName.'/all.xml', $title_str);
        foreach($file_array as $fval){
           file_put_contents($dirName.'/all.xml', file_get_contents($dirName.'/'.$fval),FILE_APPEND);
           unlink($dirName.'/'.$fval);
        }
        $table_str = $xml->createTableToStr(file_get_contents($dirName.'/all.xml'), '门店单款分配表');
        unlink($dirName.'/all.xml');
        if($xml->getTableXml($table_str,$dirName.'/dealer.xml',true,"门店单款分配表")=='link'){
            $result['link'] = $dirName.'/dealer.xml';
            Flight::display('data/link.html', $result);
        }else{
            rmdir($dirName);
        }
        exit;
    }

    public static function Action_monitor_data_export($r){
        set_time_limit(0);
        @ini_set('memory_limit','1024M');

        $excel_name     = '预演监控数据';
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array('PageLimit'=>'0','execltype'=>'2007'));
        $sheetIndex     = 0;
        //陈列数据
        $ProductDisplay = new ProductDisplay;

        $options                = array();
        $options['tablename']   = "product_display as pd 
                                    left join product_display_member_color as pdmc on pd.id=pdmc.display_id
                                    left join orderlistproductcolor as opc on pdmc.product_id = opc.product_id and pdmc.keyword_id=opc.product_color_id";
        $options['fields']      ="pd.*,sum(opc.num) as num";
        $options['group']       ="pd.id";
        $options['order']       ="pd.id";
        $options['limit']       =100;
        // $options['db_debug']    =true;

        $display_list   = $ProductDisplay->find($where,$options);

        $ExcelWriter->newSheet($sheetIndex,'陈列数据');
        $ExcelWriter->setval('A1','陈列编号',array('fill'=>'green','align'=>'center'));
        $ExcelWriter->setval('B1','陈列名称',array('fill'=>'green','align'=>'center'));
        $ExcelWriter->setval('C1','订量',    array('fill'=>'green','align'=>'center'));
        $line = 2;
        foreach ($display_list as $key => $row) {
            $col    = 0;
            $ExcelWriter->setCellVaule($col++, $line, $row['bianhao']);
            $ExcelWriter->setCellVaule($col++, $line, $row['name']);
            $ExcelWriter->setCellVaule($col++, $line, $row['num']);
            $line++;
        }

        $ProductDisplay = new ProductDisplay();

        $where          = '1';

        $condition      = array();
        foreach ($product_display as $value) {
            $condition[]= $value['pd_type2'];
        }
        $pd_type2_list  = implode(",", $condition); 

        $ProductGroup   = new ProductGroup;

        $options                = array();
        $options['tablename']   = "product_group as pg left join product_group_member as pgm on pg.id=pgm.group_id 
                                    left join orderlistproductcolor as opc on pgm.product_id=opc.product_id and pgm.color_id=opc.product_color_id 
                                    left join orderlist as o on pgm.product_id=o.product_id and pgm.color_id=o.product_color_id ";
        $options['fields']      = "pg.*,sum(DISTINCT opc.num) as num,count(DISTINCT o.user_id) as usernum,user_id,count(DISTINCT pgm.product_id,pgm.color_id) as pc_num";
        $options['group']       = "pg.id";
        $options['order']       = "num DESC,pg.id";
        $options['limit']       = 10000;
        // $options['db_debug']    = true;

        $group_list           = $ProductGroup->find($where,$options);
        
        $sheetIndex++;
        $ExcelWriter->newSheet($sheetIndex,'搭配数据');
        $ExcelWriter->setval('A1','陈列编号',array('fill'=>'green','align'=>'center'));
        $ExcelWriter->setval('B1','陈列名称',array('fill'=>'green','align'=>'center'));
        $ExcelWriter->setval('C1','订量',    array('fill'=>'green','align'=>'center'));
        $ExcelWriter->setval('D1','人数',    array('fill'=>'green','align'=>'center'));

        $line   = 2;
        foreach ($group_list as $key => $row) {
            $col    = 0;
            $ExcelWriter->setCellVaule($col++,$line,$row['dp_num']);
            $ExcelWriter->setCellVaule($col++,$line,$row['name']);
            $ExcelWriter->setCellVaule($col++,$line,$row['num']);
            $ExcelWriter->setCellVaule($col++,$line,$row['usernum'] ? $row['usernum'] : 0);
            $line++;
        }

        $ExcelWriter->refreshIndex();
    }

    /* 迷你服饰报表 */
    public static function Action_mini_report($r,$username){        
        set_time_limit(0);
        @ini_set('memory_limit','1024M');


        $User   =    new User();
        $user   = $User->findone("username='{$username}'");

        $excel_name     = sprintf("%s订货单", $user['name']);

        $productColor   = new ProductColor;
        $color          = $productColor->findone("1",array("fields"=>"count(color_id) as color_num","group"=>"product_id","order"=>"color_num DESC"));
        $color_max_num  = $color['color_num'];

        $dbh            = Flight::DatabaseMaster();
        $fields         = "p.*, GROUP_CONCAT(DISTINCT pc.color_id) as color_ids,o.discount_unit_price,sum(o.num) as num,sum(o.discount_amount) as amount";
        $tablename      = "product as p left join product_color as pc on p.id=pc.product_id left join orderlist as o on pc.product_id=o.product_id ";
        if($user['type']==2){
            $where      = "p.status<>0 and o.zd_user_id={$user['id']}";
        }elseif($user['type']==1){
            $where      = "p.status<>0 and o.user_id={$user['id']}";
        }else{
            $where      = "p.status<>0";
        }
        $group          = "p.id";
        $order          = "p.id asc";
        $sql            = "SELECT {$fields} FROM {$tablename} WHERE {$where} GROUP BY {$group} ORDER BY {$order}";
        
        $sth            = $dbh->prepare($sql);
        $sth->execute();
        $titles = array("图片","款号", "款名", "单价","吊牌价","颜色");
        for($i=1;$i<$color_max_num;$i++){
            $titles =   array_merge($titles,array(""));
        }
        $titles = array_merge($titles,array("数量","金额","备注"));

        $ExcelWriter    = new ExcelWriter($excel_name, $options=array("titles"=>$titles));

        $num    = 6;
        $col    = 2;
        $sheet  = $ExcelWriter->getActiveSheet();
        $color_key  = sprintf("%c%d",65 + 4 + $color_max_num, 1);
        $sheet->mergeCells("F1:".$color_key);

        while($row  = $sth->fetch()){
            $color_ids      = explode(",", $row['color_ids']);
            $color_list     = array();
            foreach($color_ids as $color_id){
                $color_list[$color_id]   = Keywords::cache_get(array($color_id));
            }
            $KEY1   = $col;
            $KEY2   = $KEY1+1;
            $col    +=2;

            $sheet->mergeCells("A{$KEY1}:A{$KEY2}");
            $sheet->mergeCells("B{$KEY1}:B{$KEY2}");
            $sheet->mergeCells("C{$KEY1}:C{$KEY2}");
            $sheet->mergeCells("D{$KEY1}:D{$KEY2}");
            $sheet->mergeCells("E{$KEY1}:E{$KEY2}");
            $ExcelWriter->setCellImage("A{$KEY1}", DOCUMENT_ROOT . "thumb/75/". $row['defaultimage']);
            $sheet->SetCellValue("B{$KEY1}", $row['kuanhao']);
            $sheet->SetCellValue("C{$KEY1}", $row['name']);
            $sheet->SetCellValue("D{$KEY1}", $row['discount_unit_price']);
            $sheet->SetCellValue("E{$KEY1}", $row['price']);
            $sheet->getRowDimension($KEY1)->setRowHeight(40);
            $sheet->getRowDimension($KEY1+1)->setRowHeight(40);
            $color_start_num     = 0;
		$color_num 	= 0;
		$color_amount  = 0;	
            foreach($color_list as $color_id=>$color){
                $color_start_num++ ;
                $c          =   65 + 4 + $color_start_num;
                $color_key  =   sprintf("%c%d", $c , $KEY1);
                $sheet->SetCellValue($color_key, $color);
                $color_key  = sprintf("%c%d",65 + 4 + $color_start_num, $KEY1+1);
                if($user['type']==2){
                    $sql2       = "SELECT sum(o.num) as num,sum(o.discount_amount) as amount FROM orderlist as o WHERE o.product_id={$row['id']} and o.product_color_id={$color_id} and o.zd_user_id={$user['id']}";
                }elseif($user['type']==1){                 
                    $sql2       = "SELECT sum(o.num) as num,sum(o.discount_amount) as amount FROM orderlist as o WHERE o.product_id={$row['id']} and o.product_color_id={$color_id} and o.user_id={$user['id']}";
                }else{
                    $sql2       = "SELECT sum(o.num) as num,sum(o.discount_amount) as amount FROM orderlist as o WHERE o.product_id={$row['id']} and o.product_color_id={$color_id}";
                }
                $sth2       = $dbh->prepare($sql2);
                $sth2->execute();
                $colororder = $sth2->fetch();

                $sheet->SetCellValue($color_key, $colororder['num']);
		$color_num 	+= $colororder['num'];
		$color_amount  += $colororder['amount'];
            }
            $KEY    = sprintf("%c%d",65 + 5 + $color_max_num, $KEY1);
            $KEY2   = sprintf("%c%d",65 + 5 + $color_max_num, $KEY1+1);
            $sheet->mergeCells("{$KEY}:{$KEY2}");
            $sheet->SetCellValue("{$KEY}", $color_num);

            $KEY    = sprintf("%c%d",65 + 6 + $color_max_num, $KEY1);
            $KEY2   = sprintf("%c%d",65 + 6 + $color_max_num, $KEY1+1);
            $sheet->mergeCells("{$KEY}:{$KEY2}");
            $sheet->SetCellValue("{$KEY}", $color_amount);

            $KEY    = sprintf("%c%d",65 + 7 + $color_max_num, $KEY1);
            $KEY2   = sprintf("%c%d",65 + 7 + $color_max_num, $KEY1+1);
            $sheet->mergeCells("{$KEY}:{$KEY2}");
        }
        $sheet->getColumnDimension('A')->setWidth(12);
    }
    /* 迷你结束 */
    /*尤西子报表*/
    public static function Action_yxz_report($r){
        set_time_limit(0);
        @ini_set('memory_limit','1024M');

        $size_factory = new ProductsAttributeFactory('size');
        $size_list    = $size_factory->getAllList();

        $OrderList = new OrderList;
        
        $options                = array();
        $options['tablename']   = "product_color as pc 
                                    left join product as p on p.id=pc.product_id
                                    left join orderlist as o on pc.product_id=o.product_id and pc.color_id=o.product_color_id";
        $options['fields']      = "p.*,sum(o.num) as num,sum(o.amount) as amount,
                                    COUNT(DISTINCT o.user_id) as user_num,GROUP_CONCAT(DISTINCT pc.color_id) as color_ids";
        $options['order']       = "num DESC";
        $options['group']       = "p.id";
        $options['limit']       = 10000;
        // $options['db_debug']    = true;

        $condition      = array();
        $condition[]    = "p.status <> 0";
        $where  =   implode(' AND ', $condition);

        $list   =   $OrderList->find($where,$options);
        // print_r($list);exit;
        $options    =   array();
        $options['fields']  =   "sum(num) as num";
        // $options['db_debug']= true;
        
        $excel_name     = sprintf("%s总报表", $user['name']);
        $ExcelWriter    = new ExcelWriter($excel_name);

        $ExcelWriter->setTitle('总量排行');
        $sheet  = $ExcelWriter->getActiveSheet();

        $sheet->mergeCells("A1:A2");
        $sheet->mergeCells("B1:B2");
        $sheet->mergeCells("C1:C2");
        $sheet->mergeCells("D1:D2");
        $sheet->mergeCells("E1:E2");
        $sheet->mergeCells("F1:F2");
        $sheet->mergeCells("G1:G2");
        $sheet->mergeCells("H1:H2");
        $sheet->SetCellValue("A1", "排名");
        $sheet->SetCellValue("B1", "编号");
        $sheet->SetCellValue("C1", "图片");
        $sheet->SetCellValue("D1", "货号");
        $sheet->SetCellValue("E1", "系列");
        $sheet->SetCellValue("F1", "波段");
        $sheet->SetCellValue("G1", "类别");
        $sheet->SetCellValue("H1", "颜色");

        $size_index = count($size_list);
        $size_key = sprintf("%c%d",65 + 7 + $size_index, 1);
        $sheet->mergeCells("I1:{$size_key}");
        $sheet->SetCellValue("I1", "尺码");

        $total_key= sprintf("%c",65 + 8 + $size_index);
        $sheet->mergeCells("{$total_key}1:{$total_key}2");
        $sheet->SetCellValue("{$total_key}1", "合计");
        
        $price_key= sprintf("%c",65 + 9 + $size_index);
        $sheet->mergeCells("{$price_key}1:{$price_key}2");
        $sheet->SetCellValue("{$price_key}1", "金额");
        
        $total_num_key= sprintf("%c",65 + 10 + $size_index);
        $sheet->mergeCells("{$total_num_key}1:{$total_num_key}2");
        $sheet->SetCellValue("{$total_num_key}1", "总数量");
        
        $user_num_key= sprintf("%c",65 + 11 + $size_index);
        $sheet->mergeCells("{$user_num_key}1:{$user_num_key}2");
        $sheet->SetCellValue("{$user_num_key}1", "下单人数");
        
        foreach ($size_list as $key=>$size) {
            $size_key  = sprintf("%c%d",65 + 8 + $key, 2);
            $sheet->SetCellValue($size_key,$size['keywords']['name']);
        }

        $line   = 3;
        $rank   = 1;
        foreach ($list as $row) {
            $color_ids      = explode(",", $row['color_ids']);
            $color_list     = array();
            foreach ($color_ids as $color_id) {
                $color_list[$color_id] = Keywords::cache_get(array($color_id));
            }
            $color_num  = count($color_list);
            $lines      = $color_num+$line-1;
            $rows       = $line;
            if($color_num==1){
                $sheet->getRowDimension($rows)->setRowHeight(80);
            }else{
                foreach ($color_list as $value) {
                    $sheet->getRowDimension($rows++)->setRowHeight(40);
                }
            }
            $sheet->mergeCells("A{$line}:A{$lines}");
            $sheet->mergeCells("B{$line}:B{$lines}");
            $sheet->mergeCells("C{$line}:C{$lines}");
            $sheet->mergeCells("D{$line}:D{$lines}");
            $sheet->mergeCells("E{$line}:E{$lines}");
            $sheet->mergeCells("F{$line}:F{$lines}");
            $sheet->mergeCells("G{$line}:G{$lines}");
            $sheet->mergeCells("{$price_key}{$line}:{$price_key}{$lines}");
            $sheet->mergeCells("{$total_num_key}{$line}:{$total_num_key}{$lines}");
            $sheet->mergeCells("{$user_num_key}{$line}:{$user_num_key}{$lines}");

            $sheet->SetCellValue("A{$line}",$rank++);
            $sheet->SetCellValue("B{$line}",$row['bianhao']);
            $ExcelWriter->setCellImage("C{$line}", DOCUMENT_ROOT . "thumb/75/". $row['defaultimage']);
            $sheet->SetCellValue("D{$line}",Keywords::cache_get($row['huohao']));
            $sheet->SetCellValue("E{$line}",Keywords::cache_get($row['series_id']));
            $sheet->SetCellValue("F{$line}",Keywords::cache_get($row['wave_id']));
            $sheet->SetCellValue("G{$line}",Keywords::cache_get($row['category_id']));
            
            $color_line = $line;
            foreach ($color_list as $color_id => $name) {
                $sheet->SetCellValue("H{$color_line}",$name);
                $size_index = 1;
                $total_num  = 0;
                foreach ($size_list as $size) {
                    $size_key  = sprintf("%c%d",65 + 7 + $size_index++, $color_line);
                    $order = $OrderList->findone("product_id={$row['id']} AND product_color_id={$color_id} AND product_size_id={$size['keyword_id']}",$options);
                    $total_num += $order['num'];
                    $sheet->SetCellValue($size_key,$order['num']);
                }
                $sheet->SetCellValue("{$total_key}{$color_line}",$total_num);
                $color_line++;
            }

            $sheet->SetCellValue("{$price_key}{$line}",$row['amount']);
            $sheet->SetCellValue("{$total_num_key}{$line}",$row['num']);
            $sheet->SetCellValue("{$user_num_key}{$line}",$row['user_num']);
            $line += $color_num ;
        }

        $sheet->getColumnDimension('C')->setWidth(12);

        /*类别排名*/
        $ExcelWriter->newSheet(1,'类别排名');
        $sheet  = $ExcelWriter->getActiveSheet();

        $sheet->mergeCells("A1:A2");
        $sheet->mergeCells("B1:B2");
        $sheet->mergeCells("C1:C2");
        $sheet->mergeCells("D1:D2");
        $sheet->mergeCells("E1:E2");
        $sheet->mergeCells("F1:F2");
        $sheet->mergeCells("G1:G2");
        $sheet->mergeCells("H1:H2");
        $sheet->SetCellValue("A1", "排名");
        $sheet->SetCellValue("B1", "编号");
        $sheet->SetCellValue("C1", "图片");
        $sheet->SetCellValue("D1", "货号");
        $sheet->SetCellValue("E1", "系列");
        $sheet->SetCellValue("F1", "波段");
        $sheet->SetCellValue("G1", "类别");
        $sheet->SetCellValue("H1", "颜色");

        $size_index = count($size_list);
        $size_key = sprintf("%c%d",65 + 7 + $size_index, 1);
        $sheet->mergeCells("I1:{$size_key}");
        $sheet->SetCellValue("I1", "尺码");

        $total_key= sprintf("%c",65 + 8 + $size_index);
        $sheet->mergeCells("{$total_key}1:{$total_key}2");
        $sheet->SetCellValue("{$total_key}1", "合计");
        
        $price_key= sprintf("%c",65 + 9 + $size_index);
        $sheet->mergeCells("{$price_key}1:{$price_key}2");
        $sheet->SetCellValue("{$price_key}1", "金额");
        
        $total_num_key= sprintf("%c",65 + 10 + $size_index);
        $sheet->mergeCells("{$total_num_key}1:{$total_num_key}2");
        $sheet->SetCellValue("{$total_num_key}1", "总数量");
        
        $user_num_key= sprintf("%c",65 + 11 + $size_index);
        $sheet->mergeCells("{$user_num_key}1:{$user_num_key}2");
        $sheet->SetCellValue("{$user_num_key}1", "下单人数");
        
        foreach ($size_list as $key=>$size) {
            $size_key  = sprintf("%c%d",65 + 8 + $key, 2);
            $sheet->SetCellValue($size_key,$size['keywords']['name']);
        }

        $category_list = array();
        foreach($list as $row){
            $category_list[$row['category_id']][] = $row;
        }

        $line   = 3;
        foreach ($category_list as $category){
            $rank   = 1;
            foreach ($category as $row) {
                $color_ids      = explode(",", $row['color_ids']);
                $color_list     = array();
                foreach ($color_ids as $color_id) {
                    $color_list[$color_id] = Keywords::cache_get(array($color_id));
                }
                $color_num  = count($color_list);
                $lines      = $color_num+$line-1;
                $rows       = $line;
                if($color_num==1){
                    $sheet->getRowDimension($rows)->setRowHeight(80);
                }else{
                    foreach ($color_list as $value) {
                        $sheet->getRowDimension($rows++)->setRowHeight(40);
                    }
                }
                $sheet->mergeCells("A{$line}:A{$lines}");
                $sheet->mergeCells("B{$line}:B{$lines}");
                $sheet->mergeCells("C{$line}:C{$lines}");
                $sheet->mergeCells("D{$line}:D{$lines}");
                $sheet->mergeCells("E{$line}:E{$lines}");
                $sheet->mergeCells("F{$line}:F{$lines}");
                $sheet->mergeCells("G{$line}:G{$lines}");
                $sheet->mergeCells("{$price_key}{$line}:{$price_key}{$lines}");
                $sheet->mergeCells("{$total_num_key}{$line}:{$total_num_key}{$lines}");
                $sheet->mergeCells("{$user_num_key}{$line}:{$user_num_key}{$lines}");

                $sheet->SetCellValue("A{$line}",$rank++);
                $sheet->SetCellValue("B{$line}",$row['bianhao']);
                $ExcelWriter->setCellImage("C{$line}", DOCUMENT_ROOT . "thumb/75/". $row['defaultimage']);
                $sheet->SetCellValue("D{$line}",Keywords::cache_get($row['huohao']));
                $sheet->SetCellValue("E{$line}",Keywords::cache_get($row['series_id']));
                $sheet->SetCellValue("F{$line}",Keywords::cache_get($row['wave_id']));
                $sheet->SetCellValue("G{$line}",Keywords::cache_get($row['category_id']));
                
                $color_line = $line;
                foreach ($color_list as $color_id => $name) {
                    $sheet->SetCellValue("H{$color_line}",$name);
                    $size_index = 1;
                    $total_num  = 0;
                    foreach ($size_list as $size) {
                        $size_key  = sprintf("%c%d",65 + 7 + $size_index++, $color_line);
                        $order = $OrderList->findone("product_id={$row['id']} AND product_color_id={$color_id} AND product_size_id={$size['keyword_id']}",$options);
                        $total_num += $order['num'];
                        $sheet->SetCellValue($size_key,$order['num']);
                    }
                    $sheet->SetCellValue("{$total_key}{$color_line}",$total_num);
                    $color_line++;
                }

                $sheet->SetCellValue("{$price_key}{$line}",$row['amount']);
                $sheet->SetCellValue("{$total_num_key}{$line}",$row['num']);
                $sheet->SetCellValue("{$user_num_key}{$line}",$row['user_num']);
                $line += $color_num ;
            }
        }

        $sheet->getColumnDimension('C')->setWidth(12);
        $ExcelWriter->refreshIndex();
    }
    public static function Action_img_list($r){
        $Product = new Product;
        $product_list = $Product->find("status <> 0",array("fields"=>"defaultimage","limit"=>1000));
        // $html   =   '';
        foreach ($product_list as $row) {
            echo '<img style="float:left" src="/thumb/75/';
            echo $row["defaultimage"];
            echo '">';
        }
    }
}

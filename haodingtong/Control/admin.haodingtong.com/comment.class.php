<?php

class Control_comment {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $data       = $r->query;
        $p          = $data->p;
        $search     = trim($data->search);
        $limit      = 15;
        $ProductComment     = new ProductComment;
        $condition  = array();
        if($search){
            $qt     = addslashes($search);
            $condition[]    = "content like '%{$qt}%'";
        }
        $where  = implode(' AND ', $condition);
        if(!$where) $where = "1";
        $list   = $ProductComment->find($where, array("limit"=>$limit, 'page'=>$p, "order"=>"id desc"));
        $list   = Flight::listFetch($list, 'user', 'user_id', 'id');
        $total  = $ProductComment->getCount($where);
        $pagelist   = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);

        $result['list']     = $list;
        $result['pagelist'] = $pagelist;
        $result['search']   = $search;

        Flight::display('comment/index.html', $result);
    }

    public static function Action_delete($r, $id){
        Flight::validateEditorHasLogin();

        $ProductComment = new ProductComment;
        $ProductComment->update(array('status'=>0), "id=$id");

        Flight::redirect($r->referrer);
    }

    public static function Action_restore($r, $id){
        Flight::validateEditorHasLogin();

        $ProductComment = new ProductComment;
        $ProductComment->update(array('status'=>1), "id=$id");

        Flight::redirect($r->referrer);
    }


    public static function Action_load($r){
        // STATIC::traverse(DOCUMENT_ROOT . "/tmpl/data");

    }

    public static function traverse($path = '.') {
        $current_dir = opendir($path);    //opendir()返回一个目录句柄,失败返回false
        $filelist   = array();
        $product    = array();
        $comment    = array();
        $series_list = array(
"丛林之旅"  => 21,
"胜利者"   =>22,
    "心族系列"  => 38,
    "甜美系列"  => 39,
    "休闲系列"  => 40,
    "职场系列"  => 41,
    "Office系列"  => 103,
    "度假系列"  => 104
        );
        $color_list     = array(
29,
35,
55,
56,
57,
58,
59
            );
        $size_list  = array(28, 34, 53, 54);
        
        while(($file = readdir($current_dir)) !== false) {    //readdir()返回打开目录句柄中的一个条目
            $sub_dir = $path . DIRECTORY_SEPARATOR . $file;    //构建子目录路径
            if($file == '.' || $file == '..') {
                continue;
            } else if(is_dir($sub_dir)) {    //如果是目录,进行递归
                STATIC::traverse($sub_dir);
            } else {    //如果是文件,直接输出
                if($file == "intro.txt"){
                    $intro  = file_get_contents($path .'/'. $file);
                    $path1 = str_replace(' ', '', $path);
                    $path1 = str_replace('T', 'TTT', $path1);
                    $path1 = str_replace('t', 'ttt', $path1);
                    $path1 = str_replace('OL', 'OL1', $path1);

                    $product['name']    = substr(iconv('GB2312', 'UTF-8', $path1), -3 * 15);
                    echo $product['name'], "<br>";
                    if(!$product['name']){
                        echo "error:{$path}";exit;
                    }
                    $series     = iconv('GB2312', 'UTF-8', $path);
                    $series     = preg_replace("/^.*data\\\/", "", $series);
                    $series     = preg_replace("/\\\.*$/", "", $series);
                    if($series_list[$series]){
                        $product['series_id']   = $series_list[$series];
                    }else{
                        $product['series_id']   = $series_list[array_rand($series_list)];
                    }

                    $attrs = explode("\r\n", $intro);
                    foreach($attrs as $string){
                        $string = iconv('GB2312', 'UTF-8', $string);
                        if(preg_match('/价格.*?([\d.]+)/', $string, $matches)){
                            $product['price']   = $matches[1];
                        }elseif(preg_match("/货号: (.*)$/", $string, $matches)){
                            $product['huohao']  = $matches[1];
                        }else{
                            $product['content'] .= $string . " ";
                        }
                    }
                }elseif($file == "pinglun.txt"){
                    $comment_content = file_get_contents($path . '/'. $file);
                    $comment_list   = explode("\r\n", $comment_content);
                    foreach($comment_list as $string){
                        $string = iconv('GB2312', 'UTF-8', $string);
                        if($string){
                            $comment[]  = $string;
                        }
                    }
                }else{
                    $filelist[]  = $file;
                }
            }
        }
        
        $Product    = new Product;
        $ProductImage   = new ProductImage;
        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $ProductComment = new ProductComment;
        if(count($product)){
            $p  = $Product->create($product);
            $pid = $p->insert();
            if($pid){
                $imagepath  = DOCUMENT_ROOT . 'img/';
                $i = 0;
                foreach($filelist as $f){
                    $filepath = "data/{$pid}/";
                    if(!is_dir($imagepath . $filepath)){
                        mkdir($imagepath. $filepath);
                    }
                    copy($path .'/'. $f, $imagepath. $filepath. $f);
                    if($i == 0){
                        $Product->update(array('defaultimage'=>$filepath.$f), "id=$pid");
                    }
                    $i++;
                    $ProductImage->create(array('product_id'=>$pid, 'image'=>$filepath.$f))->insert();
                }
                foreach($color_list as $c){
                    $ProductColor->create(array('product_id'=>$pid, 'color_id'=>$c))->insert();
                }
                foreach($size_list as $s){
                    $ProductSize->create(array('product_id'=>$pid, 'size_id'=>$s))->insert();
                }
                foreach($comment as $ct){
                    $uid    = rand(11, 35);
                    $score  = rand(1,5);
                    $ProductComment->create(array('product_id'=>$pid, 'user_id'=>$uid, 'content'=>$ct, 'score'=>$score))->insert();
                }
            }
        }
        
    }

    

}
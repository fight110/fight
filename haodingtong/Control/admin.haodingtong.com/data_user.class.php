<?php

class Control_data_user {
    public static function Action_index ($r) {
        Flight::validateEditorHasLogin();
        header("Content-type: text/html; charset=utf-8"); 
        echo "<form action='/data_user/export' method='POST'><input type='text' name='username' placeholder='输入帐号'><input type='submit'></form> 多个账户用;分隔";
    }

    public static function Action_export($r){
        Flight::validateEditorHasLogin();

        $Company        = new Company;
        $User           = new User;
        $Product        = new Product;
        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $OrderList      = new OrderList;
        $OrderListBak   = new OrderListBak;
        $Factory        = new ProductsAttributeFactory('size');
        
        $fairname       = str_replace(' ', '', $Company->fairname);
        $limit          = $r->query->limit  ? $r->query->limit  : 40;
        $p              = $r->query->p      ? $r->query->p      : 1;
        $username       = $r->data->username;

        if($username){
            $unamelist      = explode(";", $username);
            foreach($unamelist as $uname){
                $user   = $User->findone("username='{$uname}'");
                $user_id_list[] = $user['id'];
            }
            $user_id    = implode(",", $user_id_list);
            $user_name  = count($unamelist) == 1 ? $user['name']    : "自定义";
            $excel_name     = sprintf("%s-%s订货本", $user_name, $fairname);
        }else{
            $excel_name     = sprintf("%s订货本", $fairname);
        }
        
        $ExcelWriter    = new ExcelWriter($excel_name, $options=array());
        $sheet  = $ExcelWriter->getActiveSheet();

        $k      = 0;
        $i      = 1;
        $sheet->SetCellValue(STATIC::get_Excel_line($k++) . "{$i}", '编号');
        $sheet->SetCellValue(STATIC::get_Excel_line($k++) . "{$i}", '款号');
        $sheet->SetCellValue(STATIC::get_Excel_line($k++) . "{$i}", '款名');
        $sheet->SetCellValue(STATIC::get_Excel_line($k++) . "{$i}", '大类');
        $sheet->SetCellValue(STATIC::get_Excel_line($k++) . "{$i}", '小类');
        $sheet->SetCellValue(STATIC::get_Excel_line($k++) . "{$i}", '波段');
        $sheet->SetCellValue(STATIC::get_Excel_line($k++) . "{$i}", '单价');
        $sheet->SetCellValue(STATIC::get_Excel_line($k++) . "{$i}", '删除款');
        $sheet->SetCellValue(STATIC::get_Excel_line($k++) . "{$i}", '颜色');

        $size_group_list    = $Factory->get_group_list();
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
            $size_k     = $k;
            for($j = 0; $j < $newSizeLength; $j++){
                $size_id    = $ary[$j];
                $newSizeList[$j][]  = $size_id;
                $newSizeHash[$size_id]  = $j;
                $sheet->SetCellValue(STATIC::get_Excel_line($size_k++) . "{$i}", Keywords::cache_get($size_id));
            }
            $i++;
        }

        $plist  = $Product->find("1", array("limit"=>10000,"order"=>"id asc"));
        foreach($plist as $product){
            $product_id = $product['id'];
            $pclist     = $ProductColor->get_color_list($product_id);
            $size_list  = $ProductSize->get_size_list($product_id);
            foreach($pclist as $pc) {
                $k  = 0;
                $sheet->SetCellValue(STATIC::get_Excel_line($k++)."{$i}", $pc['skc_id']);
                $sheet->SetCellValue(STATIC::get_Excel_line($k++)."{$i}", $product['kuanhao']);
                $sheet->SetCellValue(STATIC::get_Excel_line($k++)."{$i}", $product['name']);
                $sheet->SetCellValue(STATIC::get_Excel_line($k++)."{$i}", Keywords::cache_get($product['category_id']));
                $sheet->SetCellValue(STATIC::get_Excel_line($k++)."{$i}", Keywords::cache_get($product['classes_id']));
                $sheet->SetCellValue(STATIC::get_Excel_line($k++)."{$i}", Keywords::cache_get($product['wave_id']));
                $sheet->SetCellValue(STATIC::get_Excel_line($k++)."{$i}", $product['price']);
                $sheet->SetCellValue(STATIC::get_Excel_line($k++)."{$i}", $pc['status'] ? '正常款' : '删除款');
                $sheet->SetCellValue(STATIC::get_Excel_line($k++)."{$i}", Keywords::cache_get($pc['color_id']));
                if($pc['status']){
                    if($user_id){
                        $where  = "user_id in({$user_id}) AND product_id={$product_id} AND product_color_id={$pc['color_id']}";
                        $options['fields']  = "product_size_id, sum(num) as num";
                        $options['key']     = "product_size_id";
                        $options['group']   = "product_size_id";
                        $options['limit']   = 1000;
                        $order  = $OrderList->find($where, $options);
                        foreach($size_list as $size){
                            $n      = $newSizeHash[$size['size_id']];
                            $key    = STATIC::get_Excel_line($k+$n);
                            $num    = $order[$size['size_id']]['num'];
                            $sheet->SetCellValue("{$key}{$i}", $num);
                        }
                    }
                }else{
                    // if($user_id){
                    //     $where  = "user_id in({$user_id}) AND product_id={$product_id} AND product_color_id={$pc['color_id']}";
                    //     $options['fields']  = "product_size_id, sum(num) as num";
                    //     $options['key']     = "product_size_id";
                    //     $options['group']   = "product_size_id";
                    //     $options['limit']   = 1000;
                    //     $order  = $OrderListBak->find($where, $options);
                    //     foreach($size_list as $size){
                    //         $n      = $newSizeHash[$size['size_id']];
                    //         $key    = STATIC::get_Excel_line($k+$n);
                    //         $num    = $order[$size['size_id']]['num'];
                    //         $sheet->SetCellValue("{$key}{$i}", $num);
                    //     }
                    // }
                }
                $i++;
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
}
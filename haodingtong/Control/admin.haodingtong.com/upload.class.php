<?php

class Control_upload{
    public static function Action_index($r){
        $result         = array('valid' => true);
        foreach($r->files as $file){
            $allowedExts    = array("gif", "jpeg", "jpg", "png");
            $explodes       = explode(".", $file["name"]);
            $extension      = end($explodes);
            // if ((($file["type"] == "image/gif")
            // || ($file["type"] == "image/jpeg")
            // || ($file["type"] == "image/jpg")
            // || ($file["type"] == "image/pjpeg")
            // || ($file["type"] == "image/x-png")
            // || ($file["type"] == "image/png")) && in_array($extension, $allowedExts)){
            //     $result['message']  = "后缀名不合法:{$extension}";
            // }elseif($file["size"] < 1024 * 1024 * 50){
            if(1){
                if ($file["error"] > 0){
                    $result['message']  = $file['error'];
                }else{
                    $dir            = date("Y/m/d/i/");
                    if(!is_dir(UPLOAD_PATH . $dir)){
                        mkdir(UPLOAD_PATH . $dir, 0744, true);
                    }
                    $newfilename    = $dir . md5($file['name'].rand()) . ".{$extension}";
                    if (file_exists(UPLOAD_PATH . $newfilename)){
                        $result['message']  = "创建文件失败";
                    }else{
                        move_uploaded_file($file["tmp_name"], UPLOAD_PATH . $newfilename);
                        $result['filename'] = $file['name'];
                        $result['filepath'] = $newfilename;
                    }
                }
            }else{
                $result['message']  = "文件太大了:{$file['size']}";
            }
            break;
        }
        if($result['message'])  $result['valid']    = false;

        Flight::json($result);
    }
}
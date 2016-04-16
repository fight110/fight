<?php

define('UPLOAD_PATH', DOCUMENT_ROOT . '/img/');
		
        foreach($_FILES as $file){
            $allowedExts    = array("gif", "jpeg", "jpg", "png");
            $explodes       = explode(".", $file["name"]);
            $extension      = end($explodes);
            if (!(
            	($file["type"] == "image/gif")
            || ($file["type"] == "image/jpeg")
            || ($file["type"] == "image/jpg")
            || ($file["type"] == "image/pjpeg")
            || ($file["type"] == "image/x-png")
            || ($file["type"] == "image/png")) || ! in_array($extension, $allowedExts)){
                $result['message']  = "后缀名不合法:{$extension}";
            }elseif($file["size"] < 1024 * 1024 * 3){
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
                        list($width, $height) = getimagesize(UPLOAD_PATH . $newfilename);
                        if($width > 550) $width = 550;
                        $result['url'] = "http://www.20fd.com/thumb/{$width}/$newfilename";
                    }
                }
            }else{
                $result['message']  = "文件太大了:{$file['size']}";
            }
            break;
        }
        $result['error']    = $result['message'] ? 1 : 0;

echo json_encode($result);
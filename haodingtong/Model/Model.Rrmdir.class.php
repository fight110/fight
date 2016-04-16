<?php

class Rrmdir {
    public function __construct(){
    }

    public static function rrmdir($dir) { 
       if (is_dir($dir)) { 
         $objects = scandir($dir); 
         foreach ($objects as $object) { 
           if ($object != "." && $object != "..") { 
             if (filetype($dir."/".$object) == "dir") 
                Rrmdir::rrmdir($dir."/".$object); 
             else 
                unlink($dir."/".$object); 
           } 
         } 
         reset($objects); 
         rmdir($dir); 
        } 
    } 
}





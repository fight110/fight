<?php
error_reporting(0);
$_SERVER['DOCUMENT_ROOT']   = $_SERVER['DOCUMENT_ROOT'] . '/';
require $_SERVER['DOCUMENT_ROOT'] . 'config/global.conf.php';

Flight::route('/0', function(){
    Flight::redirect("/download/haodingtong_forever.mobileconfig");
});
Flight::route('/1', function(){
	Flight::redirect("/download/haodingtong.apk");
});
Flight::route('/3', function(){
    Flight::redirect("/download/haodingtong_3.mobileconfig");
});
Flight::route('/4', function(){
    Flight::redirect("/download/haodingtong_4.mobileconfig");
});
      
Flight::route('/thumb/*', function(){
    route('thumb');
});
Flight::route('/', route);
Flight::route('/@a(/@b(/@c(/)))', route);

Flight::start();
<?php

class Control_clear {
    public static function Action_display($r){
        Flight::validateEditorHasLogin();
        $sqls[] = "TRUNCATE TABLE group_to_display";
        $sqls[] = "TRUNCATE TABLE product_display";
        $sqls[] = "TRUNCATE TABLE product_display_image";
        $sqls[] = "TRUNCATE TABLE product_display_member";
        $sqls[] = "TRUNCATE TABLE product_display_member_color";
        $dbh    = Flight::DatabaseMaster();
        foreach($sqls as $sql){
            $dbh->query($sql);
        }
        echo 'success';
    }

    public static function Action_group($r){
        Flight::validateEditorHasLogin();
        $sqls[] = "TRUNCATE TABLE group_to_display";
        $sqls[] = "TRUNCATE TABLE product_group";
        $sqls[] = "TRUNCATE TABLE product_group_image";
        $sqls[] = "TRUNCATE TABLE product_group_member";
        $dbh    = Flight::DatabaseMaster();
        foreach($sqls as $sql){
            $dbh->query($sql);
        }
        echo 'success';
    }
}
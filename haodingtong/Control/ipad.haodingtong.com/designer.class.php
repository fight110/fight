<?php

class Control_designer {
    public static function _beforeCall($r, $id=0){
        $User   = new User;
        if($User->type != 9){
            Flight::redirect("/");
            return false;
        }
    }

    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $User = new User;
        $name = $User->name;
        $Keywords   = new Keywords;
        $result['designer_id'] = $Keywords->getKeywordId($name);
        $result['control'] = 'designer';
        Flight::display("designer/index.html", $result);
    }
}

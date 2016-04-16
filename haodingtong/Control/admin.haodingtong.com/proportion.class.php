<?php

class Control_proportion {
    public static function Action_select_proportion ($r) {
        Flight::validateEditorHasLogin();

        $size_group_list        = array();
        $size_group_instances   = SizeGroup::getAllInstance();
        foreach($size_group_instances as $size_instance) {
            $intro  = array();
            $intro['size_group_id']  = $size_instance->size_group_id;
            $size_group_list[]  = $intro;
        }
        $result['size_group_list']  = $size_group_list;
        $result['control']          = 'select_proportion';

        Flight::display("proportion/select_proportion.html", $result);
    }

    public static function Action_select_proportion_list ($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->query;
        $size_group_id  = $data->size_group_id;
        if($size_group_id) {
            $size_instance  = SizeGroup::getInstance($size_group_id);
            $User                   = new User;
            $ProductProportion      = new ProductProportion;
            $proportion_list        = $ProductProportion->get_proportion_list(0, $size_instance->size_group_id);
            $result['proportion_list']  = $proportion_list;
            $result['size_list']    = $size_instance->get_size_list();
            $result['num']          = $size_instance->option('num');
            $result['limit']        = $size_instance->option('restriction');
            $result['size_group_id']         = $size_instance->size_group_id;
        }

        Flight::display("proportion/select_proportion_list.html", $result);
    }

    public static function Action_set_user_proportion ($r) {
        Flight::validateEditorHasLogin();

        $data   = $r->data;
        $size_group_id      = $data->size_group_id;
        $proportion     = $data->proportion;
        if($proportion) {
            $User               = new User;
            $ProductProportion  = new ProductProportion;
            $ProductProportion->create_proportion(0, $size_group_id, $proportion);
        }
        $result     = array();

        Flight::json($result);
    }

}
<?php
class Control_plot {
    public static function _beforeCall($r, $id=0){
        Flight::validateUserHasLogin();
    }

    public static function Action_index($r){
        Flight::notFound();        
    }

    public static function Action_plot($r){
        $result     = FrontSetting::build();

        $result['control'] 	=	"plot";
        Flight::display("plot/plot.html",$result);
    }

    public static function Action_user_plot($r){
        $data       =   $r->data;
        $area1      =   $data->area1;
        $area2      =   $data->area2;
        $search_user=   addslashes($data->search_user);

        $condition  =   array();
        if($area1){
            $condition[]    =   "u.area1={$area1}";
        }
        if($area2){
            $condition[]    =   "u.area2={$area2}";
        }
        if($search_user){
            $condition[]    =   "u.username='{$search_user}' or u.name='{$search_user}'";
        }

        $PlotUser   =   new PlotUser;
        $options    =   array();

        $options['tablename']   =   "plot_user as pu left join user as u on pu.user_id=u.id";
        $options['fields']      =   "pu.time_axis,sum(pu.amount) as amount,sum(pu.num) as num";
        $options['group']       =   "pu.time_axis";
        $options['order']       =   "pu.time_axis desc";
        // $options['db_debug']    =   true;
        $options['limit']       =   10;

        $where  =   implode(' AND ', $condition);
        $list   =   $PlotUser->find($where,$options);
        $result['list']     =   $list;
        Flight::json($result);
    }

    public static function Action_product_plot($r){
        $data           =   $r->data;
        $search_product =   addslashes($data->search_product);
        $keys   =   array("category_id","classes_id","wave_id","series_id","season_id","sxz_id","medium_id","nannczhuan_id","style_id","price_band_id","theme_id");
        
        $condition  =   array();
        foreach($keys as $key){
            if($data[$key])
                $condition[]    = "p.{$key}={$data[$key]}";
        }
        if($search_product){
            $condition[]        =   "p.kuanhao='{$search_product}' or p.id in (select product_id from product_color where skc_id='{$search_product}')";
        }

        $PlotProduct    =   new PlotProduct;
        $options        =   array();

        $options['tablename']   =   "plot_product as pp left join product as p on pp.product_id=p.id";
        $options['fields']      =   "pp.time_axis,sum(pp.amount) as amount,sum(pp.num) as num";
        $options['group']       =   "pp.time_axis";
        $options['order']       =   "pp.time_axis desc";
        // $options['db_debug']    =   true;
        $options['limit']       =   10;

        $where  =   implode(' AND ', $condition);
        $list   =   $PlotProduct->find($where,$options);
        $result['list']     =   $list;
        Flight::json($result);
    }
}

<?php

class OrderListIReport Extends OrderList
{
	public function __construct()
	{
		$this->setFactory('orderlist');
	}

	/**
	 * 获取尺码组相关信息
	 */
	public static function fetchSizeGroupIds()
	{
		// 尺码组计算
		$size_group_list = SizeGroup::getAllInstance();
		// 枚举各尺码组可用尺码信息
		$size_ids_array       = array();
		$size_ids             = array();
		$max_size_group_count = 0;
		foreach ($size_group_list as $size_group) {
			$size_name_list = $size_group->get_size_list();
			if (!$size_name_list) {
				continue;
			}
			$group_count = count($size_name_list);
			if ($group_count > $max_size_group_count) {
				$max_size_group_count = $group_count;
			}
			$group_size_ids = array();
			foreach ($size_name_list as $size) {
				$group_size_ids[] = $size['size_id'];
			}
			$size_ids_array[] = $group_size_ids;
		}

		// 按尺码顺序获取尺码ID
		for ($i = 0; $i < $max_size_group_count; $i++) {
			$size_ids[$i] = array();
			foreach ($size_ids_array as $seq => $group_size_ids) {
				if (isset($group_size_ids[$i])) {
					$size_ids[$i][] = $group_size_ids[$i];
				}
			}
		}
		return $size_ids;
	}


	/**
	 * 根据查询参数生成WHERE查询的SQL子句
	 * @param $cond
	 * @return string
	 */
	public function getWhere($cond)
	{
		$query_keys  = array(
			'orderlist' => array('product_color_id',),
			'product' => array('category_id', 'wave_id', 'sxz_id', 'classes_id', 'style_id', 'series_id', 'theme_id', 'kuanhao', 'name',),
			'product_color' => array('skc_id', 'color_id',),
			'user' => array('property', 'client_name', 'area1', 'area2',),
		);
		$table_abbrs = array(
			'orderlist' => 'o',
			'product' => 'p',
			'product_color' => 'pc',
			'user' => 'u',
		);

		$conditions = array();
		$cond_keys  = array_keys($cond);
		foreach ($query_keys as $table => $table_keys) {
			foreach ($table_keys as $key) {
				// 客户名称的键名与表中不一致
				$key_name = $key == 'client_name' ? 'name' : $key;
				// 搜索字段
				if (in_array($key, $cond_keys) and is_array($cond[$key]) and $cond[$key]) {
					$conditions[] = $table_abbrs[$table] . ".$key_name IN(" . implode(",", array_map(function ($s) {
							return (int)$s;
						}, $cond[$key])) . ")";
				}
			}
		}

		if (in_array('key', $cond_keys) and $cond['key']) {
			$conditions[] = "(u.name LIKE '%" . $cond['key'] . "%' OR u.username LIKE '" . $cond['key'] . "' OR pc.skc_id LIKE '" . $cond['key'] . "' OR p.kuanhao LIKE '" . $cond['key'] . "')";
		}
		return implode(" AND ", $conditions);
	}

	public function getOrderListSum($cond = array())
	{
		$tablename            = "orderlist AS o " .
			"LEFT JOIN product AS p ON o.product_id=p.id " .
			"LEFT JOIN user AS u ON o.user_id=u.id " .
			"LEFT JOIN product_color AS pc ON o.product_id=pc.product_id AND o.product_color_id=pc.color_id " .
			"LEFT JOIN user_discount AS ud ON ud.user_id=o.user_id AND ud.category_id=p.category_id ";
		$fields               = "SUM(o.num) as num, SUM(o.amount) as price";
		$where                = $this->getWhere($cond);
		$options['tablename'] = $tablename;
		$options['fields']    = $fields;
//		$options['db_debug']  = true;
		return $this->findone($where, $options);
	}

	/**
	 * 根据条件查询订单信息
	 * @param array $cond
	 * @param array $keyword_ids
	 * @param string $order
	 * @param int $page
	 * @return array
	 */
	public function getOrderList($cond = array(), $keyword_ids = array(), $order = '', $page = 0, $page_size = 5000)
	{
		$tablename = "orderlist o " .
			"LEFT JOIN product p ON o.product_id=p.id " .
			"LEFT JOIN user u ON o.user_id=u.id " .
			"LEFT JOIN product_color pc ON o.product_id=pc.product_id AND o.product_color_id=pc.color_id " .
			"LEFT JOIN user_discount ud ON ud.user_id=o.user_id AND ud.category_id=p.category_id " .
			"LEFT JOIN location l ON u.area1=l.id " .
			"LEFT JOIN location l2 ON u.area2=l2.id ";
		/*
		$fields = "o.product_id, o.product_color_id, u.name as client_name, l.name AS area1,l2.name AS area2, u.property," .
			"p.name,p.kuanhao,p.bianhao,p.size_group_id,p.category_id,p.classes_id,p.wave_id,p.style_id,p.price_band_id,p.brand_id," .
			"p.series_id,p.theme_id,p.nannvzhuan_id,p.sxz_id,p.season_id,p.defaultimage,p.mininum," .
			"SUM(o.num) as num,p.price as p_price,SUM(o.amount) as price,SUM(o.discount_amount) as discount_price,GROUP_CONCAT(o.product_size_id,':',o.num) as F,pc.skc_id";
		*/

		$fields = "p.nannvzhuan_id, p.season_id, p.bianhao, p.size_group_id, p.price_band_id, p.brand_id,p.price as p_price, " .
			"SUM(o.num) as num, SUM(o.amount) as price, SUM(o.discount_amount) as discount_price";#.", GROUP_CONCAT(o.product_size_id,':',o.num) as F";

		$field_keys      = array(
			'product_color_id' => 'o.product_color_id',
			'name' => 'p.name',
			'kuanhao' => 'p.kuanhao',
			'category_id' => 'p.category_id',
			'wave_id' => 'p.wave_id',
			'sxz_id' => 'p.sxz_id',
			'classes_id' => 'p.classes_id',
			'style_id' => 'p.style_id',
			'series_id' => 'p.series_id',
			'theme_id' => 'p.theme_id',
			'skc_id' => 'pc.skc_id',
			'color_id' => 'pc.color_id',
			'client_name' => 'u.name as client_name',
			'property' => 'u.property',
			'area1' => 'l.name AS area1',
			'area2' => 'l2.name AS area2',
		);
		$used_field_keys = array();
		foreach ($keyword_ids as $keyword_id) {
			$used_field_keys[] = $field_keys[$keyword_id];
		}
		$fields = join(',', array(join(',', $used_field_keys), $fields));


		$group_key_map = array(
			'product_color_id' => 'o.product_color_id',
			'name' => 'o.product_id',
			'kuanhao' => 'o.product_id',
			'category_id' => 'p.category_id',
			'wave_id' => 'p.wave_id',
			'sxz_id' => 'p.sxz_id',
			'classes_id' => 'p.classes_id',
			'style_id' => 'p.style_id',
			'series_id' => 'p.series_id',
			'theme_id' => 'p.theme_id',
			'skc_id' => 'pc.skc_id',
			'color_id' => 'pc.color_id',
			'client_name' => 'o.user_id',
			'property' => 'u.property',
			'area1' => 'u.area1',
			'area2' => 'u.area2',
		);
		$group_keys    = array();
		$order_keys    = array();
		foreach ($keyword_ids as $group_key) {
			// 分组字段
			if (in_array($group_key, array_keys($group_key_map))) {
				$group_keys[] = $group_key_map[$group_key];
			}

			// 排序字段
			switch ($group_key) {
				case 'product_color_id':
					$tablename .= "LEFT JOIN products_attr AS kw_color ON kw_color.keyword_id=o.product_color_id AND kw_color.field='color' ";
					$order_keys[] = 'kw_color.rank ASC';
					break;
				case 'category_id':
					$tablename .= "LEFT JOIN products_attr AS kw_category ON kw_category.keyword_id=p.category_id AND kw_category.field='category' ";
					$order_keys[] = 'kw_category.rank ASC';
					break;
				case 'wave_id':
					$tablename .= "LEFT JOIN products_attr AS kw_wave ON kw_wave.keyword_id=p.wave_id AND kw_wave.field='wave' ";
					$order_keys[] = 'kw_wave.rank ASC';
					break;
				case 'sxz_id':
					$tablename .= "LEFT JOIN products_attr AS kw_sxz ON kw_sxz.keyword_id=p.sxz_id AND kw_sxz.field='sxz' ";
					$order_keys[] = 'kw_sxz.rank ASC';
					break;
				case 'classes_id':
					$tablename .= "LEFT JOIN products_attr AS kw_classes ON kw_classes.keyword_id=p.classes_id AND kw_classes.field='classes' ";
					$order_keys[] = 'kw_classes.rank ASC';
					break;
				case 'style_id':
					$tablename .= "LEFT JOIN products_attr AS kw_style ON kw_style.keyword_id=p.style_id AND kw_style.field='style' ";
					$order_keys[] = 'kw_style.rank ASC';
					break;
				case 'series_id':
					$tablename .= "LEFT JOIN products_attr AS kw_series ON kw_series.keyword_id=p.series_id AND kw_series.field='series' ";
					$order_keys[] = 'kw_series.rank ASC';
					break;
				case 'theme_id':
					$tablename .= "LEFT JOIN products_attr AS kw_theme ON kw_theme.keyword_id=p.theme_id AND kw_theme.field='theme' ";
					$order_keys[] = 'kw_theme.rank ASC';
					break;
				default:
					if (in_array($group_key, array_keys($group_key_map))) {
						$order_keys[] = $group_key_map[$group_key];
					}
//					foreach ($query_keys as $table => $table_keys) {
//						if (in_array($group_key, $table_keys)) {
//							// 客户名称的键名与表中不一致
//							$key_name     = $group_key == 'client_name' ? 'name' : $group_key;
//							$order_keys[] = $table_abbrs[$table] . "." . $key_name . ' ASC';
//						}
//					}
			}
		}
		$group_string = join(',', array_unique($group_keys));
		$order_string = join(', ', array_unique($order_keys));


		// 按尺码顺序号统计订单总数
		$size_ids    = $this->fetchSizeGroupIds();
		$size_fields = array();
		foreach ($size_ids as $seq => $size_id_array) {
			$size_key = "size_s" . $seq;
			if (count($size_id_array)) {
				$size_fields[] = "SUM(CASE WHEN o.product_size_id IN (" . join(',', $size_id_array) . ") THEN o.num END) AS $size_key";
			} else {
				$size_fields[] = "0 AS $size_key";
			}
		}
		$fields = join(',', array(join(',', $size_fields), $fields));


		$where                = $this->getWhere($cond);
		$options['tablename'] = $tablename;
		$options['fields']    = $fields;
		$options['group']     = $group_string;  //		$options['group']     = "o.product_id, o.product_color_id";
		$options['order']     = $order ? ($order . ' DESC') : $order_string;  //		$options['order']     = 'pc.skc_id asc';
		$options['limit']     = $page ? $page_size : 1000000;    // 有页码时控制每页获取，否则几乎全部获取
		$page and $options['page'] = $page;
//		$options['db_debug']  = true;
		return $this->find($where, $options);
	}

	/**
	 * 根据条件分组统计查询订单信息
	 * @param array $cond
	 * @param array $keyword_ids
	 * @param string $order
	 * @param int $page
	 * @return array
	 */
	public function getOrderListGroup($cond = array(), $keyword_ids = array(), $page = 0, $page_size = 5000)
	{
		$tablename = "orderlist o " .
			"LEFT JOIN user u ON o.user_id=u.id " .
			"LEFT JOIN product_color pc ON o.product_id=pc.product_id AND o.product_color_id=pc.color_id " .
			"LEFT JOIN product p ON pc.product_id=p.id " .
			"LEFT JOIN user_discount ud ON ud.user_id=o.user_id AND ud.category_id=p.category_id " .
			"LEFT JOIN location l ON u.area1=l.id " .
			"LEFT JOIN location l2 ON u.area2=l2.id ";

		$fields = "1 AS is_group, p.nannvzhuan_id, p.season_id, p.bianhao, p.size_group_id, p.price_band_id, p.brand_id,p.price as p_price, " .
			"SUM(o.num) as num, SUM(o.amount) as price, SUM(o.discount_amount) as discount_price";#.", GROUP_CONCAT(o.product_size_id,':',o.num) as F";

		$field_keys = array(
			'product_color_id' => 'COUNT(DISTINCT o.product_color_id) AS product_color_id',
			'name' => 'COUNT(DISTINCT o.product_id) AS product_id',
			'kuanhao' => 'COUNT(DISTINCT p.kuanhao) AS kuanhao',
			'category_id' => 'COUNT(DISTINCT p.category_id) AS category_id',
			'wave_id' => 'COUNT(DISTINCT p.wave_id) AS wave_id',
			'sxz_id' => 'COUNT(DISTINCT p.sxz_id) AS sxz_id',
			'classes_id' => 'COUNT(DISTINCT p.classes_id) AS classes_id',
			'style_id' => 'COUNT(DISTINCT p.style_id) AS style_id',
			'series_id' => 'COUNT(DISTINCT p.series_id) AS series_id',
			'theme_id' => 'COUNT(DISTINCT p.theme_id) AS theme_id',
			'skc_id' => 'COUNT(DISTINCT pc.skc_id) AS skc_id',
			'color_id' => 'COUNT(DISTINCT pc.color_id) AS color_id',
			'client_name' => 'COUNT(DISTINCT u.id) AS client_name',
			'property' => 'COUNT(DISTINCT u.property) AS property',
			'area1' => 'COUNT(DISTINCT l.id) AS area1',
			'area2' => 'COUNT(DISTINCT l2.id) AS area2',
		);

		$first_field_keys            = array(
			'product_color_id' => 'o.product_color_id',
			'name' => 'p.name',
			'kuanhao' => 'p.kuanhao',
			'category_id' => 'p.category_id',
			'wave_id' => 'p.wave_id',
			'sxz_id' => 'p.sxz_id',
			'classes_id' => 'p.classes_id',
			'style_id' => 'p.style_id',
			'series_id' => 'p.series_id',
			'theme_id' => 'p.theme_id',
			'skc_id' => 'pc.skc_id',
			'color_id' => 'pc.color_id',
			'client_name' => 'u.name as client_name',
			'property' => 'u.property',
			'area1' => 'l.name AS area1',
			'area2' => 'l2.name AS area2',
		);
		$field_keys[$keyword_ids[0]] = $first_field_keys[$keyword_ids[0]];

		$used_field_keys = array();
		foreach ($keyword_ids as $keyword_id) {
			$used_field_keys[] = $field_keys[$keyword_id];
		}
		$fields = join(',', array(join(',', $used_field_keys), $fields));


		$group_key_map  = array(
			'product_color_id' => 'o.product_color_id',
			'name' => 'o.product_id',
			'kuanhao' => 'o.product_id',
			'category_id' => 'p.category_id',
			'wave_id' => 'p.wave_id',
			'sxz_id' => 'p.sxz_id',
			'classes_id' => 'p.classes_id',
			'style_id' => 'p.style_id',
			'series_id' => 'p.series_id',
			'theme_id' => 'p.theme_id',
			'skc_id' => 'pc.skc_id',
			'color_id' => 'pc.color_id',
			'client_name' => 'o.user_id',
			'property' => 'u.property',
			'area1' => 'u.area1',
			'area2' => 'u.area2',
		);
		$group_string   = $group_key_map[$keyword_ids[0]];
		$main_key_field = $group_string . ' AS main_id ';
		$fields .= ', ' . $main_key_field;


		// 按尺码顺序号统计订单总数
		$size_ids    = $this->fetchSizeGroupIds();
		$size_fields = array();
		foreach ($size_ids as $seq => $size_id_array) {
			$size_key = "size_s" . $seq;
			if (count($size_id_array)) {
				$size_fields[] = "SUM(CASE WHEN o.product_size_id IN (" . join(',', $size_id_array) . ") THEN o.num END) AS $size_key";
			} else {
				$size_fields[] = "0 AS $size_key";
			}
		}
		$fields = join(',', array(join(',', $size_fields), $fields));


		$where                = $this->getWhere($cond);
		$options['tablename'] = $tablename;
		$options['fields']    = $fields;
		$options['group']     = $group_string;  //		$options['group']     = "o.product_id, o.product_color_id";
		$options['limit']     = $page ? $page_size : 1000000;    // 有页码时控制每页获取，否则几乎全部获取
		$page and $options['page'] = $page;
//		$options['db_debug'] = true;
		return $this->find($where, $options);
	}

	public function getArea1List()
	{
		$where                = 'pid = 0';
		$options['tablename'] = 'location';
		$options['group']     = '';
		$options['order']     = 'id ASC';
		$options['limit']     = 1000000;
//		$options['db_debug']  = true;
		return $this->find($where, $options);

	}

	public function getArea2List()
	{
		$where                = 'pid != 0';
		$options['tablename'] = 'location';
		$options['group']     = '';
		$options['order']     = 'id ASC';
		$options['limit']     = 1000000;
//		$options['db_debug']  = true;
		return $this->find($where, $options);

	}

}





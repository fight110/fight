<?php

/**
 * 智能报表
 */
class Control_ireport
{
	public static $tpl_data = array();
	public static $dimension_keyword_ids = array(
		'property' => '终端属性',
		'client_name' => '客户名称',
		'skc_id' => '圆牌号',
		'category_id' => '',
		'wave_id' => '',
		'sxz_id' => '',
		'classes_id' => '',
		'style_id' => '',
		'series_id' => '',
		'theme_id' => '',
		'area1' => '销售区域',
		'area2' => '二级区域',
		'kuanhao' => '款号',
		'name' => '款名',
		'product_color_id' => '颜色',
	);

	public static $keyword_groups = array(
		'category' => '',
		'wave' => '',
		'sxz' => '',
		'classes' => '',
		'style' => '',
		'series' => '',
		'theme' => '',
		'area1' => '销售区域',
		'area2' => '二级区域',
		'property' => '终端属性',
	);
	public static $group_keywords = array();
	public static $keyword_list = array();
	public static $is_group_query = false;

	public static function _beforeCall($r, $id = 0)
	{
		Flight::validateUserHasLogin();
		self::assign(FrontSetting::build());

		$ol         = new OrderListIReport();
		$area1_list = $ol->getArea1List();
		$area2_list = $ol->getArea2List();

		self::$keyword_list = include DOCUMENT_ROOT . "haodingtong/Config/keyword.conf.php";
		// 初始化维度关键字信息
		foreach (self::$dimension_keyword_ids as $k => &$kw) {
			isset(self::$keyword_list[$k]) and $kw = self::$keyword_list[$k];
		}
		unset($kw);
		// 初始化分组查询关键字信息
		foreach (self::$keyword_groups as $k => &$kw) {
			isset(self::$keyword_list[$k . '_id']) and $kw = self::$keyword_list[$k . '_id'];
			$keywords = array();
			switch ($k) {
				case 'area1':
					foreach ($area1_list as $area) {
						$keywords[$area['id']] = $area['name'];
					}
					break;
				case 'area2':
					foreach ($area2_list as $area) {
						$keywords[$area['id']] = $area['name'];
					}
					break;
				default:
					$attrFact = new ProductsAttributeFactory($k);
					$attrList = $attrFact->getAllList();
					foreach ($attrList as $area) {
						$keywords[$area['keyword_id']] = $area['keywords']['name'];
					}
			}
			self::$group_keywords[$k] = $keywords;
		}
		unset($kw);
	}

	/**
	 * 模版变量赋值方法(作为$show变量的键值)
	 * @param string $key 变量名
	 * @param mixed $val 变量值
	 */
	public static function assign($key, $val = null)
	{
		$data = is_array($key) ? $key : array($key => $val);
		foreach ($data as $k => $v) {
			self::$tpl_data[$k] = $v;
		}
	}

	/**
	 * 模版显示方法
	 * @param string $tpl 模版文件
	 * @param array $data 模版数据
	 */
	public static function display($tpl, $data = array())
	{
		Flight::display($tpl, $data ? array_merge(self::$tpl_data, $data) : self::$tpl_data);
	}

	/**
	 * 智能报表首页
	 * @param $r
	 */
	public static function Action_index($r)
	{
		self::assign('dimension_keyword_ids', self::$dimension_keyword_ids);
		self::assign('keyword_groups', self::$keyword_groups);
		self::assign('group_keywords', self::$group_keywords);

		self::display("ireport/index.html");
	}

	public static $size_names = array();
	public static $size_groups_array = array();

	/**
	 * 获取尺码组相关信息
	 */
	public static function fetchSizeGroup()
	{
		// 尺码组计算
		$size_group_list = SizeGroup::getAllInstance();
		// 枚举各尺码组可用尺码信息
		$size_names_array     = array();
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
			$group_size_ids   = array();
			$group_size_names = array();
			foreach ($size_name_list as $size) {
				$group_size_ids[]   = $size['size_id'];
				$group_size_names[] = $size['name'];
			}
			self::$size_groups_array[$size_group->size_group_id] = $group_size_ids;
			$size_names_array[]                                  = $group_size_names;
		}

		// 按尺码顺序计算尺码名称
		for ($i = 0; $i < $max_size_group_count; $i++) {
			self::$size_names[$i] = array();
			foreach ($size_names_array as $seq => $group_size_names) {
				if (isset($group_size_names[$i])) {
					self::$size_names[$i][] = $group_size_names[$i];
				} else {
					self::$size_names[$i][] = '';
				}
			}
		}

	}

	/**
	 * 为订单列表项附加尺码信息
	 * @param array $order_list 订单列表数组
	 */
	public static function attachSizeInfo(&$order_list)
	{
		$size_seqs = array_keys(self::$size_names);
		foreach ($order_list as &$row) {
			$size_list = array();
			foreach ($size_seqs as $seq) {
				$size_list[$seq] = $row['size_s' . $seq];
			}
			$row['size_list'] = $size_list;
		}
		unset($row);
	}

	/**
	 * 根据搜索数据创建数据库搜索条件需要的数据
	 * @param $search_data
	 * @return array
	 */
	public static function getSearchConditions($search_data)
	{
		$conditions = array();
		foreach ($search_data as $key => $data) {
			$conditions[$key . (isset(self::$keyword_list[$key . '_id']) ? '_id' : '')] = $data;
		}
		return $conditions;
	}

	/**
	 * 分配订单列表头部模版数据
	 * @param $keyword_ids
	 */
	public static function assignOrderListHeader($keyword_ids)
	{
		self::assign('dimension_keyword_ids', self::$dimension_keyword_ids);
		self::assign('keyword_ids', $keyword_ids);

		$keyword_keys = array_merge(array_keys(self::$keyword_list), array('product_color_id', 'property'));
		self::assign('keyword_keys', $keyword_keys);

		// 获取尺码组信息
		self::fetchSizeGroup();
		self::assign('total_size_groups', count(self::$size_groups_array));
		self::assign('size_names', self::$size_names);
	}

	/**
	 * 分配订单列表模版数据
	 *
	 * @param $search_data
	 * @param $keyword_ids
	 * @param $order
	 * @param int $page
	 * @param int $page_size
	 */
	public static function assignOrderListData($search_data, $keyword_ids, $order, $page = 0, $page_size = 50)
	{

		$ol         = new OrderListIReport();
		$conditions = self::getSearchConditions($search_data);
		// 获取总数与总价
		$total_num = $total_price = 0;
		if ($ol_sum = $ol->getOrderListSum($conditions)) {
			$total_num   = $ol_sum['num'];
			$total_price = $ol_sum['price'];
		}
		self::assign('total_num', $total_num);
		self::assign('total_price', $total_price);


		// 获取订单信息
		if ($page) {
			$order_list = $ol->getOrderList($conditions, $keyword_ids, $order, $page, $page_size);
		} else {
			$order_list = $ol->getOrderList($conditions, $keyword_ids, $order);
		}

		// 结果中增加分组统计(暂时按主字段名匹配，第一字段值重复时可能有误)
		if (self::$is_group_query) {
			$order_group_list       = $ol->getOrderListGroup($conditions, $keyword_ids);
			$group_order_list_by_id = array();
			$group_key              = $keyword_ids[0];
			foreach ($order_group_list as $order_group) {
				$group_order_list_by_id[$order_group[$group_key]] = $order_group;
			}
			$last_group_val        = false;
			$order_list_with_group = array();
			foreach ($order_list as $order) {
				if ($order[$group_key] !== $last_group_val) {
					array_push($order_list_with_group, $group_order_list_by_id[$order[$group_key]]);
				}
				array_push($order_list_with_group, $order);
				$last_group_val = $order[$group_key];
			}
			$order_list = $order_list_with_group;
		}

		// 订单数据中附加尺码信息
		self::attachSizeInfo($order_list);

		self::assign('data_list', $order_list);
		return $order_list ? true : false;
	}

	/**
	 * 智能报表生成方法
	 * @param $r
	 */
	public static function Action_report($r)
	{
//		register_shutdown_function(function () {
//			$error = error_get_last();
//			$error and var_dump($error);
//		});

		$data                 = $r->method == 'POST' ? $r->data : $r->query;
		$search               = $data->search;
		$keyword_ids          = $data->keyword_ids;
		$order                = $data->order;
		self::$is_group_query = $data->is_group_query;
//		$page        = isset($data->page) ? $data->page : 0;

		// 订单列头
		self::assignOrderListHeader($keyword_ids);

		// 导出数据量可能较大，放宽内存限制
		ini_set('memory_limit', '1G');

		// 订单数据
		self::assignOrderListData($search, $keyword_ids, $order);

		self::display("ireport/report.html");
	}

	public static function Action_report_pager($r)
	{
		$info = $r->method == 'POST' ? $r->data : $r->query;
		$page = $info->p ? $info->p : 0;
		parse_str($info->q, $data);

		$search               = $data['search'];
		$keyword_ids          = $data['keyword_ids'];
		$order                = $data['order'];
		self::$is_group_query = $data['is_group_query'];

		self::assign('page', $page);

//		var_dump($page);
//		var_dump($search);
//		var_dump($keyword_ids);
//		var_dump($data);
//		die;

		// 订单列头
		self::assignOrderListHeader($keyword_ids);

		// 订单数据
		$has_data = self::assignOrderListData($search, $keyword_ids, $order, $page, 100);

		$has_data and self::display("ireport/report.html");
		die;    // 直接退出，防止重复加载
	}

	/**
	 * 智能报表导出方法
	 * @param $r
	 */
	public static function Action_export($r)
	{
//		register_shutdown_function(function () {
//			$error = error_get_last();
//			$error and var_dump($error);
//		});

		$data                 = $r->method == 'POST' ? $r->data : $r->query;
		$search               = $data->search;
		$keyword_ids          = $data->keyword_ids;
		$order                = $data->order;
		self::$is_group_query = $data->is_group_query;

//		self::assignOrderListData($search, $keyword_ids, $order);


		header("Cache-Control: public");
		header("Pragma: public");
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=智能报表.csv");
		header('Content-Type:application/octet-stream');

		// 导出数据量可能较大，放宽内存限制
		// ini_set('memory_limit', '2G');
		set_time_limit(300);

		// 订单列头
		self::assignOrderListHeader($keyword_ids);

		// 一次全部输出
		self::assignOrderListData($search, $keyword_ids, $order);
		ob_start();
		self::display("ireport/report.csv");
		$csv = ob_get_clean();
		$csv and print iconv("utf-8", 'gbk', $csv);

//		// 输出订单列头
//		ob_start();
//		self::display("ireport/report_header.csv");
//		$csv_header = ob_get_clean();
//		$csv_header and print iconv("utf-8", 'gbk', $csv_header);
//
//		// 订单数据分页输出
//		$page = 0;
//		while (++$page) {
//			$ret = self::assignOrderListData($search, $keyword_ids, $order, $page, 10000);
//			if (!$ret) {
//				break;
//			}
//			ob_start();
//			self::display("ireport/report_data.csv");
//			$csv_data = ob_get_clean();
//			$csv_data and print iconv("utf-8", 'gbk', $csv_data);
//			flush();
//		}
	}


}
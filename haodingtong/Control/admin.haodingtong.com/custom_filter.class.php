<?php

/**
 * 自定义过滤字段 Custom Filter.
 * @author: Jack Zhu
 */
class Control_custom_filter
{
	public static $tpl_data = array();
	public static $all_filter_key_names = array();
	public static $default_filter_keys = array(
		'series_id', 'season_id', 'wave_id', 'category_id', 'classes_id', 'nannvzhuan_id', 'order', 'ordered', 'is_need'
	);

	public static $keyword_list = array();

	public static function _beforeCall($r, $id = 0)
	{
		Flight::validateUserHasLogin();
		self::assign(FrontSetting::build());

		self::$keyword_list         = include DOCUMENT_ROOT . "haodingtong/Config/keyword.conf.php";
		self::$all_filter_key_names = array_merge(self::$keyword_list, array(
			'order' => '排序',
			'ordered' => '已订',
			'is_need' => '必定款',

		));
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
	 * 自定义筛选字段首页
	 */
	public static function Action_index($r)
	{
		$option_model = new Option();
		if ($r->method == 'POST') {
			$keyword_ids = $r->data->keyword_ids;
			$ret         = $option_model->setToJSON('custom_filter', $keyword_ids);
			die($ret ? '保存成功！' : '保存失败！');
		}
		self::assign('all_filter_key_names', self::$all_filter_key_names);

		self::display("custom_filter/index.html");
	}


	/**
	 * 获取当前已选取的筛选字段
	 */
	public static function Action_current($r)
	{
		$option_model         = new Option();
		$custom_filter_option = $option_model->getFromJSON('custom_filter');
		Flight::json($custom_filter_option);
	}

	/**
	 * 获取默认的筛选字段
	 */
	public static function Action_default($r)
	{
		Flight::json(self::$default_filter_keys);
	}
}
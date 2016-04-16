--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `name`, `type`, `area1`, `area2`, `bianhao`, `exp_num`, `exp_price`, `exp_pnum`, `max_num`, `user_level`, `is_online`) VALUES
(1, 'admin', 'haodingtong', '管理员', 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
(2, '0', 'haodingtong', '总经理', 3, 0, 0, '', 0, 0, 0, 0, 0, 0),
(3, '10000', '123456', '测试帐号', 1, 0, 0, '', 0, 0, 0, 0, 0, 0);

--
-- 转存表中的数据 `user_indicator`
--

INSERT INTO `user_indicator` (`id`, `user_id`, `type`, `field`, `keyword_id`, `field2`, `keyword_id2`, `exp_pnum`, `exp_skc`, `exp_num`, `exp_amount`, `exp_skc_depth`, `ord_pnum`, `ord_skc`, `ord_num`, `ord_amount`, `ord_discount_amount`, `status`) VALUES
(1, 3, 1, '', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1);

--
-- 转存表中的数据 `menulist`
--

INSERT INTO `menulist` (`id`, `pid`, `utype`, `name`, `link`, `status`, `rank`, `tagname`) VALUES
(1, 0, 1, '订货准备', '', 1, 1, ''),
(2, 0, 1, '轻松订货', '', 1, 3, ''),
(3, 0, 1, '订货管理', '', 1, 4, ''),
(4, 0, 1, '分析报表', '', 1, 5, ''),
(5, 0, 2, '货品信息', '', 1, 6, ''),
(6, 0, 2, '数据分析', '', 1, 7, ''),
(7, 0, 2, '现场管控', '', 1, 8, ''),
(8, 0, 2, '其他', '', 1, 9, ''),
(9, 0, 3, '货品信息', '', 1, 6, ''),
(10, 0, 3, '数据分析', '', 1, 7, ''),
(11, 0, 3, '现场管控', '', 1, 8, ''),
(12, 0, 3, '其他', '', 1, 9, ''),
(13, 0, 4, '货品信息', '', 1, 6, ''),
(14, 0, 4, '数据分析', '', 1, 7, ''),
(15, 0, 4, '现场管控', '', 1, 8, ''),
(16, 0, 4, '其他', '', 1, 9, ''),
(17, 0, 5, '系统管理', '', 1, 11, ''),
(18, 0, 5, '订货管理', '', 1, 12, ''),
(19, 0, 5, '导出导入', '', 1, 13, ''),
(20, 1, 1, '指引说明', '/dealer1/notice', 1, 1, 'notice'),
(21, 1, 1, '专题', '/dealer1/zt', 1, 2, 'zt'),
(22, 2, 1, '陈列订货', '/dealer1/display', 1, 1, 'display'),
(23, 2, 1, '搭配订货', '/dealer1/group', 1, 2, 'group'),
(24, 2, 1, '单款订货', '/dealer1', 1, 3, 'dealer1'),
(25, 3, 1, '订货排名', '/dealer1/?c=seeall&order=all+num+desc&view=T', 0, 1, 'seeall'),
(26, 3, 1, '异常订单', '/dealer1/wrongorders', 1, 2, 'wrongorders'),
(27, 3, 1, '收藏评款', '/dealer1/store', 1, 3, 'store'),
(28, 4, 1, '订单总览', '/dealer1/ordersview', 1, 1, 'ordersview'),
(29, 4, 1, '订单分析', '/dealer1/analysisall', 1, 2, 'analysisall'),
-- (30, 4, 1, '货品构成', '/dealer1/hpgc', 1, 3, 'hpgc'),
(31, 2, 1, '我的短消息', '/message', 0, 6, 'message'),
(32, 2, 1, '新陈列订货', '/dealer1/display_new', 0, 1, 'display_new'),
(33, 5, 2, '全部货品', '/dealer2#all', 1, 1, 'adall'),
(34, 6, 2, '订单分析', '/dealer2/analysis', 1, 4, 'analysis'),
(35, 6, 2, '收藏评款', '/dealer2/store', 0, 5, 'store'),
-- (36, 6, 2, '审单打印s', '/dealer2/exp_print', 0, 6, 'exp_print'),
-- (37, 6, 2, '货品构成', '/dealer2/hpgc', 1, 7, 'hpgc'),
(38, 6, 2, '订单总览', '/dealer2/ordersview', 1, 8, 'ordersview'),
(39, 7, 2, '异常订单', '/dealer2/wrongorders', 1, 4, 'wrongorders'),
(40, 8, 2, '数据筛选', '/dealer2/filter', 1, 2, 'filter'),
(41, 8, 2, '返回订货', '/index/gotod1', 1, 7, ''),
(42, 9, 3, '全部货品', '/ad#all', 1, 1, 'ad'),
(43, 9, 3, '删除款式', '/ad/orders', 1, 2, 'orders'),
(44, 10, 3, '订单分析', '/ad/analysis', 1, 1, 'analysis'),
(45, 10, 3, '用户分析', '/ad/user_analysis', 0, 2, 'user_analysis'),
(46, 10, 3, '指标达成s', '/ad/exp1?isZongdai=1', 0, 3, 'exp'),
(47, 10, 3, '收藏评款', '/ad/store', 1, 4, 'store'),
(48, 11, 3, '搭配监控', '/ad/group', 0, 1, 'group'),
(49, 11, 3, '陈列监控', '/ad/display', 0, 2, 'display'),
(50, 11, 3, '秀款推送', '/show', 1, 3, 'show'),
(51, 11, 3, '异常订单', '/ad/wrongorders', 1, 4, 'wrongorders'),
(52, 12, 3, '面料分析', '/ad/fabric', 0, 1, 'fabric'),
(53, 12, 3, '数据筛选', '/ad/filter', 1, 2, 'filter'),
-- (54, 10, 3, '指标达成2s', '/ad/exp2?isZongdai=1', 0, 7, 'exp2'),
-- (55, 10, 3, '货品构成', '/ad/hpgc', 1, 7, 'hpgc'),
(56, 11, 3, '新陈列监控', '/ad/display_new', 0, 2, 'display_new'),
(57, 13, 4, '全部货品', '/ad#all', 1, 1, 'adall'),
(58, 14, 4, '订单分析', '/ad/analysis', 1, 1, 'analysis'),
(59, 14, 4, '收藏评款', '/ad/store', 1, 4, 'store'),
(60, 14, 4, '审单打印', '/ad/exp_print_new', 0, 6, 'exp_print_new'),
-- (61, 14, 4, '货品构成', '/ad/hpgc', 1, 7, 'hpgc'),
(62, 14, 4, '订单总览', '/ad/ordersview', 1, 50, 'ordersview'),
(63, 15, 4, '异常订单', '/ad/wrongorders', 1, 4, 'wrongorders'),
(64, 16, 4, '数据筛选', '/ad/filter', 1, 2, 'filter'),
(65, 17, 5, '品牌信息', '/company', 1, 1, 'company'),
(66, 17, 5, '功能配置', '/company_config', 1, 2, 'company_config'),
(67, 17, 5, '基本字段', '/keyword/?t=style', 1, 3, 'keyword'),
(68, 17, 5, '授权管理', '/network', 1, 5, 'network'),
(69, 18, 5, '账号管理', '/dealer', 1, 1, 'dealer'),
(70, 18, 5, '货品管理', '/product', 1, 2, 'product'),
(71, 18, 5, '订单管理', '/order_manage/copy', 1, 3, 'order_manage'),
(72, 19, 5, '导出', '/data', 1, 1, 'data'),
(73, 19, 5, '导入', '/import', 1, 2, 'import'),
(74, 18, 5, '促销政策', '/product/perferential', 1, 4, 'perferential'),
-- (75, 10, 3, '审单打印s', '/ad/exp_print_new', 0, 6, 'exp_print_new'),
(76, 17, 5, '公告管理', '/status', 1, 7, 'message'),
(77, 18, 5, '起订量设置', '/moq', 0, 5, 'moq'),
(78, 1, 1, '配比设置', '/dealer1/select_proportion', 0, 1, 'select_proportion'),
(79, 4, 1, '指标细分', '/dealer1/exp_complete', 0, 3, 'exp_complete'),
(80, 17, 5, '菜单管理', '/menu', 1, 4, 'menu'),
(81, 4, 1, '指标达成', '/analysis/indicator', 1, 4, 'indicator'),
(82, 6, 2, '指标达成', '/analysis/indicator', 1, 9, 'indicator'),
(83, 14, 4, '指标达成', '/analysis/indicator_analysis?type=1', 1, 8, 'indicator'),
(84, 10, 3, '指标达成', '/analysis/indicator_analysis?type=1', 1, 9, 'indicator'),
(85, 10, 3, '起投量', '/ad/product_color_moq', 0, 10, 'product_color_moq'),
(86, 10, 3, '排行榜分布', '/analysis/rank_distribute', 0, 11, 'rank_distribute'),
(87, 14, 4, '排行榜分布', '/analysis/rank_distribute', 0, 11, 'rank_distribute'),
(88, 6, 2, '排行榜分布', '/analysis/rank_distribute', 0, 12, 'rank_distribute'),
(89, 10, 3, '审单打印', '/ad/exp_print_new2', 1, 6, 'exp_print_new2'),
(90, 14, 4, '审单打印', '/ad/exp_print_new2', 1, 6, 'exp_print_new2'),
(91, 6, 2, '审单打印', '/dealer2/exp_print_new2', 1, 6, 'exp_print_new2'),
(92, 4, 1, '三维分析', '/analysis/three_analysis', 0, 5, 'three_analysis'),
(93, 6, 2, '三维分析', '/analysis/three_analysis', 0, 13, 'three_analysis'),
(94, 14, 4, '三维分析', '/analysis/three_analysis', 0, 12, 'three_analysis'),
(95, 10, 3, '三维分析', '/analysis/three_analysis', 0, 12, 'three_analysis'),
(96, 10, 3, '订单总览', '/ad/ordersview', 1, 13, 'ordersview'),
(97, 10, 3, '订货排名', '/ad/?c=seeall&order=all+num+desc&view=T', 0, 1, 'seeall'),
(98, 3, 1, '订货排名', '/analysis/ranking_list', 1, 4, 'ranking_list'),
(99, 5, 2, '订货排行', '/analysis/ranking_list', 1, 2, 'ranking_list'),
(100, 13, 4, '订货排行', '/analysis/ranking_list', 1, 2, 'ranking_list'),
(101, 10, 3, '订货排行', '/analysis/ranking_list', 1, 1, 'ranking_list'),
(102, 17, 5, '域名管理', '/domain', 1, 6, 'domain'),
(103, 110 , 1, '店铺还原', '/pushorder/display_order', 0, 2, 'display_order'),
(104, 110 , 1, '推演订货', '/pushorder/group_order', 0, 1, 'group_order'),
(105, 10 , 3, '滚动排行', '/analysis/dynamic_ranking', 0, 99, 'dynamic_ranking'),
(106, 0, 9, '设计师分析', '', 1, 14, ''),
(107, 106, 9, '订货排行', '/designer', 1, 1, 'designer'),
(108, 11, 3, '搭配推送', '/pushorder/ad_group_order', 1, 5, 'group_order'),
-- (109, 11, 3, '预演监控', '/pushorder/group_order_monitor',1,6,'group_order_monitor'),
(109, 11, 3, '预演监控', '/pushorder/push_monitor',1,6,'push_monitor'),
(110, 0, 1, '推演订货','',0,2,''),
(111, 4, 1, '色系分析','/analysis/color_analysis',0,6,'color_analysis'),
(112, 17, 5, '注册管理','/register/examine',1,8,'examine'),
(113, 4, 1, '历史数据','/custom/meforever_history',0,7,'meforever_history'),
(114, 6, 2, '省代历史','/custom/meforever_report',0,14,'meforever_report'),
(115, 10, 3, '省代历史','/custom/meforever_report',0,14,'meforever_report'),
(116, 10, 3, '历史数据','/custom/meforever_history',0,15,'meforever_history'),
(117, 7, 2, '店铺还原','/pushorder/display_order',1,1,'display_order'),
(118, 15, 4, '店铺还原','/pushorder/display_order',1,1,'display_order'),
(119, 11, 3, '店铺还原','/pushorder/display_order',1,7,'display_order'),
(120, 12, 3, '智能报表', '/ireport', 1, 99, 'ireport'),
(121, 10, 3, '驾驶舱', '/plot/plot', 1, 16, 'plot');

-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2015 年 04 月 28 日 10:55
-- 服务器版本: 5.5.27
-- PHP 版本: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `haodingtong`
--

-- --------------------------------------------------------

--
-- 表的结构 `budget`
--

CREATE TABLE IF NOT EXISTS `budget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `field` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `keyword_id` int(11) NOT NULL,
  `percent` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`field`,`keyword_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `budget_count`
--

CREATE TABLE IF NOT EXISTS `budget_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `budget` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `company`
--

CREATE TABLE IF NOT EXISTS `company` (
  `id` tinyint(4) NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `devicelist`
--

CREATE TABLE IF NOT EXISTS `devicelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `domain` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `post_time` datetime NOT NULL,
  `edit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `useragent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `key1` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `key2` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `devicelist_active`
--

CREATE TABLE IF NOT EXISTS `devicelist_active` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `device_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT NULL,
  `loginout_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `info` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `fabric_moq`
--

CREATE TABLE IF NOT EXISTS `fabric_moq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fabric_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `minimum` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fabric_id` (`fabric_id`,`color_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `group_to_display`
--

CREATE TABLE IF NOT EXISTS `group_to_display` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `display_id` int(11) NOT NULL COMMENT '陈列id',
  `group_id` int(11) NOT NULL COMMENT '搭配id',
  PRIMARY KEY (`id`),
  KEY `display_id` (`display_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `keywords`
--

CREATE TABLE IF NOT EXISTS `keywords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `location`
--

CREATE TABLE IF NOT EXISTS `location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- 表的结构 `menulist`
--

CREATE TABLE IF NOT EXISTS `menulist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL COMMENT '父节点',
  `utype` tinyint(4) NOT NULL COMMENT '用户类型',
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '菜单名称',
  `link` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '菜单链接',
  `status` tinyint(1) NOT NULL COMMENT '开关',
  `rank` smallint(6) NOT NULL,
  `tagname` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `utype` (`utype`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='新菜单';

-- --------------------------------------------------------

--
-- 表的结构 `message`
--

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authorid` mediumint(9) NOT NULL DEFAULT '0' COMMENT '发布者ID',
  `author` varchar(15) COLLATE utf8_unicode_ci NOT NULL COMMENT '发布者',
  `post_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '信息标题',
  `message` text COLLATE utf8_unicode_ci NOT NULL COMMENT '信息内容',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户短消息';

-- --------------------------------------------------------

--
-- 表的结构 `moq`
--

CREATE TABLE IF NOT EXISTS `moq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `minimum` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword_id` (`keyword_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `orderlist`
--

CREATE TABLE IF NOT EXISTS `orderlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_color_id` int(11) NOT NULL,
  `product_size_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `discount_unit_price` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `zd_user_id` int(11) NOT NULL,
  `zd_discount_unit_price` decimal(10,2) NOT NULL,
  `zd_discount_amount` decimal(10,2) NOT NULL,
  `post_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_ip` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`,`product_color_id`,`product_size_id`),
  KEY `product_id` (`product_id`,`product_color_id`),
  KEY `zd_user_id` (`zd_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `orderlistbak`
--

CREATE TABLE IF NOT EXISTS `orderlistbak` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_color_id` int(11) NOT NULL,
  `product_size_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `discount_unit_price` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `zd_user_id` int(11) NOT NULL,
  `zd_discount_unit_price` decimal(10,2) NOT NULL,
  `zd_discount_amount` decimal(10,2) NOT NULL,
  `post_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_ip` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`,`product_color_id`,`product_size_id`),
  KEY `product_id` (`product_id`,`product_color_id`),
  KEY `zd_user_id` (`zd_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `orderlistdetail`
--

CREATE TABLE IF NOT EXISTS `orderlistdetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_color_id` int(11) NOT NULL,
  `product_size_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `post_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_ip` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `display_id` (`display_id`,`user_id`,`product_id`,`product_color_id`,`product_size_id`),
  KEY `product_id` (`product_id`,`product_color_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `orderlisthistory`
--

CREATE TABLE IF NOT EXISTS `orderlisthistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `series_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `classes_id` int(11) NOT NULL,
  `wave_id` int(11) NOT NULL,
  `price_band_id` int(11) NOT NULL,
  `kuanhao` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `color_id` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `orderlistproduct`
--

CREATE TABLE IF NOT EXISTS `orderlistproduct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `pnum` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `discount_price` decimal(10,2) NOT NULL,
  `sku` int(11) NOT NULL,
  `skc` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `orderlistproductcolor`
--

CREATE TABLE IF NOT EXISTS `orderlistproductcolor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `product_color_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) NOT NULL,
  `unum` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`product_color_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `orderlistuser`
--

CREATE TABLE IF NOT EXISTS `orderlistuser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `pnum` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) NOT NULL,
  `zd_discount_price` decimal(10,2) NOT NULL,
  `sku` int(11) NOT NULL,
  `skc` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `orderlistuserproductcolor`
--

CREATE TABLE IF NOT EXISTS `orderlistuserproductcolor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_color_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `unit_price` int(11) NOT NULL,
  `discount_unit_price` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `s1` smallint(5) unsigned NOT NULL,
  `s2` smallint(5) unsigned NOT NULL,
  `s3` smallint(5) unsigned NOT NULL,
  `s4` smallint(5) unsigned NOT NULL,
  `s5` smallint(5) unsigned NOT NULL,
  `s6` smallint(5) unsigned NOT NULL,
  `s7` smallint(5) unsigned NOT NULL,
  `s8` smallint(5) unsigned NOT NULL,
  `s9` smallint(5) unsigned NOT NULL,
  `s10` smallint(5) unsigned NOT NULL,
  `s11` smallint(5) unsigned NOT NULL,
  `s12` smallint(5) unsigned NOT NULL,
  `s13` smallint(5) unsigned NOT NULL,
  `s14` smallint(5) unsigned NOT NULL,
  `s15` smallint(5) unsigned NOT NULL,
  `s16` smallint(5) unsigned NOT NULL,
  `s17` smallint(5) unsigned NOT NULL,
  `s18` smallint(5) unsigned NOT NULL,
  `s19` smallint(5) unsigned NOT NULL,
  `s20` smallint(5) unsigned NOT NULL,
  `s21` smallint(5) unsigned NOT NULL,
  `s22` smallint(5) unsigned NOT NULL,
  `s23` smallint(5) unsigned NOT NULL,
  `s24` smallint(5) unsigned NOT NULL,
  `s25` smallint(5) unsigned NOT NULL,
  `s26` smallint(5) unsigned NOT NULL,
  `s27` smallint(5) unsigned NOT NULL,
  `s28` smallint(5) unsigned NOT NULL,
  `s29` smallint(5) unsigned NOT NULL,
  `s30` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`,`product_color_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `orderlist_proportion`
--

CREATE TABLE IF NOT EXISTS `orderlist_proportion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `product_id` int(11) NOT NULL COMMENT '货品ID',
  `product_color_id` int(11) NOT NULL COMMENT '颜色ID',
  `proportion_id` int(11) NOT NULL COMMENT '配码ID',
  `xnum` int(11) NOT NULL COMMENT '箱数',
  `num` int(11) NOT NULL COMMENT '件数',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`,`product_color_id`,`proportion_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='配码订单表';

-- --------------------------------------------------------

--
-- 表的结构 `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bianhao` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `kuanhao` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `huohao` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `category_id` int(11) NOT NULL,
  `medium_id` int(11) NOT NULL COMMENT '中类',
  `classes_id` int(11) NOT NULL,
  `series_id` int(11) NOT NULL,
  `wave_id` int(11) NOT NULL,
  `style_id` int(11) NOT NULL,
  `price_band_id` int(11) NOT NULL,
  `theme_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `sxz_id` int(11) NOT NULL,
  `nannvzhuan_id` int(11) NOT NULL,
  `neiwaida_id` int(11) NOT NULL,
  `changduankuan_id` int(11) NOT NULL,
  `edition_id` int(11) NOT NULL COMMENT '版型',
  `contour_id` int(11) NOT NULL COMMENT '轮廓',
  `designer_id` int(11) NOT NULL COMMENT '设计师',
  `brand_id` int(11) NOT NULL,
  `fabric_id` int(11) NOT NULL,
  `fabric_unit` decimal(10,2) NOT NULL,
  `minimum` int(11) NOT NULL, 
  `price` decimal(10,2) NOT NULL,
  `price_purchase` decimal(10,2) NOT NULL,
  `price_purchase_status` tinyint(1) NOT NULL DEFAULT '0',
  `isspot` tinyint(1) NOT NULL DEFAULT '1',
  `designer` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `date_market` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `defaultimage` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `hot` tinyint(1) NOT NULL DEFAULT '0',
  `message` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `is_proportion` tinyint(1) NOT NULL DEFAULT '0',
  `proportion_list` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_need` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否必订款',
  `mininum` int(11) NOT NULL COMMENT '最小起订量', 
  `basenum` int(10) NOT NULL DEFAULT '0',
  `size_group_id` int(11) NOT NULL,
  `order_start_num` int(11) NOT NULL COMMENT '连码起订',
  `price_1` decimal(10,2) NOT NULL,
  `price_2` decimal(10,2) NOT NULL,
  `df1_id` int(11) NOT NULL,
  `df2_id` int(11) NOT NULL,
  `df3_id` int(11) NOT NULL,
  `df4_id` int(11) NOT NULL,
  `df5_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fabric_id` (`fabric_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `products_attr`
--

CREATE TABLE IF NOT EXISTS `products_attr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `keyword_id` int(11) NOT NULL,
  `rank` smallint(6) NOT NULL DEFAULT '99',
  PRIMARY KEY (`id`),
  UNIQUE KEY `field` (`field`,`keyword_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `products_attr_group`
--

CREATE TABLE IF NOT EXISTS `products_attr_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attr_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `products_size_group`
--

CREATE TABLE IF NOT EXISTS `products_size_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `size_group_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `products_size_group_options`
--

CREATE TABLE IF NOT EXISTS `products_size_group_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `size_group_id` int(11) NOT NULL,
  `restriction` int(11) NOT NULL COMMENT '限制数',
  `num` int(11) NOT NULL COMMENT '合计数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_color`
--

CREATE TABLE IF NOT EXISTS `product_color` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `skc_id` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `color_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `is_need` tinyint(1) NOT NULL,
  `mininum` int(11) NOT NULL,
  `main_push_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`color_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_comment`
--

CREATE TABLE IF NOT EXISTS `product_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `score` tinyint(4) NOT NULL,
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `post_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_ip` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_display`
--

CREATE TABLE IF NOT EXISTS `product_display` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bianhao` int(10) NOT NULL,
  `name` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `pd_type` int(11) NOT NULL COMMENT '陈列筛选1',
  `pd_type2` int(11) NOT NULL COMMENT '陈列筛选2',
  `defaultimage` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `contrast_image` varchar(60) COLLATE utf8_unicode_ci NOT NULL COMMENT '新搭配对比图',
  `background_image` varchar(60) COLLATE utf8_unicode_ci NOT NULL COMMENT '新搭配订货底图',
  `intro` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_display_image`
--

CREATE TABLE IF NOT EXISTS `product_display_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display_id` int(11) NOT NULL,
  `image` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_display_member`
--

CREATE TABLE IF NOT EXISTS `product_display_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rank` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id` (`display_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_display_member_color`
--

CREATE TABLE IF NOT EXISTS `product_display_member_color` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `keyword_id` int(11) NOT NULL,
  `image` varchar(60) COLLATE utf8_unicode_ci NOT NULL COMMENT '新陈列订货图片',
  PRIMARY KEY (`id`),
  UNIQUE KEY `display_id` (`display_id`,`product_id`,`keyword_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_group`
--

CREATE TABLE IF NOT EXISTS `product_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dp_num` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `dp_type` int(11) NOT NULL,
  `dp_type2` int(11) NOT NULL COMMENT '搭配筛选2',
  `defaultimage` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `contrast_image` varchar(60) COLLATE utf8_unicode_ci NOT NULL COMMENT '新搭配对比图',
  `background_image` varchar(60) COLLATE utf8_unicode_ci NOT NULL COMMENT '新搭配订货底图',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_group_image`
--

CREATE TABLE IF NOT EXISTS `product_group_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `image` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_group_member`
--

CREATE TABLE IF NOT EXISTS `product_group_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) DEFAULT NULL COMMENT '颜色',
  `image` varchar(60) COLLATE utf8_unicode_ci NOT NULL COMMENT '新搭配订货图片',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id` (`group_id`,`product_id`),
  KEY `product_id` (`product_id`,`color_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_image`
--

CREATE TABLE IF NOT EXISTS `product_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL COMMENT '颜色ID',
  `image` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_perferential`
--

CREATE TABLE IF NOT EXISTS `product_perferential` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `kuanhao` varchar(20) NOT NULL,
  `start_num` int(11) NOT NULL,
  `end_num` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='优惠政策';

-- --------------------------------------------------------

--
-- 表的结构 `product_proportion`
--

CREATE TABLE IF NOT EXISTS `product_proportion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `size_group_id` int(11) NOT NULL COMMENT '尺码组名称',
  `proportion` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`size_group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_show`
--

CREATE TABLE IF NOT EXISTS `product_show` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` tinyint(4) NOT NULL,
  `dp_num` int(11) NOT NULL,
  `bianhaos` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `product_ids` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_size`
--

CREATE TABLE IF NOT EXISTS `product_size` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`size_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `product_stock`
--

CREATE TABLE IF NOT EXISTS `product_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL COMMENT '产品ID',
  `product_color_id` int(11) NOT NULL COMMENT '颜色ID',
  `product_size_id` int(11) NOT NULL COMMENT '尺码ID',
  `totalnum` int(11) unsigned NOT NULL COMMENT '总库存量',
  `ordernum` int(11) unsigned NOT NULL COMMENT '已订量',
  `giftnum` int(10) unsigned NOT NULL COMMENT '赠品数量',
  `post_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '设置时间',
  `create_ip` int(11) unsigned DEFAULT NULL COMMENT '设置IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`product_id`,`product_color_id`,`product_size_id`),
  KEY `product_id` (`product_id`,`product_color_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='产品库存表';

-- --------------------------------------------------------

--
-- 表的结构 `review_cancel_log`
--

CREATE TABLE IF NOT EXISTS `review_cancel_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `num` int(11) NOT NULL COMMENT '物品数量',
  `price` decimal(11,2) NOT NULL COMMENT '物品金额',
  `discount_price` decimal(11,2) DEFAULT NULL,
  `dtime` datetime NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `rule`
--

CREATE TABLE IF NOT EXISTS `rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `rule_detail`
--

CREATE TABLE IF NOT EXISTS `rule_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) NOT NULL,
  `keyword_id` int(11) NOT NULL,
  `percent` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_id` (`rule_id`,`keyword_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `rule_user`
--

CREATE TABLE IF NOT EXISTS `rule_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `field` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `rule_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `runtime`
--

CREATE TABLE IF NOT EXISTS `runtime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `time` decimal(10,7) NOT NULL,
  `post_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `more` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(4) NOT NULL,
  `area1` int(11) NOT NULL,
  `area2` int(11) NOT NULL,
  `bianhao` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `property` int(11) NOT NULL,
  `exp_num` int(11) NOT NULL,
  `exp_price` int(11) NOT NULL,
  `exp_pnum` int(11) NOT NULL,
  `max_num` int(11) NOT NULL,
  `user_level` int(11) NOT NULL,
  `is_online` tinyint(1) NOT NULL DEFAULT '0',
  `is_lock` tinyint(1) NOT NULL DEFAULT '0',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `is_stock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否备货账户',
  `mid` int(11) NOT NULL,
  `discount` decimal(4,3) NOT NULL DEFAULT '1.000',
  `permission_brand` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `permission_isspot` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `current_isspot` int(11) NOT NULL,
  `ad` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `discount_type` tinyint(1) NOT NULL DEFAULT '0',
  `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态',
  `auth` int(1) NOT NULL DEFAULT '1' COMMENT '是否授权',
  `ad_id` int(11) NOT NULL DEFAULT '0' COMMENT '大区经理',
  `mulit_name` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `mid` (`mid`),
  KEY `ad_id` (`ad_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_discount`
--

CREATE TABLE IF NOT EXISTS `user_discount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `field` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) NOT NULL,
  `category_discount` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`field`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_exp`
--

CREATE TABLE IF NOT EXISTS `user_exp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `exp_num` int(11) NOT NULL,
  `exp_price` int(11) NOT NULL,
  `exp_pnum` int(11) NOT NULL,
  `exp_skc` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`brand_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_exp_complete`
--

CREATE TABLE IF NOT EXISTS `user_exp_complete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `field` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `keyword_id` int(11) NOT NULL,
  `exp_num` int(11) NOT NULL,
  `exp_price` int(11) NOT NULL,
  `exp_pnum` int(11) NOT NULL,
  `exp_skc` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`field`,`keyword_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_product`
--

CREATE TABLE IF NOT EXISTS `user_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rateval` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_product_discount`
--

CREATE TABLE IF NOT EXISTS `user_product_discount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `product_id` int(11) DEFAULT NULL COMMENT '产品款号',
  `kuanhao_discount` decimal(4,3) DEFAULT NULL COMMENT '款号折扣',
  `category_id` int(11) DEFAULT NULL COMMENT '大类ID',
  `category_discount` decimal(4,3) DEFAULT NULL COMMENT '大类折扣',
  `flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '标记（0-折扣有效，1-折扣失效）',
  `creater` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `add_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户款号折扣';

-- --------------------------------------------------------

--
-- 表的结构 `user_session`
--

CREATE TABLE IF NOT EXISTS `user_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '会话ID',
  `user_id` int(11) NOT NULL COMMENT '当前登入用户',
  `domain` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '域名',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `user_session_list_id` int(11) NOT NULL COMMENT '登入信息ID',
  `login_num` smallint(6) NOT NULL COMMENT '登入次数',
  `message` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户会话';

-- --------------------------------------------------------

--
-- 表的结构 `user_session_list`
--

CREATE TABLE IF NOT EXISTS `user_session_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_session_id` int(11) NOT NULL COMMENT '用户会话ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `login_time` datetime NOT NULL COMMENT '登入时间',
  `logout_time` datetime NOT NULL COMMENT '登出时间',
  `ip_address` int(10) unsigned NOT NULL,
  `useragent` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '客户端信息',
  `devicename` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_session_id` (`user_session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_slave`
--

CREATE TABLE IF NOT EXISTS `user_slave` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_slave_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`user_slave_id`),
  UNIQUE KEY `user_slave_id` (`user_slave_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_sms`
--

CREATE TABLE IF NOT EXISTS `user_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` mediumint(9) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `mid` int(11) NOT NULL DEFAULT '0' COMMENT '短消息ID',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已阅(0-未阅,1-已阅)',
  `post_time` datetime NOT NULL COMMENT '阅读时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id` (`uid`,`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户消息';

-- --------------------------------------------------------

--
-- 表的结构 `user_target`
--

CREATE TABLE IF NOT EXISTS `user_target` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `other_id` int(11) NOT NULL,
  `target_num` int(11) DEFAULT NULL,
  `target_price` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`other_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `orderlist_agent`
--

CREATE TABLE IF NOT EXISTS `orderlist_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '总代ID',
  `product_id` int(11) NOT NULL COMMENT '货品ID',
  `product_color_id` int(11) NOT NULL,
  `num` int(11) NOT NULL COMMENT '合计订量',
  `amount` int(11) NOT NULL COMMENT '合计金额',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`,`product_color_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='总代订单合计';
-- --------------------------------------------------------

--
-- 表的结构 `orderlist_area`
--

CREATE TABLE IF NOT EXISTS `orderlist_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area1` int(11) NOT NULL COMMENT '区域ID',
  `product_id` int(11) NOT NULL COMMENT '货品ID',
  `product_color_id` int(11) NOT NULL,
  `num` int(11) NOT NULL COMMENT '合计订量',
  `amount` int(11) NOT NULL COMMENT '合计金额',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`area1`,`product_id`,`product_color_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='总代订单合计' ;

--
-- 表的结构 `user_indicator`
--

CREATE TABLE IF NOT EXISTS `user_indicator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `type` tinyint(1) NOT NULL COMMENT '用户类型',
  `field` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '属性ID',
  `keyword_id` int(11) NOT NULL COMMENT '属性值',  
  `field2` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '属性ID2',
  `keyword_id2` int(11) NOT NULL COMMENT '属性值2',
  `exp_pnum` int(11) NOT NULL COMMENT '指标款量',
  `exp_skc` int(11) NOT NULL COMMENT '指标款色',
  `exp_num` int(11) NOT NULL COMMENT '指标订量',
  `exp_amount` int(11) NOT NULL COMMENT '指标金额',
  `exp_skc_depth` int(11) NOT NULL COMMENT '指标款色深度',
  `ord_pnum` int(11) NOT NULL COMMENT '订单款量',
  `ord_skc` int(11) NOT NULL COMMENT '订单款色',
  `ord_num` int(11) NOT NULL COMMENT '订单数量',
  `ord_amount` int(11) NOT NULL COMMENT '订单金额',
  `ord_discount_amount` int(11) NOT NULL COMMENT '折后金额',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field` (`field`,`keyword_id`,`field2`,`keyword_id2`,`user_id`,`type`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='新用户指标系统';


-- --------------------------------------------------------

--
-- 表的结构 `product_color_moq`
--

CREATE TABLE IF NOT EXISTS `product_color_moq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `product_color_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`product_color_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='款色起订量表';

-- --------------------------------------------------------

--
-- 表的结构 `user_size_history`
--

CREATE TABLE IF NOT EXISTS `user_size_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `keyword_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`keyword_id`,`size_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户尺码历史数据对照';

-- --------------------------------------------------------

--
-- 表的结构 `domain`
--

CREATE TABLE IF NOT EXISTS `domain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '域名地址',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '名称',
  `type` tinyint(4) NOT NULL COMMENT '类型-前后台',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------
--
-- 表的结构 `products_color_group`
--

CREATE TABLE IF NOT EXISTS `products_color_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `keyword_id` int(11) NOT NULL,
  `rgb` varchar(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword_id` (`keyword_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='色块分析表';

-- --------------------------------------------------------

--
-- 表的结构 `user_register`
--

CREATE TABLE IF NOT EXISTS `user_register` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `post_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `user_guide`
--

CREATE TABLE IF NOT EXISTS `user_guide` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_color_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`,`product_color_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `custom_meforever_history`
--

CREATE TABLE IF NOT EXISTS `custom_meforever_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `area` int(11) NOT NULL COMMENT '面积',
  `category_id` int(11) NOT NULL,
  `medium_id` int(11) NOT NULL,
  `wave_id` int(11) NOT NULL,
  `ship_num` int(11) NOT NULL COMMENT '发货数量',
  `ship_price` int(11) NOT NULL COMMENT '发货吊牌额',
  `ship_skc` int(11) NOT NULL COMMENT '发货款色数',
  `sales_num` int(11) NOT NULL COMMENT '销售数量',
  `sales_price` int(11) NOT NULL COMMENT '销售金额',
  `sales_skc` int(11) NOT NULL COMMENT '销售款色',
  `order_num` int(11) NOT NULL COMMENT '订单数量',
  `order_price` int(11) NOT NULL COMMENT '订单金额',
  `order_skc` int(11) NOT NULL COMMENT '订单skc',
  `stock_num` int(11) NOT NULL COMMENT '库存数量',
  `stock_price` int(11) NOT NULL COMMENT '库存金额',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`category_id`,`medium_id`,`wave_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `options`
--

CREATE TABLE `options` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`key` VARCHAR(50) NOT NULL COMMENT '选项键',
	`value` TEXT NOT NULL COMMENT '选项值',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `key` (`key`)
)
COMMENT='选项配置'
COLLATE='utf8_general_ci'
ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `plot_user`
--

CREATE TABLE IF NOT EXISTS `plot_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '产品id',
  `amount` int(11) DEFAULT NULL COMMENT '金额',
  `discount_amount` int(11) DEFAULT NULL COMMENT '折后金额',
  `num` int(11) DEFAULT NULL COMMENT '数量',
  `zd_discount_amount` int(11) DEFAULT NULL COMMENT '总代金额',
  `time_axis` int(11) DEFAULT NULL COMMENT '时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`time_axis`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `plot_product`
--

CREATE TABLE IF NOT EXISTS `plot_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL COMMENT '产品id',
  `num` int(11) DEFAULT NULL COMMENT '数量',
  `amount` int(11) DEFAULT NULL COMMENT '金额',
  `time_axis` int(11) DEFAULT NULL COMMENT '时间戳',  
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`time_axis`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

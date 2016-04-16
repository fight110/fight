<?php

class Option Extends BaseClass
{
	public function __construct()
	{
		$this->setFactory('options');

	}

	/**
	 * 获取选项值
	 * @param string $key 选项键
	 * @return string 选项值
	 */
	public function get($key)
	{
		$sql = "SELECT * FROM {$this->tablename} WHERE `key`=?";
		$sth = $this->dbh_slave->prepare($sql);
		$sth->execute(array($key));
		$row = $sth->fetch();
		return $row['value'];
	}

	/**
	 * 设置选项值
	 * @param string $key
	 * @param string $value
	 * @return int  影响行数
	 */
	public function set($key, $value)
	{
		$sql = "REPLACE INTO {$this->tablename} SET `key`=?, `value`=?";
		$sth = $this->dbh->prepare($sql);
		$sth->execute(array($key, $value));
		return $sth->rowCount();
	}

	/** 从存储的JSON对象获取选项值
	 * @param string $key
	 * @return mixed
	 */
	public function getFromJSON($key)
	{
		$ret = $this->get($key);
		return $ret ? json_decode($ret, true) : null;
	}

	/** 将选项值转换成jSON对象后存储
	 * @param dtring $key
	 * @param mixed $value
	 * @return int 影响行数
	 */
	public function setToJSON($key, $value)
	{
		$value = json_encode($value);
		return $this->set($key, $value);
	}
}
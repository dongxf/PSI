<?php

namespace UnitTest\Service;

/**
 * 测试用例基类
 *
 * @author 李静波
 */
class BaseTestCase {
	/**
	 *
	 * @var \Think\Model $db
	 */
	protected $db;

	function __construct($db) {
		$this->db = $db;
	}
}
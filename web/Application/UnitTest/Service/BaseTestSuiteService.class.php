<?php

namespace UnitTest\Service;

/**
 * 测试套件基类
 *
 * @author 李静波
 */
class BaseTestSuiteService {
	/**
	 *
	 * @var \Think\Model $db
	 */
	protected $db;

	function __construct() {
		$this->db = M();
	}

	protected function setup() {
		$this->db->startTrans();
	}

	protected function teardown() {
		$this->db->rollback();
	}
}
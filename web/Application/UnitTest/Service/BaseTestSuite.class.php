<?php

namespace UnitTest\Service;

/**
 * 测试套件基类
 *
 * @author 李静波
 */
class BaseTestSuite {
	/**
	 *
	 * @var \Think\Model $db
	 */
	protected $db;
	protected $tests = [];

	function __construct() {
		$this->db = M();
	}

	protected function setup() {
		$this->db->startTrans();
	}

	protected function teardown() {
		$this->db->rollback();
	}

	protected function regTest($test) {
		$this->tests[] = $test;
	}

	public function run() {
		$this->setup();
		
		foreach ( $this->tests as $test ) {
			$rc = $test->run($this->db);
		}
		
		$this->teardown();
	}
}
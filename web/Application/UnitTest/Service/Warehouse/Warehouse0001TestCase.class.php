<?php

namespace UnitTest\Service\Warehouse;

use Home\DAO\WarehouseDAO;

/**
 * 仓库用例基类
 *
 * @author 李静波
 */
class Warehouse0001TestCase extends BaseTestCase {

	private $id ="Warehouse0001";
	private $name = "UnitTest\\Service\\Warehouse\\Warehouse0001TestCase";
	/**
	 * 运行测试用例
	 */
	function run($db) {
		$dao = new WarehouseDAO($db);
		
		return $this->toResult($this->$id, $this->$name, 1, "");
	}
}
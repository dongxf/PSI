<?php

namespace UnitTest\Service;

/**
 * PSI单元测试 Service
 *
 * @author 李静波
 */
class PSIUnitTestService {

	/**
	 * 返回所有的单元测试测试结果
	 *
	 * @return array
	 */
	public function getAllUnitTestsResult() {
		$result = [];
		
		// 下面的是示例数据
		$result[] = [
				"id" => "A000001",
				"name" => "UnitTest\\WarehouseUnitTest\\testAddWarehouse",
				"result" => 1,
				"msg" => ""
		];
		$result[] = [
				"id" => "A000002",
				"name" => "UnitTest\\WarehouseUnitTest\\testDeleteWarehouse",
				"result" => 0,
				"msg" => "要删除的仓库不存在"
		];
		$result[] = [
				"id" => "A000003",
				"name" => "UnitTest\\WarehouseUnitTest\\testEditWarehouse",
				"result" => 1,
				"msg" => ""
		];
		$result[] = [
				"id" => "A000004",
				"name" => "UnitTest\\WarehouseUnitTest\\testEditWarehouseDataOrg",
				"result" => 1,
				"msg" => ""
		];
		
		return $result;
	}
}
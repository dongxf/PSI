<?php

namespace Home\Service;

/**
 * 业务设置Service
 *
 * @author 李静波
 */
class BizConfigService extends PSIBaseService {

	/**
	 * 返回所有的配置项
	 */
	public function allConfigs($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select id, name, value, note  
				from t_config  
				order by show_order";
		$data = M()->query($sql);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$id = $v["id"];
			$result[$i]["id"] = $id;
			$result[$i]["name"] = $v["name"];
			$result[$i]["value"] = $v["value"];
			
			if ($id == "1001-01") {
				$result[$i]["displayValue"] = $v["value"] == 1 ? "使用不同计量单位" : "使用同一个计量单位";
			} else if ($id == "1003-02") {
				$result[$i]["displayValue"] = $v["value"] == 0 ? "移动平均法" : "先进先出法";
			} else if ($id == "2002-01") {
				$result[$i]["displayValue"] = $v["value"] == 1 ? "允许编辑销售单价" : "不允许编辑销售单价";
			} else if ($id == "2001-01" || $id == "2002-02") {
				$result[$i]["displayValue"] = $this->getWarehouseName($v["value"]);
			} else {
				$result[$i]["displayValue"] = $v["value"];
			}
			$result[$i]["note"] = $v["note"];
		}
		
		return $result;
	}

	/**
	 * 返回所有的配置项，附带着附加数据集
	 */
	public function allConfigsWithExtData() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select id, name, value from t_config order by id";
		$db = M();
		$result = $db->query($sql);
		
		$extDataList = array();
		$sql = "select id, name from t_warehouse order by code";
		$data = $db->query($sql);
		$warehouse = array(
				array(
						"id" => "",
						"name" => "[没有设置]"
				)
		);
		
		$extDataList["warehouse"] = array_merge($warehouse, $data);
		
		return array(
				"dataList" => $result,
				"extData" => $extDataList
		);
	}

	/**
	 * 保存配置项
	 */
	public function edit($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		
		$refPreList = array(
				"9003-01",
				"9003-02",
				"9003-03",
				"9003-04",
				"9003-05",
				"9003-06",
				"9003-07"
		);
		
		// 检查值是否合法
		foreach ( $params as $key => $value ) {
			if ($key == "9001-01") {
				$v = intval($value);
				if ($v < 0) {
					return $this->bad("增值税税率不能为负数");
				}
				if ($v > 17) {
					return $this->bad("增值税税率不能大于17");
				}
			}
			
			if ($key == "9002-01") {
				if (! $value) {
					$value = "开源进销存PSI";
				}
			}
			
			if ($key == "1003-02") {
				// 存货计价方法
				$sql = "select name, value from t_config where id = '%s' ";
				$data = $db->query($sql, $key);
				if (! $data) {
					continue;
				}
				$oldValue = $data[0]["value"];
				if ($value == $oldValue) {
					continue;
				}
				
				$sql = "select count(*) as cnt from t_inventory_detail
						where ref_type <> '库存建账' ";
				$data = $db->query($sql);
				$cnt = $data[0]["cnt"];
				if ($cnt > 0) {
					return $this->bad("已经有业务发生，不能再调整存货计价方法");
				}
			}
			
			if (in_array($key, $refPreList)) {
				if ($value == null || $value == "") {
					return $this->bad("单号前缀不能为空");
				}
			}
		}
		
		foreach ( $params as $key => $value ) {
			$sql = "select name, value from t_config where id = '%s' ";
			$data = $db->query($sql, $key);
			if (! $data) {
				continue;
			}
			
			$itemName = $data[0]["name"];
			
			$oldValue = $data[0]["value"];
			if ($value == $oldValue) {
				continue;
			}
			
			if ($key == "9001-01") {
				$value = intval($value);
			}
			
			if ($key == "9002-01") {
				if ($this->isDemo()) {
					return $this->bad("在演示环境下不能修改产品名称，请原谅。");
				}
			}
			
			if (in_array($key, $refPreList)) {
				if ($this->isDemo()) {
					return $this->bad("在演示环境下不能修改单号前缀，请原谅。");
				}
				
				// 单号前缀保持大写
				$value = strtoupper($value);
			}
			
			$sql = "update t_config set value = '%s'
				where id = '%s' ";
			$db->execute($sql, $value, $key);
			
			if ($key == "1003-02") {
				$v = $value == 0 ? "移动平均法" : "先进先出法";
				$log = "把[{$itemName}]设置为[{$v}]";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "业务设置");
			} else if ($key == "2001-01") {
				$v = $this->getWarehouseName($value);
				$log = "把[{$itemName}]设置为[{$v}]";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "业务设置");
			} else if ($key == "2002-01") {
				$v = $value == 1 ? "允许编辑销售单价" : "不允许编辑销售单价";
				$log = "把[{$itemName}]设置为[{$v}]";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "业务设置");
			} else if ($key == "2002-02") {
				$v = $this->getWarehouseName($value);
				$log = "把[{$itemName}]设置为[{$v}]";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "业务设置");
			} else {
				$log = "把[{$itemName}]设置为[{$value}]";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "业务设置");
			}
		}
		
		return $this->ok();
	}

	private function getWarehouseName($id) {
		$data = M()->query("select name from t_warehouse where id = '%s' ", $id);
		if ($data) {
			return $data[0]["name"];
		} else {
			return "[没有设置]";
		}
	}

	/**
	 * 仓库是否需要设置组织机构
	 *
	 * @return true 仓库需要设置组织机构
	 */
	public function warehouseUsesOrg() {
		// 2015-11-04: 使用数据域后，就不需要这个功能了
		return false;
		
		// $sql = "select value from t_config where id = '1003-01' ";
		// $data = M()->query($sql);
		// if ($data) {
		// return $data[0]["value"] == "1";
		// } else {
		// return false;
		// }
	}

	/**
	 * 获得增值税税率
	 */
	public function getTaxRate() {
		$db = M();
		$sql = "select value from t_config where id = '9001-01' ";
		$data = $db->query($sql);
		if ($data) {
			$result = $data[0]["value"];
			return intval($result);
		} else {
			return 17;
		}
	}

	/**
	 * 获得本产品名称，默认值是：开源进销存PSI
	 */
	public function getProductionName() {
		$db = M();
		$sql = "select value from t_config where id = '9002-01' ";
		$data = $db->query($sql);
		if ($data) {
			return $data[0]["value"];
		} else {
			return "开源进销存PSI";
		}
	}

	/**
	 * 获得存货计价方法
	 * 0： 移动平均法
	 * 1：先进先出法
	 */
	public function getInventoryMethod() {
		$result = 0;
		
		$db = M();
		$sql = "select value from t_config where id = '1003-02' ";
		$data = $db->query($sql);
		if (! $data) {
			return $result;
		}
		
		$result = intval($data[0]["value"]);
		
		return $result;
	}

	/**
	 * 获得采购订单单号前缀
	 */
	public function getPOBillRefPre() {
		$result = "PO";
		
		$db = M();
		$id = "9003-01";
		$sql = "select value from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "PO";
			}
		}
		
		return $result;
	}

	/**
	 * 获得采购入库单单号前缀
	 */
	public function getPWBillRefPre() {
		$result = "PW";
		
		$db = M();
		$id = "9003-02";
		$sql = "select value from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "PW";
			}
		}
		
		return $result;
	}
	
	/**
	 * 获得采购退货出库单单号前缀
	 */
	public function getPRBillRefPre() {
		$result = "PR";
		
		$db = M();
		$id = "9003-03";
		$sql = "select value from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "PR";
			}
		}
		
		return $result;
	}
	
	/**
	 * 获得销售出库单单号前缀
	 */
	public function getWSBillRefPre() {
		$result = "WS";
	
		$db = M();
		$id = "9003-04";
		$sql = "select value from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$result = $data[0]["value"];
				
			if ($result == null || $result == "") {
				$result = "WS";
			}
		}
	
		return $result;
	}
	
	/**
	 * 获得销售退货入库单单号前缀
	 */
	public function getSRBillRefPre() {
		$result = "SR";
	
		$db = M();
		$id = "9003-05";
		$sql = "select value from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$result = $data[0]["value"];
	
			if ($result == null || $result == "") {
				$result = "SR";
			}
		}
	
		return $result;
	}
}
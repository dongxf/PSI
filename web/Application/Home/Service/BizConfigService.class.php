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
			} else if ($id == "1003-01") {
				$result[$i]["displayValue"] = $v["value"] == 1 ? "仓库需指定组织机构" : "仓库不需指定组织机构";
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
		}
		
		$db = M();
		
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
			
			$sql = "update t_config set value = '%s'
				where id = '%s' ";
			$db->execute($sql, $value, $key);
			
			if ($key == "1003-01") {
				$v = $value == 1 ? "仓库需指定组织机构" : "仓库不需指定组织机构";
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
		$sql = "select value from t_config where id = '1003-01' ";
		$data = M()->query($sql);
		if ($data) {
			return $data[0]["value"] == "1";
		} else {
			return false;
		}
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
}
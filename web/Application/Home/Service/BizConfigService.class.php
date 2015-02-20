<?php

namespace Home\Service;

/**
 * 业务设置Service
 *
 * @author 李静波
 */
class BizConfigService extends PSIBaseService {
	public function allConfigs($params) {
		$sql = "select id, name, value, note " . " from t_config " . " order by id";
		$data = M()->query($sql);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$id = $v["id"];
			$result[$i]["id"] = $id;
			$result[$i]["name"] = $v["name"];
			$result[$i]["value"] = $v["value"];
			
			if ($id == "2002-01") {
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
	public function allConfigsWithExtData($params) {
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
	public function edit($params) {
		$db = M();
		
		foreach ( $params as $key => $value ) {
			$sql = "select value from t_config where id = '%s' ";
			$data = $db->query($sql, $key);
			if (!$data) {
				continue;
			}
			$oldValue = $data[0]["value"];
			if ($value == $oldValue) {
				continue;
			}
			
			$sql = "update t_config set value = '%s'
				where id = '%s' ";
			$db->execute($sql, $value, $key);
			
			if ($key == "2001-01") {
				$v = $this->getWarehouseName($value);
				$log = "把[采购入库默认仓库]设置为[{$v}]";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "业务设置");
			} else if ($key == "2002-01") {
				$v = $value == 1 ? "允许编辑销售单价" : "不允许编辑销售单价";
				$log = "把[销售出库单允许编辑销售单价]设置为[{$v}]";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "业务设置");
			} else if ($key == "2002-02") {
				$v = $this->getWarehouseName($value);
				$log = "把[销售出库默认仓库]设置为[{$v}]";
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
}
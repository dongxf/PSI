<?php

namespace Home\Service;

/**
 * 业务设置Service
 *
 * @author 李静波
 */
class BizConfigService extends PSIBaseService {

	public function allConfigs($params) {
		$sql = "select id, name, value, note "
				. " from t_config "
				. " order by id";
		$data = M()->query($sql);
		$result = array();

		foreach ($data as $i => $v) {
			$id = $v["id"];
			$result[$i]["id"] = $id;
			$result[$i]["name"] = $v["name"];
			$result[$i]["value"] = $v["value"];
			
			if ($id == "2002-01") {
				$result[$i]["displayValue"] = $v["value"] == 1 ? "允许编辑销售单价" : "不允许编辑销售单价";
			} else {
				$result[$i]["displayValue"] = $v["value"];
			}
			$result[$i]["note"] = $v["note"];
		}

		return $result;
	}
	
	public function edit($params) {
		$db = M();
		$sql = "update t_config "
				. " set value = '%s' "
				. " where id = '%s' ";
		
		foreach ($params as $key => $value) {
			$db->execute($sql, $value, $key);
			
			if ($key == "2002-01") {
				$v  = $value == 1 ? "允许编辑销售单价" : "不允许编辑销售单价";
				$log = "把[销售出库单允许编辑销售单价]设置为[{$v}]";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "业务设置");
			}
		}
		
		return $this->ok();
	}
}

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
			if ($id == "2002-01") {
				$result[$i]["value"] = $v["value"] == 1 ? "允许编辑销售单价" : "不允许编辑销售单价";
			} else {
				$result[$i]["value"] = $v["value"];
			}
			$result[$i]["note"] = $v["note"];
		}

		return $result;
	}
}

<?php

namespace Home\Service;

/**
 * 库存报表Service
 *
 * @author 李静波
 */
class InventoryReportService extends PSIBaseService {

	/**
	 * 安全库存明细表 - 数据查询
	 */
	public function safetyInventoryQueryData($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$result = array();
		
		return $result;
	}
}
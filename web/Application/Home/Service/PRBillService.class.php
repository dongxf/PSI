<?php

namespace Home\Service;

use Think\Model\AdvModel;

/**
 * 采购退货出库单Service
 *
 * @author 李静波
 */
class PRBillService extends PSIBaseService {

	/**
	 * 生成新的采购退货出库单单号
	 *
	 * @return string
	 */
	private function genNewBillRef() {
		$pre = "PR";
		$mid = date("Ymd");
		
		$sql = "select ref from t_pr_bill where ref like '%s' order by ref desc limit 1";
		$data = M()->query($sql, $pre . $mid . "%");
		$sufLength = 3;
		$suf = str_pad("1", $sufLength, "0", STR_PAD_LEFT);
		if ($data) {
			$ref = $data[0]["ref"];
			$nextNumber = intval(substr($ref, 10)) + 1;
			$suf = str_pad($nextNumber, $sufLength, "0", STR_PAD_LEFT);
		}
		
		return $pre . $mid . $suf;
	}

	public function prBillInfo($params) {
		$id = $params["id"];
		
		$result = array();
		
		if ($id) {
			// 编辑
		} else {
			// 新建
			$us = new UserService();
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
		}
		
		return $result;
	}

	public function editPRBill($params) {
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		return $this->todo();
	}
}
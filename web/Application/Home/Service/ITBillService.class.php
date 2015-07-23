<?php

namespace Home\Service;

/**
 * 库间调拨Service
 *
 * @author 李静波
 */
class ITBillService extends PSIBaseService {

	/**
	 * 生成新的调拨单单号
	 *
	 * @return string
	 */
	private function genNewBillRef() {
		$pre = "IT";
		$mid = date("Ymd");
		
		$sql = "select ref from t_it_bill where ref like '%s' order by ref desc limit 1";
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
}
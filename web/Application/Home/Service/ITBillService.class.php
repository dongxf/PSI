<?php

namespace Home\Service;

use Think\Model\AdvModel;
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
	
	/**
	 * 调拨单主表列表信息
	 */
	public function itbillList($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		
		$sql = "select t.id, t.ref, t.bizdt, t.bill_status,
					fw.name as from_warehouse_name,
					tw.name as to_warehouse_name,
					u.name as biz_user_name,
					u1.name as input_user_name
				from t_it_bill t, t_warehouse fw, t_warehouse tw,
				   t_user u, t_user u1
				where t.from_warehouse_id = fw.id 
				  and t.to_warehouse_id = tw.id
				  and t.biz_user_id = u.id
				  and t.input_user_id = u1.id
				order by t.ref
				limit $start , $limit
				";
		$data = $db->query($sql);
		$result = array();
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待调拨" : "已调拨";
			$result[$i]["fromWarehouseName"] = $v["from_warehouse_name"];
			$result[$i]["toWarehouseName"] = $v["to_warehouse_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
		}
		
		$sql = "select count(*) as cnt
				from t_it_bill t, t_warehouse fw, t_warehouse tw,
				   t_user u, t_user u1
				where t.from_warehouse_id = fw.id 
				  and t.to_warehouse_id = tw.id
				  and t.biz_user_id = u.id
				  and t.input_user_id = u1.id
				";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		
		return array("dataList" => $result, "totalCount" => $cnt);
	}
	
	public function editITBill() {
// 		$json = $params["jsonStr"];
// 		$bill = json_decode(html_entity_decode($json), true);
// 		if ($bill == null) {
// 			return $this->bad("传入的参数错误，不是正确的JSON格式");
// 		}
		
		return $this->todo();
	}
}
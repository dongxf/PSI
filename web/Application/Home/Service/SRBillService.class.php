<?php
namespace Home\Service;

/**
 * 退货入库单Service
 *
 * @author 李静波
 */
class SRBillService extends PSIBaseService {
	public function srbillList($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];

        $db = M();
		$sql = "select w.id, w.ref, w.bizdt, c.name as customer_name, u.name as biz_user_name,"
				. " user.name as input_user_name, h.name as warehouse_name, w.rejection_sale_money,"
				. " w.bill_status "
				. " from t_sr_bill w, t_customer c, t_user u, t_user user, t_warehouse h "
				. " where w.customer_id = c.id and w.biz_user_id = u.id "
				. " and w.input_user_id = user.id and w.warehouse_id = h.id "
				. " order by w.ref desc "
				. " limit " . $start . ", " . $limit;
		$data = $db->query($sql);
		$result = array();

		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["customerName"] = $v["customer_name"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待出库" : "已出库";
			$result[$i]["amount"] = $v["rejection_sale_money"];
		}
        
		$sql = "select count(*) as cnt "
				. " from t_sr_bill w, t_customer c, t_user u, t_user user, t_warehouse h "
				. " where w.customer_id = c.id and w.biz_user_id = u.id "
				. " and w.input_user_id = user.id and w.warehouse_id = h.id ";
        $data = $db->query($sql);
        $cnt = $data[0]["cnt"];

		return array("dataList" => $result, "totalCount" => $cnt);
	}
}

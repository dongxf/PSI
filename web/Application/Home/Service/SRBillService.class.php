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

	public function srBillInfo($params) {
		$id = $params["id"];
		$us = new UserService();
		if (!$id) {
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
			return $result;
		} else {
			$db = M();
			$result = array();
			$sql = "select w.id, w.ref, w.bizdt, c.id as customer_id, c.name as customer_name, "
					. " u.id as biz_user_id, u.name as biz_user_name,"
					. " h.id as warehouse_id, h.name as warehouse_name "
					. " from t_sr_bill w, t_customer c, t_user u, t_warehouse h "
					. " where w.customer_id = c.id and w.biz_user_id = u.id "
					. " and w.warehouse_id = h.id "
					. " and w.id = '%s' ";
			$data = $db->query($sql, $id);
			if ($data) {
				$result["ref"] = $data[0]["ref"];
				$result["bizDT"] = date("Y-m-d", strtotime($data[0]["bizdt"]));
				$result["customerId"] = $data[0]["customer_id"];
				$result["customerName"] = $data[0]["customer_name"];
				$result["warehouseId"] = $data[0]["warehouse_id"];
				$result["warehouseName"] = $data[0]["warehouse_name"];
				$result["bizUserId"] = $data[0]["biz_user_id"];
				$result["bizUserName"] = $data[0]["biz_user_name"];
			}

			$sql = "select d.id, g.id as goods_id, g.code, g.name, g.spec, u.name as unit_name, d.goods_count, "
					. "d.goods_price, d.goods_money "
					. " from t_sr_bill_detail d, t_goods g, t_goods_unit u "
					. " where d.srbill_id = '%s' and d.goods_id = g.id and g.unit_id = u.id"
					. " order by d.show_order";
			$data = $db->query($sql, $id);
			$items = array();
			foreach ($data as $i => $v) {
				$items[$i]["id"] = $v["id"];
				$items[$i]["goodsId"] = $v["goods_id"];
				$items[$i]["goodsCode"] = $v["code"];
				$items[$i]["goodsName"] = $v["name"];
				$items[$i]["goodsSpec"] = $v["spec"];
				$items[$i]["unitName"] = $v["unit_name"];
				$items[$i]["goodsCount"] = $v["goods_count"];
				$items[$i]["goodsPrice"] = $v["goods_price"];
				$items[$i]["goodsMoney"] = $v["goods_money"];
			}

			$result["items"] = $items;

			return $result;
		}
	}
}

<?php

namespace Home\Service;

/**
 * 查看单据Service
 *
 * @author 李静波
 */
class BillViewService extends PSIBaseService {

	public function pwBillInfo($params) {
		$ref = $params["ref"];
		
		$result = array();
		
		$db = M();
		$sql = "select p.id, s.name as supplier_name,
				w.name as  warehouse_name,
				u.name as biz_user_name, p.biz_dt
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u
				where p.ref = '%s' and p.supplier_id = s.id and p.warehouse_id = w.id
				  and p.biz_user_id = u.id";
		$data = $db->query($sql, $ref);
		if ($data) {
			$v = $data[0];
			$id = $v["id"];
			
			$result["supplierName"] = $v["supplier_name"];
			$result["warehouseName"] = $v["warehouse_name"];
			$result["bizUserName"] = $v["biz_user_name"];
			$result["bizDT"] = date("Y-m-d", strtotime($v["biz_dt"]));
			
			$items = array();
			$sql = "select p.id, p.goods_id, g.code, g.name, g.spec, u.name as unit_name,
					p.goods_count, p.goods_price, p.goods_money
					from t_pw_bill_detail p, t_goods g, t_goods_unit u
					where p.goods_Id = g.id and g.unit_id = u.id and p.pwbill_id = '%s'
					order by p.show_order";
			$data = $db->query($sql, $id);
			foreach ( $data as $i => $v ) {
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
		}
		
		return $result;
	}

	public function wsBillInfo($params) {
		$ref = $params["ref"];
		
		$result = array();
		
		// 编辑
		$db = M();
		$sql = "select w.id, w.bizdt, c.name as customer_name,
					  u.name as biz_user_name,
					  h.name as warehouse_name
					from t_ws_bill w, t_customer c, t_user u, t_warehouse h
					where w.customer_id = c.id and w.biz_user_id = u.id
					  and w.warehouse_id = h.id
					  and w.ref = '%s' ";
		$data = $db->query($sql, $ref);
		if ($data) {
			$id = $data[0]["id"];
			
			$result["bizDT"] = date("Y-m-d", strtotime($data[0]["bizdt"]));
			$result["customerName"] = $data[0]["customer_name"];
			$result["warehouseName"] = $data[0]["warehouse_name"];
			$result["bizUserName"] = $data[0]["biz_user_name"];
			
			// 明细表
			$sql = "select d.id, g.id as goods_id, g.code, g.name, g.spec, u.name as unit_name, d.goods_count,
					d.goods_price, d.goods_money, d.sn_note
					from t_ws_bill_detail d, t_goods g, t_goods_unit u
					where d.wsbill_id = '%s' and d.goods_id = g.id and g.unit_id = u.id
					order by d.show_order";
			$data = $db->query($sql, $id);
			$items = array();
			foreach ( $data as $i => $v ) {
				$items[$i]["id"] = $v["id"];
				$items[$i]["goodsId"] = $v["goods_id"];
				$items[$i]["goodsCode"] = $v["code"];
				$items[$i]["goodsName"] = $v["name"];
				$items[$i]["goodsSpec"] = $v["spec"];
				$items[$i]["unitName"] = $v["unit_name"];
				$items[$i]["goodsCount"] = $v["goods_count"];
				$items[$i]["goodsPrice"] = $v["goods_price"];
				$items[$i]["goodsMoney"] = $v["goods_money"];
				$items[$i]["sn"] = $v["sn_note"];
			}
			
			$result["items"] = $items;
		}
		
		return $result;
	}
}
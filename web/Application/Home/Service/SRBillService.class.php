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
		$sql = "select w.id, w.ref, w.bizdt, c.name as customer_name, u.name as biz_user_name,
				 user.name as input_user_name, h.name as warehouse_name, w.rejection_sale_money,
				 w.bill_status 
				 from t_sr_bill w, t_customer c, t_user u, t_user user, t_warehouse h 
				 where w.customer_id = c.id and w.biz_user_id = u.id 
				 and w.input_user_id = user.id and w.warehouse_id = h.id 
				 order by w.ref desc 
				 limit " . $start . ", " . $limit;
		$data = $db->query($sql);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["customerName"] = $v["customer_name"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待入库" : "已入库";
			$result[$i]["amount"] = $v["rejection_sale_money"];
		}
		
		$sql = "select count(*) as cnt 
				 from t_sr_bill w, t_customer c, t_user u, t_user user, t_warehouse h 
				 where w.customer_id = c.id and w.biz_user_id = u.id 
				 and w.input_user_id = user.id and w.warehouse_id = h.id ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function srBillInfo($params) {
		$id = $params["id"];
		$us = new UserService();
		if (! $id) {
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
			return $result;
		} else {
			$db = M();
			$result = array();
			$sql = "select w.id, w.ref, w.bizdt, c.id as customer_id, c.name as customer_name, 
					 u.id as biz_user_id, u.name as biz_user_name,
					 h.id as warehouse_id, h.name as warehouse_name 
					 from t_sr_bill w, t_customer c, t_user u, t_warehouse h 
					 where w.customer_id = c.id and w.biz_user_id = u.id 
					 and w.warehouse_id = h.id 
					 and w.id = '%s' ";
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
			
			$sql = "select d.id, g.id as goods_id, g.code, g.name, g.spec, u.name as unit_name, d.goods_count, 
					d.goods_price, d.goods_money 
					 from t_sr_bill_detail d, t_goods g, t_goods_unit u 
					 where d.srbill_id = '%s' and d.goods_id = g.id and g.unit_id = u.id
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
			}
			
			$result["items"] = $items;
			
			return $result;
		}
	}

	/**
	 * 列出要选择的可以做退货入库的销售出库单
	 */
	public function selectWSBillList($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		$sql = "select w.id, w.ref, w.bizdt, c.name as customer_name, u.name as biz_user_name,
				 user.name as input_user_name, h.name as warehouse_name, w.sale_money,
				 w.bill_status 
				 from t_ws_bill w, t_customer c, t_user u, t_user user, t_warehouse h 
				 where w.customer_id = c.id and w.biz_user_id = u.id 
				 and w.input_user_id = user.id and w.warehouse_id = h.id 
				 and w.bill_status <> 0
				 order by w.ref desc 
				 limit " . $start . ", " . $limit;
		$data = $db->query($sql);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["customerName"] = $v["customer_name"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待出库" : "已出库";
			$result[$i]["amount"] = $v["sale_money"];
		}
		
		$sql = "select count(*) as cnt 
				 from t_ws_bill w, t_customer c, t_user u, t_user user, t_warehouse h 
				 where w.customer_id = c.id and w.biz_user_id = u.id 
				 and w.input_user_id = user.id and w.warehouse_id = h.id 
				 and w.bill_status <> 0 ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 新增或编辑销售退货入库单
	 */
	public function editSRBill($params) {
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = M();
		$idGen = new IdGenService();
		
		$id = $bill["id"];
		$bizDT = $bill["bizDT"];
		$customerId = $bill["customerId"];
		$warehouseId = $bill["warehouseId"];
		$bizUserId = $bill["bizUserId"];
		$items = $bill["items"];
		$wsBillId = $bill["wsBillId"];
		
		$sql = "select count(*) as cnt from t_ws_bill where id = '%s' ";
		$data = $db->query($sql, $wsBillId);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("选择的销售出库单不存在");
		}
		
		$sql = "select count(*) as cnt from t_customer where id = '%s' ";
		$data = $db->query($sql, $customerId);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("选择的客户不存在");
		}
		
		$sql = "select count(*) as cnt from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("选择的仓库不存在");
		}
		
		if ($id) {
			// 编辑
			return $this->todo();
		} else {
			// 新增
			$db->startTrans();
			try {
				$id = $idGen->newId();
				$ref = $this->genNewBillRef();
				$sql = "insert into t_sr_bill(id, bill_status, bizdt, biz_user_id, customer_id, 
						  date_created, input_user_id, ref, warehouse_id, ws_bill_id)
						values ('%s', 0, '%s', '%s', '%s', 
						  now(), '%s', '%s', '%s', '%s')";
				$us = new UserService();
				$db->execute($sql, $id, $bizDT, $bizUserId, $customerId, $us->getLoginUserId(), 
						$ref, $warehouseId, $wsBillId);
				
				foreach ( $items as $i => $v ) {
					$wsBillDetailId = $v["id"];
					$sql = "select inventory_price, goods_count, goods_price, goods_money
							from t_ws_bill_detail 
							where id = '%s' ";
					$data = $db->query($sql, $wsBillDetailId);
					if (!$data) {
						continue;
					}
					$goodsCount = $data[0]["goods_count"];
					$goodsPrice = $data[0]["goods_price"];
					$goodsMoney = $data[0]["goods_money"];
					$inventoryPrice = $data[0]["inentory_price"];
					$rejCount = $v["rejCount"];
					$rejPrice = $v["rejPrice"];
					if($rejCount == null) {
						$rejCount = 0;
					}
					$rejSaleMoney = $rejCount * $rejPrice;
					$inventoryMoney = $rejCount * $inventoryPrice;
					$goodsId = $v["goodsId"];
					
					$sql = "insert into t_sr_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						goods_price, inventory_money, inventory_price, rejection_goods_count, 
						rejection_goods_price, rejection_sale_money, show_order, srbill_id, wsbilldetail_id)
						values('%s', now(), '%s', %d, %f, %f, %f, %f, %d,
						%f, %f, %d, '%s', '%s') ";
					$db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsMoney, $goodsPrice,
							$inventoryMoney, $inventoryPrice, $rejCount, $rejPrice, $rejSaleMoney, $i,
							$id, $wsBillDetailId);
				}
				
				$db->commit();
				
				return $this->ok($id);
			} catch ( Exception $ex ) {
				$db->rollback();
				return $this->bad("数据库错误，请联系管理员");
			}
		}
	}

	/**
	 * 获得销售出库单的信息
	 */
	public function getWSBillInfoForSRBill($params) {
		$result = array();
		
		$id = $params["id"];
		$db = M();
		$sql = "select c.name as customer_name, w.ref, h.id as warehouse_id, 
				  h.name as warehouse_name, c.id as customer_id
				from t_ws_bill w, t_customer c, t_warehouse h
				where w.id = '%s' and w.customer_id = c.id and w.warehouse_id = h.id ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $result;
		}
		
		$result["ref"] = $data[0]["ref"];
		$result["customerName"] = $data[0]["customer_name"];
		$result["warehouseId"] = $data[0]["warehouse_id"];
		$result["warehouseName"] = $data[0]["warehouse_name"];
		$result["customerId"] = $data[0]["customer_id"];
		
		$sql = "select d.id, g.id as goods_id, g.code, g.name, g.spec, u.name as unit_name, d.goods_count, 
					d.goods_price, d.goods_money 
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
			$items[$i]["rejPrice"] = $v["goods_price"];
		}
		
		$result["items"] = $items;
		
		return $result;
	}

	/**
	 * 生成新的销售退货入库单单号
	 *
	 * @return string
	 */
	private function genNewBillRef() {
		$pre = "SR";
		$mid = date("Ymd");
		
		$sql = "select ref from t_sr_bill where ref like '%s' order by ref desc limit 1";
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
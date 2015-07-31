<?php

namespace Home\Service;

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
			$db = M();
			$sql = "select p.ref, p.warehouse_id, w.name as warehouse_name,
						p.biz_user_id, u.name as biz_user_name, pw.ref as pwbill_ref,
						s.name as supplier_name, s.id as supplier_id,
						p.pw_bill_id as pwbill_id, p.bizdt
					from t_pr_bill p, t_warehouse w, t_user u, t_pw_bill pw, t_supplier s
					where p.id = '%s' 
						and p.warehouse_id = w.id
						and p.biz_user_id = u.id
						and p.pw_bill_id = pw.id
						and p.supplier_id = s.id ";
			$data = $db->query($sql, $id);
			if (! $data) {
				return $result;
			}
			
			$result["ref"] = $data[0]["ref"];
			$result["bizUserId"] = $data[0]["biz_user_id"];
			$result["bizUserName"] = $data[0]["biz_user_name"];
			$result["warehouseId"] = $data[0]["warehouse_id"];
			$result["warehouseName"] = $data[0]["warehouse_name"];
			$result["pwbillRef"] = $data[0]["pwbill_ref"];
			$result["supplierId"] = $data[0]["supplier_id"];
			$result["supplierName"] = $data[0]["supplier_name"];
			$result["pwbillId"] = $data[0]["pwbill_id"];
			$result["bizDT"] = $this->toYMD($data[0]["bizdt"]);
			
			$items = array();
			$sql = "select p.id, p.goods_id, g.code as goods_code, g.name as goods_name,
						g.spec as goods_spec, u.name as unit_name, p.goods_count,
						p.goods_price, p.goods_money, p.rejection_goods_count as rej_count,
						p.rejection_goods_price as rej_price, p.rejection_money as rej_money
					from t_pr_bill_detail p, t_goods g, t_goods_unit u
					where p.prbill_id = '%s'
						and p.goods_id = g.id
						and g.unit_id = u.id
					order by p.show_order";
			$data = $db->query($sql, $id);
			foreach ( $data as $i => $v ) {
				$items[$i]["id"] = $v["id"];
				$items[$i]["goodsId"] = $v["goods_id"];
				$items[$i]["goodsCode"] = $v["goods_code"];
				$items[$i]["goodsName"] = $v["goods_name"];
				$items[$i]["goodsSpec"] = $v["goods_spec"];
				$items[$i]["unitName"] = $v["unit_name"];
				$items[$i]["goodsCount"] = $v["goods_count"];
				$items[$i]["goodsPrice"] = $v["goods_price"];
				$items[$i]["goodsMoney"] = $v["goods_money"];
				$items[$i]["rejCount"] = $v["rej_count"];
				$items[$i]["rejPrice"] = $v["rej_price"];
				$items[$i]["rejMoney"] = $v["rej_money"];
			}
			
			$result["items"] = $items;
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
		
		$db = M();
		
		$id = $bill["id"];
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$sql = "select name from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			return $this->bad("选择的仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			return $this->bad("选择的业务人员不存在，无法保存");
		}
		
		$pwBillId = $bill["pwBillId"];
		$sql = "select supplier_id from t_pw_bill where id = '%s' ";
		$data = $db->query($sql, $pwBillId);
		if (! $data) {
			return $this->bad("选择采购入库单不存在，无法保存");
		}
		$supplierId = $data[0]["supplier_id"];
		
		$items = $bill["items"];
		
		$idGen = new IdGenService();
		$us = new UserService();
		
		if ($id) {
			// 编辑采购退货出库单
			$db->startTrans();
			try {
				$sql = "select ref, bill_status
						from t_pr_bill
						where id = '%s' ";
				$data = $db->query($sql, $id);
				if (! $data) {
					$db->rollback();
					return $this->bad("要编辑的采购退货出库单不存在");
				}
				$ref = $data[0]["ref"];
				$billStatus = $data[0]["bill_status"];
				if ($billStatus != 0) {
					$db->rollback();
					return $this->bad("采购退货出库单(单号：$ref)已经提交，不能再被编辑");
				}
				
				// 明细表
				$sql = "delete from t_pr_bill_detail where prbill_id = '%s' ";
				$db->execute($sql, $id);
				
				$sql = "insert into t_pr_bill_detail(id, date_created, goods_id, goods_count, goods_price,
						goods_money, rejection_goods_count, rejection_goods_price, rejection_money, show_order,
						prbill_id, pwbilldetail_id)
						values ('%s', now(), '%s', %d, %f, %f, %d, %f, %f, %d, '%s', '%s')";
				foreach ( $items as $i => $v ) {
					$pwbillDetailId = $v["id"];
					$goodsId = $v["goodsId"];
					$goodsCount = $v["goodsCount"];
					$goodsPrice = $v["goodsPrice"];
					$goodsMoney = $goodsCount * $goodsPrice;
					$rejCount = $v["rejCount"];
					$rejPrice = $v["rejPrice"];
					$rejMoney = $rejCount * $rejPrice;
					
					$rc = $db->execute($sql, $pwbillDetailId, $goodsId, $goodsCount, $goodsPrice, 
							$goodsMoney, $rejCount, $rejPrice, $rejMoney, $i, $id, $pwbillDetailId);
					if (! $rc) {
						$db->rollback();
						return $this->sqlError();
					}
				}
				
				$sql = "select sum(rejection_money) as rej_money 
						from t_pr_bill_detail 
						where prbill_id = '%s' ";
				$data = $db->query($sql, $id);
				$rejMoney = $data[0]["rej_money"];
				
				$sql = "update t_pr_bill
						set rejection_money = %f,
							bizdt = '%s', biz_user_id = '%s',
							date_created = now(), input_user_id = '%s',
							warehouse_id = '%s'
						where id = '%s' ";
				$rc = $db->execute($sql, $rejMoney, $bizDT, $bizUserId, $us->getLoginUserId(), 
						$warehouseId, $id);
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
				
				$bs = new BizlogService();
				$log = "编辑采购退货出库单，单号：$ref";
				$bs->insertBizlog($log, "采购退货出库");
				
				$db->commit();
			} catch ( Exception $e ) {
				$db->rollback();
				return $this->sqlError();
			}
		} else {
			// 新增采购退货出库单
			$db->startTrans();
			try {
				$id = $idGen->newId();
				$ref = $this->genNewBillRef();
				
				// 主表
				$sql = "insert into t_pr_bill(id, bill_status, bizdt, biz_user_id, supplier_id, date_created,
							input_user_id, ref, warehouse_id, pw_bill_id)
						values ('%s', 0, '%s', '%s', '%s', now(), '%s', '%s', '%s', '%s')";
				$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $supplierId, 
						$us->getLoginUserId(), $ref, $warehouseId, $pwBillId);
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
				
				// 明细表
				$sql = "insert into t_pr_bill_detail(id, date_created, goods_id, goods_count, goods_price,
						goods_money, rejection_goods_count, rejection_goods_price, rejection_money, show_order,
						prbill_id, pwbilldetail_id)
						values ('%s', now(), '%s', %d, %f, %f, %d, %f, %f, %d, '%s', '%s')";
				foreach ( $items as $i => $v ) {
					$pwbillDetailId = $v["id"];
					$goodsId = $v["goodsId"];
					$goodsCount = $v["goodsCount"];
					$goodsPrice = $v["goodsPrice"];
					$goodsMoney = $goodsCount * $goodsPrice;
					$rejCount = $v["rejCount"];
					$rejPrice = $v["rejPrice"];
					$rejMoney = $rejCount * $rejPrice;
					
					$rc = $db->execute($sql, $pwbillDetailId, $goodsId, $goodsCount, $goodsPrice, 
							$goodsMoney, $rejCount, $rejPrice, $rejMoney, $i, $id, $pwbillDetailId);
					if (! $rc) {
						$db->rollback();
						return $this->sqlError();
					}
				}
				
				$sql = "select sum(rejection_money) as rej_money 
						from t_pr_bill_detail 
						where prbill_id = '%s' ";
				$data = $db->query($sql, $id);
				$rejMoney = $data[0]["rej_money"];
				
				$sql = "update t_pr_bill
						set rejection_money = %f
						where id = '%s' ";
				$rc = $db->execute($sql, $rejMoney, $id);
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
				
				$bs = new BizlogService();
				$log = "新建采购退货出库单，单号：$ref";
				$bs->insertBizlog($log, "采购退货出库");
				
				$db->commit();
			} catch ( Exception $e ) {
				$db->rollback();
				return $this->sqlError();
			}
		}
		
		return $this->ok($id);
	}

	public function selectPWBillList($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$ref = $params["ref"];
		$supplierId = $params["supplierId"];
		$warehouseId = $params["warehouseId"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		
		$result = array();
		
		$db = M();
		
		$sql = "select p.id, p.ref, p.biz_dt, s.name as supplier_name, p.goods_money,
					w.name as warehouse_name, u1.name as biz_user_name, u2.name as input_user_name
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u1, t_user u2
				where (p.supplier_id = s.id) 
					and (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id) 
					and (p.input_user_id = u2.id)
					and (p.bill_status = 1000)";
		$queryParamas = array();
		
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParamas[] = "%$ref%";
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParamas[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParamas[] = $warehouseId;
		}
		if ($fromDT) {
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParamas[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
			$queryParamas[] = $toDT;
		}
		
		$sql .= " order by p.ref desc limit %d, %d";
		$queryParamas[] = $start;
		$queryParamas[] = $limit;
		
		$data = $db->query($sql, $queryParamas);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = $this->toYMD($v["biz_dt"]);
			$result[$i]["supplierName"] = $v["supplier_name"];
			$result[$i]["amount"] = $v["goods_money"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
		}
		
		$sql = "select count(*) as cnt
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u1, t_user u2
				where (p.supplier_id = s.id)
					and (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id)
					and (p.input_user_id = u2.id)
					and (p.bill_status = 1000)";
		$queryParamas = array();
		
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParamas[] = "%$ref%";
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParamas[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParamas[] = $warehouseId;
		}
		if ($fromDT) {
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParamas[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
			$queryParamas[] = $toDT;
		}
		
		$data = $db->query($sql, $queryParamas);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function getPWBillInfoForPRBill($params) {
		$id = $params["id"];
		$result = array();
		
		$db = M();
		
		$sql = "select p.ref,s.id as supplier_id, s.name as supplier_name,
					w.id as warehouse_id, w.name as warehouse_name 
				from t_pw_bill p, t_supplier s, t_warehouse w
				where p.supplier_id = s.id
					and p.warehouse_id = w.id
					and p.id = '%s' ";
		
		$data = $db->query($sql, $id);
		if (! $data) {
			return $result;
		}
		
		$result["ref"] = $data[0]["ref"];
		$result["supplierId"] = $data[0]["supplier_id"];
		$result["supplierName"] = $data[0]["supplier_name"];
		$result["warehouseId"] = $data[0]["warehouse_id"];
		$result["warehouseName"] = $data[0]["warehouse_name"];
		
		$items = array();
		
		$sql = "select p.id, g.id as goods_id, g.code as goods_code, g.name as goods_name,
					g.spec as goods_spec, u.name as unit_name, 
					p.goods_count, p.goods_price, p.goods_money
				from t_pw_bill_detail p, t_goods g, t_goods_unit u
				where p.goods_id = g.id
					and g.unit_id = u.id
					and p.pwbill_id = '%s'
				order by p.show_order ";
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$items[$i]["id"] = $v["id"];
			$items[$i]["goodsId"] = $v["goods_id"];
			$items[$i]["goodsCode"] = $v["goods_code"];
			$items[$i]["goodsName"] = $v["goods_name"];
			$items[$i]["goodsSpec"] = $v["goods_spec"];
			$items[$i]["unitName"] = $v["unit_name"];
			$items[$i]["goodsCount"] = $v["goods_count"];
			$items[$i]["goodsPrice"] = $v["goods_price"];
			$items[$i]["goodsMoney"] = $v["goods_money"];
			$items[$i]["rejPrice"] = $v["goods_price"];
		}
		
		$result["items"] = $items;
		
		return $result;
	}

	public function prbillList($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		$result = array();
		$sql = "select p.id, p.ref, p.bill_status, w.name as warehouse_name, p.bizdt,
					p.rejection_money, u1.name as biz_user_name, u2.name as input_user_name,
					s.name as supplier_name
				from t_pr_bill p, t_warehouse w, t_user u1, t_user u2, t_supplier s
				where p.warehouse_id = w.id
					and p.biz_user_id = u1.id
					and p.input_user_id = u2.id
					and p.supplier_id = s.id
				order by p.ref desc
				limit %d, %d";
		$data = $db->query($sql, $start, $limit);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待出库" : "已出库";
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["supplierName"] = $v["supplier_name"];
			$result[$i]["rejMoney"] = $v["rejection_money"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["bizDT"] = $this->toYMD($v["bizdt"]);
		}
		
		$sql = "select count(*) as cnt
				from t_pr_bill p, t_warehouse w, t_user u1, t_user u2, t_supplier s
				where p.warehouse_id = w.id
					and p.biz_user_id = u1.id
					and p.input_user_id = u2.id
					and p.supplier_id = s.id ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function prBillDetailList($params) {
		$id = $params["id"];
		
		$db = M();
		$sql = "select g.code, g.name, g.spec, u.name as unit_name, 
					p.rejection_goods_count as rej_count, p.rejection_goods_price as rej_price, 
					p.rejection_money as rej_money
				from t_pr_bill_detail p, t_goods g, t_goods_unit u
				where p.goods_id = g.id and g.unit_id = u.id and p.prbill_id = '%s'
				order by p.show_order";
		$result = array();
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["rejCount"] = $v["rej_count"];
			$result[$i]["rejPrice"] = $v["rej_price"];
			$result[$i]["rejMoney"] = $v["rej_money"];
		}
		
		return $result;
	}

	public function deletePRBill($params) {
		$id = $params["id"];
		
		$db = M();
		
		$db->startTrans();
		try {
			$sql = "select ref, bill_status from t_pr_bill where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				$db->rollback();
				return $this->bad("要删除的采购退货出库单不存在");
			}
			$ref = $data[0]["ref"];
			$billStatus = $data[0]["bill_status"];
			if ($billStatus != 0) {
				$db->rollback();
				return $this->bad("采购退货出库单(单号：$ref)已经提交，不能被删除");
			}
			
			$sql = "delete from t_pr_bill_detail where prbill_id = '%s'";
			$db->execute($sql, $id);
			
			$sql = "delete from t_pr_bill where id = '%s' ";
			$db->execute($sql, $id);
			
			$bs = new BizlogService();
			$log = "删除采购退货出库单，单号：$ref";
			$bs->insertBizlog($log, "采购退货出库");
			
			$db->commit();
		} catch ( Exception $e ) {
			$db->rollback();
			return $this->sqlError();
		}
		
		return $this->ok();
	}
}
<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 销售退货入库单 DAO
 *
 * @author 李静波
 */
class SRBillDAO extends PSIBaseExDAO {

	/**
	 * 生成新的销售退货入库单单号
	 *
	 * @return string
	 */
	private function genNewBillRef($companyId) {
		$db = $this->db;
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getSRBillRefPre($companyId);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_sr_bill where ref like '%s' order by ref desc limit 1";
		$data = M()->query($sql, $pre . $mid . "%");
		$sufLength = 3;
		$suf = str_pad("1", $sufLength, "0", STR_PAD_LEFT);
		if ($data) {
			$ref = $data[0]["ref"];
			$nextNumber = intval(substr($ref, strlen($pre . $mid))) + 1;
			$suf = str_pad($nextNumber, $sufLength, "0", STR_PAD_LEFT);
		}
		
		return $pre . $mid . $suf;
	}

	/**
	 * 销售退货入库单主表信息列表
	 */
	public function srbillList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$warehouseId = $params["warehouseId"];
		$customerId = $params["customerId"];
		$sn = $params["sn"];
		$paymentType = $params["paymentType"];
		
		$sql = "select w.id, w.ref, w.bizdt, c.name as customer_name, u.name as biz_user_name,
				 	user.name as input_user_name, h.name as warehouse_name, w.rejection_sale_money,
				 	w.bill_status, w.date_created, w.payment_type
				 from t_sr_bill w, t_customer c, t_user u, t_user user, t_warehouse h
				 where (w.customer_id = c.id) and (w.biz_user_id = u.id)
				 and (w.input_user_id = user.id) and (w.warehouse_id = h.id) ";
		$queryParams = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::SALE_REJECTION, "w", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (w.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (w.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (w.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (w.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($customerId) {
			$sql .= " and (w.customer_id = '%s') ";
			$queryParams[] = $customerId;
		}
		if ($warehouseId) {
			$sql .= " and (w.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($sn) {
			$sql .= " and (w.id in (
					  select d.srbill_id
					  from t_sr_bill_detail d
					  where d.sn_note like '%s')) ";
			$queryParams[] = "%$sn%";
		}
		if ($paymentType != - 1) {
			$sql .= " and (w.payment_type = %d) ";
			$queryParams[] = $paymentType;
		}
		
		$sql .= " order by w.bizdt desc, w.ref desc
				 limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
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
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["paymentType"] = $v["payment_type"];
		}
		
		$sql = "select count(*) as cnt
				 from t_sr_bill w, t_customer c, t_user u, t_user user, t_warehouse h
				 where (w.customer_id = c.id) and (w.biz_user_id = u.id)
				 and (w.input_user_id = user.id) and (w.warehouse_id = h.id) ";
		$queryParams = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::SALE_REJECTION, "w", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (w.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (w.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (w.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (w.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($customerId) {
			$sql .= " and (w.customer_id = '%s') ";
			$queryParams[] = $customerId;
		}
		if ($warehouseId) {
			$sql .= " and (w.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($sn) {
			$sql .= " and (w.id in (
					  select d.srbill_id
					  from t_sr_bill_detail d
					  where d.sn_note like '%s')) ";
			$queryParams[] = "%$sn%";
		}
		if ($paymentType != - 1) {
			$sql .= " and (w.payment_type = %d) ";
			$queryParams[] = $paymentType;
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 销售退货入库单明细信息列表
	 */
	public function srBillDetailList($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$sql = "select s.id, g.code, g.name, g.spec, u.name as unit_name,
				   s.rejection_goods_count, s.rejection_goods_price, s.rejection_sale_money,
					s.sn_note
				from t_sr_bill_detail s, t_goods g, t_goods_unit u
				where s.srbill_id = '%s' and s.goods_id = g.id and g.unit_id = u.id
					and s.rejection_goods_count > 0
				order by s.show_order";
		$data = $db->query($sql, $id);
		
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["rejCount"] = $v["rejection_goods_count"];
			$result[$i]["rejPrice"] = $v["rejection_goods_price"];
			$result[$i]["rejSaleMoney"] = $v["rejection_sale_money"];
			$result[$i]["sn"] = $v["sn_note"];
		}
		return $result;
	}

	/**
	 * 列出要选择的可以做退货入库的销售出库单
	 */
	public function selectWSBillList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$ref = $params["ref"];
		$customerId = $params["customerId"];
		$warehouseId = $params["warehouseId"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$sn = $params["sn"];
		
		$sql = "select w.id, w.ref, w.bizdt, c.name as customer_name, u.name as biz_user_name,
				 user.name as input_user_name, h.name as warehouse_name, w.sale_money
				 from t_ws_bill w, t_customer c, t_user u, t_user user, t_warehouse h
				 where (w.customer_id = c.id) and (w.biz_user_id = u.id)
				 and (w.input_user_id = user.id) and (w.warehouse_id = h.id)
				 and (w.bill_status = 1000) ";
		$queryParamas = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::SALE_REJECTION, "w", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParamas = $rs[1];
		}
		
		if ($ref) {
			$sql .= " and (w.ref like '%s') ";
			$queryParamas[] = "%$ref%";
		}
		if ($customerId) {
			$sql .= " and (w.customer_id = '%s') ";
			$queryParamas[] = $customerId;
		}
		if ($warehouseId) {
			$sql .= " and (w.warehouse_id = '%s') ";
			$queryParamas[] = $warehouseId;
		}
		if ($fromDT) {
			$sql .= " and (w.bizdt >= '%s') ";
			$queryParamas[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (w.bizdt <= '%s') ";
			$queryParamas[] = $toDT;
		}
		if ($sn) {
			$sql .= " and (w.id in (
						select wsbill_id
						from t_ws_bill_detail d
						where d.sn_note like '%s'
					))";
			$queryParamas[] = "%$sn%";
		}
		$sql .= " order by w.ref desc
				 limit %d, %d";
		$queryParamas[] = $start;
		$queryParamas[] = $limit;
		$data = $db->query($sql, $queryParamas);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["customerName"] = $v["customer_name"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["amount"] = $v["sale_money"];
		}
		
		$sql = "select count(*) as cnt
				 from t_ws_bill w, t_customer c, t_user u, t_user user, t_warehouse h
				 where (w.customer_id = c.id) and (w.biz_user_id = u.id)
				 and (w.input_user_id = user.id) and (w.warehouse_id = h.id)
				 and (w.bill_status = 1000) ";
		$queryParamas = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::SALE_REJECTION, "w", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParamas = $rs[1];
		}
		
		if ($ref) {
			$sql .= " and (w.ref like '%s') ";
			$queryParamas[] = "%$ref%";
		}
		if ($customerId) {
			$sql .= " and (w.customer_id = '%s') ";
			$queryParamas[] = $customerId;
		}
		if ($warehouseId) {
			$sql .= " and (w.warehouse_id = '%s') ";
			$queryParamas[] = $warehouseId;
		}
		if ($fromDT) {
			$sql .= " and (w.bizdt >= '%s') ";
			$queryParamas[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (w.bizdt <= '%s') ";
			$queryParamas[] = $toDT;
		}
		if ($sn) {
			$sql .= " and (w.id in (
						select wsbill_id
						from t_ws_bill_detail d
						where d.sn_note like '%s'
					))";
			$queryParamas[] = "%$sn%";
		}
		
		$data = $db->query($sql, $queryParamas);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 获得销售出库单的信息
	 */
	public function getWSBillInfoForSRBill($params) {
		$db = $this->db;
		
		$result = array();
		
		$id = $params["id"];
		
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
			$items[$i]["rejPrice"] = $v["goods_price"];
			$items[$i]["sn"] = $v["sn_note"];
		}
		
		$result["items"] = $items;
		
		return $result;
	}

	/**
	 * 新建销售退货入库单
	 *
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function addSRBill(& $bill) {
		$db = $this->db;
		
		$bizDT = $bill["bizDT"];
		$customerId = $bill["customerId"];
		$warehouseId = $bill["warehouseId"];
		$bizUserId = $bill["bizUserId"];
		$items = $bill["items"];
		$wsBillId = $bill["wsBillId"];
		$paymentType = $bill["paymentType"];
		
		$wsBillDAO = new WSBillDAO($db);
		$wsBill = $wsBillDAO->getWSBillById($wsBillId);
		if (! $wsBill) {
			return $this->bad("选择的销售出库单不存在");
		}
		
		$customerDAO = new CustomerDAO($db);
		$customer = $customerDAO->getCustomerById($customerId);
		if (! $customer) {
			return $this->bad("选择的客户不存在");
		}
		
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("选择的仓库不存在");
		}
		
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("选择的业务员不存在");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$dataOrg = $bill["dataOrg"];
		$companyId = $bill["companyId"];
		$loginUserId = $bill["loginUserId"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$id = $this->newId();
		$ref = $this->genNewBillRef($companyId);
		$sql = "insert into t_sr_bill(id, bill_status, bizdt, biz_user_id, customer_id,
					date_created, input_user_id, ref, warehouse_id, ws_bill_id, payment_type,
					data_org, company_id)
				values ('%s', 0, '%s', '%s', '%s',
					  now(), '%s', '%s', '%s', '%s', %d, '%s', '%s')";
		
		$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $customerId, $loginUserId, $ref, 
				$warehouseId, $wsBillId, $paymentType, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		foreach ( $items as $i => $v ) {
			$wsBillDetailId = $v["id"];
			$sql = "select inventory_price, goods_count, goods_price, goods_money
					from t_ws_bill_detail
					where id = '%s' ";
			$data = $db->query($sql, $wsBillDetailId);
			if (! $data) {
				continue;
			}
			$goodsCount = $data[0]["goods_count"];
			$goodsPrice = $data[0]["goods_price"];
			$goodsMoney = $data[0]["goods_money"];
			$inventoryPrice = $data[0]["inventory_price"];
			$rejCount = $v["rejCount"];
			$rejPrice = $v["rejPrice"];
			if ($rejCount == null) {
				$rejCount = 0;
			}
			$rejSaleMoney = $rejCount * $rejPrice;
			$inventoryMoney = $rejCount * $inventoryPrice;
			$goodsId = $v["goodsId"];
			$sn = $v["sn"];
			
			$sql = "insert into t_sr_bill_detail(id, date_created, goods_id, goods_count, goods_money,
					goods_price, inventory_money, inventory_price, rejection_goods_count,
					rejection_goods_price, rejection_sale_money, show_order, srbill_id, wsbilldetail_id,
						sn_note, data_org, company_id)
					values('%s', now(), '%s', %d, %f, %f, %f, %f, %d,
					%f, %f, %d, '%s', '%s', '%s', '%s', '%s') ";
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsMoney, 
					$goodsPrice, $inventoryMoney, $inventoryPrice, $rejCount, $rejPrice, 
					$rejSaleMoney, $i, $id, $wsBillDetailId, $sn, $dataOrg, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 更新主表的汇总信息
		$sql = "select sum(rejection_sale_money) as rej_money,
				sum(inventory_money) as inv_money
				from t_sr_bill_detail
				where srbill_id = '%s' ";
		$data = $db->query($sql, $id);
		$rejMoney = $data[0]["rej_money"];
		$invMoney = $data[0]["inv_money"];
		$profit = $invMoney - $rejMoney;
		$sql = "update t_sr_bill
				set rejection_sale_money = %f, inventory_money = %f, profit = %f
				where id = '%s' ";
		$rc = $db->execute($sql, $rejMoney, $invMoney, $profit, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	public function getSRBillById($id) {
		$db = $this->db;
		$sql = "select bill_status, ref, data_org, company_id from t_sr_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return null;
		} else {
			return array(
					"ref" => $data[0]["ref"],
					"billStatus" => $data[0]["bill_status"],
					"dataOrg" => $data[0]["data_org"],
					"companyId" => $data[0]["company_id"]
			);
		}
	}

	/**
	 * 编辑销售退货入库单
	 *
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function updateSRBill(& $bill) {
		$db = $this->db;
		
		$id = $bill["id"];
		
		$oldBill = $this->getSRBillById($id);
		if (! $oldBill) {
			return $this->bad("要编辑的销售退货入库单不存在");
		}
		$billStatus = $oldBill["billStatus"];
		if ($billStatus != 0) {
			return $this->bad("销售退货入库单已经提交，不能再编辑");
		}
		$ref = $oldBill["ref"];
		$dataOrg = $oldBill["dataOrg"];
		$companyId = $oldBill["companyId"];
		
		$bizDT = $bill["bizDT"];
		$customerId = $bill["customerId"];
		$warehouseId = $bill["warehouseId"];
		$bizUserId = $bill["bizUserId"];
		$items = $bill["items"];
		$paymentType = $bill["paymentType"];
		
		$customerDAO = new CustomerDAO($db);
		$customer = $customerDAO->getCustomerById($customerId);
		if (! $customer) {
			return $this->bad("选择的客户不存在");
		}
		
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("选择的仓库不存在");
		}
		
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("选择的业务员不存在");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		// 主表
		$sql = "update t_sr_bill
				set bizdt = '%s', biz_user_id = '%s', date_created = now(),
				   input_user_id = '%s', warehouse_id = '%s',
					payment_type = %d
				where id = '%s' ";
		$db->execute($sql, $bizDT, $bizUserId, $loginUserId, $warehouseId, $paymentType, $id);
		
		// 退货明细
		$sql = "delete from t_sr_bill_detail where srbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		foreach ( $items as $i => $v ) {
			$wsBillDetailId = $v["id"];
			$sql = "select inventory_price, goods_count, goods_price, goods_money
					from t_ws_bill_detail
					where id = '%s' ";
			$data = $db->query($sql, $wsBillDetailId);
			if (! $data) {
				continue;
			}
			$goodsCount = $data[0]["goods_count"];
			$goodsPrice = $data[0]["goods_price"];
			$goodsMoney = $data[0]["goods_money"];
			$inventoryPrice = $data[0]["inventory_price"];
			$rejCount = $v["rejCount"];
			$rejPrice = $v["rejPrice"];
			if ($rejCount == null) {
				$rejCount = 0;
			}
			$rejSaleMoney = $v["rejMoney"];
			$inventoryMoney = $rejCount * $inventoryPrice;
			$goodsId = $v["goodsId"];
			$sn = $v["sn"];
			
			$sql = "insert into t_sr_bill_detail(id, date_created, goods_id, goods_count, goods_money,
					goods_price, inventory_money, inventory_price, rejection_goods_count,
					rejection_goods_price, rejection_sale_money, show_order, srbill_id, wsbilldetail_id,
						sn_note, data_org, company_id)
					values('%s', now(), '%s', %d, %f, %f, %f, %f, %d,
						%f, %f, %d, '%s', '%s', '%s', '%s', '%s') ";
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsMoney, 
					$goodsPrice, $inventoryMoney, $inventoryPrice, $rejCount, $rejPrice, 
					$rejSaleMoney, $i, $id, $wsBillDetailId, $sn, $dataOrg, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 更新主表的汇总信息
		$sql = "select sum(rejection_sale_money) as rej_money,
				sum(inventory_money) as inv_money
				from t_sr_bill_detail
				where srbill_id = '%s' ";
		$data = $db->query($sql, $id);
		$rejMoney = $data[0]["rej_money"];
		if (! $rejMoney) {
			$rejMoney = 0;
		}
		$invMoney = $data[0]["inv_money"];
		if (! $invMoney) {
			$invMoney = 0;
		}
		$profit = $invMoney - $rejMoney;
		$sql = "update t_sr_bill
				set rejection_sale_money = %f, inventory_money = %f, profit = %f
				where id = '%s' ";
		$rc = $db->execute($sql, $rejMoney, $invMoney, $profit, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 获得退货入库单单据数据
	 */
	public function srBillInfo($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		if (! $id) {
			// 新增单据
			$result["bizUserId"] = $params["loginUserId"];
			$result["bizUserName"] = $params["loginUserName"];
			return $result;
		} else {
			// 编辑单据
			$result = array();
			$sql = "select w.id, w.ref, w.bill_status, w.bizdt, c.id as customer_id, c.name as customer_name,
					 u.id as biz_user_id, u.name as biz_user_name,
					 h.id as warehouse_id, h.name as warehouse_name, wsBill.ref as ws_bill_ref,
						w.payment_type
					 from t_sr_bill w, t_customer c, t_user u, t_warehouse h, t_ws_bill wsBill
					 where w.customer_id = c.id and w.biz_user_id = u.id
					 and w.warehouse_id = h.id
					 and w.id = '%s' and wsBill.id = w.ws_bill_id";
			$data = $db->query($sql, $id);
			if ($data) {
				$result["ref"] = $data[0]["ref"];
				$result["billStatus"] = $data[0]["bill_status"];
				$result["bizDT"] = date("Y-m-d", strtotime($data[0]["bizdt"]));
				$result["customerId"] = $data[0]["customer_id"];
				$result["customerName"] = $data[0]["customer_name"];
				$result["warehouseId"] = $data[0]["warehouse_id"];
				$result["warehouseName"] = $data[0]["warehouse_name"];
				$result["bizUserId"] = $data[0]["biz_user_id"];
				$result["bizUserName"] = $data[0]["biz_user_name"];
				$result["wsBillRef"] = $data[0]["ws_bill_ref"];
				$result["paymentType"] = $data[0]["payment_type"];
			}
			
			$sql = "select d.id, g.id as goods_id, g.code, g.name, g.spec, u.name as unit_name, d.goods_count,
					d.goods_price, d.goods_money,
					d.rejection_goods_count, d.rejection_goods_price, d.rejection_sale_money,
					d.wsbilldetail_id, d.sn_note
					 from t_sr_bill_detail d, t_goods g, t_goods_unit u
					 where d.srbill_id = '%s' and d.goods_id = g.id and g.unit_id = u.id
					 order by d.show_order";
			$data = $db->query($sql, $id);
			$items = array();
			foreach ( $data as $i => $v ) {
				$items[$i]["id"] = $v["wsbilldetail_id"];
				$items[$i]["goodsId"] = $v["goods_id"];
				$items[$i]["goodsCode"] = $v["code"];
				$items[$i]["goodsName"] = $v["name"];
				$items[$i]["goodsSpec"] = $v["spec"];
				$items[$i]["unitName"] = $v["unit_name"];
				$items[$i]["goodsCount"] = $v["goods_count"];
				$items[$i]["goodsPrice"] = $v["goods_price"];
				$items[$i]["goodsMoney"] = $v["goods_money"];
				$items[$i]["rejCount"] = $v["rejection_goods_count"];
				$items[$i]["rejPrice"] = $v["rejection_goods_price"];
				$items[$i]["rejMoney"] = $v["rejection_sale_money"];
				$items[$i]["sn"] = $v["sn_note"];
			}
			
			$result["items"] = $items;
			
			return $result;
		}
	}

	/**
	 * 删除销售退货入库单
	 */
	public function deleteSRBill(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$bill = $this->getSRBillById($id);
		if (! $bill) {
			return $this->bad("要删除的销售退货入库单不存在");
		}
		
		$billStatus = $bill["billStatus"];
		$ref = $bill["ref"];
		if ($billStatus != 0) {
			return $this->bad("销售退货入库单[单号: {$ref}]已经提交，不能删除");
		}
		
		$sql = "delete from t_sr_bill_detail where srbill_id = '%s'";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_sr_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}
}
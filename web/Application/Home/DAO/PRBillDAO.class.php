<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 采购退货出库单 DAO
 *
 * @author 李静波
 */
class PRBillDAO extends PSIBaseExDAO {

	/**
	 * 生成新的采购退货出库单单号
	 *
	 * @return string
	 */
	private function genNewBillRef($companyId) {
		$db = $this->db;
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getPRBillRefPre($companyId);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_pr_bill where ref like '%s' order by ref desc limit 1";
		$data = $db->query($sql, $pre . $mid . "%");
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
	 * 新建采购退货出库单
	 *
	 * @param array $bill        	
	 */
	public function addPRBill(& $bill) {
		$db = $this->db;
		
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("选择的仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("选择的业务人员不存在，无法保存");
		}
		
		$pwBillId = $bill["pwBillId"];
		$sql = "select supplier_id from t_pw_bill where id = '%s' ";
		$data = $db->query($sql, $pwBillId);
		if (! $data) {
			return $this->bad("选择采购入库单不存在，无法保存");
		}
		$supplierId = $data[0]["supplier_id"];
		
		$receivingType = $bill["receivingType"];
		
		$items = $bill["items"];
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$id = $this->newId();
		
		$dataOrg = $bill["dataOrg"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		$companyId = $bill["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		// 新增采购退货出库单
		$ref = $this->genNewBillRef($companyId);
		
		// 主表
		$sql = "insert into t_pr_bill(id, bill_status, bizdt, biz_user_id, supplier_id, date_created,
					input_user_id, ref, warehouse_id, pw_bill_id, receiving_type, data_org, company_id)
				values ('%s', 0, '%s', '%s', '%s', now(), '%s', '%s', '%s', '%s', %d, '%s', '%s')";
		$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $supplierId, $loginUserId, $ref, 
				$warehouseId, $pwBillId, $receivingType, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 明细表
		$sql = "insert into t_pr_bill_detail(id, date_created, goods_id, goods_count, goods_price,
				goods_money, rejection_goods_count, rejection_goods_price, rejection_money, show_order,
				prbill_id, pwbilldetail_id, data_org, company_id)
				values ('%s', now(), '%s', %d, %f, %f, %d, %f, %f, %d, '%s', '%s', '%s', '%s')";
		foreach ( $items as $i => $v ) {
			$pwbillDetailId = $v["id"];
			$goodsId = $v["goodsId"];
			$goodsCount = $v["goodsCount"];
			$goodsPrice = $v["goodsPrice"];
			$goodsMoney = $goodsCount * $goodsPrice;
			$rejCount = $v["rejCount"];
			$rejPrice = $v["rejPrice"];
			$rejMoney = $v["rejMoney"];
			
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsPrice, 
					$goodsMoney, $rejCount, $rejPrice, $rejMoney, $i, $id, $pwbillDetailId, $dataOrg, 
					$companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
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
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	public function getPRBillById($id) {
		$db = $this->db;
		
		$sql = "select ref, bill_status, data_org, company_id
				from t_pr_bill
				where id = '%s' ";
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
	 * 编辑采购退货出库单
	 *
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function updatePRBill(& $bill) {
		$db = $this->db;
		
		$id = $bill["id"];
		
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("选择的仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("选择的业务人员不存在，无法保存");
		}
		
		$pwBillId = $bill["pwBillId"];
		$sql = "select supplier_id from t_pw_bill where id = '%s' ";
		$data = $db->query($sql, $pwBillId);
		if (! $data) {
			return $this->bad("选择采购入库单不存在，无法保存");
		}
		$supplierId = $data[0]["supplier_id"];
		
		$receivingType = $bill["receivingType"];
		
		$items = $bill["items"];
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$oldBill = $this->getPRBillById($id);
		if (! $oldBill) {
			return $this->bad("要编辑的采购退货出库单不存在");
		}
		$ref = $oldBill["ref"];
		$companyId = $oldBill["companyId"];
		$billStatus = $oldBill["billStatus"];
		if ($billStatus != 0) {
			return $this->bad("采购退货出库单(单号：$ref)已经提交，不能再被编辑");
		}
		$dataOrg = $oldBill["data_org"];
		
		// 明细表
		$sql = "delete from t_pr_bill_detail where prbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "insert into t_pr_bill_detail(id, date_created, goods_id, goods_count, goods_price,
				goods_money, rejection_goods_count, rejection_goods_price, rejection_money, show_order,
				prbill_id, pwbilldetail_id, data_org, company_id)
				values ('%s', now(), '%s', %d, %f, %f, %d, %f, %f, %d, '%s', '%s', '%s', '%s')";
		foreach ( $items as $i => $v ) {
			$pwbillDetailId = $v["id"];
			$goodsId = $v["goodsId"];
			$goodsCount = $v["goodsCount"];
			$goodsPrice = $v["goodsPrice"];
			$goodsMoney = $goodsCount * $goodsPrice;
			$rejCount = $v["rejCount"];
			$rejPrice = $v["rejPrice"];
			$rejMoney = $v["rejMoney"];
			
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsPrice, 
					$goodsMoney, $rejCount, $rejPrice, $rejMoney, $i, $id, $pwbillDetailId, $dataOrg, 
					$companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$sql = "select sum(rejection_money) as rej_money
				from t_pr_bill_detail
				where prbill_id = '%s' ";
		$data = $db->query($sql, $id);
		$rejMoney = $data[0]["rej_money"];
		if (! $rejMoney) {
			$rejMoney = 0;
		}
		
		$sql = "update t_pr_bill
				set rejection_money = %f,
					bizdt = '%s', biz_user_id = '%s',
					date_created = now(), input_user_id = '%s',
					warehouse_id = '%s', receiving_type = %d
				where id = '%s' ";
		$rc = $db->execute($sql, $rejMoney, $bizDT, $bizUserId, $loginUserId, $warehouseId, 
				$receivingType, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 选择可以退货的采购入库单
	 */
	public function selectPWBillList($params) {
		$db = $this->db;
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$ref = $params["ref"];
		$supplierId = $params["supplierId"];
		$warehouseId = $params["warehouseId"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$result = array();
		
		$sql = "select p.id, p.ref, p.biz_dt, s.name as supplier_name, p.goods_money,
					w.name as warehouse_name, u1.name as biz_user_name, u2.name as input_user_name
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u1, t_user u2
				where (p.supplier_id = s.id)
					and (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id)
					and (p.input_user_id = u2.id)
					and (p.bill_status = 1000)";
		$queryParamas = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParamas = $rs[1];
		}
		
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
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParamas = $rs[1];
		}
		
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

	/**
	 * 根据采购入库单的id查询采购入库单的详细信息
	 */
	public function getPWBillInfoForPRBill($params) {
		$db = $this->db;
		
		// 采购入库单id
		$id = $params["id"];
		
		$result = array();
		
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

	/**
	 * 采购退货出库单列表
	 */
	public function prbillList($params) {
		$db = $this->db;
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$warehouseId = $params["warehouseId"];
		$supplierId = $params["supplierId"];
		$receivingType = $params["receivingType"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$result = array();
		$queryParams = array();
		$sql = "select p.id, p.ref, p.bill_status, w.name as warehouse_name, p.bizdt,
					p.rejection_money, u1.name as biz_user_name, u2.name as input_user_name,
					s.name as supplier_name, p.date_created, p.receiving_type
				from t_pr_bill p, t_warehouse w, t_user u1, t_user u2, t_supplier s
				where (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id)
					and (p.input_user_id = u2.id)
					and (p.supplier_id = s.id) ";
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (p.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (p.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParams[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($receivingType != - 1) {
			$sql .= " and (p.receiving_type = %d) ";
			$queryParams[] = $receivingType;
		}
		
		$sql .= " order by p.bizdt desc, p.ref desc
				limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
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
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["receivingType"] = $v["receiving_type"];
		}
		
		$sql = "select count(*) as cnt
				from t_pr_bill p, t_warehouse w, t_user u1, t_user u2, t_supplier s
				where (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id)
					and (p.input_user_id = u2.id)
					and (p.supplier_id = s.id) ";
		$queryParams = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (p.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (p.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParams[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($receivingType != - 1) {
			$sql .= " and (p.receiving_type = %d) ";
			$queryParams[] = $receivingType;
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 采购退货出库单明细列表
	 */
	public function prBillDetailList($params) {
		$db = $this->db;
		
		// id：采购退货出库单id
		$id = $params["id"];
		
		$sql = "select g.code, g.name, g.spec, u.name as unit_name,
					p.rejection_goods_count as rej_count, p.rejection_goods_price as rej_price,
					p.rejection_money as rej_money
				from t_pr_bill_detail p, t_goods g, t_goods_unit u
				where p.goods_id = g.id and g.unit_id = u.id and p.prbill_id = '%s'
					and p.rejection_goods_count > 0
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

	public function prBillInfo($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$result = array();
		
		if ($id) {
			// 编辑
			$sql = "select p.ref, p.bill_status, p.warehouse_id, w.name as warehouse_name,
						p.biz_user_id, u.name as biz_user_name, pw.ref as pwbill_ref,
						s.name as supplier_name, s.id as supplier_id,
						p.pw_bill_id as pwbill_id, p.bizdt, p.receiving_type
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
			$result["billStatus"] = $data[0]["bill_status"];
			$result["bizUserId"] = $data[0]["biz_user_id"];
			$result["bizUserName"] = $data[0]["biz_user_name"];
			$result["warehouseId"] = $data[0]["warehouse_id"];
			$result["warehouseName"] = $data[0]["warehouse_name"];
			$result["pwbillRef"] = $data[0]["pwbill_ref"];
			$result["supplierId"] = $data[0]["supplier_id"];
			$result["supplierName"] = $data[0]["supplier_name"];
			$result["pwbillId"] = $data[0]["pwbill_id"];
			$result["bizDT"] = $this->toYMD($data[0]["bizdt"]);
			$result["receivingType"] = $data[0]["receiving_type"];
			
			$items = array();
			$sql = "select p.pwbilldetail_id as id, p.goods_id, g.code as goods_code, g.name as goods_name,
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
			$result["bizUserId"] = $params["loginUserId"];
			$result["bizUserName"] = $params["loginUserName"];
		}
		
		return $result;
	}

	/**
	 * 删除采购退货出库单
	 */
	public function deletePRBill(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$bill = $this->getPRBillById($id);
		
		if (! $bill) {
			return $this->bad("要删除的采购退货出库单不存在");
		}
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		if ($billStatus != 0) {
			return $this->bad("采购退货出库单(单号：$ref)已经提交，不能被删除");
		}
		
		$sql = "delete from t_pr_bill_detail where prbill_id = '%s'";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_pr_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}
}
<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 采购订单 DAO
 *
 * @author 李静波
 */
class POBillDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	/**
	 * 生成新的采购订单号
	 */
	private function genNewBillRef($params) {
		$db = $this->db;
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getPOBillRefPre($params);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_po_bill where ref like '%s' order by ref desc limit 1";
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
	 * 获得采购订单主表信息列表
	 */
	public function pobillList($params) {
		$db = $this->db;
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$supplierId = $params["supplierId"];
		$paymentType = $params["paymentType"];
		
		$loginUserId = $params["loginUserId"];
		
		$queryParams = array();
		
		$result = array();
		$sql = "select p.id, p.ref, p.bill_status, p.goods_money, p.tax, p.money_with_tax,
					s.name as supplier_name, p.contact, p.tel, p.fax, p.deal_address,
					p.deal_date, p.payment_type, p.bill_memo, p.date_created,
					o.full_name as org_name, u1.name as biz_user_name, u2.name as input_user_name,
					p.confirm_user_id, p.confirm_date
				from t_po_bill p, t_supplier s, t_org o, t_user u1, t_user u2
				where (p.supplier_id = s.id) and (p.org_id = o.id)
					and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id) ";
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_ORDER, "p", $loginUserId);
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
			$queryParams[] = "%$ref%";
		}
		if ($fromDT) {
			$sql .= " and (p.deal_date >= '%s')";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.deal_date <= '%s')";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s')";
			$queryParams[] = $supplierId;
		}
		if ($paymentType != - 1) {
			$sql .= " and (p.payment_type = %d) ";
			$queryParams[] = $paymentType;
		}
		$sql .= " order by p.deal_date desc, p.ref desc
				  limit %d , %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["billStatus"] = $v["bill_status"];
			$result[$i]["dealDate"] = $this->toYMD($v["deal_date"]);
			$result[$i]["dealAddress"] = $v["deal_address"];
			$result[$i]["supplierName"] = $v["supplier_name"];
			$result[$i]["contact"] = $v["contact"];
			$result[$i]["tel"] = $v["tel"];
			$result[$i]["fax"] = $v["fax"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
			$result[$i]["tax"] = $v["tax"];
			$result[$i]["moneyWithTax"] = $v["money_with_tax"];
			$result[$i]["paymentType"] = $v["payment_type"];
			$result[$i]["billMemo"] = $v["bill_memo"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["orgName"] = $v["org_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["dateCreated"] = $v["date_created"];
			
			$confirmUserId = $v["confirm_user_id"];
			if ($confirmUserId) {
				$sql = "select name from t_user where id = '%s' ";
				$d = $db->query($sql, $confirmUserId);
				if ($d) {
					$result[$i]["confirmUserName"] = $d[0]["name"];
					$result[$i]["confirmDate"] = $v["confirm_date"];
				}
			}
		}
		
		$sql = "select count(*) as cnt
				from t_po_bill p, t_supplier s, t_org o, t_user u1, t_user u2
				where (p.supplier_id = s.id) and (p.org_id = o.id)
					and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id)
				";
		$queryParams = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_ORDER, "p", $loginUserId);
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
			$queryParams[] = "%$ref%";
		}
		if ($fromDT) {
			$sql .= " and (p.deal_date >= '%s')";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.deal_date <= '%s')";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s')";
			$queryParams[] = $supplierId;
		}
		if ($paymentType != - 1) {
			$sql .= " and (p.payment_type = %d) ";
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
	 * 采购订单的商品明细
	 */
	public function poBillDetailList($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$sql = "select p.id, g.code, g.name, g.spec, p.goods_count, p.goods_price, p.goods_money,
					p.tax_rate, p.tax, p.money_with_tax, u.name as unit_name
				from t_po_bill_detail p, t_goods g, t_goods_unit u
				where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
				order by p.show_order";
		$result = array();
		$data = $db->query($sql, $id);
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["goodsCount"] = $v["goods_count"];
			$result[$i]["goodsPrice"] = $v["goods_price"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
			$result[$i]["taxRate"] = $v["tax_rate"];
			$result[$i]["tax"] = $v["tax"];
			$result[$i]["moneyWithTax"] = $v["money_with_tax"];
			$result[$i]["unitName"] = $v["unit_name"];
		}
		
		return $result;
	}
}
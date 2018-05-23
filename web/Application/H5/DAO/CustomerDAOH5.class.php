<?php

namespace H5\DAO;

use Home\DAO\CustomerDAO;
use Home\Common\FIdConst;
use Home\DAO\DataOrgDAO;
use Home\DAO\WarehouseDAO;

/**
 * 用户 DAO - H5
 *
 * @author 李静波
 */
class CustomerDAOH5 extends CustomerDAO {

	public function queryCustomerCategoryH5($params) {
		$db = $this->db;
		
		$query = $params["query"];
		
		$queryParams = [];
		if ($query == "*" || $query == "?") {
			// 所有分类
			$sql = "select id, name
					from t_customer_category
					order by code";
		} else {
			$sql = "select id, name
				from t_customer_category
				where code like '%s' or name like '%s'
				order by code ";
			$queryParams[] = "%$query%";
			$queryParams[] = "%$query%";
		}
		
		$data = $db->query($sql, $queryParams);
		
		$result = [];
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"name" => $v["name"]
			];
		}
		
		return $result;
	}

	public function customerListForH5($params) {
		$db = $this->db;
		
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$code = $params["code"];
		$name = $params["name"];
		$address = $params["address"];
		$contact = $params["contact"];
		$mobile = $params["mobile"];
		$tel = $params["tel"];
		$qq = $params["qq"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$sql = "select id, category_id, code, name, address, contact01, qq01, tel01, mobile01,
				 	contact02, qq02, tel02, mobile02, init_receivables, init_receivables_dt,
					address_receipt, bank_name, bank_account, tax_number, fax, note, data_org,
					sales_warehouse_id
				 from t_customer where (1 = 1) ";
		$queryParam = [];
		if ($categoryId) {
			$sql .= " and (category_id = '%s')";
			$queryParam[] = $categoryId;
		}
		
		if ($code) {
			$sql .= " and (code like '%s' ) ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (name like '%s' or py like '%s' ) ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($address) {
			$sql .= " and (address like '%s' or address_receipt like '%s') ";
			$queryParam[] = "%$address%";
			$queryParam[] = "%{$address}%";
		}
		if ($contact) {
			$sql .= " and (contact01 like '%s' or contact02 like '%s' ) ";
			$queryParam[] = "%{$contact}%";
			$queryParam[] = "%{$contact}%";
		}
		if ($mobile) {
			$sql .= " and (mobile01 like '%s' or mobile02 like '%s' ) ";
			$queryParam[] = "%{$mobile}%";
			$queryParam[] = "%{$mobile}";
		}
		if ($tel) {
			$sql .= " and (tel01 like '%s' or tel02 like '%s' ) ";
			$queryParam[] = "%{$tel}%";
			$queryParam[] = "%{$tel}";
		}
		if ($qq) {
			$sql .= " and (qq01 like '%s' or qq02 like '%s' ) ";
			$queryParam[] = "%{$qq}%";
			$queryParam[] = "%{$qq}";
		}
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "t_customer", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by code limit %d, %d";
		$queryParam[] = $start;
		$queryParam[] = $limit;
		$result = [];
		$data = $db->query($sql, $queryParam);
		$warehouseDAO = new WarehouseDAO($db);
		foreach ( $data as $v ) {
			$initDT = $v["init_receivables_dt"] ? $this->toYMD($v["init_receivables_dt"]) : null;
			
			$warehouseId = $v["sales_warehouse_id"];
			$warehouseName = "";
			if ($warehouseId) {
				$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
				$warehouseName = $warehouse["name"];
			}
			
			$result[] = [
					"id" => $v["id"],
					"categoryId" => $v["category_id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"address" => $v["address"],
					"addressReceipt" => $v["address_receipt"],
					"contact01" => $v["contact01"],
					"qq01" => $v["qq01"],
					"tel01" => $v["tel01"],
					"mobile01" => $v["mobile01"],
					"contact02" => $v["contact02"],
					"qq02" => $v["qq02"],
					"tel02" => $v["tel02"],
					"mobile02" => $v["mobile02"],
					"initReceivables" => $v["init_receivables"],
					"initReceivablesDT" => $initDT,
					"bankName" => $v["bank_name"],
					"bankAccount" => $v["bank_account"],
					"tax" => $v["tax_number"],
					"fax" => $v["fax"],
					"note" => $v["note"],
					"dataOrg" => $v["data_org"],
					"warehouseName" => $warehouseName
			];
		}
		
		$sql = "select count(*) as cnt from t_customer where (1  = 1) ";
		$queryParam = [];
		if ($categoryId) {
			$sql .= " and (category_id = '%s')";
			$queryParam[] = $categoryId;
		}
		if ($code) {
			$sql .= " and (code like '%s' ) ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (name like '%s' or py like '%s' ) ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($address) {
			$sql .= " and (address like '%s' or address_receipt like '%s') ";
			$queryParam[] = "%$address%";
			$queryParam[] = "%$address%";
		}
		if ($contact) {
			$sql .= " and (contact01 like '%s' or contact02 like '%s' ) ";
			$queryParam[] = "%{$contact}%";
			$queryParam[] = "%{$contact}%";
		}
		if ($mobile) {
			$sql .= " and (mobile01 like '%s' or mobile02 like '%s' ) ";
			$queryParam[] = "%{$mobile}%";
			$queryParam[] = "%{$mobile}";
		}
		if ($tel) {
			$sql .= " and (tel01 like '%s' or tel02 like '%s' ) ";
			$queryParam[] = "%{$tel}%";
			$queryParam[] = "%{$tel}";
		}
		if ($qq) {
			$sql .= " and (qq01 like '%s' or qq02 like '%s' ) ";
			$queryParam[] = "%{$qq}%";
			$queryParam[] = "%{$qq}";
		}
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "t_customer", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$data = $db->query($sql, $queryParam);
		$totalCount = $data[0]["cnt"];
		
		$totalPage = ceil($totalCount / 10);
		
		return [
				"customerList" => $result,
				"totalPage" => $totalPage,
				"currentPage" => $page
		];
	}

	public function customerDetail($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$result = [];
		
		$sql = "select category_id, code, name, contact01, qq01, mobile01, tel01,
					contact02, qq02, mobile02, tel02, address, address_receipt,
					init_receivables, init_receivables_dt,
					bank_name, bank_account, tax_number, fax, note, sales_warehouse_id
				from t_customer
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$result["categoryId"] = $data[0]["category_id"];
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["contact01"] = $data[0]["contact01"];
			$result["qq01"] = $data[0]["qq01"];
			$result["mobile01"] = $data[0]["mobile01"];
			$result["tel01"] = $data[0]["tel01"];
			$result["contact02"] = $data[0]["contact02"];
			$result["qq02"] = $data[0]["qq02"];
			$result["mobile02"] = $data[0]["mobile02"];
			$result["tel02"] = $data[0]["tel02"];
			$result["address"] = $data[0]["address"];
			$result["addressReceipt"] = $data[0]["address_receipt"];
			$result["initReceivables"] = $data[0]["init_receivables"];
			$d = $data[0]["init_receivables_dt"];
			if ($d) {
				$result["initReceivablesDT"] = $this->toYMD($d);
			}
			$result["bankName"] = $data[0]["bank_name"];
			$result["bankAccount"] = $data[0]["bank_account"];
			$result["tax"] = $data[0]["tax_number"];
			$result["fax"] = $data[0]["fax"];
			$result["note"] = $data[0]["note"];
			
			$result["warehouseId"] = null;
			$result["warehouseName"] = null;
			$warehouseId = $data[0]["sales_warehouse_id"];
			if ($warehouseId) {
				$warehouseDAO = new WarehouseDAO($db);
				$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
				if ($warehouse) {
					$result["warehouseId"] = $warehouseId;
					$result["warehouseName"] = $warehouse["name"];
				}
			}
		}
		
		return $result;
	}
}
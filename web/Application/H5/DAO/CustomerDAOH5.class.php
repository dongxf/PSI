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
		if ($query == "*" || $query == "?" || $query = "？" || $query = "＊") {
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
		
		$sql = "select c.id, c.code, c.name, c.address, c.contact01, c.contact02,
					c.address_receipt, g.name as category_name, g.ps_id
				 from t_customer c, t_customer_category g where (c.category_id = g.id) ";
		$queryParam = [];
		if ($categoryId) {
			$sql .= " and (c.category_id = '%s')";
			$queryParam[] = $categoryId;
		}
		
		if ($code) {
			$sql .= " and (c.code like '%s' ) ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (c.name like '%s' or c.py like '%s' ) ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($address) {
			$sql .= " and (c.address like '%s' or c.address_receipt like '%s') ";
			$queryParam[] = "%$address%";
			$queryParam[] = "%{$address}%";
		}
		if ($contact) {
			$sql .= " and (c.contact01 like '%s' or c.contact02 like '%s' ) ";
			$queryParam[] = "%{$contact}%";
			$queryParam[] = "%{$contact}%";
		}
		if ($mobile) {
			$sql .= " and (c.mobile01 like '%s' or c.mobile02 like '%s' ) ";
			$queryParam[] = "%{$mobile}%";
			$queryParam[] = "%{$mobile}";
		}
		if ($tel) {
			$sql .= " and (c.tel01 like '%s' or c.tel02 like '%s' ) ";
			$queryParam[] = "%{$tel}%";
			$queryParam[] = "%{$tel}";
		}
		if ($qq) {
			$sql .= " and (c.qq01 like '%s' or c.qq02 like '%s' ) ";
			$queryParam[] = "%{$qq}%";
			$queryParam[] = "%{$qq}";
		}
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "c", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by c.code limit %d, %d";
		$queryParam[] = $start;
		$queryParam[] = $limit;
		$result = [];
		$data = $db->query($sql, $queryParam);
		$warehouseDAO = new WarehouseDAO($db);
		foreach ( $data as $v ) {
			// 价格体系
			$psId = $v["ps_id"];
			$priceSystem = "";
			if ($psId) {
				$sql = "select name from t_price_system
						where id = '%s' ";
				$d = $db->query($sql, $psId);
				if ($d) {
					$priceSystem = $d[0]["name"];
				}
			}
			
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"address" => $v["address"],
					"addressReceipt" => $v["address_receipt"],
					"contact01" => $v["contact01"],
					"contact02" => $v["contact02"],
					"categoryName" => $v["category_name"],
					"priceSystem" => $priceSystem
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
		
		$sql = "select c.code, c.name, c.contact01, c.qq01, c.mobile01, c.tel01,
					c.contact02, c.qq02, c.mobile02, c.tel02, c.address, c.address_receipt,
					c.init_receivables, c.init_receivables_dt,
					c.bank_name, c.bank_account, c.tax_number, c.fax, c.note, c.sales_warehouse_id,
					g.name as category_name, g.ps_id
				from t_customer c, t_customer_category g
				where c.id = '%s' and c.category_id = g.id";
		$data = $db->query($sql, $id);
		if ($data) {
			$result["categoryName"] = $data[0]["category_name"];
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
			
			// 销售出库仓库
			$result["warehouseName"] = null;
			$warehouseId = $data[0]["sales_warehouse_id"];
			if ($warehouseId) {
				$warehouseDAO = new WarehouseDAO($db);
				$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
				if ($warehouse) {
					$result["warehouseName"] = $warehouse["name"];
				}
			}
			
			// 价格体系
			$psId = $data[0]["ps_id"];
			$priceSystem = null;
			if ($psId) {
				$sql = "select name from t_price_system where id = '%s' ";
				$data = $db->query($sql, $psId);
				if ($data) {
					$priceSystem = $data[0]["name"];
				}
			}
			$result["priceSystem"] = $priceSystem;
		}
		
		return $result;
	}
}
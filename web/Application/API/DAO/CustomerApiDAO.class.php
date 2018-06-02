<?php

namespace API\DAO;

use Home\DAO\PSIBaseExDAO;
use Home\DAO\DataOrgDAO;
use Home\Common\FIdConst;

/**
 * 客户API DAO
 *
 * @author 李静波
 */
class CustomerApiDAO extends PSIBaseExDAO {

	public function customerList($params) {
		$db = $this->db;
		
		$page = $params["page"];
		if (! $page) {
			$page = 1;
		}
		$limit = $params["limit"];
		if (! $limit) {
			$limit = 10;
		}
		
		$start = ($page - 1) * $limit;
		
		$loginUserId = $params["userId"];
		
		$categoryId = $params["categoryId"];
		$code = $params["code"];
		$name = $params["name"];
		$address = $params["address"];
		$contact = $params["contact"];
		$mobile = $params["mobile"];
		$tel = $params["tel"];
		$qq = $params["qq"];
		
		$result = [];
		$queryParam = [];
		
		$sql = "select c.code, c.name, g.name as category_name
				from t_customer c, t_customer_category g
				where (c.category_id = g.id)";
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "c", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		if ($categoryId != "-1") {
			$sql .= " and (c.category_id = '%s') ";
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
		
		$sql .= "order by g.code, c.code
				limit %d, %d";
		$queryParam[] = $start;
		$queryParam[] = $limit;
		
		$data = $db->query($sql, $queryParam);
		foreach ( $data as $v ) {
			$result[] = [
					"code" => $v["code"],
					"name" => $v["name"],
					"categoryName" => $v["category_name"]
			];
		}
		
		$sql = "select count(*) as cnt
				from t_customer c
				where (1 = 1) ";
		$queryParam = [];
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "c", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		if ($categoryId != "-1") {
			$sql .= " and (c.category_id = '%s') ";
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
		
		$data = $db->query($sql, $queryParam);
		$cnt = $data[0]["cnt"];
		
		$totalPage = ceil($cnt / $limit);
		
		return [
				"dataList" => $result,
				"totalPage" => $totalPage,
				"totalCount" => $cnt
		];
	}

	public function categoryListWithAllCategory($params) {
		$db = $this->db;
		
		$result = [];
		
		$result[] = [
				"id" => "-1",
				"name" => "[所有分类]"
		];
		
		$ds = new DataOrgDAO($db);
		$queryParam = [];
		$sql = "select c.id, c.code, c.name
				from t_customer_category c ";
		$rs = $ds->buildSQL(FIdConst::CUSTOMER_CATEGORY, "c", $loginUserId);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by c.code";
		
		$data = $db->query($sql, $queryParam);
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"]
			];
		}
		
		return $result;
	}

	public function categoryList($params) {
		$db = $this->db;
		
		$result = [];
		
		$ds = new DataOrgDAO($db);
		$queryParam = [];
		$sql = "select c.id, c.code, c.name
				from t_customer_category c ";
		$rs = $ds->buildSQL(FIdConst::CUSTOMER_CATEGORY, "c", $loginUserId);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by c.code";
		
		$data = $db->query($sql, $queryParam);
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"]
			];
		}
		
		return $result;
	}
}
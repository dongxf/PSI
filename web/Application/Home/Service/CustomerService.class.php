<?php

namespace Home\Service;

use Home\Common\FIdConst;
use Home\DAO\CustomerDAO;
use Home\DAO\IdGenDAO;

/**
 * 客户Service
 *
 * @author 李静波
 */
class CustomerService extends PSIBaseService {
	private $LOG_CATEGORY = "客户关系-客户资料";

	/**
	 * 客户分类列表
	 */
	public function categoryList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new CustomerDAO();
		return $dao->categoryList($params);
	}

	/**
	 * 新建或编辑客户分类
	 */
	public function editCategory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new CustomerDAO($db);
		
		$log = null;
		
		if ($id) {
			// 编辑
			$rc = $dao->updateCustomerCategory($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑客户分类: 编码 = {$code}, 分类名 = {$name}";
		} else {
			// 新增
			$idGen = new IdGenDAO($db);
			$id = $idGen->newId();
			$params["id"] = $id;
			
			$us = new UserService();
			$params["dataOrg"] = $us->getLoginUserDataOrg();
			$params["companyId"] = $us->getCompanyId();
			
			$rc = $dao->addCustomerCategory($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增客户分类：编码 = {$code}, 分类名 = {$name}";
		}
		
		if ($log) {
			$bs = new BizlogService($db);
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除客户分类
	 */
	public function deleteCategory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new CustomerDAO($db);
		
		$category = $dao->getCustomerCategoryById($id);
		if (! $category) {
			$db->rollback();
			return $this->bad("要删除的分类不存在");
		}
		
		$params["name"] = $category["name"];
		
		$rc = $dao->deleteCustomerCategory($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除客户分类： 编码 = {$category['code']}, 分类名称 = {$category['name']}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 新建或编辑客户资料
	 */
	public function editCustomer($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		$ps = new PinyinService();
		$params["py"] = $ps->toPY($name);
		
		$db = M();
		$db->startTrans();
		
		$dao = new CustomerDAO($db);
		
		$us = new UserService();
		$params["dataOrg"] = $us->getLoginUserDataOrg();
		$params["companyId"] = $us->getCompanyId();
		
		$category = $dao->getCustomerCategoryById($params["categoryId"]);
		if (! $category) {
			$db->rollback();
			return $this->bad("客户分类不存在");
		}
		
		$log = null;
		
		if ($id) {
			// 编辑
			$rc = $dao->updateCustomer($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑客户：编码 = {$code}, 名称 = {$name}";
		} else {
			// 新增
			$idGen = new IdGenDAO($db);
			$id = $idGen->newId();
			
			$params["id"] = $id;
			
			$rc = $dao->addCustomer($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增客户：编码 = {$code}, 名称 = {$name}";
		}
		
		// 处理应收账款
		$rc = $dao->initReceivables($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService($db);
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得某个分类的客户列表
	 */
	public function customerList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
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
		
		$sql = "select id, category_id, code, name, address, contact01, qq01, tel01, mobile01, 
				 contact02, qq02, tel02, mobile02, init_receivables, init_receivables_dt,
					address_receipt, bank_name, bank_account, tax_number, fax, note, data_org
				 from t_customer where (category_id = '%s') ";
		$queryParam = array();
		$queryParam[] = $categoryId;
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
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "t_customer");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by code limit %d, %d";
		$queryParam[] = $start;
		$queryParam[] = $limit;
		$result = array();
		$db = M();
		$data = $db->query($sql, $queryParam);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["categoryId"] = $v["category_id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["address"] = $v["address"];
			$result[$i]["addressReceipt"] = $v["address_receipt"];
			$result[$i]["contact01"] = $v["contact01"];
			$result[$i]["qq01"] = $v["qq01"];
			$result[$i]["tel01"] = $v["tel01"];
			$result[$i]["mobile01"] = $v["mobile01"];
			$result[$i]["contact02"] = $v["contact02"];
			$result[$i]["qq02"] = $v["qq02"];
			$result[$i]["tel02"] = $v["tel02"];
			$result[$i]["mobile02"] = $v["mobile02"];
			$result[$i]["initReceivables"] = $v["init_receivables"];
			if ($v["init_receivables_dt"]) {
				$result[$i]["initReceivablesDT"] = date("Y-m-d", 
						strtotime($v["init_receivables_dt"]));
			}
			$result[$i]["bankName"] = $v["bank_name"];
			$result[$i]["bankAccount"] = $v["bank_account"];
			$result[$i]["tax"] = $v["tax_number"];
			$result[$i]["fax"] = $v["fax"];
			$result[$i]["note"] = $v["note"];
			$result[$i]["dataOrg"] = $v["data_org"];
		}
		
		$sql = "select count(*) as cnt from t_customer where (category_id  = '%s') ";
		$queryParam = array();
		$queryParam[] = $categoryId;
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
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "t_customer");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$data = $db->query($sql, $queryParam);
		
		return array(
				"customerList" => $result,
				"totalCount" => $data[0]["cnt"]
		);
	}

	/**
	 * 删除客户资料
	 */
	public function deleteCustomer($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = M();
		$db->startTrans();
		
		$dao = new CustomerDAO($db);
		
		$customer = $dao->getCustomerById($id);
		
		if (! $customer) {
			$db->rollback();
			return $this->bad("要删除的客户资料不存在");
		}
		$code = $customer["code"];
		$name = $customer["name"];
		$params["code"] = $code;
		$params["name"] = $name;
		
		$rc = $dao->deleteCustomer($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除客户资料：编码 = {$code},  名称 = {$name}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 客户字段，查询数据
	 */
	public function queryData($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$queryKey = $params["queryKey"];
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select id, code, name, mobile01, tel01, fax, address_receipt, contact01
				from t_customer 
				where (code like '%s' or name like '%s' or py like '%s' 
					or mobile01 like '%s' or mobile02 like '%s' ) ";
		$queryParams = array();
		$key = "%{$queryKey}%";
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL("1007-01", "t_customer");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by code limit 20";
		
		return M()->query($sql, $queryParams);
	}

	/**
	 * 获得某个客户的详情
	 */
	public function customerInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = array();
		
		$db = M();
		$sql = "select category_id, code, name, contact01, qq01, mobile01, tel01,
					contact02, qq02, mobile02, tel02, address, address_receipt,
					init_receivables, init_receivables_dt,
					bank_name, bank_account, tax_number, fax, note
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
		}
		
		return $result;
	}

	/**
	 * 判断给定id的客户是否存在
	 *
	 * @param string $customerId        	
	 *
	 * @return true: 存在
	 */
	public function customerExists($customerId, $db) {
		if (! $db) {
			$db = M();
		}
		
		if (! $customerId) {
			return false;
		}
		
		$sql = "select count(*) as cnt from t_customer where id = '%s' ";
		$data = $db->query($sql, $customerId);
		return $data[0]["cnt"] == 1;
	}

	/**
	 * 根据客户Id查询客户名称
	 */
	public function getCustomerNameById($customerId, $db) {
		if (! $db) {
			$db = M();
		}
		
		$sql = "select name from t_customer where id = '%s' ";
		$data = $db->query($sql, $customerId);
		if ($data) {
			return $data[0]["name"];
		} else {
			return "";
		}
	}
}
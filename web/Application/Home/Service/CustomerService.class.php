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
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new CustomerDAO();
		return $dao->customerList($params);
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
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new CustomerDAO();
		return $dao->queryData($params);
	}

	/**
	 * 获得某个客户的详情
	 */
	public function customerInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new CustomerDAO();
		return $dao->customerInfo($params);
	}

	/**
	 * 判断给定id的客户是否存在
	 *
	 * @param string $customerId        	
	 *
	 * @return true: 存在
	 */
	public function customerExists($customerId, $db) {
		$dao = new CustomerDAO($db);
		
		$customer = $dao->getCustomerById($customerId);
		
		return $customer != null;
	}

	/**
	 * 根据客户Id查询客户名称
	 */
	public function getCustomerNameById($customerId, $db) {
		$dao = new CustomerDAO($db);
		
		$customer = $dao->getCustomerById($customerId);
		if ($customer) {
			return $customer["name"];
		} else {
			return "";
		}
	}
}
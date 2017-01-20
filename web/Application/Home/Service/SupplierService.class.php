<?php

namespace Home\Service;

use Home\DAO\SupplierDAO;
use Home\Service\BizlogService;
use Home\Service\IdGenService;

/**
 * 供应商档案Service
 *
 * @author 李静波
 */
class SupplierService extends PSIBaseService {
	private $LOG_CATEGORY = "基础数据-供应商档案";

	/**
	 * 供应商分类列表
	 */
	public function categoryList($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new SupplierDAO();
		return $dao->categoryList($params);
	}

	/**
	 * 某个分类下的供应商档案列表
	 */
	public function supplierList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new SupplierDAO();
		return $dao->supplierList($params);
	}

	/**
	 * 新建或编辑供应商分类
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
		
		$dao = new SupplierDAO($db);
		
		$log = null;
		
		if ($id) {
			// 编辑
			$rc = $dao->updateSupplierCategory($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑供应商分类: 编码 = $code, 分类名 = $name";
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$params["id"] = $id;
			
			$us = new UserService();
			$params["dataOrg"] = $us->getLoginUserDataOrg();
			$params["companyId"] = $us->getCompanyId();
			
			$rc = $dao->addSupplierCategory($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增供应商分类：编码 = $code, 分类名 = $name";
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
	 * 删除供应商分类
	 */
	public function deleteCategory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		$dao = new SupplierDAO($db);
		
		$category = $dao->getSupplierCategoryById($id);
		if (! $category) {
			$db->rollback();
			return $this->bad("要删除的分类不存在");
		}
		
		$params["name"] = $category["name"];
		
		$rc = $dao->deleteSupplierCategory($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除供应商分类： 编码 = {$category['code']}, 分类名称 = {$category['name']}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 新建或编辑供应商档案
	 */
	public function editSupplier($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$address = $params["address"];
		$addressShipping = $params["addressShipping"];
		$contact01 = $params["contact01"];
		$mobile01 = $params["mobile01"];
		$tel01 = $params["tel01"];
		$qq01 = $params["qq01"];
		$contact02 = $params["contact02"];
		$mobile02 = $params["mobile02"];
		$tel02 = $params["tel02"];
		$qq02 = $params["qq02"];
		$initPayables = $params["initPayables"];
		$initPayablesDT = $params["initPayablesDT"];
		$bankName = $params["bankName"];
		$bankAccount = $params["bankAccount"];
		$tax = $params["tax"];
		$fax = $params["fax"];
		$note = $params["note"];
		
		$ps = new PinyinService();
		$py = $ps->toPY($name);
		
		$categoryId = $params["categoryId"];
		
		$db = M();
		$db->startTrans();
		
		$us = new UserService();
		$dataOrg = $us->getLoginUserDataOrg();
		$companyId = $us->getCompanyId();
		
		$sql = "select count(*) as cnt from t_supplier_category where id = '%s' ";
		$data = $db->query($sql, $categoryId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$db->rollback();
			return $this->bad("供应商分类不存在");
		}
		
		$log = null;
		
		if ($id) {
			// 编辑
			// 检查编码是否已经存在
			$sql = "select count(*) as cnt from t_supplier where code = '%s'  and id <> '%s' ";
			$data = $db->query($sql, $code, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("编码为 [$code] 的供应商已经存在");
			}
			
			$sql = "update t_supplier 
					set code = '%s', name = '%s', category_id = '%s', py = '%s', 
					contact01 = '%s', qq01 = '%s', tel01 = '%s', mobile01 = '%s', 
					contact02 = '%s', qq02 = '%s', tel02 = '%s', mobile02 = '%s',
					address = '%s', address_shipping = '%s',
					bank_name = '%s', bank_account = '%s', tax_number = '%s',
					fax = '%s', note = '%s'
					where id = '%s'  ";
			
			$rc = $db->execute($sql, $code, $name, $categoryId, $py, $contact01, $qq01, $tel01, 
					$mobile01, $contact02, $qq02, $tel02, $mobile02, $address, $addressShipping, 
					$bankName, $bankAccount, $tax, $fax, $note, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "编辑供应商：编码 = $code, 名称 = $name";
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId();
			
			// 检查编码是否已经存在
			$sql = "select count(*) as cnt from t_supplier where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("编码为 [$code] 的供应商已经存在");
			}
			
			$sql = "insert into t_supplier (id, category_id, code, name, py, contact01, 
					qq01, tel01, mobile01, contact02, qq02,
					tel02, mobile02, address, address_shipping,
					bank_name, bank_account, tax_number, fax, note, data_org, company_id) 
					values ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
							'%s', '%s', '%s', '%s',
							'%s', '%s', '%s', '%s', '%s', '%s', '%s')  ";
			$rc = $db->execute($sql, $id, $categoryId, $code, $name, $py, $contact01, $qq01, $tel01, 
					$mobile01, $contact02, $qq02, $tel02, $mobile02, $address, $addressShipping, 
					$bankName, $bankAccount, $tax, $fax, $note, $dataOrg, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "新增供应商：编码 = {$code}, 名称 = {$name}";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService();
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		// 处理应付期初余额
		
		$initPayables = floatval($initPayables);
		if ($initPayables && $initPayablesDT) {
			$sql = "select count(*) as cnt 
					from t_payables_detail 
					where ca_id = '%s' and ca_type = 'supplier' and ref_type <> '应付账款期初建账' 
						and company_id = '%s' ";
			$data = $db->query($sql, $id, $companyId);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				// 已经有往来业务发生，就不能修改应付账了
				$db->commit();
				return $this->ok($id);
			}
			
			$sql = "update t_supplier 
					set init_payables = %f, init_payables_dt = '%s' 
					where id = '%s' ";
			$rc = $db->execute($sql, $initPayables, $initPayablesDT, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 应付明细账
			$sql = "select id from t_payables_detail 
					where ca_id = '%s' and ca_type = 'supplier' and ref_type = '应付账款期初建账' 
						and company_id = '%s' ";
			$data = $db->query($sql, $id, $companyId);
			if ($data) {
				$payId = $data[0]["id"];
				$sql = "update t_payables_detail 
						set pay_money = %f ,  balance_money = %f , biz_date = '%s', date_created = now(), act_money = 0 
						where id = '%s' ";
				$rc = $db->execute($sql, $initPayables, $initPayables, $initPayablesDT, $payId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$idGen = new IdGenService();
				$payId = $idGen->newId();
				$sql = "insert into t_payables_detail (id, pay_money, act_money, balance_money, ca_id,
						ca_type, ref_type, ref_number, biz_date, date_created, data_org, company_id) 
						values ('%s', %f, 0, %f, '%s', 'supplier', '应付账款期初建账', '%s', '%s', now(), '%s', '%s') ";
				$rc = $db->execute($sql, $payId, $initPayables, $initPayables, $id, $id, 
						$initPayablesDT, $dataOrg, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 应付总账
			$sql = "select id from t_payables 
					where ca_id = '%s' and ca_type = 'supplier' 
						and company_id = '%s' ";
			$data = $db->query($sql, $id, $companyId);
			if ($data) {
				$pId = $data[0]["id"];
				$sql = "update t_payables 
						set pay_money = %f ,  balance_money = %f , act_money = 0 
						where id = '%s' ";
				$rc = $db->execute($sql, $initPayables, $initPayables, $pId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$idGen = new IdGenService();
				$pId = $idGen->newId();
				$sql = "insert into t_payables (id, pay_money, act_money, balance_money, ca_id, 
							ca_type, data_org, company_id)
						values ('%s', %f, 0, %f, '%s', 'supplier', '%s', '%s') ";
				$rc = $db->execute($sql, $pId, $initPayables, $initPayables, $id, $dataOrg, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除供应商
	 */
	public function deleteSupplier($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$sql = "select code, name from t_supplier where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的供应商档案不存在");
		}
		$code = $data[0]["code"];
		$name = $data[0]["name"];
		
		// 判断是否能删除供应商
		$sql = "select count(*) as cnt from t_pw_bill where supplier_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("供应商档案 [{$code} {$name}] 在采购入库单中已经被使用，不能删除");
		}
		$sql = "select count(*) as cnt 
				from t_payables_detail p, t_payment m 
				where p.ref_type = m.ref_type and p.ref_number = m.ref_number 
				and p.ca_id = '%s' and p.ca_type = 'supplier' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("供应商档案 [{$code} {$name}] 已经产生付款记录，不能删除");
		}
		
		// 判断采购退货出库单中是否使用该供应商
		$sql = "select count(*) as cnt from t_pr_bill where supplier_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("供应商档案 [{$code} {$name}] 在采购退货出库单中已经被使用，不能删除");
		}
		
		// 判断在采购订单中是否已经使用该供应商
		$sql = "select count(*) as cnt from t_po_bill where supplier_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("供应商档案 [{$code} {$name}] 在采购订单中已经被使用，不能删除");
		}
		
		$sql = "delete from t_supplier where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 删除应付总账、明细账
		$sql = "delete from t_payables where ca_id = '%s' and ca_type = 'supplier' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_payables_detail where ca_id = '%s' and ca_type = 'supplier' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "删除供应商档案：编码 = {$code},  名称 = {$name}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 供应商字段， 查询数据
	 */
	public function queryData($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select id, code, name, tel01, fax, address_shipping, contact01 
				from t_supplier
				where (code like '%s' or name like '%s' or py like '%s') ";
		$queryParams = array();
		$key = "%{$queryKey}%";
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL("1004-01", "t_supplier");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by code 
				limit 20";
		return M()->query($sql, $queryParams);
	}

	/**
	 * 获得某个供应商档案的详情
	 */
	public function supplierInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = array();
		
		$db = M();
		$sql = "select category_id, code, name, contact01, qq01, mobile01, tel01,
					contact02, qq02, mobile02, tel02, address, address_shipping,
					init_payables, init_payables_dt,
					bank_name, bank_account, tax_number, fax, note
				from t_supplier
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
			$result["addressShipping"] = $data[0]["address_shipping"];
			$result["initPayables"] = $data[0]["init_payables"];
			$d = $data[0]["init_payables_dt"];
			if ($d) {
				$result["initPayablesDT"] = $this->toYMD($d);
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
	 * 判断供应商是否存在
	 */
	public function supplierExists($supplierId, $db) {
		if (! $db) {
			$db = M();
		}
		
		$sql = "select count(*) as cnt from t_supplier where id = '%s' ";
		$data = $db->query($sql, $supplierId);
		return $data[0]["cnt"] == 1;
	}

	/**
	 * 根据供应商Id查询供应商名称
	 */
	public function getSupplierNameById($supplierId, $db) {
		if (! $db) {
			$db = M();
		}
		
		$sql = "select name from t_supplier where id = '%s' ";
		$data = $db->query($sql, $supplierId);
		if ($data) {
			return $data[0]["name"];
		} else {
			return "";
		}
	}
}
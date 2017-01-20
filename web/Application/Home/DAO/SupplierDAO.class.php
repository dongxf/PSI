<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 供应商 DAO
 *
 * @author 李静波
 */
class SupplierDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	/**
	 * 供应商分类列表
	 */
	public function categoryList($params) {
		$db = $this->db;
		
		$code = $params["code"];
		$name = $params["name"];
		$address = $params["address"];
		$contact = $params["contact"];
		$mobile = $params["mobile"];
		$tel = $params["tel"];
		$qq = $params["qq"];
		
		$loginUserId = $params["loginUserId"];
		
		$sql = "select c.id, c.code, c.name, count(s.id) as cnt
				from t_supplier_category c
				left join t_supplier s
				on (c.id = s.category_id)";
		$queryParam = array();
		if ($code) {
			$sql .= " and (s.code like '%s') ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (s.name like '%s' or s.py like '%s' ) ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($address) {
			$sql .= " and (s.address like '%s' or s.address_shipping like '%s') ";
			$queryParam[] = "%{$address}%";
			$queryParam[] = "%{$address}%";
		}
		if ($contact) {
			$sql .= " and (s.contact01 like '%s' or s.contact02 like '%s' ) ";
			$queryParam[] = "%{$contact}%";
			$queryParam[] = "%{$contact}%";
		}
		if ($mobile) {
			$sql .= " and (s.mobile01 like '%s' or s.mobile02 like '%s' ) ";
			$queryParam[] = "%{$mobile}%";
			$queryParam[] = "%{$mobile}";
		}
		if ($tel) {
			$sql .= " and (s.tel01 like '%s' or s.tel02 like '%s' ) ";
			$queryParam[] = "%{$tel}%";
			$queryParam[] = "%{$tel}";
		}
		if ($qq) {
			$sql .= " and (s.qq01 like '%s' or s.qq02 like '%s' ) ";
			$queryParam[] = "%{$qq}%";
			$queryParam[] = "%{$qq}";
		}
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::SUPPLIER_CATEGORY, "c", $loginUserId);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " group by c.id
				order by c.code";
		
		return $db->query($sql, $queryParam);
	}

	/**
	 * 某个分类下的供应商档案列表
	 */
	public function supplierList($params) {
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
		
		$sql = "select id, category_id, code, name, contact01, qq01, tel01, mobile01,
				contact02, qq02, tel02, mobile02, init_payables, init_payables_dt,
				address, address_shipping,
				bank_name, bank_account, tax_number, fax, note, data_org
				from t_supplier
				where (category_id = '%s')";
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
			$sql .= " and (address like '%s' or address_shipping like '%s') ";
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
		$rs = $ds->buildSQL(FIdConst::SUPPLIER, "t_supplier", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$queryParam[] = $start;
		$queryParam[] = $limit;
		$sql .= " order by code
				limit %d, %d";
		$result = array();
		$data = $db->query($sql, $queryParam);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["categoryId"] = $v["category_id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["address"] = $v["address"];
			$result[$i]["addressShipping"] = $v["address_shipping"];
			$result[$i]["contact01"] = $v["contact01"];
			$result[$i]["qq01"] = $v["qq01"];
			$result[$i]["tel01"] = $v["tel01"];
			$result[$i]["mobile01"] = $v["mobile01"];
			$result[$i]["contact02"] = $v["contact02"];
			$result[$i]["qq02"] = $v["qq02"];
			$result[$i]["tel02"] = $v["tel02"];
			$result[$i]["mobile02"] = $v["mobile02"];
			$result[$i]["initPayables"] = $v["init_payables"];
			if ($v["init_payables_dt"]) {
				$result[$i]["initPayablesDT"] = date("Y-m-d", strtotime($v["init_payables_dt"]));
			}
			$result[$i]["bankName"] = $v["bank_name"];
			$result[$i]["bankAccount"] = $v["bank_account"];
			$result[$i]["tax"] = $v["tax_number"];
			$result[$i]["fax"] = $v["fax"];
			$result[$i]["note"] = $v["note"];
			$result[$i]["dataOrg"] = $v["data_org"];
		}
		
		$sql = "select count(*) as cnt from t_supplier where (category_id  = '%s') ";
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
			$sql .= " and (address like '%s') ";
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
		$rs = $ds->buildSQL(FIdConst::SUPPLIER, "t_supplier", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		$data = $db->query($sql, $queryParam);
		
		return array(
				"supplierList" => $result,
				"totalCount" => $data[0]["cnt"]
		);
	}

	/**
	 * 新增供应商分类
	 */
	public function addSupplierCategory($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		// 检查分类编码是否已经存在
		$sql = "select count(*) as cnt from t_supplier_category where code = '%s' ";
		$data = $db->query($sql, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [$code] 的分类已经存在");
		}
		
		$sql = "insert into t_supplier_category (id, code, name, data_org, company_id)
					values ('%s', '%s', '%s', '%s', '%s') ";
		$rc = $db->execute($sql, $id, $code, $name, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑供应商分类
	 */
	public function updateSupplierCategory($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		// 检查分类编码是否已经存在
		$sql = "select count(*) as cnt from t_supplier_category where code = '%s' and id <> '%s' ";
		$data = $db->query($sql, $code, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [$code] 的分类已经存在");
		}
		
		$sql = "update t_supplier_category
				set code = '%s', name = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $code, $name, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	public function getSupplierCategoryById($id) {
		$db = $this->db;
		
		$sql = "select code, name from t_supplier_category where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			return array(
					"code" => $data[0]["code"],
					"name" => $data[0]["name"]
			);
		} else {
			return null;
		}
	}

	/**
	 * 删除供应商分类
	 */
	public function deleteSupplierCategory($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$name = $params["name"];
		
		$sql = "select count(*) as cnt 
				from t_supplier 
				where category_id = '%s' ";
		$query = $db->query($sql, $id);
		$cnt = $query[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("当前分类 [{$name}] 下还有供应商档案，不能删除");
		}
		
		$sql = "delete from t_supplier_category where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 新建供应商档案
	 */
	public function addSupplier($params) {
		$db = $this->db;
		
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
		$bankName = $params["bankName"];
		$bankAccount = $params["bankAccount"];
		$tax = $params["tax"];
		$fax = $params["fax"];
		$note = $params["note"];
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		$categoryId = $params["categoryId"];
		$py = $params["py"];
		
		// 检查编码是否已经存在
		$sql = "select count(*) as cnt from t_supplier where code = '%s' ";
		$data = $db->query($sql, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
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
			return $this->sqlError(__METHOD__, __LINE__);
		}
		// 操作成功
		return null;
	}

	/**
	 * 初始化应付账款
	 */
	public function initPayables($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$initPayables = $params["initPayables"];
		$initPayablesDT = $params["initPayablesDT"];
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
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
				return null;
			}
			
			$sql = "update t_supplier
					set init_payables = %f, init_payables_dt = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $initPayables, $initPayablesDT, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
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
					return $this->sqlError(__METHOD__, __LINE__);
				}
			} else {
				$idGen = new IdGenDAO($db);
				$payId = $idGen->newId();
				$sql = "insert into t_payables_detail (id, pay_money, act_money, balance_money, ca_id,
						ca_type, ref_type, ref_number, biz_date, date_created, data_org, company_id)
						values ('%s', %f, 0, %f, '%s', 'supplier', '应付账款期初建账', '%s', '%s', now(), '%s', '%s') ";
				$rc = $db->execute($sql, $payId, $initPayables, $initPayables, $id, $id, 
						$initPayablesDT, $dataOrg, $companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
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
					return $this->sqlError(__METHOD__, __LINE__);
				}
			} else {
				$idGen = new IdGenDAO($db);
				$pId = $idGen->newId();
				$sql = "insert into t_payables (id, pay_money, act_money, balance_money, ca_id,
							ca_type, data_org, company_id)
						values ('%s', %f, 0, %f, '%s', 'supplier', '%s', '%s') ";
				$rc = $db->execute($sql, $pId, $initPayables, $initPayables, $id, $dataOrg, 
						$companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑供应商档案
	 */
	public function updateSupplier($params) {
		$db = $this->db;
		
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
		$bankName = $params["bankName"];
		$bankAccount = $params["bankAccount"];
		$tax = $params["tax"];
		$fax = $params["fax"];
		$note = $params["note"];
		
		$categoryId = $params["categoryId"];
		
		// 检查编码是否已经存在
		$sql = "select count(*) as cnt from t_supplier where code = '%s'  and id <> '%s' ";
		$data = $db->query($sql, $code, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
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
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}
}
<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 客户资料 DAO
 *
 * @author 李静波
 */
class CustomerDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	/**
	 * 客户分类列表
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
		
		$sql = "select c.id, c.code, c.name, count(u.id) as cnt
				 from t_customer_category c
				 left join t_customer u
				 on (c.id = u.category_id) ";
		$queryParam = array();
		if ($code) {
			$sql .= " and (u.code like '%s') ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (u.name like '%s' or u.py like '%s' ) ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($address) {
			$sql .= " and (u.address like '%s' or u.address_receipt like '%s') ";
			$queryParam[] = "%{$address}%";
			$queryParam[] = "%{$address}%";
		}
		if ($contact) {
			$sql .= " and (u.contact01 like '%s' or u.contact02 like '%s' ) ";
			$queryParam[] = "%{$contact}%";
			$queryParam[] = "%{$contact}%";
		}
		if ($mobile) {
			$sql .= " and (u.mobile01 like '%s' or u.mobile02 like '%s' ) ";
			$queryParam[] = "%{$mobile}%";
			$queryParam[] = "%{$mobile}";
		}
		if ($tel) {
			$sql .= " and (u.tel01 like '%s' or u.tel02 like '%s' ) ";
			$queryParam[] = "%{$tel}%";
			$queryParam[] = "%{$tel}";
		}
		if ($qq) {
			$sql .= " and (u.qq01 like '%s' or u.qq02 like '%s' ) ";
			$queryParam[] = "%{$qq}%";
			$queryParam[] = "%{$qq}";
		}
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER_CATEGORY, "c", $loginUserId);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " group by c.id
				 order by c.code";
		return $db->query($sql, $queryParam);
	}

	/**
	 * 新增客户分类
	 */
	public function addCustomerCategory($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		// 检查分类编码是否已经存在
		$sql = "select count(*) as cnt from t_customer_category where code = '%s' ";
		$data = $db->query($sql, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}] 的客户分类已经存在");
		}
		
		$sql = "insert into t_customer_category (id, code, name, data_org, company_id)
				values ('%s', '%s', '%s', '%s', '%s') ";
		$rc = $db->execute($sql, $id, $code, $name, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑客户分类
	 */
	public function updateCustomerCategory($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		// 检查分类编码是否已经存在
		$sql = "select count(*) as cnt from t_customer_category where code = '%s' and id <> '%s' ";
		$data = $db->query($sql, $code, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}] 的分类已经存在");
		}
		
		$sql = "update t_customer_category
				set code = '%s', name = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $code, $name, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	public function getCustomerCategoryById($id) {
		$db = $this->db;
		
		$sql = "select code, name from t_customer_category where id = '%s' ";
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
	 * 删除客户分类
	 */
	public function deleteCustomerCategory($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$name = $params["name"];
		
		$sql = "select count(*) as cnt from t_customer where category_id = '%s' ";
		$query = $db->query($sql, $id);
		$cnt = $query[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("当前分类 [{$name}] 下还有客户资料，不能删除");
		}
		
		$sql = "delete from t_customer_category where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	public function addCustomer($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$address = $params["address"];
		$addressReceipt = $params["addressReceipt"];
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
		
		$py = $params["py"];
		$categoryId = $params["categoryId"];
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		// 检查编码是否已经存在
		$sql = "select count(*) as cnt from t_customer where code = '%s' ";
		$data = $db->query($sql, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}] 的客户已经存在");
		}
		
		$sql = "insert into t_customer (id, category_id, code, name, py, contact01,
				qq01, tel01, mobile01, contact02, qq02, tel02, mobile02, address, address_receipt,
				bank_name, bank_account, tax_number, fax, note, data_org, company_id)
				values ('%s', '%s', '%s', '%s', '%s', '%s',
						'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
						'%s', '%s', '%s', '%s', '%s', '%s', '%s')  ";
		$rc = $db->execute($sql, $id, $categoryId, $code, $name, $py, $contact01, $qq01, $tel01, 
				$mobile01, $contact02, $qq02, $tel02, $mobile02, $address, $addressReceipt, 
				$bankName, $bankAccount, $tax, $fax, $note, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 初始化应收账款
	 */
	public function initReceivables($params) {
		$db = $this->db;
		
		$idGen = new IdGenDAO($db);
		
		$id = $params["id"];
		$initReceivables = $params["initReceivables"];
		$initReceivablesDT = $params["initReceivablesDT"];
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		$initReceivables = floatval($initReceivables);
		if ($initReceivables && $initReceivablesDT) {
			$sql = "select count(*) as cnt
					from t_receivables_detail
					where ca_id = '%s' and ca_type = 'customer' and ref_type <> '应收账款期初建账'
						and company_id = '%s' ";
			$data = $db->query($sql, $id, $companyId);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				// 已经有应收业务发生，就不再更改期初数据
				return null;
			}
			
			$sql = "update t_customer
					set init_receivables = %f, init_receivables_dt = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $initReceivables, $initReceivablesDT, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 应收明细账
			$sql = "select id from t_receivables_detail
					where ca_id = '%s' and ca_type = 'customer' and ref_type = '应收账款期初建账'
						and company_id = '%s' ";
			$data = $db->query($sql, $id, $companyId);
			if ($data) {
				$rvId = $data[0]["id"];
				$sql = "update t_receivables_detail
						set rv_money = %f, act_money = 0, balance_money = %f, biz_date ='%s', date_created = now()
						where id = '%s' ";
				$rc = $db->execute($sql, $initReceivables, $initReceivables, $initReceivablesDT, 
						$rvId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			} else {
				$rvId = $idGen->newId();
				$sql = "insert into t_receivables_detail (id, rv_money, act_money, balance_money,
						biz_date, date_created, ca_id, ca_type, ref_number, ref_type, data_org, company_id)
						values ('%s', %f, 0, %f, '%s', now(), '%s', 'customer', '%s', '应收账款期初建账', '%s', '%s') ";
				$rc = $db->execute($sql, $rvId, $initReceivables, $initReceivables, 
						$initReceivablesDT, $id, $id, $dataOrg, $companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
			
			// 应收总账
			$sql = "select id from t_receivables
					where ca_id = '%s' and ca_type = 'customer'
						and company_id = '%s' ";
			$data = $db->query($sql, $id, $companyId);
			if ($data) {
				$rvId = $data[0]["id"];
				$sql = "update t_receivables
						set rv_money = %f, act_money = 0, balance_money = %f
						where id = '%s' ";
				$rc = $db->execute($sql, $initReceivables, $initReceivables, $rvId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			} else {
				$rvId = $idGen->newId();
				$sql = "insert into t_receivables (id, rv_money, act_money, balance_money,
							ca_id, ca_type, data_org, company_id)
						values ('%s', %f, 0, %f, '%s', 'customer', '%s', '%s')";
				$rc = $db->execute($sql, $rvId, $initReceivables, $initReceivables, $id, $dataOrg, 
						$companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
		} else {
			$sql = "update t_customer
					set init_receivables = null, init_receivables_dt = null
					where id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 应收明细账
			$sql = "delete from t_receivables_detail
					where ca_id = '%s' and ca_type = 'customer' and ref_type = '应收账款期初建账'
						and company_id = '%s' ";
			$rc = $db->execute($sql, $id, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			// 应收总账
			$sql = "delete from t_receivables
					where ca_id = '%s' and ca_type = 'customer'
						and company_id = '%s' ";
			$rc = $db->execute($sql, $id, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 操作成功
		return null;
	}

	public function updateCustomer($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$address = $params["address"];
		$addressReceipt = $params["addressReceipt"];
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
		
		$py = $params["py"];
		$categoryId = $params["categoryId"];
		
		// 检查编码是否已经存在
		$sql = "select count(*) as cnt from t_customer where code = '%s'  and id <> '%s' ";
		$data = $db->query($sql, $code, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}] 的客户已经存在");
		}
		
		$sql = "update t_customer
				set code = '%s', name = '%s', category_id = '%s', py = '%s',
				contact01 = '%s', qq01 = '%s', tel01 = '%s', mobile01 = '%s',
				contact02 = '%s', qq02 = '%s', tel02 = '%s', mobile02 = '%s',
				address = '%s', address_receipt = '%s',
				bank_name = '%s', bank_account = '%s', tax_number = '%s',
				fax = '%s', note = '%s'
				where id = '%s'  ";
		
		$rc = $db->execute($sql, $code, $name, $categoryId, $py, $contact01, $qq01, $tel01, 
				$mobile01, $contact02, $qq02, $tel02, $mobile02, $address, $addressReceipt, 
				$bankName, $bankAccount, $tax, $fax, $note, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 删除客户资料
	 */
	public function deleteCustomer($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$code = $params["code"];
		$name = $params["name"];
		
		// 判断是否能删除客户资料
		$sql = "select count(*) as cnt from t_ws_bill where customer_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("客户资料 [{$code} {$name}] 已经在销售出库单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt
				from t_receivables_detail r, t_receiving v
				where r.ref_number = v.ref_number and r.ref_type = v.ref_type
				  and r.ca_id = '%s' and r.ca_type = 'customer' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("客户资料 [{$code} {$name}] 已经有收款记录，不能删除");
		}
		
		// 判断在销售退货入库单中是否使用了客户资料
		$sql = "select count(*) as cnt from t_sr_bill where customer_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("客户资料 [{$code} {$name}]已经在销售退货入库单中使用了，不能删除");
		}
		
		$sql = "delete from t_customer where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 删除客户应收总账和明细账
		$sql = "delete from t_receivables where ca_id = '%s' and ca_type = 'customer' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_receivables_detail where ca_id = '%s' and ca_type = 'customer' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	public function getCustomerById($id) {
		$db = $this->db;
		
		$sql = "select code, name from t_customer where id = '%s' ";
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
	 * 获得某个分类的客户列表
	 */
	public function customerList($params) {
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
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "t_customer", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by code limit %d, %d";
		$queryParam[] = $start;
		$queryParam[] = $limit;
		$result = array();
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
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "t_customer", $loginUserId);
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
	 * 客户字段，查询数据
	 */
	public function queryData($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		
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
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER_BILL, "t_customer", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by code limit 20";
		
		return $db->query($sql, $queryParams);
	}

	/**
	 * 获得某个客户的详情
	 */
	public function customerInfo($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$result = array();
		
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
}
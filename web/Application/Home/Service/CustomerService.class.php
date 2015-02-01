<?php

namespace Home\Service;

/**
 * 客户Service
 *
 * @author 李静波
 */
class CustomerService extends PSIBaseService {

	public function categoryList() {
		return M()->query("select id, code, name from t_customer_category order by code");
	}

	public function editCategory($params) {
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];

		$db = M();

		if ($id) {
			// 编辑
			// 检查分类编码是否已经存在
			$sql = "select count(*) as cnt from t_customer_category where code = '%s' and id <> '%s' ";
			$data = $db->query($sql, $code, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [{$code}] 的分类已经存在");
			}

			$sql = "update t_customer_category"
					. " set code = '%s', name = '%s' "
					. " where id = '%s' ";
			$db->execute($sql, $code, $name, $id);

			$log = "编辑客户分类: 编码 = {$code}, 分类名 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "客户关系-客户资料");
		} else {
			// 新增
			// 检查分类编码是否已经存在
			$sql = "select count(*) as cnt from t_customer_category where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [{$code}] 的分类已经存在");
			}

			$idGen = new IdGenService();
			$id = $idGen->newId();

			$sql = "insert into t_customer_category (id, code, name) values ('%s', '%s', '%s') ";
			$db->execute($sql, $id, $code, $name);

			$log = "新增客户分类：编码 = {$code}, 分类名 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "客户关系-客户资料");
		}

		return $this->ok($id);
	}

	public function deleteCategory($params) {
		$id = $params["id"];

		$db = M();

		$data = $db->query("select code, name from t_customer_category where id = '%s' ", $id);
		if (!$data) {
			return $this->bad("要删除的分类不存在");
		}

		$category = $data[0];

		$query = $db->query("select count(*) as cnt from t_customer where category_id = '%s' ", $id);
		$cnt = $query[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("当前分类 [{$category['name']}] 下还有客户资料，不能删除");
		}

		$db->execute("delete from t_customer_category where id = '%s' ", $id);
		$log = "删除客户分类： 编码 = {$category['code']}, 分类名称 = {$category['name']}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "客户关系-客户资料");

		return $this->ok();
	}

	public function editCustomer($params) {
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$contact01 = $params["contact01"];
		$mobile01 = $params["mobile01"];
		$tel01 = $params["tel01"];
		$qq01 = $params["qq01"];
		$contact02 = $params["contact02"];
		$mobile02 = $params["mobile02"];
		$tel02 = $params["tel02"];
		$qq02 = $params["qq02"];
		$initReceivables = $params["initReceivables"];
		$initReceivablesDT = $params["initReceivablesDT"];
		
		$ps = new PinyinService();
		$py = $ps->toPY($name);

		$categoryId = $params["categoryId"];

		$db = M();

		$sql = "select count(*) as cnt from t_customer_category where id = '%s' ";
		$data = $db->query($sql, $categoryId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->bad("客户分类不存在");
		}

		if ($id) {
			// 编辑
			// 检查编码是否已经存在
			$sql = "select count(*) as cnt from t_customer where code = '%s'  and id <> '%s' ";
			$data = $db->query($sql, $code, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [{$code}] 的客户已经存在");
			}

			$sql = "update t_customer "
					. " set code = '%s', name = '%s', category_id = '%s', py = '%s', "
					. "       contact01 = '%s', qq01 = '%s', tel01 = '%s', mobile01 = '%s', "
					. "       contact02 = '%s', qq02 = '%s', tel02 = '%s', mobile02 = '%s' "
					. " where id = '%s'  ";

			$db->execute($sql, $code, $name, $categoryId, $py, $contact01, $qq01, $tel01, $mobile01, $contact02, $qq02, $tel02, $mobile02, $id);

			$log = "编辑客户：编码 = {$code}, 名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "客户关系-客户资料");
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId();

			// 检查编码是否已经存在
			$sql = "select count(*) as cnt from t_customer where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [{$code}] 的客户已经存在");
			}

			$sql = "insert into t_customer (id, category_id, code, name, py, contact01, "
					. "qq01, tel01, mobile01, contact02, qq02,"
					. " tel02, mobile02) "
					. " values ('%s', '%s', '%s', '%s', '%s', '%s', "
					. "  '%s', '%s', '%s', '%s', '%s',"
					. "  '%s', '%s')  ";
			$db->execute($sql, $id, $categoryId, $code, $name, $py, $contact01, $qq01, $tel01, $mobile01, $contact02, $qq02, $tel02, $mobile02);

			$log = "新增客户：编码 = {$code}, 名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "客户关系-客户资料");
		}
		
		// 处理应收账款
		$initReceivables = floatval($initReceivables);
		if ($initReceivables && $initReceivablesDT) {
			$sql = "select count(*) as cnt from t_receivables_detail "
					. " where ca_id = '%s' and ca_type = 'customer' and ref_type <> '应收建账' ";
			$data = $db->query($sql, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				// 已经有应收业务发生，就不再更改期初数据
				return $this->ok($id);
			}
			
			$sql = "update t_customer "
					. " set init_receivables = %f, init_receivables_dt = '%s' "
					. " where id = '%s' ";
			$db->execute($sql, $initReceivables, $initReceivablesDT, $id);
			
			// 应收明细账
			$sql = "select id from t_receivables_detail "
					. " where ca_id = '%s' and ca_type = 'customer' and ref_type = '应收建账' ";
			$data = $db->query($sql, $id);
			if ($data) {
				$rvId = $data[0]["id"];
				$sql = "update t_receivables_detail"
						. " set rv_money = %f, act_money = 0, balance_money = %f, date_created = '%s' "
						. " where id = '%s' ";
				$db->execute($sql, $initReceivables, $initReceivables, $initReceivablesDT, $rvId);
			} else {
				$idGen = new IdGenService();
				$rvId = $idGen->newId();
				$sql = "insert into t_receivables_detail (id, rv_money, act_money, balance_money,"
						. " date_created, ca_id, ca_type, ref_number, ref_type)"
						. " values ('%s', %f, 0, %f, '%s', '%s', 'customer', '', '应收建账') ";
				$db->execute($sql, $rvId, $initReceivables, $initReceivables, $initReceivablesDT, $id);
			}
			
			// 应收总账
			$sql = "select id from t_receivables where ca_id = '%s' and ca_type = 'customer' ";
			$data = $db->query($sql, $id);
			if ($data) {
				$rvId = $data[0]["id"];
				$sql = "update t_receivables "
						. " set rv_money = %f, act_money = 0, balance_money = %f"
						. " where id = '%s' ";
				$db->execute($sql, $initReceivables, $initReceivables, $rvId);
			}else {
				$idGen = new IdGenService();
				$rvId = $idGen->newId();
				$sql = "insert into t_receivables (id, rv_money, act_money, balance_money,"
						. "ca_id, ca_type) values ('%s', %f, 0, %f, '%s', 'customer')";
				$db->execute($sql, $rvId, $initReceivables, $initReceivables, $id);
			}
		}

		return $this->ok($id);
	}

	public function customerList($params) {
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		$sql = "select id, category_id, code, name, contact01, qq01, tel01, mobile01, "
				. " contact02, qq02, tel02, mobile02, init_receivables, init_receivables_dt "
				. " from t_customer where category_id = '%s' "
				. " order by code "
				. " limit " . $start . ", " . $limit;
		$result = array();
		$db = M();
		$data = $db->query($sql, $categoryId);
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["categoryId"] = $v["category_id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
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
				$result[$i]["initReceivablesDT"] = date("Y-m-d", strtotime($v["init_receivables_dt"]));
			}
		}

		$sql = "select count(*) as cnt from t_customer where category_id  = '%s'";
		$data = $db->query($sql, $categoryId);

		return array("customerList" => $result, "totalCount" => $data[0]["cnt"]);
	}

	public function deleteCustomer($params) {
		$id = $params["id"];
		$db = M();
		$sql = "select code, name from t_customer where id = '%s' ";
		$data = $db->query($sql, $id);
		if (!$data) {
			return $this->bad("要删除的客户资料不存在");
		}
		$code = $data[0]["code"];
		$name = $data[0]["name"];

		// TODO 需要判断是否能删除客户资料

		$db->startTrans();
		try {
			$sql = "delete from t_customer where id = '%s' ";
			$db->execute($sql, $id);

			$log = "删除客户资料：编码 = {$code},  名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "客户关系-客户资料");

			$db->commit();
		} catch (Exception $exc) {
			$db->rollback();

			// TODO LOG
			return $this->bad("数据库操作失败，请联系管理员");
		}
		return $this->ok();
	}

	public function queryData($params) {
		$queryKey = $params["queryKey"];
		if (!queryKey) {
			return array();
		}
		
		$sql = "select id, code, name"
				. " from t_customer "
				. " where code like '%s' or name like '%s' or py like '%s' "
				. " limit 20";
		$key = "%{$queryKey}%";
		return M()->query($sql, $key, $key, $key);
	}
}

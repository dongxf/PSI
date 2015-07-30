<?php

namespace Home\Service;

use Home\Service\IdGenService;
use Home\Service\BizlogService;

/**
 * 供应商档案Service
 *
 * @author 李静波
 */
class SupplierService extends PSIBaseService {

	public function categoryList() {
		$sql = "select c.id, c.code, c.name, count(s.id) as cnt 
				from t_supplier_category c 
				left join t_supplier s 
				on c.id = s.category_id 
				group by c.id 
				order by c.code";
		
		return M()->query($sql);
	}

	public function supplierList($params) {
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		$sql = "select id, category_id, code, name, contact01, qq01, tel01, mobile01, 
				contact02, qq02, tel02, mobile02, init_payables, init_payables_dt 
				from t_supplier 
				where category_id = '%s' 
				order by code 
				limit %d, %d";
		$result = array();
		$db = M();
		$data = $db->query($sql, $categoryId, $start, $limit);
		foreach ( $data as $i => $v ) {
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
			$result[$i]["initPayables"] = $v["init_payables"];
			if ($v["init_payables_dt"]) {
				$result[$i]["initPayablesDT"] = date("Y-m-d", strtotime($v["init_payables_dt"]));
			}
		}
		
		$sql = "select count(*) as cnt from t_supplier where category_id  = '%s'";
		$data = $db->query($sql, $categoryId);
		
		return array(
				"supplierList" => $result,
				"totalCount" => $data[0]["cnt"]
		);
	}

	public function editCategory($params) {
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		$db = M();
		
		if ($id) {
			// 编辑
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
			$db->execute($sql, $code, $name, $id);
			
			$log = "编辑供应商分类: 编码 = $code, 分类名 = $name";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-供应商档案");
		} else {
			// 新增
			// 检查分类编码是否已经存在
			$sql = "select count(*) as cnt from t_supplier_category where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [$code] 的分类已经存在");
			}
			
			$idGen = new IdGenService();
			$id = $idGen->newId();
			
			$sql = "insert into t_supplier_category (id, code, name) values ('%s', '%s', '%s') ";
			$db->execute($sql, $id, $code, $name);
			
			$log = "新增供应商分类：编码 = $code, 分类名 = $name";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-供应商档案");
		}
		
		return $this->ok($id);
	}

	public function deleteCategory($params) {
		$id = $params["id"];
		
		$db = M();
		$data = $db->query("select code, name from t_supplier_category where id = '%s' ", $id);
		if (! $data) {
			return $this->bad("要删除的分类不存在");
		}
		
		$category = $data[0];
		
		$query = $db->query("select count(*) as cnt from t_supplier where category_id = '%s' ", $id);
		$cnt = $query[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("当前分类 [{$category['name']}] 下还有供应商档案，不能删除");
		}
		
		$db->execute("delete from t_supplier_category where id = '%s' ", $id);
		$log = "删除供应商分类： 编码 = {$category['code']}, 分类名称 = {$category['name']}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "基础数据-供应商档案");
		
		return $this->ok();
	}

	public function editSupplier($params) {
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
		$initPayables = $params["initPayables"];
		$initPayablesDT = $params["initPayablesDT"];
		
		$ps = new PinyinService();
		$py = $ps->toPY($name);
		
		$categoryId = $params["categoryId"];
		
		$db = M();
		
		$sql = "select count(*) as cnt from t_supplier_category where id = '%s' ";
		$data = $db->query($sql, $categoryId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->bad("供应商分类不存在");
		}
		
		if ($id) {
			// 编辑
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
					contact02 = '%s', qq02 = '%s', tel02 = '%s', mobile02 = '%s' 
					where id = '%s'  ";
			
			$db->execute($sql, $code, $name, $categoryId, $py, $contact01, $qq01, $tel01, $mobile01, $contact02, $qq02, $tel02, $mobile02, $id);
			
			$log = "编辑供应商：编码 = $code, 名称 = $name";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-供应商档案");
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId();
			
			// 检查编码是否已经存在
			$sql = "select count(*) as cnt from t_supplier where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [$code] 的供应商已经存在");
			}
			
			$sql = "insert into t_supplier (id, category_id, code, name, py, contact01, 
					qq01, tel01, mobile01, contact02, qq02,
					tel02, mobile02) 
					values ('%s', '%s', '%s', '%s', '%s', '%s', " . "  '%s', '%s', '%s', '%s', '%s',
							'%s', '%s')  ";
			$db->execute($sql, $id, $categoryId, $code, $name, $py, $contact01, $qq01, $tel01, $mobile01, $contact02, $qq02, $tel02, $mobile02);
			
			$log = "新增供应商：编码 = {$code}, 名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-供应商档案");
		}
		
		// 处理应付期初余额
		
		$initPayables = floatval($initPayables);
		if ($initPayables && $initPayablesDT) {
			$sql = "select count(*) as cnt 
					from t_payables_detail 
					where ca_id = '%s' and ca_type = 'supplier' and ref_type <> '应付账款期初建账' ";
			$data = $db->query($sql, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				// 已经有往来业务发生，就不能修改应付账了
				return $this->ok($id);
			}
			
			$sql = "update t_supplier 
					set init_payables = %f, init_payables_dt = '%s' 
					where id = '%s' ";
			$db->execute($sql, $initPayables, $initPayablesDT, $id);
			
			// 应付明细账
			$sql = "select id from t_payables_detail 
					where ca_id = '%s' and ca_type = 'supplier' and ref_type = '应付账款期初建账' ";
			$data = $db->query($sql, $id);
			if ($data) {
				$payId = $data[0]["id"];
				$sql = "update t_payables_detail 
						set pay_money = %f ,  balance_money = %f , biz_date = '%s', date_created = now(), act_money = 0 
						where id = '%s' ";
				$db->execute($sql, $initPayables, $initPayables, $initPayablesDT, $payId);
			} else {
				$idGen = new IdGenService();
				$payId = $idGen->newId();
				$sql = "insert into t_payables_detail (id, pay_money, act_money, balance_money, ca_id,
						ca_type, ref_type, ref_number, biz_date, date_created) 
						values ('%s', %f, 0, %f, '%s', 'supplier', '应付账款期初建账', '%s', '%s', now()) ";
				$db->execute($sql, $payId, $initPayables, $initPayables, $id, $id, $initPayablesDT);
			}
			
			// 应付总账
			$sql = "select id from t_payables where ca_id = '%s' and ca_type = 'supplier' ";
			$data = $db->query($sql, $id);
			if ($data) {
				$pId = $data[0]["id"];
				$sql = "update t_payables 
						set pay_money = %f ,  balance_money = %f , act_money = 0 
						where id = '%s' ";
				$db->execute($sql, $initPayables, $initPayables, $pId);
			} else {
				$idGen = new IdGenService();
				$pId = $idGen->newId();
				$sql = "insert into t_payables (id, pay_money, act_money, balance_money, ca_id, ca_type)
						values ('%s', %f, 0, %f, '%s', 'supplier') ";
				$db->execute($sql, $pId, $initPayables, $initPayables, $id, $initPayablesDT);
			}
		}
		
		return $this->ok($id);
	}

	public function deleteSupplier($params) {
		$id = $params["id"];
		$db = M();
		$sql = "select code, name from t_supplier where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要删除的供应商档案不存在");
		}
		$code = $data[0]["code"];
		$name = $data[0]["name"];
		
		// 判断是否能删除供应商
		$sql = "select count(*) as cnt from t_pw_bill where supplier_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("供应商档案 [{$code} {$name}] 在采购入库单中已经被使用，不能删除");
		}
		$sql = "select count(*) as cnt 
				from t_payables_detail p, t_payment m 
				where p.ref_type = m.ref_type and p.ref_number = m.ref_number 
				and p.ca_id = '%s' and p.ca_type = 'supplier' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("供应商档案 [{$code} {$name}] 已经产生付款记录，不能删除");
		}
		
		$db->startTrans();
		try {
			$sql = "delete from t_supplier where id = '%s' ";
			$db->execute($sql, $id);
			
			// 删除应付总账、明细账
			$sql = "delete from t_payables where ca_id = '%s' and ca_type = 'supplier' ";
			$db->execute($sql, $id);
			$sql = "delete from t_payables_detail where ca_id = '%s' and ca_type = 'supplier' ";
			$db->execute($sql, $id);
			
			$log = "删除供应商档案：编码 = {$code},  名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-供应商档案");
			
			$db->commit();
		} catch ( Exception $exc ) {
			$db->rollback();
			
			return $this->bad("数据库操作失败，请联系管理员");
		}
		return $this->ok();
	}

	public function queryData($queryKey) {
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select id, code, name from t_supplier
				where code like '%s' or name like '%s' or py like '%s' 
				order by code 
				limit 20";
		$key = "%{$queryKey}%";
		return M()->query($sql, $key, $key, $key);
	}
}
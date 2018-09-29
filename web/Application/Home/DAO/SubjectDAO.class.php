<?php

namespace Home\DAO;

use Home\Common\FIdConst;
use Home\Service\PinyinService;

/**
 * 会计科目 DAO
 *
 * @author 李静波
 */
class SubjectDAO extends PSIBaseExDAO {

	/**
	 * 公司列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function companyList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$sql = "select g.id, g.org_code, g.name
				from t_org g
				where (g.parent_id is null) ";
		
		$ds = new DataOrgDAO($db);
		$queryParams = [];
		$rs = $ds->buildSQL(FIdConst::GL_SUBJECT, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.org_code ";
		
		$result = [];
		
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"code" => $v["org_code"],
					"name" => $v["name"]
			
			];
		}
		
		return $result;
	}

	private function subjectListInternal($parentId, $companyId) {
		$db = $this->db;
		
		$sql = "select id, code, name, category, is_leaf from t_subject
				where parent_id = '%s' and company_id = '%s'
				order by code ";
		$data = $db->query($sql, $parentId, $companyId);
		$result = [];
		foreach ( $data as $v ) {
			// 递归调用自己
			$children = $this->subjectListInternal($v["id"], $companyId);
			
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"category" => $v["category"],
					"isLeaf" => $v["is_leaf"] == 1 ? "末级科目" : null,
					"children" => $children,
					"leaf" => count($children) == 0,
					"iconCls" => "PSI-Subject",
					"expanded" => true
			];
		}
		
		return $result;
	}

	/**
	 * 某个公司的科目码列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function subjectList($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		
		// 判断$companyId是否是公司id
		$sql = "select count(*) as cnt
				from t_org where id = '%s' and parent_id is null ";
		$data = $db->query($sql, $companyId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->emptyResult();
		}
		
		$result = [];
		
		$sql = "select id, code, name, category, is_leaf from t_subject
				where parent_id is null and company_id = '%s'
				order by code ";
		$data = $db->query($sql, $companyId);
		foreach ( $data as $v ) {
			$children = $this->subjectListInternal($v["id"], $companyId);
			
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"category" => $v["category"],
					"isLeaf" => $v["is_leaf"] == 1 ? "末级科目" : null,
					"children" => $children,
					"leaf" => count($children) == 0,
					"iconCls" => "PSI-Subject",
					"expanded" => true
			];
		}
		
		return $result;
	}

	private function insertSubjectInternal($code, $name, $category, $companyId, $py, $dataOrg) {
		$db = $this->db;
		
		$sql = "select count(*) as cnt from t_subject where code = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $code, $companyId);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return;
		}
		
		$id = $this->newId();
		
		$sql = "insert into t_subject(id, category, code, name, is_leaf, py, data_org, company_id, parent_id)
				values ('%s', '%s', '%s', '%s', 0, '%s', '%s', '%s', null)";
		$rc = $db->execute($sql, $id, $category, $code, $name, $py, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		return null;
	}

	/**
	 * 国家标准科目表
	 *
	 * @return array
	 */
	private function getStandardSubjectList() {
		$result = [];
		
		$result[] = [
				"code" => "1001",
				"name" => "库存现金",
				"category" => 1
		];
		$result[] = [
				"code" => "1002",
				"name" => "银行存款",
				"category" => 1
		];
		$result[] = [
				"code" => "1012",
				"name" => "其他货币资金",
				"category" => 1
		];
		$result[] = [
				"code" => "1101",
				"name" => "交易性金融资产",
				"category" => 1
		];
		$result[] = [
				"code" => "1121",
				"name" => "应收票据",
				"category" => 1
		];
		$result[] = [
				"code" => "1122",
				"name" => "应收账款",
				"category" => 1
		];
		$result[] = [
				"code" => "1123",
				"name" => "预付账款",
				"category" => 1
		];
		$result[] = [
				"code" => "1131",
				"name" => "应收股利",
				"category" => 1
		];
		$result[] = [
				"code" => "1132",
				"name" => "应收利息",
				"category" => 1
		];
		$result[] = [
				"code" => "1221",
				"name" => "其他应收款",
				"category" => 1
		];
		$result[] = [
				"code" => "1231",
				"name" => "坏账准备",
				"category" => 1
		];
		$result[] = [
				"code" => "1401",
				"name" => "材料采购",
				"category" => 1
		];
		$result[] = [
				"code" => "1402",
				"name" => "在途物资",
				"category" => 1
		];
		$result[] = [
				"code" => "1403",
				"name" => "原材料",
				"category" => 1
		];
		$result[] = [
				"code" => "1405",
				"name" => "库存商品",
				"category" => 1
		];
		$result[] = [
				"code" => "1406",
				"name" => "发出商品",
				"category" => 1
		];
		$result[] = [
				"code" => "1408",
				"name" => "委托加工物资",
				"category" => 1
		];
		$result[] = [
				"code" => "1411",
				"name" => "周转材料",
				"category" => 1
		];
		$result[] = [
				"code" => "1511",
				"name" => "长期股权投资",
				"category" => 1
		];
		$result[] = [
				"code" => "1601",
				"name" => "固定资产",
				"category" => 1
		];
		$result[] = [
				"code" => "1602",
				"name" => "累计折旧",
				"category" => 1
		];
		$result[] = [
				"code" => "1604",
				"name" => "在建工程",
				"category" => 1
		];
		$result[] = [
				"code" => "1605",
				"name" => "工程物资",
				"category" => 1
		];
		$result[] = [
				"code" => "1606",
				"name" => "固定资产清理",
				"category" => 1
		];
		$result[] = [
				"code" => "1701",
				"name" => "无形资产",
				"category" => 1
		];
		$result[] = [
				"code" => "1702",
				"name" => "累计摊销",
				"category" => 1
		];
		$result[] = [
				"code" => "1801",
				"name" => "长期待摊费用",
				"category" => 1
		];
		$result[] = [
				"code" => "1901",
				"name" => "待处理财产损溢",
				"category" => 1
		];
		$result[] = [
				"code" => "2001",
				"name" => "短期借款",
				"category" => 2
		];
		$result[] = [
				"code" => "2201",
				"name" => "应付票据",
				"category" => 2
		];
		$result[] = [
				"code" => "2202",
				"name" => "应付账款",
				"category" => 2
		];
		$result[] = [
				"code" => "2203",
				"name" => "预收账款",
				"category" => 2
		];
		$result[] = [
				"code" => "2211",
				"name" => "应付职工薪酬",
				"category" => 2
		];
		$result[] = [
				"code" => "2221",
				"name" => "应交税费",
				"category" => 2
		];
		$result[] = [
				"code" => "2231",
				"name" => "应付利息",
				"category" => 2
		];
		$result[] = [
				"code" => "2232",
				"name" => "应付股利",
				"category" => 2
		];
		$result[] = [
				"code" => "2241",
				"name" => "其他应付款",
				"category" => 2
		];
		$result[] = [
				"code" => "2501",
				"name" => "长期借款",
				"category" => 2
		];
		$result[] = [
				"code" => "4001",
				"name" => "实收资本",
				"category" => 4
		];
		$result[] = [
				"code" => "4002",
				"name" => "资本公积",
				"category" => 4
		];
		$result[] = [
				"code" => "4101",
				"name" => "盈余公积",
				"category" => 4
		];
		$result[] = [
				"code" => "4103",
				"name" => "本年利润",
				"category" => 4
		];
		$result[] = [
				"code" => "4104",
				"name" => "利润分配",
				"category" => 4
		];
		$result[] = [
				"code" => "5001",
				"name" => "生产成本",
				"category" => 5
		];
		$result[] = [
				"code" => "5101",
				"name" => "制造费用",
				"category" => 5
		];
		$result[] = [
				"code" => "5201",
				"name" => "劳务成本",
				"category" => 5
		];
		$result[] = [
				"code" => "6001",
				"name" => "主营业务收入",
				"category" => 6
		];
		$result[] = [
				"code" => "6051",
				"name" => "其他业务收入",
				"category" => 6
		];
		$result[] = [
				"code" => "6111",
				"name" => "投资收益",
				"category" => 6
		];
		$result[] = [
				"code" => "6301",
				"name" => "营业外收入",
				"category" => 6
		];
		$result[] = [
				"code" => "6401",
				"name" => "主营业务成本",
				"category" => 6
		];
		$result[] = [
				"code" => "6402",
				"name" => "其他业务成本",
				"category" => 6
		];
		$result[] = [
				"code" => "6403",
				"name" => "营业税金及附加",
				"category" => 6
		];
		$result[] = [
				"code" => "6601",
				"name" => "销售费用",
				"category" => 6
		];
		$result[] = [
				"code" => "6602",
				"name" => "管理费用",
				"category" => 6
		];
		$result[] = [
				"code" => "6603",
				"name" => "财务费用",
				"category" => 6
		];
		$result[] = [
				"code" => "6701",
				"name" => "资产减值损失",
				"category" => 6
		];
		$result[] = [
				"code" => "6711",
				"name" => "营业外支出",
				"category" => 6
		];
		$result[] = [
				"code" => "6801",
				"name" => "所得税费用",
				"category" => 6
		];
		
		return $result;
	}

	/**
	 * 初始国家标准科目
	 */
	public function init(& $params, $pinYinService) {
		$db = $this->db;
		
		$dataOrg = $params["dataOrg"];
		
		$companyId = $params["id"];
		$sql = "select name 
				from t_org
				where id = '%s' and parent_id is null";
		$data = $db->query($sql, $companyId);
		if (! $data) {
			return $this->badParam("companyId");
		}
		
		$companyName = $data[0]["name"];
		
		$sql = "select count(*) as cnt from t_subject where company_id = '%s' ";
		$data = $db->query($sql, $companyId);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("国家科目表已经初始化完毕，不能再次初始化");
		}
		
		$subjectList = $this->getStandardSubjectList();
		foreach ( $subjectList as $v ) {
			$code = $v["code"];
			$name = $v["name"];
			$category = $v["category"];
			
			$rc = $this->insertSubjectInternal($code, $name, $category, $companyId, 
					$pinYinService->toPY($name), $dataOrg);
			if ($rc) {
				return $rc;
			}
		}
		
		// 操作成功
		$params["companyName"] = $companyName;
		
		return null;
	}

	/**
	 * 上级科目字段 - 查询数据
	 *
	 * @param string $queryKey        	
	 */
	public function queryDataForParentSubject($queryKey, $companyId) {
		$db = $this->db;
		
		// length(code) < 8 : 只查询一级二级科目
		$sql = "select code, name
				from t_subject
				where (code like '%s') and (length(code) < 8) 
					and (company_id = '%s') 
				order by code 
				limit 20 ";
		$queryParams = [];
		$queryParams[] = "{$queryKey}%";
		$queryParams[] = $companyId;
		$data = $db->query($sql, $queryParams);
		
		$result = [];
		
		foreach ( $data as $v ) {
			$result[] = [
					"code" => $v["code"],
					"name" => $v["name"]
			];
		}
		
		return $result;
	}

	/**
	 * 新增科目
	 *
	 * @param array $params        	
	 */
	public function addSubject(& $params) {
		$db = $this->db;
		
		$dataOrg = $params["dataOrg"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		$code = $params["code"];
		$name = $params["name"];
		$isLeaf = $params["isLeaf"];
		
		$parentCode = $params["parentCode"];
		$sql = "select id, category 
				from t_subject 
				where company_id = '%s' and code = '%s' ";
		$data = $db->query($sql, $companyId, $parentCode);
		if (! $data) {
			return $this->bad("上级科目不存在");
		}
		$parentId = $data[0]["id"];
		$category = $data[0]["category"];
		
		// 检查科目码是否正确
		if (strlen($parentCode) == 4) {
			// 上级科目是一级科目
			if (strlen($code) != 6) {
				return $this->bad("二级科目码的长度需要是6位");
			}
			if (substr($code, 0, 4) != $parentCode) {
				return $this->bad("二级科目码的前四位必须是一级科目码");
			}
		} else if (strlen($parentCode) == 6) {
			// 上级科目是二级科目
			if (strlen($code) != 8) {
				return $this->bad("三级科目码的长度需要是8位");
			}
			if (substr($code, 0, 6) != $parentCode) {
				return $this->bad("三级科目码的前六位必须是二级科目码");
			}
		} else {
			return $this->bad("上级科目只能是一级科目或者是二级科目");
		}
		
		// 判断科目码是否已经存在
		$sql = "select count(*) as cnt from t_subject
				where company_id = '%s' and code = '%s' ";
		$data = $db->query($sql, $companyId, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("科目码[{$code}]已经存在");
		}
		
		$ps = new PinyinService();
		$py = $ps->toPY($name);
		
		$id = $this->newId();
		$sql = "insert into t_subject(id, category, code, name, is_leaf, py, data_org,
					company_id, parent_id)
				values ('%s', '%s', '%s', '%s', %d, '%s', '%s',
					'%s', '%s')";
		$rc = $db->execute($sql, $id, $category, $code, $name, $isLeaf, $py, $dataOrg, $companyId, 
				$parentId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		$params["id"] = $id;
		return null;
	}

	/**
	 * 编辑科目
	 *
	 * @param array $params        	
	 */
	public function updateSubject(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$isLeaf = $params["isLeaf"];
		$companyId = $params["companyId"];
		
		$sql = "select parent_id from t_subject where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要编辑的科目不存在");
		}
		
		$parentId = $data[0]["parent_id"];
		if (! $parentId) {
			// 当前科目是一级科目，一级科目只能编辑“末级科目”
			$sql = "update t_subject set is_leaf = %d
					where id = '%s' ";
			$rc = $db->execute($sql, $isLeaf, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else {
			// 二级或三级科目
			$ps = new PinyinService();
			$py = $ps->toPY($name);
			$sql = "update t_subject
						set name = '%s', py = '%s', is_leaf = %d
					where id = '%s' ";
			$rc = $db->execute($sql, $name, $py, $isLeaf, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 某个科目的详情
	 *
	 * @param array $params        	
	 */
	public function subjectInfo($params) {
		$db = $this->db;
		
		// 科目id
		$id = $params["id"];
		
		$sql = "select code, name, is_leaf, parent_id 
				from t_subject
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->emptyResult();
		}
		
		$v = $data[0];
		
		$result = [
				"code" => $v["code"],
				"name" => $v["name"],
				"isLeaf" => $v["is_leaf"],
				"parentCode" => "[无]"
		];
		
		$parentId = $v["parent_id"];
		$sql = "select code, name
					from t_subject
					where id = '%s' ";
		$data = $db->query($sql, $parentId);
		if ($data) {
			$result["parentCode"] = $data[0]["code"];
		}
		
		return $result;
	}

	/**
	 * 删除科目
	 *
	 * @param array $params        	
	 */
	public function deleteSubject(&$params) {
		$db = $this->db;
		
		// 科目id
		$id = $params["id"];
		
		$sql = "select code, parent_id, company_id from t_subject where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要删除的科目不存在");
		}
		$companyId = $data[0]["company_id"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->bad("当前科目的companyId字段值异常");
		}
		
		$code = $data[0]["code"];
		$parentId = $data[0]["parent_id"];
		if (! $parentId) {
			return $this->bad("不能删除一级科目");
		}
		
		// 检查科目是否有下级科目
		$sql = "select count(*) as cnt from t_subject where parent_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("科目[{$code}]还有下级科目，不能删除");
		}
		
		// 判断科目是否在账样中使用
		$sql = "select count(*) as cnt 
				from t_acc_fmt
				where company_id = '%s' and subject_code = '%s' ";
		$data = $db->query($sql, $companyId, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("科目[{$code}]已经在账样中使用，不能删除");
		}
		
		$sql = "delete from t_subject where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		$params["code"] = $code;
		return null;
	}
}
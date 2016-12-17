<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 仓库 DAO
 *
 * @author 李静波
 */
class WarehouseDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	/**
	 * 获得所有的仓库列表
	 */
	public function warehouseList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		
		$sql = "select id, code, name, inited, data_org from t_warehouse ";
		$ds = new DataOrgDAO();
		$queryParams = array();
		$rs = $ds->buildSQL(FIdConst::WAREHOUSE, "t_warehouse", $loginUserId);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= " order by code";
		
		$result = array();
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$item = array(
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"inited" => $v["inited"],
					"dataOrg" => $v["data_org"]
			);
			
			$result[] = $item;
		}
		
		return $result;
	}

	/**
	 * 新增一个仓库
	 */
	public function addWarehouse($params) {
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$py = $params["py"];
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		$db = $this->db;
		
		// 检查同编号的仓库是否存在
		$sql = "select count(*) as cnt from t_warehouse where code = '%s' ";
		$data = $db->query($sql, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [$code] 的仓库已经存在");
		}
		
		$sql = "insert into t_warehouse(id, code, name, inited, py, data_org, company_id)
					values ('%s', '%s', '%s', 0, '%s', '%s', '%s')";
		$rc = $db->execute($sql, $id, $code, $name, $py, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 修改仓库
	 */
	public function updateWarehouse($params) {
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$py = $params["py"];
		
		$db = $this->db;
		
		// 检查同编号的仓库是否存在
		$sql = "select count(*) as cnt from t_warehouse where code = '%s' and id <> '%s' ";
		$data = $db->query($sql, $code, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [$code] 的仓库已经存在");
		}
		
		$sql = "update t_warehouse
					set code = '%s', name = '%s', py = '%s'
					where id = '%s' ";
		$rc = $db->execute($sql, $code, $name, $py, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}
}
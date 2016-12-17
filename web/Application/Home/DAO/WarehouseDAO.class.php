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

	/**
	 * 删除仓库
	 */
	public function deleteWarehouse($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$sql = "select code, name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要删除的仓库不存在");
		}
		
		// 判断仓库是否能删除
		$warehouse = $data[0];
		$warehouseName = $warehouse["name"];
		if ($warehouse["inited"] == 1) {
			return $this->bad("仓库[{$warehouseName}]已经建账，不能删除");
		}
		
		// 判断仓库是否在采购入库单中使用
		$sql = "select count(*) as cnt from t_pw_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在采购入库单中使用，不能删除");
		}
		
		// 判断仓库是否在采购退货出库单中使用
		$sql = "select count(*) as cnt from t_pr_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在采购退货出库单中使用，不能删除");
		}
		
		// 判断仓库是否在销售出库单中使用
		$sql = "select count(*) as cnt from t_ws_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在销售出库单中使用，不能删除");
		}
		
		// 判断仓库是否在销售退货入库单中使用
		$sql = "select count(*) as cnt from t_sr_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在销售退货入库单中使用，不能删除");
		}
		
		// 判断仓库是否在调拨单中使用
		$sql = "select count(*) as cnt from t_it_bill
				where from_warehouse_id = '%s' or to_warehouse_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在调拨单中使用，不能删除");
		}
		
		// 判断仓库是否在盘点单中使用
		$sql = "select count(*) as cnt from t_ic_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在盘点单中使用，不能删除");
		}
		
		$sql = "delete from t_warehouse where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	public function getWarehouseById($id) {
		$db = $this->db;
		$sql = "select code, name, data_org from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			return null;
		}
		
		return array(
				"code" => $data[0]["code"],
				"name" => $data[0]["name"],
				"dataOrg" => $data[0]["data_org"]
		);
	}

	/**
	 * 编辑仓库数据域
	 */
	public function editDataOrg($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$dataOrg = $params["dataOrg"];
		
		$sql = "select name, data_org from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要编辑数据域的仓库不存在");
		}
		
		$name = $data[0]["name"];
		$oldDataOrg = $data[0]["data_org"];
		if ($oldDataOrg == $dataOrg) {
			return $this->bad("数据域没有改动，不用保存");
		}
		
		// 检查新数据域是否存在
		$sql = "select count(*) as cnt from t_user where data_org = '%s' ";
		$data = $db->query($sql, $dataOrg);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("数据域[{$dataOrg}]不存在");
		}
		
		$sql = "update t_warehouse
				set data_org = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $dataOrg, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}
}
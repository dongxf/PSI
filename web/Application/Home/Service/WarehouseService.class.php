<?php

namespace Home\Service;

use Home\Service\IdGenService;
use Home\Service\BizlogService;
use Org\Util\ArrayList;

/**
 * 基础数据仓库Service
 *
 * @author 李静波
 */
class WarehouseService extends PSIBaseService {

	/**
	 * 所以仓库的列表信息
	 */
	public function warehouseList() {
		return M()->query("select id, code, name, inited from t_warehouse order by code");
	}

	/**
	 * 新建或编辑仓库
	 */
	public function editWarehouse($params) {
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$ps = new PinyinService();
		$py = $ps->toPY($name);
		$db = M();
		
		if ($id) {
			// 修改
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
			$db->execute($sql, $code, $name, $py, $id);
			$log = "编辑仓库：编码 = $code,  名称 = $name";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-仓库");
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId();
			
			// 检查同编号的仓库是否存在
			$sql = "select count(*) as cnt from t_warehouse where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [$code] 的仓库已经存在");
			}
			
			$sql = "insert into t_warehouse(id, code, name, inited, py) 
					values ('%s', '%s', '%s', 0, '%s' )";
			$db->execute($sql, $id, $code, $name, $py);
			
			$log = "新增仓库：编码 = {$code},  名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-仓库");
		}
		
		return $this->ok($id);
	}

	/**
	 * 删除仓库
	 */
	public function deleteWarehouse($params) {
		$id = $params["id"];
		
		$db = M();
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
		$db->execute($sql, $id);
		
		$log = "删除仓库： 编码 = {$warehouse['code']}， 名称 = {$warehouse['name']}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "基础数据-仓库");
		
		return $this->ok();
	}

	public function queryData($queryKey, $fid) {
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$cs = new BizConfigService();
		if ($cs->warehouseUsesOrg()) {
			$us = new UserService();
			$userId = $us->getLoginUserId();
			
			$sql = "select id, code, name 
					from t_warehouse
					where (code like '%s' or name like '%s' or py like '%s') 
					  and id in (
							select warehouse_id 
							from t_warehouse_org
							where org_id = '%s' and org_type = '1' and bill_fid = '%s'
					    	union
							select w.warehouse_id
							from t_warehouse_org w, t_org o, t_user u
							where w.bill_fid = '%s' and w.org_type = '0' and w.org_id = o.id and o.id = u.org_id and u.id = '%s'
					  )
					order by code";
			$key = "%{$queryKey}%";
			return M()->query($sql, $key, $key, $key, $userId, $fid, $fid, $userId);
		} else {
			$sql = "select id, code, name from t_warehouse 
					where code like '%s' or name like '%s' or py like '%s' 
					order by code";
			$key = "%{$queryKey}%";
			return M()->query($sql, $key, $key, $key);
		}
	}

	public function warehouseOrgList($params) {
		$warehouseId = $params["warehouseId"];
		$fid = $params["fid"];
		$db = M();
		$result = array();
		// 组织机构
		$sql = "select o.id, o.full_name, o.org_code
				from t_warehouse_org w, t_org o
				where w.warehouse_id = '%s' and w.bill_fid = '%s' 
				    and w.org_id = o.id and w.org_type = '0' 
				order by o.org_code";
		$data = $db->query($sql, $warehouseId, $fid);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["fullName"] = $v["full_name"];
			$result[$i]["orgCode"] = $v["org_code"];
		}
		
		$cnt = count($result);
		
		// 具体人员
		$sql = "select u.id, u.name, o.full_name, u.org_code
				from t_warehouse_org w, t_user u, t_org o
				where w.warehouse_id = '%s' and w.bill_fid = '%s'
				    and w.org_id = u.id and w.org_type = '1'
					and u.org_id = o.id
				order by u.org_code";
		$data = $db->query($sql, $warehouseId, $fid);
		foreach ( $data as $i => $v ) {
			$result[$i + $cnt]["id"] = $v["id"];
			$result[$i + $cnt]["fullName"] = $v["full_name"] . "\\" . $v["name"];
			$result[$i + $cnt]["orgCode"] = $v["org_code"];
		}
		
		return $result;
	}

	public function allOrgs() {
		$sql = "select id, name, org_code, full_name 
				from t_org where parent_id is null order by org_code";
		$db = M();
		$orgList1 = $db->query($sql);
		$result = array();
		
		// 第一级组织
		foreach ( $orgList1 as $i => $org1 ) {
			$result[$i]["id"] = $org1["id"];
			$result[$i]["text"] = $org1["name"];
			$result[$i]["orgCode"] = $org1["org_code"];
			$result[$i]["fullName"] = $org1["full_name"];
			
			$c2 = array();
			
			// 第二级
			$sql = "select id, name,  org_code, full_name  
					from t_org where parent_id = '%s' order by org_code";
			$orgList2 = $db->query($sql, $org1["id"]);
			
			foreach ( $orgList2 as $j => $org2 ) {
				$c2[$j]["id"] = $org2["id"];
				$c2[$j]["text"] = $org2["name"];
				$c2[$j]["orgCode"] = $org2["org_code"];
				$c2[$j]["fullName"] = $org2["full_name"];
				$c2[$j]["expanded"] = true;
				
				// 第三级
				$sql = "select id, name,  org_code, full_name 
						from t_org 
						where parent_id = '%s' 
						order by org_code";
				$orgList3 = $db->query($sql, $org2["id"]);
				$c3 = array();
				foreach ( $orgList3 as $k => $org3 ) {
					$c3[$k]["id"] = $org3["id"];
					$c3[$k]["text"] = $org3["name"];
					$c3[$k]["orgCode"] = $org3["org_code"];
					$c3[$k]["fullName"] = $org3["full_name"];
					$c4 = array();
					
					// 第三级组织下的用户
					$sql = "select id, name, org_code
					from t_user where org_id = '%s'
					order by org_code";
					$data = $db->query($sql, $org3["id"]);
					foreach ( $data as $i3 => $u3 ) {
						$c4[$i3]["id"] = $u3["id"];
						$c4[$i3]["text"] = $u3["name"];
						$c4[$i3]["orgCode"] = $u3["org_code"];
						$c4[$i3]["fullName"] = $org3["full_name"] . "\\" . $u3["name"];
						$c4[$i3]["leaf"] = true;
					}
					
					$c3[$k]["children"] = $c4;
					$c3[$k]["leaf"] = count($c4) == 0;
					$c3[$k]["expanded"] = true;
				}
				
				$cntC3 = count($c3);
				// 第二级组织下的用户
				$sql = "select id, name, org_code
					from t_user where org_id = '%s'
					order by org_code";
				$data = $db->query($sql, $org2["id"]);
				foreach ( $data as $i2 => $u2 ) {
					$c3[$cntC3 + $i2]["id"] = $u2["id"];
					$c3[$cntC3 + $i2]["text"] = $u2["name"];
					$c3[$cntC3 + $i2]["orgCode"] = $u2["org_code"];
					$c3[$cntC3 + $i2]["fullName"] = $org2["full_name"] . "\\" . $u2["name"];
					$c3[$cntC3 + $i2]["leaf"] = true;
				}
				
				$c2[$j]["children"] = $c3;
				$c2[$j]["leaf"] = count($c3) == 0;
			}
			
			$cntC2 = count($c2);
			// 第一级组织下的用户
			$sql = "select id, name, org_code 
					from t_user where org_id = '%s' 
					order by org_code";
			$data = $db->query($sql, $org1["id"]);
			foreach ( $data as $i1 => $u1 ) {
				$c2[$cntC2 + $i1]["id"] = $u1["id"];
				$c2[$cntC2 + $i1]["text"] = $u1["name"];
				$c2[$cntC2 + $i1]["orgCode"] = $u1["org_code"];
				$c2[$cntC2 + $i1]["fullName"] = $org1["full_name"] . "\\" . $u1["name"];
				$c2[$cntC2 + $i1]["leaf"] = true;
			}
			$result[$i]["children"] = $c2;
			$result[$i]["leaf"] = count($orgList2) == 0;
			$result[$i]["expanded"] = true;
		}
		
		return $result;
	}

	public function addOrg($params) {
		$warehouseId = $params["warehouseId"];
		$fid = $params["fid"];
		$orgId = $params["orgId"];
		
		$db = M();
		$sql = "select name from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			return $this->bad("仓库不存在");
		}
		$warehouseName = $data[0]["name"];
		
		$fidArray = array(
				"2001",
				"2002"
		);
		if (! in_array($fid, $fidArray)) {
			return $this->bad("业务类型不存在");
		}
		
		$orgType = null;
		$orgName = null;
		$sql = "select full_name from t_org where id = '%s' ";
		$data = $db->query($sql, $orgId);
		if ($data) {
			$orgName = $data[0]["full_name"];
			$orgType = "0";
		} else {
			$sql = "select name from t_user where id = '%s' ";
			$data = $db->query($sql, $orgId);
			if ($data) {
				$orgName = $data[0]["name"];
				$orgType = "1";
			} else {
				return $this->bad("组织机构不存在");
			}
		}
		
		$sql = "select count(*) as cnt from t_warehouse_org 
				where warehouse_id = '%s' and bill_fid = '%s' and org_id = '%s' ";
		$data = $db->query($sql, $warehouseId, $fid, $orgId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 1) {
			return $this->bad("当前组织机构[{$orgName}]已经添加过了，不能重复添加");
		}
		
		$sql = "insert into t_warehouse_org (warehouse_id, bill_fid, org_id, org_type)
				values ('%s', '%s', '%s', '%s')";
		$db->execute($sql, $warehouseId, $fid, $orgId, $orgType);
		
		$bizName = "";
		if ($fid == "2001") {
			$bizName = "采购入库";
		} else if ($fid == "2002") {
			$bizName = "销售出库";
		}
		
		$log = "";
		if ($orgType == "0") {
			$log = "为仓库[{$warehouseName}]的业务类型[{$bizName}]添加组织机构[{$orgName}]";
		} else {
			$log = "为仓库[{$warehouseName}]的业务类型[{$bizName}]添加用户[{$orgName}]";
		}
		
		$bs = new BizlogService();
		$bs->insertBizlog($log, "基础数据-仓库");
		
		return $this->ok();
	}

	public function deleteOrg($params) {
		$warehouseId = $params["warehouseId"];
		$fid = $params["fid"];
		$orgId = $params["orgId"];
		
		$db = M();
		$sql = "select name from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			return $this->bad("仓库不存在");
		}
		$warehouseName = $data[0]["name"];
		
		$fidArray = array(
				"2001",
				"2002"
		);
		if (! in_array($fid, $fidArray)) {
			return $this->bad("业务类型不存在");
		}
		
		$orgType = null;
		$orgName = null;
		$sql = "select full_name from t_org where id = '%s' ";
		$data = $db->query($sql, $orgId);
		if ($data) {
			$orgName = $data[0]["full_name"];
			$orgType = "0";
		} else {
			$sql = "select name from t_user where id = '%s' ";
			$data = $db->query($sql, $orgId);
			if ($data) {
				$orgName = $data[0]["name"];
				$orgType = "1";
			} else {
				return $this->bad("组织机构不存在");
			}
		}
		
		$sql = "delete from t_warehouse_org 
				where warehouse_id = '%s' and bill_fid = '%s' and org_id = '%s' ";
		$db->execute($sql, $warehouseId, $fid, $orgId);
		
		$bizName = "";
		if ($fid == "2001") {
			$bizName = "采购入库";
		} else if ($fid == "2002") {
			$bizName = "销售出库";
		}
		
		$log = "";
		if ($orgType == "0") {
			$log = "为仓库[{$warehouseName}]的业务类型[{$bizName}]移除组织机构[{$orgName}]";
		} else {
			$log = "为仓库[{$warehouseName}]的业务类型[{$bizName}]移除用户[{$orgName}]";
		}
		
		$bs = new BizlogService();
		$bs->insertBizlog($log, "基础数据-仓库");
		
		return $this->ok();
	}

	/**
	 * 在仓库启用的组织机构绑定的前提，查询当前登录用户的操作的仓库
	 */
	public function getWarehouseListForLoginUser($fid) {
		$us = new UserService();
		$userId = $us->getLoginUserId();
		$result = array();
		$sql = "select id, name
					from t_warehouse
					where id in (
							select warehouse_id
							from t_warehouse_org
							where org_id = '%s' and org_type = '1' and bill_fid = '%s'
					    	union
							select w.warehouse_id
							from t_warehouse_org w, t_org o, t_user u
							where w.bill_fid = '%s' and w.org_type = '0' 
									and w.org_id = o.id and o.id = u.org_id and u.id = '%s'
					  )
					order by code";
		$result = M()->query($sql, $userId, $fid, $fid, $userId);
		return $result;
	}

	public function orgViewWarehouseList($params) {
		$orgId = $params["orgId"];
		$result = array();
		if ($orgId == null) {
			return $result;
		}
		
		$db = M();
		$orgType = null;
		$sql = "select count(*) as cnt from t_org where id = '%s' ";
		$data = $db->query($sql, $orgId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 1) {
			$orgType = "0";
		} else {
			$sql = "select count(*) as cnt from t_user where id = '%s' ";
			$data = $db->query($sql, $orgId);
			$cnt = $data[0]["cnt"];
			if ($cnt == 1) {
				$orgType = "1";
			} else {
				// 非法的orgId
				return $result;
			}
		}
		
		$billFidArray = array(
				"2001" => "采购入库",
				"2002" => "销售出库"
		);
		
		$result["text"] = "root";
		$result["expanded"] = true;
		$result["leaf"] = false;
		$c1 = array();
		$index = 0;
		foreach ( $billFidArray as $fid => $billType ) {
			if ($orgType == "0") {
				$sql = "select w.id, w.code, w.name
				from t_warehouse w, t_warehouse_org wo
				where w.id = wo.warehouse_id and wo.org_id = '%s' 
					and wo.bill_fid = '%s' and wo.org_type = '0' 
				order by w.code";
				
				$data = $db->query($sql, $orgId, $fid);
			} else {
				$sql = "select id, name, code
					from t_warehouse
					where id in (
							select warehouse_id
							from t_warehouse_org
							where org_id = '%s' and org_type = '1' and bill_fid = '%s'
					    	union
							select w.warehouse_id
							from t_warehouse_org w, t_org o, t_user u
							where w.bill_fid = '%s' and w.org_type = '0'
									and w.org_id = o.id and o.id = u.org_id and u.id = '%s'
					  )
					order by code";
				
				$data = $db->query($sql, $orgId, $fid, $fid, $orgId);
			}
			if (! $data) {
				continue;
			}
			$c1[$index]["text"] = $billType;
			$c1[$index]["expanded"] = true;
			$c1[$index]["leaf"] = false;
			$c2 = array();
			foreach ( $data as $i => $v ) {
				$c2[$i]["text"] = $billType;
				$c2[$i]["code"] = $v["code"];
				$c2[$i]["name"] = $v["name"];
				$c2[$i]["leaf"] = true;
				
				$c2[$i]["children"] = array();
			}
			
			$c1[$index]["children"] = $c2;
			$index ++;
		}
		
		$result["children"] = $c1;
		
		return $result;
	}
}

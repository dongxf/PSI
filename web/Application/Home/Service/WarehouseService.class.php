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
	public function warehouseList() {
		return M()->query("select id, code, name, inited from t_warehouse order by code");
	}
	
	public function editWarehouse($params) {
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$ps = new PinyinService();
		$py = $ps->toPY($name);
		$db = M();
		
		if ($id) {
			// 修改
			//检查同编号的仓库是否存在
			$sql = "select count(*) as cnt from t_warehouse where code = '%s' and id <> '%s' ";
			$data = $db->query($sql, $code, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [$code] 的仓库已经存在");
			}
			
			$sql = "update t_warehouse "
					. " set code = '%s', name = '%s', py = '%s' "
					. " where id = '%s' ";
			$db->execute($sql, $code, $name, $py, $id);
			$log = "编辑仓库：编码 = $code,  名称 = $name";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-仓库");
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId();
			
			//检查同编号的仓库是否存在
			$sql = "select count(*) as cnt from t_warehouse where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [$code] 的仓库已经存在");
			}
			
			$sql = "insert into t_warehouse(id, code, name, inited, py) "
					. " values ('%s', '%s', '%s', 0, '%s' )";
			$db->execute($sql, $id, $code, $name, $py);
			
			$log = "新增仓库：编码 = {$code},  名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-仓库");
		}
		
		return $this->ok($id);
	}
	
	public function deleteWarehouse($params) {
		$id = $params["id"];
		
		$db = M();
		$sql = "select code, name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $id);
		if (!$data) {
			return $this->bad("要删除的仓库不存在");
		}
		
		// 判断仓库是否能删除
		$warehouse = $data[0];
		if ($warehouse["inited"] == 1) {
			return $this->bad("仓库已经建账，不能删除");
		}
		
		$sql = "delete from t_warehouse where id = '%s' ";
		$db->execute($sql, $id);
		
		$log = "删除仓库： 编码 = {$warehouse['code']}， 名称 = {$warehouse['name']}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "基础数据-仓库");
		
		return $this->ok();
	}
	
	public function queryData($queryKey) {
		if (!$queryKey) {
			return array();
		}
		
		$sql = "select id, code, name from t_warehouse"
				. " where code like '%s' or name like '%s' or py like '%s' "
				. " order by code";
		$key = "%{$queryKey}%";
		return M()->query($sql, $key, $key, $key);
	}
	
	public function warehouseOrgList($params) {
		$warehouseId = $params["warehouseId"];
		$fid = $params["fid"];
		$db = M();
		$result = array();
		// 组织机构
		$sql = "select o.id, o.full_name
				from t_warehouse_org w, t_org o
				where w.warehouse_id = '%s' and w.bill_fid = '%s' 
				    and w.org_id = o.id and w.org_type = '0' 
				order by o.org_code";
		$data = $db->query($sql, $warehouseId, $fid);
		foreach($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["fullName"] = $v["full_name"];
		}
		
		$cnt = count($result);
		
		// 具体人员
		$sql = "select u.id, u.name, o.full_name
				from t_warehouse_org w, t_user u, t_org o
				where w.warehouse_id = '%s' and w.bill_fid = '%s'
				    and w.org_id = u.id and w.org_type = '1'
					and u.org_id = o.id
				order by u.org_code";
		$data = $db->query($sql, $warehouseId, $fid);
		foreach($data as $i => $v) {
			$result[$i + $cnt]["id"] = $v["id"];
			$result[$i + $cnt]["fullName"] = $v["full_name"] . "\\" . $v["name"];
		}
		
		return $result;
	}
	
	public function allOrgs() {
		$sql = "select id, name,  org_code, full_name "
				. " from t_org where parent_id is null order by org_code";
		$db = M();
		$orgList1 = $db->query($sql);
		$result = array();
		
		// 第一级组织
		foreach ($orgList1 as $i => $org1) {
			$result[$i]["id"] = $org1["id"];
			$result[$i]["text"] = $org1["name"];
			$result[$i]["orgCode"] = $org1["org_code"];
			$result[$i]["fullName"] = $org1["full_name"];
		
			// 第二级
			$sql = "select id, name,  org_code, full_name "
					. " from t_org where parent_id = '%s' order by org_code";
			$orgList2 = $db->query($sql, $org1["id"]);
		
			$c2 = array();
			foreach ($orgList2 as $j => $org2) {
				$c2[$j]["id"] = $org2["id"];
				$c2[$j]["text"] = $org2["name"];
				$c2[$j]["orgCode"] = $org2["org_code"];
				$c2[$j]["fullName"] = $org2["full_name"];
				$c2[$j]["expanded"] = true;
		
				// 第三级
				$sql = "select id, name,  org_code, full_name "
						. " from t_org where parent_id = '%s' order by org_code";
				$orgList3 = $db->query($sql, $org2["id"]);
				$c3 = array();
				foreach ($orgList3 as $k => $org3) {
					$c3[$k]["id"] = $org3["id"];
					$c3[$k]["text"] = $org3["name"];
					$c3[$k]["orgCode"] = $org3["org_code"];
					$c3[$k]["fullName"] = $org3["full_name"];
					$c3[$k]["children"] = array();
					$c3[$k]["leaf"] = true;
				}
		
				$c2[$j]["children"] = $c3;
				$c2[$j]["leaf"] = count($c3) == 0;
			}
		
			$result[$i]["children"] = $c2;
			$result[$i]["leaf"] = count($orgList2) == 0;
			$result[$i]["expanded"] = true;
		}
		
		return $result;
	}
}

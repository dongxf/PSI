<?php
namespace Home\Service;

use Home\Service\IdGenService;
use Home\Service\BizlogService;

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
		$py = (new PinyinService())->toPY($name);
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
			(new BizlogService())->insertBizlog($log, "基础数据-仓库");
		} else {
			// 新增
			
			$id = (new IdGenService())->newId();
			
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
			
			$log = "新增仓库：编码 = $code,  名称 = $name";
			(new BizlogService())->insertBizlog($log, "基础数据-仓库");
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
		(new BizlogService())->insertBizlog($log, "基础数据-仓库");
		
		return $this->ok();
	}
	
	public function queryData($queryKey) {
		if (!$queryKey) {
			return [];
		}
		
		$sql = "select id, code, name from t_warehouse"
				. " where code like '%s' or name like '%s' or py like '%s' "
				. " order by code";
		$key = "%{$queryKey}%";
		return M()->query($sql, $key, $key, $key);
	}
}

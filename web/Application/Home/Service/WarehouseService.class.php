<?php

namespace Home\Service;

use Home\DAO\WarehouseDAO;
use Home\Service\BizlogService;
use Home\Service\IdGenService;

/**
 * 基础数据仓库Service
 *
 * @author 李静波
 */
class WarehouseService extends PSIBaseService {
	private $LOG_CATEGORY = "基础数据-仓库";

	/**
	 * 所有仓库的列表信息
	 */
	public function warehouseList() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params = array(
				"loginUserId" => $us->getLoginUserId()
		);
		
		$dao = new WarehouseDAO();
		
		return $dao->warehouseList($params);
	}

	/**
	 * 新建或编辑仓库
	 */
	public function editWarehouse($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		$ps = new PinyinService();
		$py = $ps->toPY($name);
		$params["py"] = $py;
		
		$db = M();
		
		$db->startTrans();
		
		$dao = new WarehouseDAO($db);
		
		$log = null;
		
		if ($id) {
			// 修改仓库
			
			$rc = $dao->updateWarehouse($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑仓库：编码 = $code,  名称 = $name";
		} else {
			// 新增仓库
			
			$us = new UserService();
			$params["dataOrg"] = $us->getLoginUserDataOrg();
			$params["companyId"] = $us->getCompanyId();
			
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$params["id"] = $id;
			
			$rc = $dao->addWarehouse($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增仓库：编码 = {$code},  名称 = {$name}";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService();
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除仓库
	 */
	public function deleteWarehouse($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new WarehouseDAO($db);
		
		$warehouse = $dao->getWarehouseById($id);
		if (! $warehouse) {
			$db->rollback();
			return $this->bad("要删除的仓库不存在");
		}
		
		$rc = $dao->deleteWarehouse($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除仓库： 编码 = {$warehouse['code']}， 名称 = {$warehouse['name']}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	public function queryData($queryKey, $fid) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params = array(
				"loginUserId" => $us->getLoginUserId(),
				"queryKey" => $queryKey
		);
		
		$dao = new WarehouseDAO();
		return $dao->queryData($params);
	}

	/**
	 * 编辑数据域
	 */
	public function editDataOrg($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		$db->startTrans();
		
		$dao = new WarehouseDAO($db);
		$id = $params["id"];
		$dataOrg = $params["dataOrg"];
		$warehouse = $dao->getWarehouseById($id);
		$oldDataOrg = $warehouse["dataOrg"];
		$name = $warehouse["name"];
		
		$rc = $dao->editDataOrg($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "把仓库[{$name}]的数据域从旧值[{$oldDataOrg}]修改为新值[{$dataOrg}]";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}
<?php

namespace Home\Service;

use Home\DAO\SubjectDAO;

/**
 * 会计科目 Service
 *
 * @author 李静波
 */
class SubjectService extends PSIBaseExService {
	private $LOG_CATEGORY = "会计科目";

	/**
	 * 返回所有的公司列表
	 *
	 * @return array
	 */
	public function companyList() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = [
				"loginUserId" => $this->getLoginUserId()
		];
		
		$dao = new SubjectDAO($this->db());
		return $dao->companyList($params);
	}

	/**
	 * 某个公司的科目码列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function subjectList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new SubjectDAO($this->db());
		return $dao->subjectList($params);
	}

	/**
	 * 初始国家标准科目
	 */
	public function init($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$params["dataOrg"] = $this->getLoginUserDataOrg();
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new SubjectDAO($db);
		$rc = $dao->init($params, new PinyinService());
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$companyName = $params["companyName"];
		$log = "为[{$companyName}]初始化国家标准会计科目";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 新增或编辑会计科目
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function editSubject($params) {
		return $this->todo();
	}

	/**
	 * 上级科目字段 - 查询数据
	 *
	 * @param string $queryKey        	
	 */
	public function queryDataForParentSubject($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new SubjectDAO($this->db());
		return $dao->queryDataForParentSubject($queryKey);
	}
}
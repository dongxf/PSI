<?php

namespace Home\Service;

use Home\DAO\SubjectDAO;

/**
 * 会计科目 Service
 *
 * @author 李静波
 */
class SubjectService extends PSIBaseExService {

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
}
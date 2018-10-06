<?php

namespace Home\Service;

use Home\DAO\FormViewDAO;

/**
 * 表单视图Service
 *
 * @author 李静波
 */
class FormViewService extends PSIBaseExService {
	private $LOG_CATEGORY = "表单视图";

	/**
	 * 视图列表 - 开发助手
	 */
	public function fvListForDev() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new FormViewDAO($this->db());
		return $dao->fvListForDev();
	}

	/**
	 * 获得表单视图的标题
	 * 
	 * @param string $viewId        	
	 * @return string
	 */
	public function getTitle(string $viewId) {
		if ($this->isNotOnline()) {
			return "";
		}
		
		$dao = new FormViewDAO($this->db());
		return $dao->getTitle($viewId);
	}
}
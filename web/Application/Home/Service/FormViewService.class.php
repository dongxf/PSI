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

	/**
	 * 获得某个表单视图的全部元数据
	 */
	public function getFormViewMetaData(string $viewId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		// TODO：目前是每次都从数据里面查询，需要优化成从本地文件中缓存以减轻数据库压力
		
		$dao = new FormViewDAO($this->db());
		return $dao->getFormViewMetaData($viewId);
	}
}
<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\SubjectService;

/**
 * 会计科目Controller
 *
 * @author 李静波
 *        
 */
class SubjectController extends PSIBaseController {

	/**
	 * 会计科目 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::GL_SUBJECT)) {
			$this->initVar();
			
			$this->assign("title", "会计科目");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Subject/index");
		}
	}

	/**
	 * 返回所有的公司列表
	 */
	public function companyList() {
		if (IS_POST) {
			$service = new SubjectService();
			$this->ajaxReturn($service->companyList());
		}
	}
}
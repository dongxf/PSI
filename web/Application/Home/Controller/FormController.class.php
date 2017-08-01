<?php

namespace Home\Controller;

use Think\Controller;
use Home\Common\FIdConst;
use Home\Service\UserService;

/**
 * 自定义表单Controller
 *
 * @author 李静波
 *        
 */
class FormController extends PSIBaseController {

	/**
	 * 自定义表单 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::FORM_SYSTEM)) {
			$this->initVar();
			
			$this->assign("title", "自定义表单");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Form/index");
		}
	}
}
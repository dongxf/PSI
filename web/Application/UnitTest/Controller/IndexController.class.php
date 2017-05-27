<?php

namespace UnitTest\Controller;

use Home\Controller\PSIBaseController;
use Home\Service\UserService;
use Think\Controller;

/**
 * 单元测试首页Controller
 *
 * @author 李静波
 *        
 */
class IndexController extends PSIBaseController {

	/**
	 * 单元测试首页
	 */
	public function index() {
		if (! $this->canUnitTest()) {
			$this->gotoLoginPage();
			return;
		}
		
		$us = new UserService();
		
		if ($us->hasPermission()) {
			$this->initVar();
			
			$this->assign("title", "单元测试首页");
			
			$this->display();
		} else {
			$this->gotoLoginPage();
		}
	}
}
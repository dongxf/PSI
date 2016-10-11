<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;

/**
 * 关于Controller
 *
 * @author 李静波
 *        
 */
class AboutController extends PSIBaseController {

	/**
	 * 关于 - 主页面
	 */
	public function index() {
		$us = new UserService();
		if ($us->getLoginUserId()) {
			$this->initVar();
			
			$this->assign("title", "关于");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/About/index");
		}
	}
}
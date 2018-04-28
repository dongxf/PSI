<?php

namespace Mobile\Controller;

use Home\Service\UserService;
use Home\Service\BizConfigService;
use Home\Controller\PSIBaseController;

/**
 * 用户登录Controller
 *
 * @author 李静波
 *        
 */
class UserController extends PSIBaseController {

	/**
	 * 登录页面
	 */
	public function login() {
		if (session("loginUserId")) {
			// 已经登录了，就返回首页
			redirect(__ROOT__);
		}
		
		$this->initVar();
		
		$bcs = new BizConfigService();
		$productionName = $bcs->getProductionName();
		
		if ($productionName == "PSI") {
			$productionName .= " - 开源ERP";
		}
		
		$this->assign("productionName", $productionName);
		
		$this->assign("title", "登录");
		
		$this->assign("year", date("Y"));
		
		$us = new UserService();
		$this->assign("demoInfo", $us->getDemoLoginInfo());
		
		$this->display();
	}

	/**
	 * 用户登录，POST方法
	 */
	public function loginPOST() {
		if (IS_POST) {
			$ip = I("post.ip");
			$ipFrom = I("post.ipFrom");
			
			session("PSI_login_user_ip", $ip);
			session("PSI_login_user_ip_from", $ipFrom);
			
			$params = array(
					"loginName" => I("post.loginName"),
					"password" => I("post.password")
			);
			
			$us = new UserService();
			$this->ajaxReturn($us->doLogin($params));
		}
	}
}
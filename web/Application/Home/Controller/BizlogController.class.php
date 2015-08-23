<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\BizlogService;
use Home\Common\FIdConst;

/**
 * 业务日志Controller
 * 
 * @author 李静波
 *        
 */
class BizlogController extends Controller {

	/**
	 * 业务日志 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		$this->assign("title", "业务日志");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		if ($us->hasPermission(FIdConst::BIZ_LOG)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	/**
	 * 查询业务日志
	 */
	public function logList() {
		if (IS_POST) {
			$params = array(
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$bs = new BizlogService();
			$this->ajaxReturn($bs->logList($params));
		}
	}
}
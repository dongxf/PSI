<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\BizConfigService;
use Home\Common\FIdConst;

/**
 * 查看单据Controller
 * 
 * @author 李静波
 *        
 */
class BillController extends Controller {

	public function index() {
		$fid = I("get.fid");
		$refType = I("get.refType");
		$ref = I("get.ref");
		
		$this->assign("fid", $fid);
		$this->assign("refType", $refType);
		$this->assign("ref", $ref);
		
		$us = new UserService();
		
		$this->assign("title", "查看单据");
		$this->assign("uri", __ROOT__ . "/");
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		$this->display();
	}
}
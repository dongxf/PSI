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
		
		$this->assign("title", "查看单据");
		$this->assign("uri", __ROOT__ . "/");
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		$us = new UserService();
		
		$pm = "0";
		if ($fid == FIdConst::INVENTORY_QUERY) {
			$pm = $us->hasPermission($fid) ? "1": "0";
		}
		$this->assign("pm", $pm);
		
		$this->display();
	}
}
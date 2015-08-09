<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\SaleReportService;

/**
 * 报表Controller
 * 
 * @author 李静波
 *        
 */
class ReportController extends Controller {

	/**
	 * 销售日报表(按商品汇总)
	 */
	public function saleDayByGoods() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_GOODS)) {
			$this->assign("title", "销售日报表(按商品汇总)");
			$this->assign("uri", __ROOT__ . "/");
			
			$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
			$dtFlag = getdate();
			$this->assign("dtFlag", $dtFlag[0]);
				
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}
	
	public function saleDayByGoodsQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByGoodsQueryData($params));
		}
	}
}
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

	/**
	 * 销售日报表(按商品汇总) - 查询数据
	 */
	public function saleDayByGoodsQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByGoodsQueryData($params));
		}
	}

	/**
	 * 销售日报表(按商品汇总) - 查询汇总数据
	 */
	public function saleDayByGoodsSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByGoodsSummaryQueryData($params));
		}
	}
	
	/**
	 * 销售日报表(按客户汇总)
	 */
	public function saleDayByCustomer() {
		$us = new UserService();
	
		if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_CUSTOMER)) {
			$this->assign("title", "销售日报表(按客户汇总)");
			$this->assign("uri", __ROOT__ . "/");
				
			$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
			$dtFlag = getdate();
			$this->assign("dtFlag", $dtFlag[0]);
				
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	/**
	 * 销售日报表(按客户汇总) - 查询数据
	 */
	public function saleDayByCustomerQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByCustomerQueryData($params));
		}
	}
	
	/**
	 * 销售日报表(按客户汇总) - 查询汇总数据
	 */
	public function saleDayByCustomerSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt")
			);
				
			$rs = new SaleReportService();
				
			$this->ajaxReturn($rs->saleDayByCustomerSummaryQueryData($params));
		}
	}
	
}
<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\PayablesService;
use Home\Service\ReceivablesService;
use Home\Common\FIdConst;

/**
 * 资金Controller
 * @author 李静波
 *
 */
class FundsController extends Controller {

	public function payIndex() {
		$us = new UserService();

		$this->assign("title", "应付账款管理");
		$this->assign("uri", __ROOT__ . "/");

		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission(FIdConst::PAYABLES)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function payCategoryList() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id")
			);
			$ps = new PayablesService();
			$this->ajaxReturn($ps->payCategoryList($params));
		}
	}

	public function payList() {
		if (IS_POST) {
			$params = array(
				"caType" => I("post.caType"),
				"categoryId" => I("post.categoryId"),
				"page" => I("post.page"),
				"start" => I("post.start"),
				"limit" => I("post.limit")
			);
			$ps = new PayablesService();
			$this->ajaxReturn($ps->payList($params));
		}
	}

	public function payDetailList() {
		if (IS_POST) {
			$params = array(
				"caType" => I("post.caType"),
				"caId" => I("post.caId"),
				"page" => I("post.page"),
				"start" => I("post.start"),
				"limit" => I("post.limit")
			);
			$ps = new PayablesService();
			$this->ajaxReturn($ps->payDetailList($params));
		}
	}

	public function payRecordList() {
		if (IS_POST) {
			$params = array(
				"refType" => I("post.refType"),
				"refNumber" => I("post.refNumber"),
				"page" => I("post.page"),
				"start" => I("post.start"),
				"limit" => I("post.limit")
			);
			$ps = new PayablesService();
			$this->ajaxReturn($ps->payRecordList($params));
		}
	}

	public function payRecInfo() {
		if (IS_POST) {
			$us = new UserService();

			$this->ajaxReturn(array("bizUserId" => $us->getLoginUserId(),
				"bizUserName" => $us->getLoginUserName()));
		}
	}

	public function addPayment() {
		if (IS_POST) {
			$params = array(
				"refType" => I("post.refType"),
				"refNumber" => I("post.refNumber"),
				"bizDT" => I("post.bizDT"),
				"actMoney" => I("post.actMoney"),
				"bizUserId" => I("post.bizUserId"),
				"remark" => I("post.remark")
			);
			$ps = new PayablesService();
			$this->ajaxReturn($ps->addPayment($params));
		}
	}

	public function refreshPayInfo() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
			);
			$ps = new PayablesService();
			$this->ajaxReturn($ps->refreshPayInfo($params));
		}
	}

	public function refreshPayDetailInfo() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
			);
			$ps = new PayablesService();
			$this->ajaxReturn($ps->refreshPayDetailInfo($params));
		}
	}

	public function rvIndex() {
		$us = new UserService();

		$this->assign("title", "应收账款管理");
		$this->assign("uri", __ROOT__ . "/");

		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission(FIdConst::RECEIVING)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function rvCategoryList() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id")
			);
			$rs = new ReceivablesService();
			$this->ajaxReturn($rs->rvCategoryList($params));
		}
	}

	public function rvList() {
		if (IS_POST) {
			$params = array(
				"caType" => I("post.caType"),
				"categoryId" => I("post.categoryId"),
				"page" => I("post.page"),
				"start" => I("post.start"),
				"limit" => I("post.limit")
			);
			$rs = new ReceivablesService();
			$this->ajaxReturn($rs->rvList($params));
		}
	}

	public function rvDetailList() {
		if (IS_POST) {
			$params = array(
				"caType" => I("post.caType"),
				"caId" => I("post.caId"),
				"page" => I("post.page"),
				"start" => I("post.start"),
				"limit" => I("post.limit")
			);
			$rs = new ReceivablesService();
			$this->ajaxReturn($rs->rvDetailList($params));
		}
	}

	public function rvRecordList() {
		if (IS_POST) {
			$params = array(
				"refType" => I("post.refType"),
				"refNumber" => I("post.refNumber"),
				"page" => I("post.page"),
				"start" => I("post.start"),
				"limit" => I("post.limit")
			);
			$rs = new ReceivablesService();
			$this->ajaxReturn($rs->rvRecordList($params));
		}
	}

	public function rvRecInfo() {
		if (IS_POST) {
			$us = new UserService();

			$this->ajaxReturn(array("bizUserId" => $us->getLoginUserId(),
				"bizUserName" => $us->getLoginUserName()));
		}
	}
	
	public function addRvRecord() {
		if (IS_POST) {
			$params = array(
				"refType" => I("post.refType"),
				"refNumber" => I("post.refNumber"),
				"bizDT" => I("post.bizDT"),
				"actMoney" => I("post.actMoney"),
				"bizUserId" => I("post.bizUserId"),
				"remark" => I("post.remark")
			);
			$rs = new ReceivablesService();
			$this->ajaxReturn($rs->addRvRecord($params));
		}
	}
	
	public function refreshRvInfo() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
			);
			$rs = new ReceivablesService();
			$this->ajaxReturn($rs->refreshRvInfo($params));
		}
	}
	
	public function refreshRvDetailInfo() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
			);
			$rs = new ReceivablesService();
			$this->ajaxReturn($rs->refreshRvDetailInfo($params));
		}
	}
}

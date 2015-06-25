<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\WSBillService;
use Home\Service\SRBillService;

class SaleController extends Controller {

    public function wsIndex() {
        $us = new UserService();

        $this->assign("title", "销售出库");
        $this->assign("uri", __ROOT__ . "/");

        $this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
        $dtFlag = getdate();
        $this->assign("dtFlag", $dtFlag[0]);

        if ($us->hasPermission(FIdConst::WAREHOUSING_SALE)) {
            $this->display();
        } else {
            redirect(__ROOT__ . "/Home/User/login");
        }
    }

    public function wsBillInfo() {
        $params = array(
            "id" => I("post.id")
        );

        $ws = new WSBillService();
        $this->ajaxReturn($ws->wsBillInfo($params));
    }

    public function editWSBill() {
        $params = array(
            "jsonStr" => I("post.jsonStr")
        );

        $ws = new WSBillService();
        $this->ajaxReturn($ws->editWSBill($params));
    }

    public function wsbillList() {
        $params = array(
            "page" => I("post.page"),
            "start" => I("post.start"),
            "limit" => I("post.limit")
        );

        $ws = new WSBillService();
        $this->ajaxReturn($ws->wsbillList($params));
    }

    public function wsBillDetailList() {
        $params = array(
            "billId" => I("post.billId")
        );

        $ws = new WSBillService();
        $this->ajaxReturn($ws->wsBillDetailList($params));
    }

    public function deleteWSBill() {
        $params = array(
            "id" => I("post.id")
        );

        $ws = new WSBillService();
        $this->ajaxReturn($ws->deleteWSBill($params));
    }

    public function commitWSBill() {
        $params = array(
            "id" => I("post.id")
        );

        $ws = new WSBillService();
        $this->ajaxReturn($ws->commitWSBill($params));
    }

	public function srIndex() {
        $us = new UserService();

        $this->assign("title", "销售退货入库");
        $this->assign("uri", __ROOT__ . "/");

        $this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
        $dtFlag = getdate();
        $this->assign("dtFlag", $dtFlag[0]);

        if ($us->hasPermission(FIdConst::SALE_REJECTION)) {
            $this->display();
        } else {
            redirect(__ROOT__ . "/Home/User/login");
        }
	}
	
	public function srbillList() {
	    $params = array(
            "page" => I("post.page"),
            "start" => I("post.start"),
            "limit" => I("post.limit")
        );

        $ws = new SRBillService();
        $this->ajaxReturn($ws->srbillList($params));
	}
	
	public function srBillInfo() {
		$params = array(
            "id" => I("post.id")
        );

        $rs = new SRBillService();
        $this->ajaxReturn($rs->srBillInfo($params));
	}
	
	public function selectWSBillList() {
        $params = array(
            "page" => I("post.page"),
            "start" => I("post.start"),
            "limit" => I("post.limit")
        );

        $rs = new SRBillService();
        $this->ajaxReturn($rs->selectWSBillList($params));
    }

    /**
     * 新增或者编辑销售退货入库单
     */
	public function editSRBill() {
        $params = array(
            "jsonStr" => I("post.jsonStr")
        );

        $rs = new SRBillService();
        $this->ajaxReturn($rs->editSRBill($params));
    }
	
	public function getWSBillInfoForSRBill() {
		$params = array(
			"id" => I("post.id")
		);
		
        $rs = new SRBillService();
        $this->ajaxReturn($rs->getWSBillInfoForSRBill($params));
	}
}

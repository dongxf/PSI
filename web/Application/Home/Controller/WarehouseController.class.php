<?php
namespace Home\Controller;
use Think\Controller;

use Home\Service\UserService;
use Home\Service\WarehouseService;

use Home\Common\FIdConst;

class WarehouseController extends Controller {
    public function index(){
		$us = new UserService();
		
		$this->assign("title", "仓库");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoginUserName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission(FIdConst::WAREHOUSE)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
    }
	
	public function warehouseList() {
		if (IS_POST) {
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->warehouseList());
		}
	}
	
	public function editWarehouse() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
				"code" => I("post.code"),
				"name" => I("post.name")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->editWarehouse($params));
		}
	}
	
	public function deleteWarehouse() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->deleteWarehouse($params));
		}
	}
	
	public function queryData() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->queryData($queryKey));
		}
	}
}
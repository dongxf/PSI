<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\PortalService;

/**
 * Portal Controller
 *
 * @author 李静波
 *        
 */
class PortalController extends Controller {

	public function inventoryPortal() {
		if (IS_POST) {
			$ps = new PortalService();
			
			$this->ajaxReturn($ps->inventoryPortal());
		}
	}

	public function salePortal() {
		if (IS_POST) {
			$ps = new PortalService();
			
			$this->ajaxReturn($ps->salePortal());
		}
	}

	public function purchasePortal() {
		if (IS_POST) {
			$ps = new PortalService();
			
			$this->ajaxReturn($ps->purchasePortal());
		}
	}
	
	public function moneyPortal() {
		if (IS_POST) {
			$ps = new PortalService();
				
			$this->ajaxReturn($ps->moneyPortal());
		}
	}
}

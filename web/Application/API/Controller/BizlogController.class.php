<?php

namespace API\Controller;

use API\Service\BizlogApiService;
use Think\Controller;
use Home\Service\FIdService;

class BizlogController extends Controller {

	public function enterModule() {
		if (IS_POST) {
			$tokenId = I("post.tokenId");
			$fid = I("post.fid");
			$fromDevice = I("post.fromDevice");
			if (! $fromDevice) {
				$fromDevice = "移动端";
			}
			
			$fidService = new FIdService();
			$fidService->insertRecentFid($fid);
			$fidName = $fidService->getFIdName($fid);
			
			$service = new BizlogApiService();
			
			$log = "从{$fromDevice}进入模块：$fidName";
			
			$result = $service->insertBizlog($tokenId, $log);
			
			$this->ajaxReturn($result);
		}
	}
}
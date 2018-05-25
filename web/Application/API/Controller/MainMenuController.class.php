<?php

namespace API\Controller;

use Think\Controller;
use API\Service\MainMenuApiService;

class MainMenuController extends Controller {

	public function mainMenuItems() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId")
			];
			
			$service = new MainMenuApiService();
			
			$this->ajaxReturn($service->mainMenuItems($params));
		}
	}
}
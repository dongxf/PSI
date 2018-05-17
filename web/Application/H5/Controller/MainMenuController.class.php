<?php

namespace H5\Controller;

use Think\Controller;

class MainMenuController extends Controller {

	public function mainMenuItems() {
		if (IS_POST) {
			$result = [];
			$result[] = [
					"caption" => "销售",
					"items" => [[
							"caption" => "销售订单",
							"url"=>"#",
							"click"=>"todo"]
					]
			];
			
			$result[] =["caption"=>"关于",
					"items"=>[]
			];
			
			$this->ajaxReturn($result);
		}
	}
}
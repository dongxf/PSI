<?php

namespace H5\Controller;

use Think\Controller;
use H5\Service\MainMenuServiceH5;

class MainMenuController extends Controller {

	public function mainMenuItems() {
		if (IS_POST) {
			$ms = new MainMenuServiceH5();
			
			$this->ajaxReturn($ms->mainMenuItems());
		}
	}
}
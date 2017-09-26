<?php

namespace Home\Controller;

/**
 * 帮助Controller
 *
 * @author 李静波
 *        
 */
class HelpController extends PSIBaseController {

	public function index() {
		$key = I("get.t");
		switch ($key) {
			case "login" :
				redirect("/help/10.html");
				break;
			case "user" :
				redirect("/help/02-01.html");
				break;
			default :
				redirect("/help");
		}
	}
}
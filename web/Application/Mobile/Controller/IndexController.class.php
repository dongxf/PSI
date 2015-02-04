<?php
namespace Mobile\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){		
		$this->assign("title", "首页");
		$this->assign("uri", __ROOT__ . "/");
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		$this->display();
    }
	
	public function demo(){		
		$this->assign("title", "报表");
		$this->assign("uri", __ROOT__ . "/");
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		$this->display();
    }
}
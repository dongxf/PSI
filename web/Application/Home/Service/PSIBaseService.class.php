<?php

namespace Home\Service;

/**
 * Service 基类
 *
 * @author 李静波
 */
class PSIBaseService {
	protected function isDemo() {
		return getenv("IS_DEMO") == "1";
	}
	
	protected function isMOPAAS() {
		// 是否部署在 http://psi.oschina.mopaas.com
		return getenv("IS_MOPAAS") == "1";
	}

	protected function ok($id = null) {
		if ($id) {
			return array("success" => true, "id" => $id);
		} else {
			return array("success" => true);
		}
	}

	protected function bad($msg) {
		return array("success" => false, "msg" => $msg);
	}

	protected function todo($info = null) {
		if ($info) {
			return array("success" => false, "msg" => "TODO: 功能还没开发, 附加信息：$info");
		} else {
			return array("success" => false, "msg" => "TODO: 功能还没开发");
		}
	}
	
	protected function sqlError() {
		return $this->bad("数据库错误，请联系管理员");
	}
}

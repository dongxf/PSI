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
			return array(
					"success" => true,
					"id" => $id
			);
		} else {
			return array(
					"success" => true
			);
		}
	}

	protected function bad($msg) {
		return array(
				"success" => false,
				"msg" => $msg
		);
	}

	protected function todo($info = null) {
		if ($info) {
			return array(
					"success" => false,
					"msg" => "TODO: 功能还没开发, 附加信息：$info"
			);
		} else {
			return array(
					"success" => false,
					"msg" => "TODO: 功能还没开发"
			);
		}
	}

	protected function sqlError() {
		return $this->bad("数据库错误，请联系管理员");
	}

	/**
	 * 把时间类型格式化成类似2015-08-13的格式
	 */
	protected function toYMD($d) {
		return date("Y-m-d", strtotime($d));
	}

	/**
	 * 盘点当前用户的session是否已经失效
	 * true: 已经不在线
	 */
	protected function isNotOnline() {
		return session("loginUserId") == null;
	}

	/**
	 * 当用户不在线的时候，返回的提示信息
	 */
	protected function notOnlineError() {
		return $this->bad("当前用户已经退出系统，请重新登录PSI");
	}

	/**
	 * 返回空列表
	 */
	protected function emptyResult() {
		return array();
	}

	/**
	 * 盘点日期是否是正确的Y-m-d格式
	 * @param string $date
	 * @return boolean true: 是正确的格式
	 */
	protected function dateIsValid($date) {
		$dt = strtotime($date);
		if (! $dt) {
			return false;
		}
		
		return date("Y-m-d", $dt) == $date;
	}
}

<?php

namespace Home\Service;

/**
 * Service 扩展基类
 *
 * @author 李静波
 */
class PSIBaseExService extends PSIBaseService {

	/**
	 * 当前登录用户的id
	 */
	protected function getLoginUserId() {
		return session("loginUserId");
	}
}

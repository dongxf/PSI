<?php

namespace Home\DAO;

/**
 * 表单视图 DAO
 *
 * @author 李静波
 */
class FormViewDAO extends PSIBaseExDAO {

	/**
	 * 视图列表 - 开发助手
	 */
	public function fvListForDev() {
		$result = [		];
		$result[]=[
				"id"=>"1",
				"name"=>"测试视图"
		];
		$cnt = 1;
		return [
				"dataList" => $result,
				"totalCount" => $cnt
		];
	}
}
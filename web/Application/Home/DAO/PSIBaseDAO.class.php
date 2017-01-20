<?php

namespace Home\DAO;

/**
 * PSI DAO 基类
 *
 * @author 李静波
 */
class PSIBaseDAO {

	/**
	 * 操作失败
	 *
	 * @param string $msg
	 *        	错误信息
	 */
	protected function bad($msg) {
		return array(
				"success" => false,
				"msg" => $msg
		);
	}

	/**
	 * 数据库错误
	 */
	protected function sqlError($fileName, $codeLine) {
		$info = "数据库错误，请联系管理员<br />错误定位：{$fileName} - {$codeLine}行";
		return $this->bad($info);
	}

	/**
	 * 把时间类型格式化成类似2015-08-13的格式
	 */
	protected function toYMD($d) {
		return date("Y-m-d", strtotime($d));
	}
}
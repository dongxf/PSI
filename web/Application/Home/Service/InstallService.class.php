<?php

namespace Home\Service;

/**
 * 安装Service
 *
 * @author 李静波
 */
class InstallService extends PSIBaseService {
	
	/**
	 * 首次运行PSI的时候，自动初始化数据库(创建表和往表里面插入初始化数据)
	 */
	public function autoInstallWhenFirstRun() {
		$db = M();
		$tableName = "t_biz_log";
		
		// 用 t_biz_log 这个表是否存在 来判断是否已经初始化了数据库
		if ($this->tableExists($db, $tableName)) {
			return;
		}
	}
}
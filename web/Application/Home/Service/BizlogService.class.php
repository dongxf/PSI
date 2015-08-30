<?php

namespace Home\Service;

/**
 * 业务日志Service
 *
 * @author 李静波
 */
class BizlogService extends PSIBaseService {

	public function logList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		
		$sql = "select b.id, u.login_name, u.name, b.ip, b.info, b.date_created, b.log_category 
				from t_biz_log b, t_user u
				where b.user_id = u.id
				order by b.date_created desc
				limit %d, %d ";
		$data = $db->query($sql, $start, $limit);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["loginName"] = $v["login_name"];
			$result[$i]["userName"] = $v["name"];
			$result[$i]["ip"] = $v["ip"];
			$result[$i]["content"] = $v["info"];
			$result[$i]["dt"] = $v["date_created"];
			$result[$i]["logCategory"] = $v["log_category"];
		}
		
		$sql = "select count(*) as cnt 
				from t_biz_log b, t_user u
				where b.user_id = u.id";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		
		return array(
				"logs" => $result,
				"totalCount" => $cnt
		);
	}

	public function insertBizlog($log, $category = "系统") {
		try {
			$us = new UserService();
			if ($us->getLoginUserId() == null) {
				return;
			}
			
			$sql = "insert into t_biz_log (user_id, info, ip, date_created, log_category) 
					values ('%s', '%s', '%s',  now(), '%s')";
			M()->execute($sql, $us->getLoginUserId(), $log, $this->getClientIP(), $category);
		} catch ( Exception $ex ) {
		}
	}

	private function getClientIP() {
		if ($this->isMOPAAS()) {
			// 部署在http://psi.oschina.mopaas.com
			
			// 下面的代码参考：http://git.oschina.net/silentboy/testphp/blob/master/index.php
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			if ($ip) {
				$result = explode(",", $ip);
				if ($result) {
					return $result[0];
				}
			}
			
			if ($_SERVER["HTTP_CLIENT_IP"]) {
				$ip = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				$ip = $_SERVER["REMOTE_ADDR"];
			}
			
			if ($ip) {
				return $ip;
			}
		}
		
		return get_client_ip();
	}

	private function tableExists($db, $tableName) {
		$dbName = C('DB_NAME');
		$sql = "select count(*) as cnt
				from information_schema.columns
				where table_schema = '%s' 
					and table_name = '%s' ";
		$data = $db->query($sql, $dbName, $tableName);
		return $data[0]["cnt"] != 0;
	}

	private function columnExists($db, $tableName, $columnName) {
		$dbName = C('DB_NAME');
		
		$sql = "select count(*) as cnt
				from information_schema.columns
				where table_schema = '%s' 
					and table_name = '%s'
					and column_name = '%s' ";
		$data = $db->query($sql, $dbName, $tableName, $columnName);
		$cnt = $data[0]["cnt"];
		return $cnt == 1;
	}
	private $CURRENT_DB_VERSION = "20150830-007";

	public function updateDatabase() {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		
		// 检查t_psi_db_version是否存在
		if (! $this->tableExists($db, "t_psi_db_version")) {
			return $this->bad("表t_psi_db_db_version不存在，数据库结构实在是太久远了，无法升级");
		}
		
		// 检查t_psi_db_version中的版本号
		$sql = "select db_version from t_psi_db_version";
		$data = $db->query($sql);
		$dbVersion = $data[0]["db_version"];
		if ($dbVersion == $this->CURRENT_DB_VERSION) {
			return $this->bad("当前数据库是最新版本，不用升级");
		}
		
		$this->t_cash($db);
		$this->t_cash_detail($db);
		$this->t_config($db);
		$this->t_customer($db);
		$this->t_fid($db);
		$this->t_goods($db);
		$this->t_goods_si($db);
		$this->t_menu_item($db);
		$this->t_permission($db);
		$this->t_pr_bill($db);
		$this->t_pw_bill($db);
		$this->t_role_permission($db);
		$this->t_supplier($db);
		$this->t_sr_bill($db);
		$this->t_sr_bill_detail($db);
		$this->t_ws_bill($db);
		$this->t_ws_bill_detail($db);
		
		$sql = "delete from t_psi_db_version";
		$db->execute($sql);
		$sql = "insert into t_psi_db_version (db_version, update_dt) 
				values ('%s', now())";
		$db->execute($sql, $this->CURRENT_DB_VERSION);
		
		$this->insertBizlog("升级数据库，数据库版本 = " . $this->CURRENT_DB_VERSION);
		
		return $this->ok();
	}

	private function t_cash($db) {
		$tableName = "t_cash";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_cash` (
					  `id` bigint(20) NOT NULL AUTO_INCREMENT,
					  `biz_date` datetime NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
			return;
		}
	}

	private function t_cash_detail($db) {
		$tableName = "t_cash_detail";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_cash_detail` (
					  `id` bigint(20) NOT NULL AUTO_INCREMENT,
					  `biz_date` datetime NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `ref_number` varchar(255) NOT NULL,
					  `ref_type` varchar(255) NOT NULL,
					  `date_created` datetime NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
			return;
		}
	}

	private function t_config($db) {
		// 移走商品双单位
		$sql = "delete from t_config where id = '1001-01'";
		$db->execute($sql);
		
		// 9000-01
		$sql = "select count(*) as cnt from t_config where id = '9000-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-01', '公司名称', '', '', 100)";
			$db->execute($sql);
		}
		
		// 9000-02
		$sql = "select count(*) as cnt from t_config where id = '9000-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-02', '公司地址', '', '', 101)";
			$db->execute($sql);
		}
		
		// 9000-03
		$sql = "select count(*) as cnt from t_config where id = '9000-03' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-03', '公司电话', '', '', 102)";
			$db->execute($sql);
		}
		
		// 9000-04
		$sql = "select count(*) as cnt from t_config where id = '9000-04' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-04', '公司传真', '', '', 103)";
			$db->execute($sql);
		}
		
		// 9000-05
		$sql = "select count(*) as cnt from t_config where id = '9000-05' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-05', '公司邮编', '', '', 104)";
			$db->execute($sql);
		}
	}

	private function t_customer($db) {
		$tableName = "t_customer";
		
		$columnName = "address";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "address_shipping";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "address_receipt";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "bank_name";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "bank_account";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "tax_number";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "fax";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "note";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_goods($db) {
		$tableName = "t_goods";
		
		$columnName = "bar_code";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_fid($db) {
		// fid 2024: 现金收支查询
		$sql = "select count(*) as cnt from t_fid where fid = '2024' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2024', '现金收支查询')";
			$db->execute($sql);
		}
	}

	private function t_goods_si($db) {
		$tableName = "t_goods_si";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_goods_si` (
					  `id` varchar(255) NOT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `warehouse_id` varchar(255) NOT NULL,
					  `safety_inventory` decimal(19,2) NOT NULL,
					  `inventory_upper` decimal(19,2) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$db->execute($sql);
			return;
		}
		
		$columnName = "inventory_upper";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} decimal(19,2) default null;";
			$db->execute($sql);
		}
	}

	private function t_menu_item($db) {
		// fid 2024: 现金收支查询
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0603' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0603', '现金收支查询', '2024', '06', 3)";
			$db->execute($sql);
		}
	}

	private function t_pr_bill($db) {
		$tableName = "t_pr_bill";
		
		$columnName = "receiving_type";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) not null default 0;";
			$db->execute($sql);
		}
	}

	private function t_permission($db) {
		// fid 2024: 现金收支查询
		$sql = "select count(*) as cnt from t_permission where id = '2024' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2024', '2024', '现金收支查询', '现金收支查询')";
			$db->execute($sql);
		}
	}

	private function t_pw_bill($db) {
		$tableName = "t_pw_bill";
		
		$columnName = "payment_type";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) not null default 0;";
			$db->execute($sql);
		}
	}

	private function t_role_permission($db) {
		// fid 2024: 现金收支查询
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2024' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2024')";
			$db->execute($sql);
		}
	}

	private function t_supplier($db) {
		$tableName = "t_supplier";
		
		$columnName = "address";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "address_shipping";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "address_receipt";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "bank_name";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "bank_account";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "tax_number";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "fax";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "note";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_sr_bill($db) {
		$tableName = "t_sr_bill";
		
		$columnName = "payment_type";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) not null default 0;";
			$db->execute($sql);
		}
	}

	private function t_sr_bill_detail($db) {
		$tableName = "t_sr_bill_detail";
		
		$columnName = "sn_note";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_ws_bill($db) {
		$tableName = "t_ws_bill";
		
		$columnName = "receiving_type";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) not null default 0;";
			$db->execute($sql);
		}
	}

	private function t_ws_bill_detail($db) {
		$tableName = "t_ws_bill_detail";
		
		$columnName = "sn_note";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}
}
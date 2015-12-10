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
		
		$this->createTables();
	}

	private function createTables() {
		$db = M();
		
		// t_biz_log
		$sql = "CREATE TABLE IF NOT EXISTS `t_biz_log` (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `date_created` datetime DEFAULT NULL,
				  `info` varchar(1000) NOT NULL,
				  `ip` varchar(255) NOT NULL,
				  `user_id` varchar(255) NOT NULL,
				  `log_category` varchar(50) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `ip_from` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		$db->execute($sql);
		
		// t_fid
		$sql = "CREATE TABLE IF NOT EXISTS `t_fid` (
				  `fid` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				";
		$db->execute($sql);
		
		// t_menu_item
		$sql = "CREATE TABLE IF NOT EXISTS `t_menu_item` (
				  `id` varchar(255) NOT NULL,
				  `caption` varchar(255) NOT NULL,
				  `fid` varchar(255) DEFAULT NULL,
				  `parent_id` varchar(255) DEFAULT NULL,
				  `show_order` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_org
		$sql = "CREATE TABLE IF NOT EXISTS `t_org` (
				  `id` varchar(255) NOT NULL,
				  `full_name` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `org_code` varchar(255) NOT NULL,
				  `parent_id` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_permission
		$sql = "CREATE TABLE IF NOT EXISTS `t_permission` (
				  `id` varchar(255) NOT NULL,
				  `fid` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `note` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_recent_fid
		$sql = "CREATE TABLE IF NOT EXISTS `t_recent_fid` (
				  `fid` varchar(255) NOT NULL,
				  `user_id` varchar(255) NOT NULL,
				  `click_count` int(11) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_role
		$sql = "CREATE TABLE IF NOT EXISTS `t_role` (
				  `id` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_role_permission
		$sql = "CREATE TABLE IF NOT EXISTS `t_role_permission` (
				  `role_id` varchar(255) DEFAULT NULL,
				  `permission_id` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_role_user
		$sql = "CREATE TABLE IF NOT EXISTS `t_role_user` (
				  `role_id` varchar(255) DEFAULT NULL,
				  `user_id` varchar(255) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_user
		$sql = "CREATE TABLE IF NOT EXISTS `t_user` (
				  `id` varchar(255) NOT NULL,
				  `enabled` int(11) NOT NULL,
				  `login_name` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `org_id` varchar(255) NOT NULL,
				  `org_code` varchar(255) NOT NULL,
				  `password` varchar(255) NOT NULL,
				  `py` varchar(255) DEFAULT NULL,
				  `gender` varchar(255) DEFAULT NULL,
				  `birthday` varchar(255) DEFAULT NULL,
				  `id_card_number` varchar(255) DEFAULT NULL,
				  `tel` varchar(255) DEFAULT NULL,
				  `tel02` varchar(255) DEFAULT NULL,
				  `address` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_warehouse
		$sql = "CREATE TABLE IF NOT EXISTS `t_warehouse` (
				  `id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `inited` int(11) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `py` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_warehouse_org
		$sql = "CREATE TABLE IF NOT EXISTS `t_warehouse_org` (
				  `warehouse_id` varchar(255) DEFAULT NULL,
				  `org_id` varchar(255) DEFAULT NULL,
				  `org_type` varchar(255) DEFAULT NULL,
				  `bill_fid` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_supplier
		$sql = "CREATE TABLE IF NOT EXISTS `t_supplier` (
				  `id` varchar(255) NOT NULL,
				  `category_id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `contact01` varchar(255) DEFAULT NULL,
				  `qq01` varchar(255) DEFAULT NULL,
				  `tel01` varchar(255) DEFAULT NULL,
				  `mobile01` varchar(255) DEFAULT NULL,
				  `contact02` varchar(255) DEFAULT NULL,
				  `qq02` varchar(255) DEFAULT NULL,
				  `tel02` varchar(255) DEFAULT NULL,
				  `mobile02` varchar(255) DEFAULT NULL,
				  `address` varchar(255) DEFAULT NULL,
				  `address_shipping` varchar(255) DEFAULT NULL,
				  `address_receipt` varchar(255) DEFAULT NULL,
				  `py` varchar(255) DEFAULT NULL,
				  `init_receivables` decimal(19,2) DEFAULT NULL, 
				  `init_receivables_dt` datetime DEFAULT NULL, 
				  `init_payables` decimal(19,2) DEFAULT NULL, 
				  `init_payables_dt` datetime DEFAULT NULL, 
				  `bank_name` varchar(255) DEFAULT NULL,
				  `bank_account` varchar(255) DEFAULT NULL,
				  `tax_number` varchar(255) DEFAULT NULL,
				  `fax` varchar(255) DEFAULT NULL,
				  `note` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_supplier_category
		$sql = "CREATE TABLE IF NOT EXISTS `t_supplier_category` (
				  `id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `parent_id` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
	}
}
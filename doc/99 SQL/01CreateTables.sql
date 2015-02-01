/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `t_biz_log`;
CREATE TABLE IF NOT EXISTS `t_biz_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date_created` datetime DEFAULT NULL,
  `info` varchar(1000) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `log_category` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `t_fid`;
CREATE TABLE IF NOT EXISTS `t_fid` (
  `fid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_menu_item`;
CREATE TABLE IF NOT EXISTS `t_menu_item` (
  `id` varchar(255) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `fid` varchar(255) DEFAULT NULL,
  `parent_id` varchar(255) DEFAULT NULL,
  `show_order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_org`;
CREATE TABLE IF NOT EXISTS `t_org` (
  `id` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `org_code` varchar(255) NOT NULL,
  `parent_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_permission`;
CREATE TABLE IF NOT EXISTS `t_permission` (
  `id` varchar(255) NOT NULL,
  `fid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_recent_fid`;
CREATE TABLE IF NOT EXISTS `t_recent_fid` (
  `fid` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `click_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_role`;
CREATE TABLE IF NOT EXISTS `t_role` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_role_permission`;
CREATE TABLE IF NOT EXISTS `t_role_permission` (
  `role_id` varchar(255) DEFAULT NULL,
  `permission_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_role_user`;
CREATE TABLE IF NOT EXISTS `t_role_user` (
  `role_id` varchar(255) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_user`;
CREATE TABLE IF NOT EXISTS `t_user` (
  `id` varchar(255) NOT NULL,
  `enabled` int(11) NOT NULL,
  `login_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `org_id` varchar(255) NOT NULL,
  `org_code` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `py` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_warehouse`;
CREATE TABLE IF NOT EXISTS `t_warehouse` (
  `id` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `inited` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `py` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `t_supplier`;
CREATE TABLE IF NOT EXISTS `t_supplier` (
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
  `py` varchar(255) DEFAULT NULL,
  `init_receivables` decimal(19,2) DEFAULT NULL, 
  `init_receivables_dt` datetime DEFAULT NULL, 
  `init_payables` decimal(19,2) DEFAULT NULL, 
  `init_payables_dt` datetime DEFAULT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_supplier_category`;
CREATE TABLE IF NOT EXISTS `t_supplier_category` (
  `id` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_goods`;
CREATE TABLE IF NOT EXISTS `t_goods` (
  `id` varchar(255) NOT NULL,
  `category_id` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sale_price` decimal(19,2) NOT NULL,
  `spec` varchar(255) NOT NULL,
  `unit_id` varchar(255) NOT NULL,
  `py` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_goods_category`;
CREATE TABLE IF NOT EXISTS `t_goods_category` (
  `id` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_goods_unit`;
CREATE TABLE IF NOT EXISTS `t_goods_unit` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_customer`;
CREATE TABLE IF NOT EXISTS `t_customer` (
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
  `py` varchar(255) DEFAULT NULL,
  `init_receivables` decimal(19,2) DEFAULT NULL, 
  `init_receivables_dt` datetime DEFAULT NULL, 
  `init_payables` decimal(19,2) DEFAULT NULL, 
  `init_payables_dt` datetime DEFAULT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_customer_category`;
CREATE TABLE IF NOT EXISTS `t_customer_category` (
  `id` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `t_invertory`;
CREATE TABLE IF NOT EXISTS `t_invertory` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `balance_count` int(11) NOT NULL,
  `balance_money` decimal(19,2) NOT NULL,
  `balance_price` decimal(19,2) NOT NULL,
  `goods_id` varchar(255) NOT NULL,
  `in_count` int(11) DEFAULT NULL,
  `in_money` decimal(19,2) DEFAULT NULL,
  `in_price` decimal(19,2) DEFAULT NULL,
  `out_count` int(11) DEFAULT NULL,
  `out_money` decimal(19,2) DEFAULT NULL,
  `out_price` decimal(19,2) DEFAULT NULL,
  `warehouse_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `t_invertory_detail`;
CREATE TABLE IF NOT EXISTS `t_invertory_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `balance_count` int(11) NOT NULL,
  `balance_money` decimal(19,2) NOT NULL,
  `balance_price` decimal(19,2) NOT NULL,
  `biz_date` datetime NOT NULL,
  `biz_user_id` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `goods_id` varchar(255) NOT NULL,
  `in_count` int(11) DEFAULT NULL,
  `in_money` decimal(19,2) DEFAULT NULL,
  `in_price` decimal(19,2) DEFAULT NULL,
  `out_count` int(11) DEFAULT NULL,
  `out_money` decimal(19,2) DEFAULT NULL,
  `out_price` decimal(19,2) DEFAULT NULL,
  `ref_number` varchar(255) DEFAULT NULL,
  `ref_type` varchar(255) NOT NULL,
  `warehouse_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `t_pw_bill`;
CREATE TABLE IF NOT EXISTS `t_pw_bill` (
  `id` varchar(255) NOT NULL,
  `bill_status` int(11) NOT NULL,
  `biz_dt` datetime NOT NULL,
  `biz_user_id` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `goods_money` decimal(19,2) NOT NULL,
  `input_user_id` varchar(255) NOT NULL,
  `ref` varchar(255) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `warehouse_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_pw_bill_detail`;
CREATE TABLE IF NOT EXISTS `t_pw_bill_detail` (
  `id` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `goods_id` varchar(255) NOT NULL,
  `goods_count` int(11) NOT NULL,
  `goods_money` decimal(19,2) NOT NULL,
  `goods_price` decimal(19,2) NOT NULL,
  `pwbill_id` varchar(255) NOT NULL,
  `show_order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `t_payables`;
CREATE TABLE IF NOT EXISTS `t_payables` (
  `id` varchar(255) NOT NULL,
  `act_money` decimal(19,2) NOT NULL,
  `balance_money` decimal(19,2) NOT NULL,
  `ca_id` varchar(255) NOT NULL,
  `ca_type` varchar(255) NOT NULL,
  `pay_money` decimal(19,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_payables_detail`;
CREATE TABLE IF NOT EXISTS `t_payables_detail` (
  `id` varchar(255) NOT NULL,
  `act_money` decimal(19,2) NOT NULL,
  `balance_money` decimal(19,2) NOT NULL,
  `ca_id` varchar(255) NOT NULL,
  `ca_type` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `pay_money` decimal(19,2) NOT NULL,
  `ref_number` varchar(255) NOT NULL,
  `ref_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_receivables`;
CREATE TABLE IF NOT EXISTS `t_receivables` (
  `id` varchar(255) NOT NULL,
  `act_money` decimal(19,2) NOT NULL,
  `balance_money` decimal(19,2) NOT NULL,
  `ca_id` varchar(255) NOT NULL,
  `ca_type` varchar(255) NOT NULL,
  `rv_money` decimal(19,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_receivables_detail`;
CREATE TABLE IF NOT EXISTS `t_receivables_detail` (
  `id` varchar(255) NOT NULL,
  `act_money` decimal(19,2) NOT NULL,
  `balance_money` decimal(19,2) NOT NULL,
  `ca_id` varchar(255) NOT NULL,
  `ca_type` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `ref_number` varchar(255) NOT NULL,
  `ref_type` varchar(255) NOT NULL,
  `rv_money` decimal(19,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `t_payment`;
CREATE TABLE IF NOT EXISTS `t_payment` (
  `id` varchar(255) NOT NULL,
  `act_money` decimal(19,2) NOT NULL,
  `biz_date` datetime NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `input_user_id` varchar(255) NOT NULL,
  `pay_user_id` varchar(255) NOT NULL,
  `bill_id` varchar(255) NOT NULL,
  `ref_type` varchar(255) NOT NULL,
  `ref_number` varchar(255) NOT NULL,
  `remark` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_ws_bill`;
CREATE TABLE IF NOT EXISTS `t_ws_bill` (
  `id` varchar(255) NOT NULL,
  `bill_status` int(11) NOT NULL,
  `bizdt` datetime NOT NULL,
  `biz_user_id` varchar(255) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `input_user_id` varchar(255) NOT NULL,
  `invertory_money` decimal(19,2) DEFAULT NULL,
  `profit` decimal(19,2) DEFAULT NULL,
  `ref` varchar(255) NOT NULL,
  `sale_money` decimal(19,2) DEFAULT NULL,
  `warehouse_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `t_ws_bill_detail`;
CREATE TABLE IF NOT EXISTS `t_ws_bill_detail` (
  `id` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `goods_id` varchar(255) NOT NULL,
  `goods_count` int(11) NOT NULL,
  `goods_money` decimal(19,2) NOT NULL,
  `goods_price` decimal(19,2) NOT NULL,
  `invertory_money` decimal(19,2) DEFAULT NULL,
  `invertory_price` decimal(19,2) DEFAULT NULL,
  `show_order` int(11) NOT NULL,
  `wsbill_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `t_receiving`;
CREATE TABLE IF NOT EXISTS `t_receiving` (
  `id` varchar(255) NOT NULL,
  `act_money` decimal(19,2) NOT NULL,
  `biz_date` datetime NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `input_user_id` varchar(255) NOT NULL,
  `remark` varchar(255) NOT NULL,
  `rv_user_id` varchar(255) NOT NULL,
  `bill_id` varchar(255) NOT NULL,
  `ref_number` varchar(255) NOT NULL,
  `ref_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

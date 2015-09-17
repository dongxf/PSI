<?php

namespace Home\Common;

/**
 * FId常数值
 *
 * @author 李静波
 */
class FIdConst {
	
	/**
	 * 首页
	 */
	const HOME = "-9997";
	
	/**
	 * 重新登录
	 */
	const RELOGIN = "-9999";
	
	/**
	 * 修改我的密码
	 */
	const CHANGE_MY_PASSWORD = "-9996";
	
	/**
	 * 使用帮助
	 */
	const HELP = "-9995";
	
	/**
	 * 关于
	 */
	const ABOUT = "-9994";
	
	/**
	 * 购买商业服务
	 */
	const PSI_SERVICE = "-9993";
	
	/**
	 * 用户管理
	 */
	const USR_MANAGEMENT = "-8999";
	
	/**
	 * 权限管理
	 */
	const PERMISSION_MANAGEMENT = "-8996";
	
	/**
	 * 业务日志
	 */
	const BIZ_LOG = "-8997";
	
	/**
	 * 基础数据-仓库
	 */
	const WAREHOUSE = "1003";
	
	/**
	 * 基础数据-供应商档案
	 */
	const SUPPLIER = "1004";
	
	/**
	 * 基础数据-商品
	 */
	const GOODS = "1001";
	
	/**
	 * 基础数据-商品计量单位
	 */
	const GOODS_UNIT = "1002";
	
	/**
	 * 客户资料
	 */
	const CUSTOMER = "1007";
	
	/**
	 * 库存建账
	 */
	const INVENTORY_INIT = "2000";
	
	/**
	 * 采购入库
	 */
	const PURCHASE_WAREHOUSE = "2001";
	
	/**
	 * 库存账查询
	 */
	const INVENTORY_QUERY = "2003";
	
	/**
	 * 应付账款管理
	 */
	const PAYABLES = "2005";
	
	/**
	 * 应收账款管理
	 */
	const RECEIVING = "2004";
	
	/**
	 * 销售出库
	 */
	const WAREHOUSING_SALE = "2002";
	
	/**
	 * 销售退货入库
	 */
	const SALE_REJECTION = "2006";
	
	/**
	 * 业务设置
	 */
	const BIZ_CONFIG = "2008";
	
	/**
	 * 库间调拨
	 */
	const INVENTORY_TRANSFER = "2009";
	
	/**
	 * 库存盘点
	 */
	const INVENTORY_CHECK = "2010";
	
	/**
	 * 采购退货出库
	 */
	const PURCHASE_REJECTION = "2007";
	
	/**
	 * 首页-销售看板
	 */
	const PORTAL_SALE = "2011-01";
	
	/**
	 * 首页-库存看板
	 */
	const PORTAL_INVENTORY = "2011-02";
	
	/**
	 * 首页-采购看板
	 */
	const PORTAL_PURCHASE = "2011-03";
	
	/**
	 * 首页-资金看板
	 */
	const PORTAL_MONEY = "2011-04";
	
	/**
	 * 销售日报表(按商品汇总)
	 */
	const REPORT_SALE_DAY_BY_GOODS = "2012";
	
	/**
	 * 销售日报表(按客户汇总)
	 */
	const REPORT_SALE_DAY_BY_CUSTOMER = "2013";
	
	/**
	 * 销售日报表(按仓库汇总)
	 */
	const REPORT_SALE_DAY_BY_WAREHOUSE = "2014";
	
	/**
	 * 销售日报表(按业务员汇总)
	 */
	const REPORT_SALE_DAY_BY_BIZUSER = "2015";
	
	/**
	 * 销售月报表(按商品汇总)
	 */
	const REPORT_SALE_MONTH_BY_GOODS = "2016";
	
	/**
	 * 销售月报表(按客户汇总)
	 */
	const REPORT_SALE_MONTH_BY_CUSTOMER = "2017";
	
	/**
	 * 销售月报表(按仓库汇总)
	 */
	const REPORT_SALE_MONTH_BY_WAREHOUSE = "2018";
	
	/**
	 * 销售月报表(按业务员汇总)
	 */
	const REPORT_SALE_MONTH_BY_BIZUSER = "2019";
	
	/**
	 * 安全库存明细表
	 */
	const REPORT_SAFETY_INVENTORY = "2020";
	
	/**
	 * 应收账款账龄分析表
	 */
	const REPORT_RECEIVABLES_AGE = "2021";
	
	/**
	 * 应付账款账龄分析表
	 */
	const REPORT_PAYABLES_AGE = "2022";
	
	/**
	 * 库存超上限明细表
	 */
	const REPORT_INVENTORY_UPPER = "2023";
	
	/**
	 * 现金收支查询
	 */
	const CASH_INDEX = "2024";
	
	/**
	 * 预收款管理
	 */
	const PRE_RECEIVING = "2025";
	
	/**
	 * 预付款管理
	 */
	const PRE_PAYMENT = "2026";
	
	/**
	 * 采购订单
	 */
	const PURCHASE_ORDER = "2027";
	
	/**
	 * 采购订单 - 审核
	 */
	const PURCHASE_ORDER_CONFIRM = "2027-01";
	
	/**
	 * 采购订单 - 生成采购入库单
	 */
	const PURCHASE_ORDER_GEN_PWBILL = "2027-02";
}

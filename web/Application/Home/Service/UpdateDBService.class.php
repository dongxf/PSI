<?php

namespace Home\Service;

use Home\Common\FIdConst;
use Think\Think;

/**
 * 数据库升级Service
 *
 * @author 李静波
 */
class UpdateDBService extends PSIBaseService {
	
	/**
	 *
	 * @var \Think\Model
	 */
	private $db;

	public function updateDatabase() {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		
		$this->db = $db;
		
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
		$this->t_goods_category($db);
		$this->t_goods_si($db);
		$this->t_menu_item($db);
		$this->t_permission($db);
		$this->t_po_bill($db);
		$this->t_po_bill_detail($db);
		$this->t_po_pw($db);
		$this->t_pr_bill($db);
		$this->t_pre_payment($db);
		$this->t_pre_payment_detail($db);
		$this->t_pre_receiving($db);
		$this->t_pre_receiving_detail($db);
		$this->t_pw_bill($db);
		$this->t_role_permission($db);
		$this->t_supplier($db);
		$this->t_supplier_category($db);
		$this->t_sr_bill($db);
		$this->t_sr_bill_detail($db);
		$this->t_ws_bill($db);
		$this->t_ws_bill_detail($db);
		
		$this->update_20151016_01($db);
		$this->update_20151031_01($db);
		$this->update_20151102_01($db);
		$this->update_20151105_01($db);
		$this->update_20151106_01($db);
		$this->update_20151106_02($db);
		$this->update_20151108_01($db);
		$this->update_20151110_01($db);
		$this->update_20151110_02($db);
		$this->update_20151111_01($db);
		$this->update_20151112_01($db);
		$this->update_20151113_01($db);
		$this->update_20151119_01($db);
		$this->update_20151119_03($db);
		$this->update_20151121_01($db);
		$this->update_20151123_01($db);
		$this->update_20151123_02($db);
		$this->update_20151123_03($db);
		$this->update_20151124_01($db);
		$this->update_20151126_01($db);
		$this->update_20151127_01($db);
		$this->update_20151128_01($db);
		$this->update_20151128_02($db);
		$this->update_20151128_03($db);
		$this->update_20151210_01($db);
		$this->update_20160105_01($db);
		$this->update_20160105_02($db);
		$this->update_20160108_01($db);
		$this->update_20160112_01($db);
		$this->update_20160116_01($db);
		$this->update_20160116_02($db);
		$this->update_20160118_01($db);
		$this->update_20160119_01($db);
		$this->update_20160120_01($db);
		$this->update_20160219_01($db);
		$this->update_20160301_01($db);
		$this->update_20160303_01($db);
		$this->update_20160314_01($db);
		$this->update_20160620_01($db);
		$this->update_20160722_01($db);
		
		$this->update_20170405_01($db);
		$this->update_20170408_01($db);
		$this->update_20170412_01($db);
		$this->update_20170412_02($db);
		$this->update_20170503_01($db);
		$this->update_20170515_01($db);
		$this->update_20170519_01($db);
		$this->update_20170530_01($db);
		$this->update_20170604_01($db);
		
		$this->update_20170606_01();
		$this->update_20170606_02();
		$this->update_20170606_03();
		$this->update_20170607_01();
		$this->update_20170609_02();
		$this->update_20170927_01();
		$this->update_20171101_01();
		$this->update_20171102_01();
		$this->update_20171102_02();
		$this->update_20171113_01();
		$this->update_20171208_01();
		$this->update_20171214_01();
		$this->update_20171226_01();
		$this->update_20171227_01();
		$this->update_20171229_01();
		
		$this->update_20180101_01();
		$this->update_20180111_01();
		$this->update_20180115_01();
		$this->update_20180117_01();
		$this->update_20180117_02();
		$this->update_20180119_01();
		$this->update_20180119_02();
		$this->update_20180125_01();
		$this->update_20180130_01();
		$this->update_20180201_01();
		$this->update_20180202_01();
		$this->update_20180203_01();
		$this->update_20180203_02();
		$this->update_20180203_03();
		$this->update_20180219_01();
		$this->update_20180305_01();
		$this->update_20180306_01();
		$this->update_20180306_02();
		$this->update_20180307_01();
		$this->update_20180313_01();
		$this->update_20180313_02();
		$this->update_20180314_01();
		$this->update_20180314_02();
		$this->update_20180316_01();
		$this->update_20180406_01();
		$this->update_20180410_01();
		$this->update_20180501_01();
		$this->update_20180501_02();
		$this->update_20180502_01();
		$this->update_20180502_02();
		$this->update_20180502_03();
		$this->update_20180502_04();
		$this->update_20180503_01();
		$this->update_20180503_02();
		$this->update_20180503_03();
		
		$sql = "delete from t_psi_db_version";
		$db->execute($sql);
		$sql = "insert into t_psi_db_version (db_version, update_dt) 
				values ('%s', now())";
		$db->execute($sql, $this->CURRENT_DB_VERSION);
		
		$bl = new BizlogService();
		$bl->insertBizlog("升级数据库，数据库版本 = " . $this->CURRENT_DB_VERSION);
		
		return $this->ok();
	}

	// ============================================
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// 注意：
	// 如果修改了数据库结构，别忘记了在InstallService中修改相应的SQL语句
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// ============================================
	private function notForgot() {
	}

	private function update_20180503_03() {
		// 本次更新：新增权限：商品-设置价格体系
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "商品";
		
		$fid = FIdConst::PRICE_SYSTEM_SETTING_GOODS;
		$name = "商品-设置商品价格体系";
		$note = "按钮权限：商品模块[设置商品价格体系]按钮权限";
		$showOrder = 701;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180503_02() {
		// 本次更新：新增权限 销售退货入库-打印
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "销售退货入库";
		
		$fid = FIdConst::SALE_REJECTION_PRINT;
		$name = "销售退货入库-打印";
		$note = "按钮权限：销售退货入库模块[打印预览]和[直接打印]按钮权限";
		$showOrder = 206;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180503_01() {
		// 本次更新：新增权限 销售出库-打印
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "销售出库";
		
		$fid = FIdConst::WAREHOUSING_SALE_PRINT;
		$name = "销售出库-打印";
		$note = "按钮权限：销售出库模块[打印预览]和[直接打印]按钮权限";
		$showOrder = 207;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180502_04() {
		// 本次更新：新增权限 销售订单-打印
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "销售订单";
		
		$fid = FIdConst::SALE_ORDER_PRINT;
		$name = "销售订单-打印";
		$note = "按钮权限：销售订单模块[打印预览]和[直接打印]按钮权限";
		$showOrder = 207;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180502_03() {
		// 本次更新：新增权限 库存盘点-打印
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "库存盘点";
		
		$fid = FIdConst::INVENTORY_CHECK_PRINT;
		$name = "库存盘点-打印";
		$note = "按钮权限：库存盘点模块[打印预览]和[直接打印]按钮权限";
		$showOrder = 206;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180502_02() {
		// 本次更新：新增权限 - 库间调拨-打印
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "库间调拨";
		
		$fid = FIdConst::INVENTORY_TRANSFER_PRINT;
		$name = "库间调拨-打印";
		$note = "按钮权限：库间调拨模块[打印预览]和[直接打印]按钮权限";
		$showOrder = 206;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180502_01() {
		// 本次更新：新增权限 - 采购退货出库打印
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "采购退货出库";
		
		$fid = FIdConst::PURCHASE_REJECTION_PRINT;
		$name = "采购退货出库-打印";
		$note = "按钮权限：采购退货出库模块[打印预览]和[直接打印]按钮权限";
		$showOrder = 206;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180501_02() {
		// 本次更新：新增权限 - 采购入库单打印
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "采购入库";
		
		$fid = FIdConst::PURCHASE_WAREHOUSE_PRINT;
		$name = "采购入库-打印";
		$note = "按钮权限：采购入库模块[打印预览]和[直接打印]按钮权限";
		$showOrder = 207;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180501_01() {
		// 本次更新：新增权限 - 采购订单打印
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "采购订单";
		
		$fid = FIdConst::PURCHASE_ORDER_PRINT;
		$name = "采购订单-打印";
		$note = "按钮权限：采购订单模块[打印预览]和[直接打印]按钮权限";
		$showOrder = 208;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180410_01() {
		// 本次更新：新增权限 - 采购入库金额和单价可见
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "采购入库";
		
		$fid = FIdConst::PURCHASE_WAREHOUSE_CAN_VIEW_PRICE;
		$name = "采购入库 - 采购单价和金额可见";
		$note = "字段权限：采购入库单的采购单价和金额可以被用户查看";
		$showOrder = 206;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180406_01() {
		// 本次更新：新增表t_bank_account
		$db = $this->db;
		$tableName = "t_bank_account";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_bank_account` (
					  `id` varchar(255) NOT NULL,
					  `bank_name` varchar(255) NOT NULL,
					  `bank_number` varchar(255) NOT NULL,
					  `memo` varchar(255) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `data_org` varchar(255) NOT NULL,
					  `company_id` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";
			$db->execute($sql);
		}
	}

	private function update_20180316_01() {
		// 本次更新： t_goods_bom商品数量改为decimal(19,8)
		$tableName = "t_goods_bom";
		
		$fieldName = "sub_goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	private function update_20180314_02() {
		// 本次更新：t_sr_bill_detail商品数量改为decimal(19,8)
		$tableName = "t_sr_bill_detail";
		
		$fieldName = "goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		$fieldName = "rejection_goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	private function update_20180314_01() {
		// 本次更新：t_ws_bill_detail商品数量改为decimal(19,8)
		$tableName = "t_ws_bill_detail";
		
		$fieldName = "goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	private function update_20180313_02() {
		// 本次更新：t_ic_bill_detail商品数量改为decimal(19,8)
		$tableName = "t_ic_bill_detail";
		
		$fieldName = "goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	private function update_20180313_01() {
		// 本次更新：t_it_bill_detail商品数量改为decimal(19,8)
		$tableName = "t_it_bill_detail";
		
		$fieldName = "goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	private function update_20180307_01() {
		// 本次更新：t_pr_bill_detail商品数量字段改为decimal(19,8)
		$tableName = "t_pr_bill_detail";
		
		$fieldName = "goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		$fieldName = "rejection_goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	private function update_20180306_02() {
		// 本次更新：t_pw_bill_detail商品数量字段改为decimal(19,8)
		$tableName = "t_pw_bill_detail";
		
		$fieldName = "goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	private function update_20180306_01() {
		// 本次更新：t_inventory、t_inventory_detail中商品数量字段改为decimal(19, 8)
		$tableName = "t_inventory";
		
		$fieldName = "balance_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		$fieldName = "in_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		$fieldName = "out_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		$fieldName = "afloat_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		
		$tableName = "t_inventory_detail";
		
		$fieldName = "balance_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		$fieldName = "in_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		$fieldName = "out_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	private function update_20180305_01() {
		// 本次更新：修改t_po_bill_detail的字段goods_count、pw_count、left_count类型为decimal(19, 8)
		$tableName = "t_po_bill_detail";
		
		$fieldName = "goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		
		$fieldName = "pw_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		
		$fieldName = "left_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	private function update_20180219_01() {
		// 本次更新：修改t_so_bill_detail的字段goods_count、ws_count、left_count类型为decimal(19, 8)
		$tableName = "t_so_bill_detail";
		
		$fieldName = "goods_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		
		$fieldName = "ws_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
		
		$fieldName = "left_count";
		$this->changeFieldTypeToDeciaml($tableName, $fieldName);
	}

	/**
	 * 判断表的字段是否需要修改成decimal(19,8)
	 *
	 * @param string $tableName        	
	 * @param string $fieldName        	
	 * @return bool
	 */
	private function fieldNeedChangeToDec(string $tableName, string $fieldName): bool {
		$db = $this->db;
		
		$dbName = C('DB_NAME');
		
		$sql = "select DATA_TYPE as dtype, NUMERIC_PRECISION as dpre, NUMERIC_SCALE as dscale  
				from information_schema.`COLUMNS` c 
				where c.TABLE_SCHEMA = '%s' and c.TABLE_NAME = '%s' and c.COLUMN_NAME = '%s'";
		$data = $db->query($sql, $dbName, $tableName, $fieldName);
		if (! $data) {
			return false;
		}
		
		$dataType = strtolower($data[0]["dtype"]);
		$dataPrecision = $data[0]["dpre"];
		$dataScale = $data[0]["dscale"];
		
		if ($dataType == "int") {
			return true;
		}
		
		if ($dataType == "decimal") {
			if ($dataScale < 8) {
				return true;
			}
		}
		
		// int和decimal之外的均不能修改
		return false;
	}

	/**
	 * 把表字段类型修改成decimal(19, 8)
	 *
	 * @param string $talbeName        	
	 * @param string $fieldName        	
	 */
	private function changeFieldTypeToDeciaml(string $tableName, string $fieldName) {
		if (! $this->fieldNeedChangeToDec($tableName, $fieldName)) {
			return;
		}
		
		$db = $this->db;
		
		$sql = "alter table " . $tableName . " modify column " . $fieldName . " decimal(19, 8)";
		$db->execute($sql);
	}

	private function update_20180203_03() {
		// 本次更新：库存盘点权限细化到按钮
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "库存盘点";
		
		// 新建盘点单
		$fid = FIdConst::INVENTORY_CHECK_ADD;
		$name = "库存盘点-新建盘点单";
		$note = "按钮权限：库存盘点模块[新建盘点单]按钮权限";
		$showOrder = 201;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 编辑盘点单
		$fid = FIdConst::INVENTORY_CHECK_EDIT;
		$name = "库存盘点-编辑盘点单";
		$note = "按钮权限：库存盘点模块[编辑盘点单]按钮权限";
		$showOrder = 202;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 删除盘点单
		$fid = FIdConst::INVENTORY_CHECK_DELETE;
		$name = "库存盘点-删除盘点单";
		$note = "按钮权限：库存盘点模块[删除盘点单]按钮权限";
		$showOrder = 203;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 提交盘点单
		$fid = FIdConst::INVENTORY_CHECK_COMMIT;
		$name = "库存盘点-提交盘点单";
		$note = "按钮权限：库存盘点模块[提交盘点单]按钮权限";
		$showOrder = 204;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 单据生成PDF
		$fid = FIdConst::INVENTORY_CHECK_PDF;
		$name = "库存盘点-单据生成PDF";
		$note = "按钮权限：库存盘点模块[单据生成PDF]按钮权限";
		$showOrder = 205;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180203_02() {
		// 本次更新：库间调拨权限细化到按钮
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "库间调拨";
		
		// 新建调拨单
		$fid = FIdConst::INVENTORY_TRANSFER_ADD;
		$name = "库间调拨-新建调拨单";
		$note = "按钮权限：库间调拨模块[新建调拨单]按钮权限";
		$showOrder = 201;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 编辑调拨单
		$fid = FIdConst::INVENTORY_TRANSFER_EDIT;
		$name = "库间调拨-编辑调拨单";
		$note = "按钮权限：库间调拨模块[编辑调拨单]按钮权限";
		$showOrder = 202;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 删除调拨单
		$fid = FIdConst::INVENTORY_TRANSFER_DELETE;
		$name = "库间调拨-删除调拨单";
		$note = "按钮权限：库间调拨模块[删除调拨单]按钮权限";
		$showOrder = 203;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 提交调拨单
		$fid = FIdConst::INVENTORY_TRANSFER_COMMIT;
		$name = "库间调拨-提交调拨单";
		$note = "按钮权限：库间调拨模块[提交调拨单]按钮权限";
		$showOrder = 204;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 单据生成PDF
		$fid = FIdConst::INVENTORY_TRANSFER_PDF;
		$name = "库间调拨-单据生成PDF";
		$note = "按钮权限：库间调拨模块[单据生成PDF]按钮权限";
		$showOrder = 205;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180203_01() {
		// 本次更新：销售退货入库权限细化到按钮
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "销售退货入库";
		
		// 新建销售退货入库单
		$fid = FIdConst::SALE_REJECTION_ADD;
		$name = "销售退货入库-新建销售退货入库单";
		$note = "按钮权限：销售退货入库模块[新建销售退货入库单]按钮权限";
		$showOrder = 201;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 编辑销售退货入库单
		$fid = FIdConst::SALE_REJECTION_EDIT;
		$name = "销售退货入库-编辑销售退货入库单";
		$note = "按钮权限：销售退货入库模块[编辑销售退货入库单]按钮权限";
		$showOrder = 202;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 删除销售退货入库单
		$fid = FIdConst::SALE_REJECTION_DELETE;
		$name = "销售退货入库-删除销售退货入库单";
		$note = "按钮权限：销售退货入库模块[删除销售退货入库单]按钮权限";
		$showOrder = 203;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 提交入库
		$fid = FIdConst::SALE_REJECTION_COMMIT;
		$name = "销售退货入库-提交入库";
		$note = "按钮权限：销售退货入库模块[提交入库]按钮权限";
		$showOrder = 204;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 单据生成PDF
		$fid = FIdConst::SALE_REJECTION_PDF;
		$name = "销售退货入库-单据生成PDF";
		$note = "按钮权限：销售退货入库模块[单据生成PDF]按钮权限";
		$showOrder = 205;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180202_01() {
		// 本次更新：销售出库权限细化到按钮
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "销售出库";
		
		// 新建销售出库单
		$fid = FIdConst::WAREHOUSING_SALE_ADD;
		$name = "销售出库-新建销售出库单";
		$note = "按钮权限：销售出库模块[新建销售出库单]按钮权限";
		$showOrder = 201;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 编辑销售出库单
		$fid = FIdConst::WAREHOUSING_SALE_EDIT;
		$name = "销售出库-编辑销售出库单";
		$note = "按钮权限：销售出库模块[编辑销售出库单]按钮权限";
		$showOrder = 202;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 删除销售出库单
		$fid = FIdConst::WAREHOUSING_SALE_DELETE;
		$name = "销售出库-删除销售出库单";
		$note = "按钮权限：销售出库模块[删除销售出库单]按钮权限";
		$showOrder = 203;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 提交出库
		$fid = FIdConst::WAREHOUSING_SALE_COMMIT;
		$name = "销售出库-提交出库";
		$note = "按钮权限：销售出库模块[提交出库]按钮权限";
		$showOrder = 204;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 单据生成PDF
		$fid = FIdConst::WAREHOUSING_SALE_PDF;
		$name = "销售出库-单据生成PDF";
		$note = "按钮权限：销售出库模块[单据生成PDF]按钮权限";
		$showOrder = 205;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180201_01() {
		// 本次更新：销售订单权限细化到按钮
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "销售订单";
		
		// 新建销售订单
		$fid = FIdConst::SALE_ORDER_ADD;
		$name = "销售订单-新建销售订单";
		$note = "按钮权限：销售订单模块[新建销售订单]按钮权限";
		$showOrder = 201;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 编辑销售订单
		$fid = FIdConst::SALE_ORDER_EDIT;
		$name = "销售订单-编辑销售订单";
		$note = "按钮权限：销售订单模块[编辑销售订单]按钮权限";
		$showOrder = 202;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 删除销售订单
		$fid = FIdConst::SALE_ORDER_DELETE;
		$name = "销售订单-删除销售订单";
		$note = "按钮权限：销售订单模块[删除销售订单]按钮权限";
		$showOrder = 203;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 单据生成PDF
		$fid = FIdConst::SALE_ORDER_PDF;
		$name = "销售订单-单据生成PDF";
		$note = "按钮权限：销售订单模块[单据生成PDF]按钮权限";
		$showOrder = 206;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180130_01() {
		// 本次更新：采购退货出库权限细化到按钮
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "采购退货出库";
		
		// 新建采购退货出库单
		$fid = FIdConst::PURCHASE_REJECTION_ADD;
		$name = "采购退货出库 - 新建采购退货出库单";
		$note = "按钮权限：采购退货出库模块[新建采购退货出库单]按钮权限";
		$showOrder = 201;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 编辑采购退货出库单
		$fid = FIdConst::PURCHASE_REJECTION_EDIT;
		$name = "采购退货出库 - 编辑采购退货出库单";
		$note = "按钮权限：采购退货出库模块[编辑采购退货出库单]按钮权限";
		$showOrder = 202;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 删除采购退货出库单
		$fid = FIdConst::PURCHASE_REJECTION_DELETE;
		$name = "采购退货出库 - 删除采购退货出库单";
		$note = "按钮权限：采购退货出库模块[删除采购退货出库单]按钮权限";
		$showOrder = 203;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 提交采购退货出库单
		$fid = FIdConst::PURCHASE_REJECTION_COMMIT;
		$name = "采购退货出库 - 提交采购退货出库单";
		$note = "按钮权限：采购退货出库模块[提交采购退货出库单]按钮权限";
		$showOrder = 204;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 单据生成PDF
		$fid = FIdConst::PURCHASE_REJECTION_PDF;
		$name = "采购退货出库 - 单据生成PDF";
		$note = "按钮权限：采购退货出库模块[单据生成PDF]按钮权限";
		$showOrder = 205;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180125_01() {
		// 本次更新：采购入库权限细化到按钮
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "采购入库";
		
		// 新建采购入库单
		$fid = FIdConst::PURCHASE_WAREHOUSE_ADD;
		$name = "采购入库 - 新建采购入库单";
		$note = "按钮权限：采购入库模块[新建采购入库单]按钮权限";
		$showOrder = 201;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 编辑采购入库单
		$fid = FIdConst::PURCHASE_WAREHOUSE_EDIT;
		$name = "采购入库 - 编辑采购入库单";
		$note = "按钮权限：采购入库模块[编辑采购入库单]按钮权限";
		$showOrder = 202;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 删除采购入库单
		$fid = FIdConst::PURCHASE_WAREHOUSE_DELETE;
		$name = "采购入库 - 删除采购入库单";
		$note = "按钮权限：采购入库模块[删除采购入库单]按钮权限";
		$showOrder = 203;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 提交入库
		$fid = FIdConst::PURCHASE_WAREHOUSE_COMMIT;
		$name = "采购入库 - 提交入库";
		$note = "按钮权限：采购入库模块[提交入库]按钮权限";
		$showOrder = 204;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 单据生成PDF
		$fid = FIdConst::PURCHASE_WAREHOUSE_PDF;
		$name = "采购入库 - 单据生成PDF";
		$note = "按钮权限：采购入库模块[单据生成PDF]按钮权限";
		$showOrder = 205;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180119_02() {
		// 本次更新：采购订单权限细化到按钮
		$db = $this->db;
		
		$ps = new PinyinService();
		
		$category = "采购订单";
		
		// 新建采购订单
		$fid = FIdConst::PURCHASE_ORDER_ADD;
		$name = "采购订单 - 新建采购订单";
		$note = "按钮权限：采购订单模块[新建采购订单]按钮权限";
		$showOrder = 201;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 编辑采购订单
		$fid = FIdConst::PURCHASE_ORDER_EDIT;
		$name = "采购订单 - 编辑采购订单";
		$note = "按钮权限：采购订单模块[编辑采购订单]按钮权限";
		$showOrder = 202;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 删除采购订单
		$fid = FIdConst::PURCHASE_ORDER_DELETE;
		$name = "采购订单 - 删除采购订单";
		$note = "按钮权限：采购订单模块[删除采购订单]按钮权限";
		$showOrder = 203;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 关闭订单
		$fid = FIdConst::PURCHASE_ORDER_CLOSE;
		$name = "采购订单 - 关闭订单/取消关闭订单";
		$note = "按钮权限：采购订单模块[关闭采购订单]和[取消采购订单关闭状态]按钮权限";
		$showOrder = 206;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
		
		// 单据生成PDF
		$fid = FIdConst::PURCHASE_ORDER_PDF;
		$name = "采购订单 - 单据生成PDF";
		$note = "按钮权限：采购订单模块[单据生成PDF]按钮权限";
		$showOrder = 207;
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py, show_order)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %d) ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py, $showOrder);
		}
	}

	private function update_20180119_01() {
		// 本次更新：调整 t_permission的备注和排序
		
		// 销售日报表（按商品汇总）
		$this->modifyPermission("2012", 100, "模块权限：通过菜单进入销售日报表(按商品汇总)模块的权限");
		
		// 销售日报表(按客户汇总)
		$this->modifyPermission("2013", 100, "模块权限：通过菜单进入销售日报表(按客户汇总)模块的权限");
		
		// 销售日报表(按仓库汇总)
		$this->modifyPermission("2014", 100, "模块权限：通过菜单进入销售日报表(按仓库汇总)模块的权限");
		
		// 销售日报表(按业务员汇总)
		$this->modifyPermission("2015", 100, "模块权限：通过菜单进入销售日报表(按业务员汇总)模块的权限");
		
		// 销售月报表(按商品汇总)
		$this->modifyPermission("2016", 100, "模块权限：通过菜单进入销售月报表(按商品汇总)模块的权限");
		
		// 销售月报表(按客户汇总)
		$this->modifyPermission("2017", 100, "模块权限：通过菜单进入销售月报表(按客户汇总)模块的权限");
		
		// 销售月报表(按仓库汇总)
		$this->modifyPermission("2018", 100, "模块权限：通过菜单进入销售月报表(按仓库汇总)模块的权限");
		
		// 销售月报表(按业务员汇总)
		$this->modifyPermission("2019", 100, "模块权限：通过菜单进入销售月报表(按业务员汇总)模块的权限");
		
		// 安全库存明细表
		$this->modifyPermission("2020", 100, "模块权限：通过菜单进入安全库存明细表模块的权限");
		
		// 应收账款账龄分析表
		$this->modifyPermission("2021", 100, "模块权限：通过菜单进入应收账款账龄分析表模块的权限");
		
		// 应付账款账龄分析表
		$this->modifyPermission("2022", 100, "模块权限：通过菜单进入应付账款账龄分析表模块的权限");
		
		// 库存超上限明细表
		$this->modifyPermission("2023", 100, "模块权限：通过菜单进入库存超上限明细表模块的权限");
		
		// 首页-销售看板
		$this->modifyPermission("2011-01", 100, "功能权限：在首页显示销售看板");
		
		// 首页-库存看板
		$this->modifyPermission("2011-02", 100, "功能权限：在首页显示库存看板");
		
		// 首页-采购看板
		$this->modifyPermission("2011-03", 100, "功能权限：在首页显示采购看板");
		
		// 首页-资金看板
		$this->modifyPermission("2011-04", 100, "功能权限：在首页显示资金看板");
	}

	private function update_20180117_02() {
		// 本次更新：调整 t_permission的备注和排序
		
		// 应收账款管理
		$this->modifyPermission("2004", 100, "模块权限：通过菜单进入应收账款管理模块的权限");
		
		// 应付账款管理
		$this->modifyPermission("2005", 100, "模块权限：通过菜单进入应付账款管理模块的权限");
		
		// 现金收支查询
		$this->modifyPermission("2024", 100, "模块权限：通过菜单进入现金收支查询模块的权限");
		
		// 预收款管理
		$this->modifyPermission("2025", 100, "模块权限：通过菜单进入预收款管理模块的权限");
		
		// 预付款管理
		$this->modifyPermission("2026", 100, "模块权限：通过菜单进入预付款管理模块的权限");
	}

	private function update_20180117_01() {
		// 本次更新：调整 t_permission的备注和排序
		
		// 库存账查询
		$this->modifyPermission("2003", 100, "模块权限：通过菜单进入库存账查询模块的权限");
		
		// 库存建账
		$this->modifyPermission("2000", 100, "模块权限：通过菜单进入库存建账模块的权限");
		
		// 库间调拨
		$this->modifyPermission("2009", 100, "模块权限：通过菜单进入库间调拨模块的权限");
		
		// 库存盘点
		$this->modifyPermission("2010", 100, "模块权限：通过菜单进入库存盘点模块的权限");
	}

	private function update_20180115_01() {
		// 本次更新：调整 t_permission的备注和排序
		
		// 销售订单
		$this->modifyPermission("2028", 100, "模块权限：通过菜单进入销售订单模块的权限");
		$this->modifyPermission("2028-01", 204, "按钮权限：销售订单模块[审核]按钮和[取消审核]按钮的权限");
		$this->modifyPermission("2028-02", 205, "按钮权限：销售订单模块[生成销售出库单]按钮的权限");
		
		// 销售出库
		$this->modifyPermission("2002", 100, "模块权限：通过菜单进入销售出库模块的权限");
		$this->modifyPermission("2002-01", 101, "功能权限：销售出库单允许编辑销售单价");
		
		// 销售退货入库
		$this->modifyPermission("2006", 100, "模块权限：通过菜单进入销售退货入库模块的权限");
	}

	private function update_20180111_01() {
		// 本次更新：调整 t_permission的备注和排序
		
		// 采购入库
		$this->modifyPermission("2001", 100, "模块权限：通过菜单进入采购入库模块的权限");
		
		// 采购退货出库
		$this->modifyPermission("2007", 100, "模块权限：通过菜单进入采购退货出库模块的权限");
	}

	private function update_20180101_01() {
		// 本次更新：调整 t_permission的备注和排序
		
		// 采购订单
		$this->modifyPermission("2027", 100, "模块权限：通过菜单进入采购订单模块的权限");
		
		$this->modifyPermission("2027-01", 204, "按钮权限：采购订单模块[审核]按钮和[取消审核]按钮的权限");
		$this->modifyPermission("2027-02", 205, "按钮权限：采购订单模块[生成采购入库单]按钮权限");
	}

	private function update_20171229_01() {
		// 本次更新：调整 t_permission的备注和排序
		
		// 业务设置
		$this->modifyPermission("2008", 100, "模块权限：通过菜单进入业务设置模块的权限");
		
		// 系统日志
		$this->modifyPermission("-8997", 100, "模块权限：通过菜单进入业务日志模块的权限");
	}

	private function update_20171227_01() {
		// 本次更新：调整 t_permission的备注和排序
		
		// 权限管理
		$this->modifyPermission("-8996", 100, "模块权限：通过菜单进入权限管理模块的权限");
		
		$this->modifyPermission("-8996-01", 201, "按钮权限：权限管理模块[新增角色]按钮权限");
		$this->modifyPermission("-8996-02", 202, "按钮权限：权限管理模块[编辑角色]按钮权限");
		$this->modifyPermission("-8996-03", 203, "按钮权限：权限管理模块[删除角色]按钮权限");
	}

	private function update_20171226_01() {
		// 本次更新：调整 t_permission的备注和排序
		
		// 客户
		$this->modifyPermission("1007", 100, "模块权限：通过菜单进入客户资料模块的权限");
		
		$this->modifyPermission("1007-03", 201, "按钮权限：客户资料模块[新增客户分类]按钮权限");
		$this->modifyPermission("1007-04", 202, "按钮权限：客户资料模块[编辑客户分类]按钮权限");
		$this->modifyPermission("1007-05", 203, "按钮权限：客户资料模块[删除客户分类]按钮权限");
		$this->modifyPermission("1007-06", 204, "按钮权限：客户资料模块[新增客户]按钮权限");
		$this->modifyPermission("1007-07", 205, "按钮权限：客户资料模块[编辑客户]按钮权限");
		$this->modifyPermission("1007-08", 206, "按钮权限：客户资料模块[删除客户]按钮权限");
		$this->modifyPermission("1007-09", 207, "按钮权限：客户资料模块[导入客户]按钮权限");
		
		$this->modifyPermission("1007-01", 300, "数据域权限：客户资料在业务单据中的使用权限");
		$this->modifyPermission("1007-02", 301, "数据域权限：客户档案模块中客户分类的数据权限");
	}

	private function update_20171214_01() {
		// 本次更新： 调整 t_permission的备注和排序
		
		// 用户管理
		$this->modifyPermission("-8999", 100, "模块权限：通过菜单进入用户管理模块的权限");
		
		$this->modifyPermission("-8999-03", 201, "按钮权限：用户管理模块[新增组织机构]按钮权限");
		$this->modifyPermission("-8999-04", 202, "按钮权限：用户管理模块[编辑组织机构]按钮权限");
		$this->modifyPermission("-8999-05", 203, "按钮权限：用户管理模块[删除组织机构]按钮权限");
		$this->modifyPermission("-8999-06", 204, "按钮权限：用户管理模块[新增用户]按钮权限");
		$this->modifyPermission("-8999-07", 205, "按钮权限：用户管理模块[编辑用户]按钮权限");
		$this->modifyPermission("-8999-08", 206, "按钮权限：用户管理模块[删除用户]按钮权限");
		$this->modifyPermission("-8999-09", 207, "按钮权限：用户管理模块[修改用户密码]按钮权限");
		
		$this->modifyPermission("-8999-01", 300, "数据域权限：组织机构在业务单据中的使用权限");
		$this->modifyPermission("-8999-02", 301, "数据域权限：业务员在业务单据中的使用权限");
	}

	private function update_20171208_01() {
		// 本次更新：t_ic_bill新增字段bill_memo，t_ic_bill_detail新增字段memo
		$db = $this->db;
		
		$tableName = "t_ic_bill";
		$columnName = "bill_memo";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
		
		$tableName = "t_ic_bill_detail";
		$columnName = "memo";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function modifyPermission($fid, $showOrder, $note) {
		$db = $this->db;
		
		$sql = "update t_permission
				set show_order = %d, note = '%s'
				where fid = '%s' ";
		$db->execute($sql, $showOrder, $note, $fid);
	}

	private function update_20171113_01() {
		// 本次更新：调整t_permission的备注和排序
		
		// 商品
		$this->modifyPermission("1001", 100, "模块权限：通过菜单进入商品模块的权限");
		
		$this->modifyPermission("1001-03", 201, "按钮权限：商品模块[新增商品分类]按钮权限");
		$this->modifyPermission("1001-04", 202, "按钮权限：商品模块[编辑商品分类]按钮权限");
		$this->modifyPermission("1001-05", 203, "按钮权限：商品模块[删除商品分类]按钮权限");
		
		$this->modifyPermission("1001-06", 204, "按钮权限：商品模块[新增商品]按钮权限");
		$this->modifyPermission("1001-07", 205, "按钮权限：商品模块[编辑商品]按钮权限");
		$this->modifyPermission("1001-08", 206, "按钮权限：商品模块[删除商品]按钮权限");
		$this->modifyPermission("1001-09", 207, "按钮权限：商品模块[导入商品]按钮权限");
		$this->modifyPermission("1001-10", 208, "按钮权限：商品模块[设置安全库存]按钮权限");
		
		$this->modifyPermission("2030-01", 209, "按钮权限：商品模块[新增子商品]按钮权限");
		$this->modifyPermission("2030-02", 210, "按钮权限：商品模块[编辑子商品]按钮权限");
		$this->modifyPermission("2030-03", 211, "按钮权限：商品模块[删除子商品]按钮权限");
		
		$this->modifyPermission("1001-01", 300, "数据域权限：商品在业务单据中的使用权限");
		$this->modifyPermission("1001-02", 301, "数据域权限：商品模块中商品分类的数据权限");
		
		$this->modifyPermission("1002", 500, "模块权限：通过菜单进入商品计量单位模块的权限");
		$this->modifyPermission("2029", 600, "模块权限：通过菜单进入商品品牌模块的权限");
		$this->modifyPermission("2031", 700, "模块权限：通过菜单进入价格体系模块的权限");
	}

	private function update_20171102_02() {
		// 本次更新： 调整 t_permission的备注和排序
		
		// 仓库
		$this->modifyPermission("1003", 100, "模块权限：通过菜单进入仓库的权限");
		$this->modifyPermission("1003-02", 201, "按钮权限：仓库模块[新增仓库]按钮权限");
		$this->modifyPermission("1003-03", 202, "按钮权限：仓库模块[编辑仓库]按钮权限");
		$this->modifyPermission("1003-04", 203, "按钮权限：仓库模块[删除仓库]按钮权限");
		$this->modifyPermission("1003-05", 204, "按钮权限：仓库模块[修改数据域]按钮权限");
		$this->modifyPermission("1003-01", 300, "数据域权限：仓库在业务单据中的使用权限");
		
		// 供应商
		$this->modifyPermission("1004", 100, "模块权限：通过菜单进入供应商档案的权限");
		$this->modifyPermission("1004-03", 201, "按钮权限：供应商档案模块[新增供应商分类]按钮权限");
		$this->modifyPermission("1004-04", 202, "按钮权限：供应商档案模块[编辑供应商分类]按钮权限");
		$this->modifyPermission("1004-05", 203, "按钮权限：供应商档案模块[删除供应商分类]按钮权限");
		$this->modifyPermission("1004-06", 204, "按钮权限：供应商档案模块[新增供应商]按钮权限");
		$this->modifyPermission("1004-07", 205, "按钮权限：供应商档案模块[编辑供应商]按钮权限");
		$this->modifyPermission("1004-08", 206, "按钮权限：供应商档案模块[删除供应商]按钮权限");
		$this->modifyPermission("1004-02", 300, "数据域权限：供应商档案模块中供应商分类的数据权限");
		$this->modifyPermission("1004-01", 301, "数据域权限：供应商档案在业务单据中的使用权限");
	}

	private function update_20171102_01() {
		// 本次更新：t_permission新增字段show_order
		$db = $this->db;
		
		$tableName = "t_permission";
		$columnName = "show_order";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20171101_01() {
		// 本次更新：t_customer新增sales_warehouse_id
		$db = $this->db;
		
		$tableName = "t_customer";
		$columnName = "sales_warehouse_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20170927_01() {
		// 本次更新：t_supplier新增字段tax_rate
		$db = $this->db;
		
		$tableName = "t_supplier";
		$columnName = "tax_rate";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20170609_02() {
		// 本次更新：修正bug - 价格体系的权限项目没有分类
		$db = $this->db;
		
		$sql = "update t_permission
				set category = '商品', py = 'JGTX', note = '通过菜单进入价格体系模块的权限'
				where id = '2031' ";
		$db->execute($sql);
	}

	private function update_20170607_01() {
		// 本次更新：新增表t_goods_price
		$db = $this->db;
		$tableName = "t_goods_price";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_goods_price` (
					  `id` varchar(255) NOT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `ps_id` varchar(255) NOT NULL,
					  `price` decimal(19,2) NOT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  `company_id` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";
			$db->execute($sql);
		}
	}

	private function update_20170606_03() {
		// 本次更新：t_customer_category新增字段ps_id
		$db = $this->db;
		
		$tableName = "t_customer_category";
		$columnName = "ps_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20170606_02() {
		// 本次更新：新增模块价格体系
		$db = $this->db;
		
		// fid
		$fid = FIdConst::PRICE_SYSTEM;
		$name = "价格体系";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		// 权限
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					value('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		// 菜单
		$sql = "select count(*) as cnt from t_menu_item
				where id = '080104' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('080104', '%s', '%s', '0801', 4)";
			$db->execute($sql, $name, $fid);
		}
	}

	private function update_20170606_01() {
		// 本次更新：新增表t_price_system
		$db = $this->db;
		$tableName = "t_price_system";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_price_system` (
					  `id` varchar(255) NOT NULL,
					  `name` varchar(255) NOT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  `company_id` varchar(255) DEFAULT NULL,
					  `factor` decimal(19,2) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";
			$db->execute($sql);
		}
	}

	private function update_20170604_01($db) {
		// 本次更新：新增表think_session ，把session持久化到数据库中
		$tableName = "think_session";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE `think_session` (
					  `session_id` varchar(255) NOT NULL,
					  `session_expire` int(11) NOT NULL,
					  `session_data` blob,
					  UNIQUE KEY `session_id` (`session_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function update_20170530_01($db) {
		// 本次更新：t_ws_bill新增字段deal_address
		$tableName = "t_ws_bill";
		$columnName = "deal_address";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20170519_01($db) {
		// 本次更新：t_pw_bill新增字段bill_memo，t_po_bill_detail新增字段memo
		$tableName = "t_pw_bill";
		$columnName = "bill_memo";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
		
		$tableName = "t_po_bill_detail";
		$columnName = "memo";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20170515_01($db) {
		// 本次更新：t_role表新增字段code
		$tableName = "t_role";
		$columnName = "code";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20170503_01($db) {
		// 本次更新：t_so_bill_detail新增字段memo
		$tableName = "t_so_bill_detail";
		$columnName = "memo";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20170412_02($db) {
		// 本次更新：t_ws_bill_detail新增字段sobilldetail_id
		$tableName = "t_ws_bill_detail";
		$columnName = "sobilldetail_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20170412_01($db) {
		// 本次更新：t_pw_bill_detail新增字段pobilldetail_id
		$tableName = "t_pw_bill_detail";
		$columnName = "pobilldetail_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) DEFAULT NULL;";
			$db->execute($sql);
		}
	}

	private function update_20170408_01($db) {
		// 本次更新：t_pw_bill新增字段expand_by_bom
		$tableName = "t_pw_bill";
		$columnName = "expand_by_bom";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) NOT NULL DEFAULT 0;";
			$db->execute($sql);
		}
	}

	private function update_20170405_01($db) {
		// 本次更新：商品构成权限
		$ps = new PinyinService();
		$category = "商品";
		
		$fid = FIdConst::GOODS_BOM_ADD;
		$name = "商品构成-新增子商品";
		$note = "商品构成新增子商品按钮的操作权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		$fid = FIdConst::GOODS_BOM_EDIT;
		$name = "商品构成-编辑子商品";
		$note = "商品构成编辑子商品按钮的操作权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		$fid = FIdConst::GOODS_BOM_DELETE;
		$name = "商品构成-删除子商品";
		$note = "商品构成删除子商品按钮的操作权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160722_01($db) {
		// 本次跟新：t_subject表新增字段parent_id
		$tableName = "t_subject";
		$columnName = "parent_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20160620_01($db) {
		// 本次更新：新增表：t_subject
		$tableName = "t_subject";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_subject` (
					  `id` varchar(255) NOT NULL,
					  `category` int NOT NULL,
					  `code` varchar(255) NOT NULL,
					  `name` varchar(255) NOT NULL,
					  `is_leaf` int NOT NULL,
					  `py` varchar(255) DEFAULT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  `company_id` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function update_20160314_01($db) {
		// 本次更新：新增表 t_goods_bom
		$tableName = "t_goods_bom";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_goods_bom` (
					  `id` varchar(255) NOT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `sub_goods_id` varchar(255) NOT NULL,
					  `parent_id` varchar(255) DEFAULT NULL,
					  `sub_goods_count` decimal(19,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function update_20160303_01($db) {
		// 本次更新：调整菜单；新增模块：基础数据-商品品牌
		
		// 调整菜单
		$sql = "update t_menu_item
				set fid = null
				where id = '0801' ";
		$db->execute($sql);
		
		$sql = "select count(*) as cnt 
				from t_menu_item 
				where id = '080101' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item (id, caption, fid, parent_id, show_order)
				values ('080101', '商品', '1001', '0801', 1)";
			$db->execute($sql);
		}
		
		$sql = "update t_menu_item
				set parent_id = '0801', id = '080102'
				where id = '0802' ";
		$db->execute($sql);
		
		// 新增模块：基础数据-商品品牌
		$fid = FIdConst::GOODS_BRAND;
		$name = "商品品牌";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$category = "商品";
			$ps = new PinyinService();
			$py = $ps->toPY($name);
			$sql = "insert into t_permission(id, fid, name, note, category, py)
					value('%s', '%s', '%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name, $category, $py);
		}
		
		$sql = "select count(*) as cnt from t_menu_item
				where id = '080103' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('080103', '%s', '%s', '0801', 3)";
			$db->execute($sql, $name, $fid);
		}
	}

	private function update_20160301_01($db) {
		// 本次更新：新增表t_goods_brand; t_goods新增字段 brand_id
		$tableName = "t_goods_brand";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_goods_brand` (
					  `id` varchar(255) NOT NULL,
					  `name` varchar(255) NOT NULL,
					  `parent_id` varchar(255) DEFAULT NULL,
					  `full_name` varchar(1000) DEFAULT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  `company_id` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$tableName = "t_goods";
		$columnName = "brand_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20160219_01($db) {
		// 本次更新：销售订单新增审核和生成销售出库单的权限
		$ps = new PinyinService();
		$category = "销售订单";
		
		$fid = FIdConst::SALE_ORDER_CONFIRM;
		$name = "销售订单 - 审核/取消审核";
		$note = "销售订单 - 审核/取消审核";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		$fid = FIdConst::SALE_ORDER_GEN_WSBILL;
		$name = "销售订单 - 生成销售出库单";
		$note = "销售订单 - 生成销售出库单";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160120_01($db) {
		// 本次更新：细化客户资料的权限到按钮级别
		$fid = FIdConst::CUSTOMER;
		$category = "客户管理";
		$note = "通过菜单进入客户资料模块的权限";
		$sql = "update t_permission
				set note = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $fid);
		
		$ps = new PinyinService();
		
		// 新增客户分类
		$fid = FIdConst::CUSTOMER_CATEGORY_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增客户分类";
			$note = "客户资料模块[新增客户分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑客户分类
		$fid = FIdConst::CUSTOMER_CATEGORY_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑客户分类";
			$note = "客户资料模块[编辑客户分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除客户分类
		$fid = FIdConst::CUSTOMER_CATEGORY_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除客户分类";
			$note = "客户资料模块[删除客户分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 新增客户
		$fid = FIdConst::CUSTOMER_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增客户";
			$note = "客户资料模块[新增客户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑客户
		$fid = FIdConst::CUSTOMER_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑客户";
			$note = "客户资料模块[编辑客户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除客户
		$fid = FIdConst::CUSTOMER_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除客户";
			$note = "客户资料模块[删除客户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 导入客户
		$fid = FIdConst::CUSTOMER_IMPORT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "导入客户";
			$note = "客户资料模块[导入客户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160119_01($db) {
		// 本次更新：细化基础数据供应商的权限到按钮级别
		$fid = "1004";
		$category = "供应商管理";
		$note = "通过菜单进入基础数据供应商档案模块的权限";
		$sql = "update t_permission
				set note = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $fid);
		
		$ps = new PinyinService();
		
		// 新增供应商分类
		$fid = FIdConst::SUPPLIER_CATEGORY_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增供应商分类";
			$note = "基础数据供应商档案模块[新增供应商分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑供应商分类
		$fid = FIdConst::SUPPLIER_CATEGORY_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑供应商分类";
			$note = "基础数据供应商档案模块[编辑供应商分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除供应商分类
		$fid = FIdConst::SUPPLIER_CATEGORY_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除供应商分类";
			$note = "基础数据供应商档案模块[删除供应商分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 新增供应商
		$fid = FIdConst::SUPPLIER_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增供应商";
			$note = "基础数据供应商档案模块[新增供应商]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑供应商
		$fid = FIdConst::SUPPLIER_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑供应商";
			$note = "基础数据供应商档案模块[编辑供应商]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除供应商
		$fid = FIdConst::SUPPLIER_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除供应商";
			$note = "基础数据供应商档案模块[删除供应商]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160118_01($db) {
		// 本次更新：细化基础数据商品的权限到按钮级别
		$fid = "1001";
		$category = "商品";
		$note = "通过菜单进入基础数据商品模块的权限";
		$sql = "update t_permission
				set note = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $fid);
		
		$ps = new PinyinService();
		
		// 新增商品分类
		$fid = FIdConst::GOODS_CATEGORY_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增商品分类";
			$note = "基础数据商品模块[新增商品分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑商品分类
		$fid = FIdConst::GOODS_CATEGORY_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑商品分类";
			$note = "基础数据商品模块[编辑商品分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除商品分类
		$fid = FIdConst::GOODS_CATEGORY_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除商品分类";
			$note = "基础数据商品模块[删除商品分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 新增商品
		$fid = FIdConst::GOODS_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增商品";
			$note = "基础数据商品模块[新增商品]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑商品
		$fid = FIdConst::GOODS_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑商品";
			$note = "基础数据商品模块[编辑商品]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除商品
		$fid = FIdConst::GOODS_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除商品";
			$note = "基础数据商品模块[删除商品]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 导入商品
		$fid = FIdConst::GOODS_IMPORT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "导入商品";
			$note = "基础数据商品模块[导入商品]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 设置商品安全库存
		$fid = FIdConst::GOODS_SI;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "设置商品安全库存";
			$note = "基础数据商品模块[设置商品安全库存]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160116_02($db) {
		// 本次更新：细化基础数据仓库的权限到按钮级别
		$fid = "1003";
		$category = "仓库";
		$note = "通过菜单进入基础数据仓库模块的权限";
		$sql = "update t_permission
				set note = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $fid);
		
		$ps = new PinyinService();
		
		// 新增仓库
		$fid = "1003-02";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增仓库";
			$note = "基础数据仓库模块[新增仓库]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑仓库
		$fid = "1003-03";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑仓库";
			$note = "基础数据仓库模块[编辑仓库]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除仓库
		$fid = "1003-04";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除仓库";
			$note = "基础数据仓库模块[删除仓库]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 修改仓库数据域
		$fid = "1003-05";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "修改仓库数据域";
			$note = "基础数据仓库模块[修改仓库数据域]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160116_01($db) {
		// 本次更新：细化用户管理模块的权限到按钮级别
		$fid = "-8999";
		$category = "用户管理";
		$note = "通过菜单进入用户管理模块的权限";
		$sql = "update t_permission
				set note = '%s',
					category = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $category, $fid);
		
		$sql = "update t_permission
				set category = '%s'
				where id in( '-8999-01', '-8999-02' ) ";
		$db->execute($sql, $category);
		
		$ps = new PinyinService();
		
		// 新增组织机构
		$fid = "-8999-03";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-新增组织机构";
			$note = "用户管理模块[新增组织机构]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑组织机构
		$fid = "-8999-04";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-编辑组织机构";
			$note = "用户管理模块[编辑组织机构]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除组织机构
		$fid = "-8999-05";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-删除组织机构";
			$note = "用户管理模块[删除组织机构]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 新增用户
		$fid = "-8999-06";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-新增用户";
			$note = "用户管理模块[新增用户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑用户
		$fid = "-8999-07";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-编辑用户";
			$note = "用户管理模块[编辑用户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除用户
		$fid = "-8999-08";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-删除用户";
			$note = "用户管理模块[删除用户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 修改用户密码
		$fid = "-8999-09";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-修改用户密码";
			$note = "用户管理模块[修改用户密码]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160112_01($db) {
		// 本次更新： 细化权限管理模块的权限到按钮级别
		$fid = "-8996";
		$category = "权限管理";
		$note = "通过菜单进入权限管理模块的权限";
		$sql = "update t_permission
				set note = '%s',
					category = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $category, $fid);
		
		$ps = new PinyinService();
		
		// 新增角色
		$fid = "-8996-01";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "权限管理-新增角色";
			$note = "权限管理模块[新增角色]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑角色
		$fid = "-8996-02";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "权限管理-编辑角色";
			$note = "权限管理模块[编辑角色]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除角色
		$fid = "-8996-03";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "权限管理-删除角色";
			$note = "权限管理模块[删除角色]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160108_01($db) {
		// 本次更新：t_permission新增字段 category、py
		$tableName = "t_permission";
		$columnName = "category";
		
		$updateData = false;
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
			
			$updateData = true;
		}
		
		$columnName = "py";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
			
			$updateData = true;
		}
		
		if (! $updateData) {
			return;
		}
		
		// 更新t_permission数据
		$ps = new PinyinService();
		$sql = "select id, name from t_permission";
		$data = $db->query($sql);
		foreach ( $data as $v ) {
			$id = $v["id"];
			$name = $v["name"];
			$sql = "update t_permission
					set py = '%s'
					where id = '%s' ";
			$db->execute($sql, $ps->toPY($name), $id);
		}
		
		// 权限分类：系统管理
		$sql = "update t_permission
				set category = '系统管理' 
				where id in ('-8996', '-8997', '-8999', '-8999-01', 
					'-8999-02', '2008')";
		$db->execute($sql);
		
		// 权限分类：商品
		$sql = "update t_permission
				set category = '商品' 
				where id in ('1001', '1001-01', '1001-02', '1002')";
		$db->execute($sql);
		
		// 权限分类：仓库
		$sql = "update t_permission
				set category = '仓库' 
				where id in ('1003', '1003-01')";
		$db->execute($sql);
		
		// 权限分类： 供应商管理
		$sql = "update t_permission
				set category = '供应商管理'
				where id in ('1004', '1004-01', '1004-02')";
		$db->execute($sql);
		
		// 权限分类：客户管理
		$sql = "update t_permission
				set category = '客户管理'
				where id in ('1007', '1007-01', '1007-02')";
		$db->execute($sql);
		
		// 权限分类：库存建账
		$sql = "update t_permission
				set category = '库存建账'
				where id in ('2000')";
		$db->execute($sql);
		
		// 权限分类：采购入库
		$sql = "update t_permission
				set category = '采购入库'
				where id in ('2001')";
		$db->execute($sql);
		
		// 权限分类：销售出库
		$sql = "update t_permission
				set category = '销售出库'
				where id in ('2002', '2002-01')";
		$db->execute($sql);
		
		// 权限分类：库存账查询
		$sql = "update t_permission
				set category = '库存账查询'
				where id in ('2003')";
		$db->execute($sql);
		
		// 权限分类：应收账款管理
		$sql = "update t_permission
				set category = '应收账款管理'
				where id in ('2004')";
		$db->execute($sql);
		
		// 权限分类：应付账款管理
		$sql = "update t_permission
				set category = '应付账款管理'
				where id in ('2005')";
		$db->execute($sql);
		
		// 权限分类：销售退货入库
		$sql = "update t_permission
				set category = '销售退货入库'
				where id in ('2006')";
		$db->execute($sql);
		
		// 权限分类：采购退货出库
		$sql = "update t_permission
				set category = '采购退货出库'
				where id in ('2007')";
		$db->execute($sql);
		
		// 权限分类：库间调拨
		$sql = "update t_permission
				set category = '库间调拨'
				where id in ('2009')";
		$db->execute($sql);
		
		// 权限分类：库存盘点
		$sql = "update t_permission
				set category = '库存盘点'
				where id in ('2010')";
		$db->execute($sql);
		
		// 权限分类：首页看板
		$sql = "update t_permission
				set category = '首页看板'
				where id in ('2011-01', '2011-02', '2011-03', '2011-04')";
		$db->execute($sql);
		
		// 权限分类：销售日报表
		$sql = "update t_permission
				set category = '销售日报表'
				where id in ('2012', '2013', '2014', '2015')";
		$db->execute($sql);
		
		// 权限分类：销售月报表
		$sql = "update t_permission
				set category = '销售月报表'
				where id in ('2016', '2017', '2018', '2019')";
		$db->execute($sql);
		
		// 权限分类：库存报表
		$sql = "update t_permission
				set category = '库存报表'
				where id in ('2020', '2023')";
		$db->execute($sql);
		
		// 权限分类：资金报表
		$sql = "update t_permission
				set category = '资金报表'
				where id in ('2021', '2022')";
		$db->execute($sql);
		
		// 权限分类：现金管理
		$sql = "update t_permission
				set category = '现金管理'
				where id in ('2024')";
		$db->execute($sql);
		
		// 权限分类：预收款管理
		$sql = "update t_permission
				set category = '预收款管理'
				where id in ('2025')";
		$db->execute($sql);
		
		// 权限分类：预付款管理
		$sql = "update t_permission
				set category = '预付款管理'
				where id in ('2026')";
		$db->execute($sql);
		
		// 权限分类：采购订单
		$sql = "update t_permission
				set category = '采购订单'
				where id in ('2027', '2027-01', '2027-02')";
		$db->execute($sql);
		
		// 权限分类：销售订单
		$sql = "update t_permission
				set category = '销售订单'
				where id in ('2028')";
		$db->execute($sql);
	}

	private function update_20160105_02($db) {
		// 本次更新：新增模块销售订单
		$fid = FIdConst::SALE_ORDER;
		$name = "销售订单";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note) 
					value('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0400' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0400', '%s', '%s', '04', 0)";
			$db->execute($sql, $name, $fid);
		}
	}

	private function update_20160105_01($db) {
		// 本次更新：新增采购订单表
		$tableName = "t_so_bill";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_so_bill` (
					  `id` varchar(255) NOT NULL,
					  `bill_status` int(11) NOT NULL,
					  `biz_dt` datetime NOT NULL,
					  `deal_date` datetime NOT NULL,
					  `org_id` varchar(255) NOT NULL,
					  `biz_user_id` varchar(255) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_money` decimal(19,2) NOT NULL,
					  `tax` decimal(19,2) NOT NULL,
					  `money_with_tax` decimal(19,2) NOT NULL,
					  `input_user_id` varchar(255) NOT NULL,
					  `ref` varchar(255) NOT NULL,
					  `customer_id` varchar(255) NOT NULL,
					  `contact` varchar(255) NOT NULL,
					  `tel` varchar(255) DEFAULT NULL,
					  `fax` varchar(255) DEFAULT NULL,
					  `deal_address` varchar(255) DEFAULT NULL,
					  `bill_memo` varchar(255) DEFAULT NULL,
					  `receiving_type` int(11) NOT NULL DEFAULT 0,
					  `confirm_user_id` varchar(255) DEFAULT NULL,
					  `confirm_date` datetime DEFAULT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  `company_id` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$tableName = "t_so_bill_detail";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_so_bill_detail` (
					  `id` varchar(255) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `goods_count` int(11) NOT NULL,
					  `goods_money` decimal(19,2) NOT NULL,
					  `goods_price` decimal(19,2) NOT NULL,
					  `sobill_id` varchar(255) NOT NULL,
					  `tax_rate` decimal(19,2) NOT NULL,
					  `tax` decimal(19,2) NOT NULL,
					  `money_with_tax` decimal(19,2) NOT NULL,
					  `ws_count` int(11) NOT NULL,
					  `left_count` int(11) NOT NULL,
					  `show_order` int(11) NOT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  `company_id` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$tableName = "t_so_ws";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_so_ws` (
					  `so_id` varchar(255) NOT NULL,
					  `ws_id` varchar(255) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function update_20151210_01($db) {
		// 本次更新： t_goods新增字段spec_py
		$tableName = "t_goods";
		$columnName = "spec_py";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151128_03($db) {
		// 本次更新：表新增company_id字段
		$tables = array(
				"t_biz_log",
				"t_role",
				"t_user",
				"t_warehouse",
				"t_supplier",
				"t_supplier_category",
				"t_goods",
				"t_goods_category",
				"t_goods_unit",
				"t_customer",
				"t_customer_category",
				"t_inventory",
				"t_inventory_detail",
				"t_pw_bill_detail",
				"t_payment",
				"t_ws_bill_detail",
				"t_receiving",
				"t_sr_bill_detail",
				"t_it_bill_detail",
				"t_ic_bill_detail",
				"t_pr_bill_detail",
				"t_config",
				"t_goods_si",
				"t_po_bill_detail"
		);
		$columnName = "company_id";
		foreach ( $tables as $tableName ) {
			if (! $this->tableExists($db, $tableName)) {
				continue;
			}
			
			if (! $this->columnExists($db, $tableName, $columnName)) {
				$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
				$db->execute($sql);
			}
		}
	}

	private function update_20151128_02($db) {
		// 本次更新：新增商品分类权限
		$fid = "1001-02";
		$name = "商品分类";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note) 
					value('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
	}

	private function update_20151128_01($db) {
		// 本次更新：新增供应商分类权限
		$fid = "1004-02";
		$name = "供应商分类";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note) 
					value('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
	}

	private function update_20151127_01($db) {
		// 本次更新：新增客户分类权限
		$fid = "1007-02";
		$name = "客户分类";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note) 
					value('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
	}

	private function update_20151126_01($db) {
		// 本次更新：销售出库单新增备注字段
		$tableName = "t_ws_bill";
		$columnName = "memo";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(1000) default null;";
			$db->execute($sql);
		}
		
		$tableName = "t_ws_bill_detail";
		$columnName = "memo";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(1000) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151124_01($db) {
		// 本次更新：调拨单、盘点单新增company_id字段
		$tableName = "t_it_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$tableName = "t_ic_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151123_03($db) {
		// 本次更新：销售退货入库单新增company_id字段
		$tableName = "t_sr_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151123_02($db) {
		// 本次更新：销售出库单新增company_id字段
		$tableName = "t_ws_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151123_01($db) {
		// 本次更新： 采购退货出库单新增company_id字段
		$tableName = "t_pr_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151121_01($db) {
		// 本次更新：采购入库单主表新增company_id字段
		$tableName = "t_pw_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151119_03($db) {
		// 本次更新： 采购订单主表增加 company_id 字段
		$tableName = "t_po_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151119_01($db) {
		// 本次更新：和资金相关的表增加 company_id 字段
		$tableList = array(
				"t_cash",
				"t_cash_detail",
				"t_payables",
				"t_payables_detail",
				"t_pre_payment",
				"t_pre_payment_detail",
				"t_pre_receiving",
				"t_pre_receiving_detail",
				"t_receivables",
				"t_receivables_detail"
		);
		
		$columnName = "company_id";
		
		foreach ( $tableList as $tableName ) {
			if (! $this->tableExists($db, $tableName)) {
				continue;
			}
			
			if (! $this->columnExists($db, $tableName, $columnName)) {
				$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
				$db->execute($sql);
			}
		}
	}

	private function update_20151113_01($db) {
		// 本次更新：t_pw_bill_detail表新增memo字段
		$tableName = "t_pw_bill_detail";
		$columnName = "memo";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(1000) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151112_01($db) {
		// 本次更新：t_biz_log表增加ip_from字段
		$tableName = "t_biz_log";
		$columnName = "ip_from";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151111_01($db) {
		// 本次更新：t_config表：单号前缀自定义
		$id = "9003-01";
		$name = "采购订单单号前缀";
		$value = "PO";
		$showOrder = 601;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-02";
		$name = "采购入库单单号前缀";
		$value = "PW";
		$showOrder = 602;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-03";
		$name = "采购退货出库单单号前缀";
		$value = "PR";
		$showOrder = 603;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-04";
		$name = "销售出库单单号前缀";
		$value = "WS";
		$showOrder = 604;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-05";
		$name = "销售退货入库单单号前缀";
		$value = "SR";
		$showOrder = 605;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-06";
		$name = "调拨单单号前缀";
		$value = "IT";
		$showOrder = 606;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-07";
		$name = "盘点单单号前缀";
		$value = "IC";
		$showOrder = 607;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
	}

	private function update_20151110_02($db) {
		// 本次更新：t_inventory_fifo_detail表增加wsbilldetail_id字段
		$tableName = "t_inventory_fifo_detail";
		$columnName = "wsbilldetail_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151110_01($db) {
		// 本次更新： t_inventory_fifo、 t_inventory_fifo_detail表增加字段 pwbilldetail_id
		$tableName = "t_inventory_fifo";
		$columnName = "pwbilldetail_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$tableName = "t_inventory_fifo_detail";
		$columnName = "pwbilldetail_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151108_01($db) {
		// 本次更新：基础数据在业务单据中的使用权限
		$fid = "-8999-01";
		$name = "组织机构在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "-8999-02";
		$name = "业务员在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "1001-01";
		$name = "商品在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "1003-01";
		$name = "仓库在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "1004-01";
		$name = "供应商档案在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "1007-01";
		$name = "客户资料在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "-8999-01";
		$name = "组织机构在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "-8999-02";
		$name = "业务员在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "1001-01";
		$name = "商品在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "1003-01";
		$name = "仓库在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "1004-01";
		$name = "供应商档案在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "1007-01";
		$name = "客户资料在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
	}

	private function update_20151106_02($db) {
		// 本次更新：业务设置去掉仓库设置组织结构；增加存货计价方法
		$sql = "delete from t_config where id = '1003-01' ";
		$db->execute($sql);
		
		$sql = "select count(*) as cnt from t_config where id = '1003-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config(id, name, value, note, show_order)
					values ('1003-02', '存货计价方法', '0', '', 401)";
			$db->execute($sql);
		}
	}

	private function update_20151106_01($db) {
		// 本次更新：先进先出，新增数据库表
		$tableName = "t_inventory_fifo";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_inventory_fifo` (
					  `id` bigint(20) NOT NULL AUTO_INCREMENT,
					  `balance_count` decimal(19,2) NOT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `balance_price` decimal(19,2) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `in_count` decimal(19,2) DEFAULT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `in_price` decimal(19,2) DEFAULT NULL,
					  `out_count` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `out_price` decimal(19,2) DEFAULT NULL,
					  `in_ref` varchar(255) DEFAULT NULL,
					  `in_ref_type` varchar(255) NOT NULL,
					  `warehouse_id` varchar(255) NOT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
					";
			$db->execute($sql);
		}
		
		$tableName = "t_inventory_fifo_detail";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_inventory_fifo_detail` (
					  `id` bigint(20) NOT NULL AUTO_INCREMENT,
					  `balance_count` decimal(19,2) NOT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `balance_price` decimal(19,2) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `in_count` decimal(19,2) DEFAULT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `in_price` decimal(19,2) DEFAULT NULL,
					  `out_count` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `out_price` decimal(19,2) DEFAULT NULL,
					  `warehouse_id` varchar(255) NOT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
					";
			$db->execute($sql);
		}
	}

	private function update_20151105_01($db) {
		// 本次更新： 在途库存、 商品多级分类
		$tableName = "t_inventory";
		$columnName = "afloat_count";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} decimal(19,2) default null;";
			$db->execute($sql);
		}
		$columnName = "afloat_money";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} decimal(19,2) default null;";
			$db->execute($sql);
		}
		$columnName = "afloat_price";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} decimal(19,2) default null;";
			$db->execute($sql);
		}
		
		$tableName = "t_goods_category";
		$columnName = "full_name";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(1000) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151102_01($db) {
		// 本次更新：新增表 t_role_permission_dataorg
		$tableName = "t_role_permission_dataorg";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_role_permission_dataorg` (
					  `role_id` varchar(255) DEFAULT NULL,
					  `permission_id` varchar(255) DEFAULT NULL,
					  `data_org` varchar(255) DEFAULT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
			return;
		}
	}

	private function update_20151031_01($db) {
		// 本次更新：商品 增加备注字段
		$tableName = "t_goods";
		$columnName = "memo";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(500) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151016_01($db) {
		// 本次更新：表结构增加data_org字段
		$tables = array(
				"t_biz_log",
				"t_org",
				"t_role",
				"t_role_permission",
				"t_user",
				"t_warehouse",
				"t_warehouse_org",
				"t_supplier",
				"t_supplier_category",
				"t_goods",
				"t_goods_category",
				"t_goods_unit",
				"t_customer",
				"t_customer_category",
				"t_inventory",
				"t_inventory_detail",
				"t_pw_bill",
				"t_pw_bill_detail",
				"t_payables",
				"t_payables_detail",
				"t_receivables",
				"t_receivables_detail",
				"t_payment",
				"t_ws_bill",
				"t_ws_bill_detail",
				"t_receiving",
				"t_sr_bill",
				"t_sr_bill_detail",
				"t_it_bill",
				"t_it_bill_detail",
				"t_ic_bill",
				"t_ic_bill_detail",
				"t_pr_bill",
				"t_pr_bill_detail",
				"t_goods_si",
				"t_cash",
				"t_cash_detail",
				"t_pre_receiving",
				"t_pre_receiving_detail",
				"t_pre_payment",
				"t_pre_payment_detail",
				"t_po_bill",
				"t_po_bill_detail"
		);
		
		$columnName = "data_org";
		foreach ( $tables as $tableName ) {
			if (! $this->tableExists($db, $tableName)) {
				continue;
			}
			
			if (! $this->columnExists($db, $tableName, $columnName)) {
				$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
				$db->execute($sql);
			}
		}
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
		$tableName = "t_config";
		
		$columnName = "show_order";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) default null;";
			$db->execute($sql);
			
			$sql = "delete from t_config";
			$db->execute($sql);
		}
		
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
		
		// 2001-01
		$sql = "select count(*) as cnt from t_config where id = '2001-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('2001-01', '采购入库默认仓库', '', '', 200)";
			$db->execute($sql);
		}
		
		// 2002-02
		$sql = "select count(*) as cnt from t_config where id = '2002-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('2002-02', '销售出库默认仓库', '', '', 300)";
			$db->execute($sql);
		}
		
		// 2002-01
		$sql = "select count(*) as cnt from t_config where id = '2002-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('2002-01', '销售出库单允许编辑销售单价', '0', '当允许编辑的时候，还需要给用户赋予权限[销售出库单允许编辑销售单价]', 301)";
			$db->execute($sql);
		}
		
		// 1003-01
		$sql = "select count(*) as cnt from t_config where id = '1003-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('1003-01', '仓库需指定组织机构', '0', '当仓库需要指定组织机构的时候，就意味着可以控制仓库的使用人', 401)";
			$db->execute($sql);
		}
		
		// 9001-01
		$sql = "select count(*) as cnt from t_config where id = '9001-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9001-01', '增值税税率', '17', '', 501)";
			$db->execute($sql);
		}
		
		// 9002-01
		$sql = "select count(*) as cnt from t_config where id = '9002-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9002-01', '产品名称', '开源进销存PSI', '', 0)";
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

	private function t_goods_category($db) {
		$tableName = "t_goods_category";
		
		$columnName = "parent_id";
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
		
		// fid 2025: 预收款管理
		$sql = "select count(*) as cnt from t_fid where fid = '2025' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2025', '预收款管理')";
			$db->execute($sql);
		}
		
		// fid 2026: 预付款管理
		$sql = "select count(*) as cnt from t_fid where fid = '2026' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2026', '预付款管理')";
			$db->execute($sql);
		}
		
		// fid 2027: 采购订单
		$sql = "select count(*) as cnt from t_fid where fid = '2027' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2027', '采购订单')";
			$db->execute($sql);
		}
		
		// fid 2027-01: 采购订单 - 审核
		$sql = "select count(*) as cnt from t_fid where fid = '2027-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2027-01', '采购订单 - 审核/取消审核')";
			$db->execute($sql);
		}
		
		// fid 2027-02: 采购订单 - 生成采购入库单
		$sql = "select count(*) as cnt from t_fid where fid = '2027-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2027-02', '采购订单 - 生成采购入库单')";
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
		
		// fid 2025: 预收款管理
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0604' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0604', '预收款管理', '2025', '06', 4)";
			$db->execute($sql);
		}
		
		// fid 2026: 预付款管理
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0605' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0605', '预付款管理', '2026', '06', 5)";
			$db->execute($sql);
		}
		
		// fid 2027: 采购订单
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0200' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0200', '采购订单', '2027', '02', 0)";
			$db->execute($sql);
		}
	}

	private function t_po_bill($db) {
		$tableName = "t_po_bill";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_po_bill` (
					  `id` varchar(255) NOT NULL,
					  `bill_status` int(11) NOT NULL,
					  `biz_dt` datetime NOT NULL,
					  `deal_date` datetime NOT NULL,
					  `org_id` varchar(255) NOT NULL,
					  `biz_user_id` varchar(255) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_money` decimal(19,2) NOT NULL,
					  `tax` decimal(19,2) NOT NULL,
					  `money_with_tax` decimal(19,2) NOT NULL,
					  `input_user_id` varchar(255) NOT NULL,
					  `ref` varchar(255) NOT NULL,
					  `supplier_id` varchar(255) NOT NULL,
					  `contact` varchar(255) NOT NULL,
					  `tel` varchar(255) DEFAULT NULL,
					  `fax` varchar(255) DEFAULT NULL,
					  `deal_address` varchar(255) DEFAULT NULL,
					  `bill_memo` varchar(255) DEFAULT NULL,
					  `payment_type` int(11) NOT NULL DEFAULT 0,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$columnName = "confirm_user_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "confirm_date";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} datetime default null;";
			$db->execute($sql);
		}
	}

	private function t_po_bill_detail($db) {
		$tableName = "t_po_bill_detail";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_po_bill_detail` (
					  `id` varchar(255) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `goods_count` int(11) NOT NULL,
					  `goods_money` decimal(19,2) NOT NULL,
					  `goods_price` decimal(19,2) NOT NULL,
					  `pobill_id` varchar(255) NOT NULL,
					  `tax_rate` decimal(19,2) NOT NULL,
					  `tax` decimal(19,2) NOT NULL,
					  `money_with_tax` decimal(19,2) NOT NULL,
					  `pw_count` int(11) NOT NULL,
					  `left_count` int(11) NOT NULL,
					  `show_order` int(11) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function t_po_pw($db) {
		$tableName = "t_po_pw";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_po_pw` (
					  `po_id` varchar(255) NOT NULL,
					  `pw_id` varchar(255) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
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

	private function t_pre_payment($db) {
		$tableName = "t_pre_payment";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_pre_payment` (
					  `id` varchar(255) NOT NULL,
					  `supplier_id` varchar(255) NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function t_pre_payment_detail($db) {
		$tableName = "t_pre_payment_detail";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_pre_payment_detail` (
					  `id` varchar(255) NOT NULL,
					  `supplier_id` varchar(255) NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `biz_date` datetime DEFAULT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `ref_number` varchar(255) NOT NULL,
					  `ref_type` varchar(255) NOT NULL,
					  `biz_user_id` varchar(255) NOT NULL,
					  `input_user_id` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function t_pre_receiving($db) {
		$tableName = "t_pre_receiving";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_pre_receiving` (
					  `id` varchar(255) NOT NULL,
					  `customer_id` varchar(255) NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function t_pre_receiving_detail($db) {
		$tableName = "t_pre_receiving_detail";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_pre_receiving_detail` (
					  `id` varchar(255) NOT NULL,
					  `customer_id` varchar(255) NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `biz_date` datetime DEFAULT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `ref_number` varchar(255) NOT NULL,
					  `ref_type` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$columnName = "biz_user_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) not null;";
			$db->execute($sql);
		}
		
		$columnName = "input_user_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) not null;";
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
		
		// fid 2025: 预收款管理
		$sql = "select count(*) as cnt from t_permission where id = '2025' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2025', '2025', '预收款管理', '预收款管理')";
			$db->execute($sql);
		}
		
		// fid 2026: 预付款管理
		$sql = "select count(*) as cnt from t_permission where id = '2026' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2026', '2026', '预付款管理', '预付款管理')";
			$db->execute($sql);
		}
		
		// fid 2027: 采购订单
		$sql = "select count(*) as cnt from t_permission where id = '2027' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2027', '2027', '采购订单', '采购订单')";
			$db->execute($sql);
		}
		
		// fid 2027-01: 采购订单 - 审核/取消审核
		$sql = "select count(*) as cnt from t_permission where id = '2027-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2027-01', '2027-01', '采购订单 - 审核/取消审核', '采购订单 - 审核/取消审核')";
			$db->execute($sql);
		}
		
		// fid 2027-02: 采购订单 - 生成采购入库单
		$sql = "select count(*) as cnt from t_permission where id = '2027-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2027-02', '2027-02', '采购订单 - 生成采购入库单', '采购订单 - 生成采购入库单')";
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
		
		// fid 2025: 预收款管理
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2025' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2025')";
			$db->execute($sql);
		}
		
		// fid 2026: 预付款管理
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2026' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2026')";
			$db->execute($sql);
		}
		
		// fid 2027: 采购订单
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2027' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2027')";
			$db->execute($sql);
		}
		
		// fid 2027-01: 采购订单 - 审核/取消审核
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2027-01' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2027-01')";
			$db->execute($sql);
		}
		
		// fid 2027-02: 采购订单 - 生成采购入库单
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2027-02' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2027-02')";
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

	private function t_supplier_category($db) {
		$tableName = "t_supplier_category";
		
		$columnName = "parent_id";
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
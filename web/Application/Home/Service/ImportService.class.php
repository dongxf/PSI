<?php

namespace Home\Service;

use Think\Exception;

require __DIR__ . '/../Common/Excel/PHPExcel.php';
require __DIR__ . '/../Common/Excel/PHPExcel/Reader/Excel5.php';
require __DIR__ . '/../Common/Excel/PHPExcel/Reader/Excel2007.php';

/**
 * PHPExcel文件 Service
 *
 * @author James(张健)
 */
class ImportService {

	/**
	 * 商品导入Service
	 * 
	 * @param
	 *        	$params
	 * @return array
	 * @throws \PHPExcel_Exception
	 */
	public function importGoodsFromExcelFile($params) {
		$dataFile = $params["datafile"];
		$ext = $params["ext"];
		$message = "";
		$success = true;
		$result = array(
				"msg" => $message,
				"success" => $success
		);
		if (! $dataFile || ! $ext)
			return $result;
			// $PHPExcel = new \PHPExcel();
			
		// 默认xlsx
		$PHPReader = new \PHPExcel_Reader_Excel2007();
		// 如果excel文件后缀名为.xls，导入这个类
		if ($ext == 'xls') {
			$PHPReader = new \PHPExcel_Reader_Excel5();
		}
		
		try {
			ini_set('max_execution_time', 120); // 120 seconds = 5 minutes
			                                    // 载入文件
			$PHPExcel = $PHPReader->load($dataFile);
			// 获取表中的第一个工作表
			$currentSheet = $PHPExcel->getSheet(0);
			// 获取总行数
			$allRow = $currentSheet->getHighestRow();
			
			// 如果没有数据行，直接返回
			if ($allRow < 2) {
				return $result;
			}
			
			$ps = new PinyinService();
			$idGen = new IdGenService();
			$bs = new BizlogService();
			$gs = new GoodsService();
			$db = M();
			$units = array(); // 将计量单位缓存，以免频繁访问数据库
			$categories = array(); // 同上
			$params = array(); // 数据参数
			
			$us = new UserService();
			$dataOrg = $us->getLoginUserDataOrg();
			
			$insertSql = "insert into t_goods (id, code, name, spec, category_id, unit_id, sale_price,	py, purchase_price, bar_code, data_org,memo) values";
			$dataSql = "('%s', '%s', '%s', '%s', '%s', '%s', %f, '%s', %f, '%s', '%s', '%s'),";
			/**
			 * 单元格定义
			 * A 商品分类编码
			 * B 商品编码
			 * C 商品名称
			 * D 规格型号
			 * E 计量单位
			 * F 销售单价
			 * G 建议采购单价
			 * H 条形码
			 * I 备注
			 */
			
			// 从第2行获取数据
			for($currentRow = 2; $currentRow <= $allRow; $currentRow ++) {
				// 数据坐标
				$index_category = 'A' . $currentRow;
				$index_code = 'B' . $currentRow;
				$index_name = 'C' . $currentRow;
				$index_spec = 'D' . $currentRow;
				$index_unit = 'E' . $currentRow;
				$index_sale_price = 'F' . $currentRow;
				$index_purchase_price = 'G' . $currentRow;
				$index_barcode = 'H' . $currentRow;
				$index_memo = 'I' . $currentRow;
				// 读取到的数据，保存到数组$arr中
				$category = $currentSheet->getCell($index_category)->getValue();
				$code = $currentSheet->getCell($index_code)->getValue();
				$name = $currentSheet->getCell($index_name)->getValue();
				$spec = $currentSheet->getCell($index_spec)->getValue();
				$unit = $currentSheet->getCell($index_unit)->getValue();
				$sale_price = $currentSheet->getCell($index_sale_price)->getValue();
				$purchase_price = $currentSheet->getCell($index_purchase_price)->getValue();
				$barcode = $currentSheet->getCell($index_barcode)->getValue();
				$memo = $currentSheet->getCell($index_memo)->getValue();
				
				// 如果为空则直接读取下一条记录
				if (! $category || ! $code || ! $name || ! $unit)
					continue;
				
				$unitId = null;
				$categoryId = null;
				
				if ($units["{$unit}"]) {
					$unitId = $units["{$unit}"];
				} else {
					$sql = "select id, `name` from t_goods_unit where `name` = '%s' ";
					$data = $db->query($sql, $unit);
					if (! $data) {
						// 新增计量单位
						$newUnitParams = array(
								"name" => $unit
						);
						$newUnit = $gs->editUnit($newUnitParams);
						$unitId = $newUnit["id"];
					} else {
						$unitId = $data[0]["id"];
					}
					$units += array(
							"{$unit}" => "{$unitId}"
					);
				}
				
				if ($categories["{$category}"]) {
					$categoryId = $categories["{$category}"];
				} else {
					$sql = "select id, code from t_goods_category where code = '%s' ";
					$data = $db->query($sql, $category);
					if (! $data) {
						// 新增分类
						continue;
					} else {
						$categoryId = $data[0]["id"];
					}
					$categories += array(
							"{$category}" => "{$categoryId}"
					);
				}
				
				// 新增
				// 检查商品编码是否唯一
				$sql = "select 1  from t_goods where code = '%s' ";
				$data = $db->query($sql, $code);
				if ($data) {
					$message .= "商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec} 已存在; \r\n";
					continue;
				}
				
				// 如果录入了条形码，则需要检查条形码是否唯一
				if ($barcode) {
					$sql = "select 1  from t_goods where bar_code = '%s' ";
					$data = $db->query($sql, $barcode);
					if ($data) {
						$message .= "商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec}，条形码 = {$barcode} 已存在;\r\n";
						continue;
					}
				}
				
				$id = $idGen->newId();
				$py = $ps->toPY($name);
				
				$insertSql .= $dataSql;
				// 数据参数加入
				array_push($params, $id, $code, $name, $spec, $categoryId, $unitId, $sale_price, 
						$py, $purchase_price, $barcode, $dataOrg, $memo);
			}
			
			$db->execute(rtrim($insertSql, ','), $params);
			
			$log = "导入方式新增商品;{$dataFile}";
			$bs->insertBizlog($log, "基础数据-商品");
		} catch ( Exception $e ) {
			$success = false;
			$message = $e;
		}
		
		$result = array(
				"msg" => $message,
				"success" => $success
		);
		return $result;
	}

	/**
	 * 客户导入Service
	 * 
	 * @param
	 *        	$params
	 * @return array
	 * @throws \PHPExcel_Exception
	 */
	public function importCustomerFromExcelFile($params) {
		$dataFile = $params["datafile"];
		$ext = $params["ext"];
		$message = "";
		$success = true;
		$result = array(
				"msg" => $message,
				"success" => $success
		);
		if (! $dataFile || ! $ext)
			return $result;
			// $PHPExcel = new \PHPExcel();
			
		// 默认xlsx
		$PHPReader = new \PHPExcel_Reader_Excel2007();
		// 如果excel文件后缀名为.xls，导入这个类
		if ($ext == 'xls') {
			$PHPReader = new \PHPExcel_Reader_Excel5();
		}
		
		try {
			// Deal with the Fatal error: Maximum execution time of 30 seconds exceeded
			ini_set('max_execution_time', 120); // 120 seconds = 5 minutes
			                                    // 载入文件
			$PHPExcel = $PHPReader->load($dataFile);
			// 获取表中的第一个工作表
			$currentSheet = $PHPExcel->getSheet(0);
			// 获取总行数
			$allRow = $currentSheet->getHighestRow();
			
			// 如果没有数据行，直接返回
			if ($allRow < 2) {
				return $result;
			}
			
			$ps = new PinyinService();
			$idGen = new IdGenService();
			$bs = new BizlogService();
			$db = M();
			$categories = array(); // 同上
			$params = array(); // 数据参数
			
			$us = new UserService();
			$dataOrg = $us->getLoginUserDataOrg();
			
			$insertSql = "insert into t_customer (id, category_id, code, `name`, py
			,contact01,qq01, tel01, mobile01, contact02, qq02, tel02, mobile02, address
			,init_receivables,init_receivables_dt,address_shipping,address_receipt
			,bank_name, bank_account, tax_number, fax, note, data_org) values";
			$dataSql = "('%s', '%s', '%s', '%s', '%s'
			,'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
			,'%s', '%s', '%s', '%s'
			,'%s', '%s', '%s', '%s', '%s', '%s'),";
			/**
			 * 单元格定义
			 * A category 客户分类编码
			 * B code 客户编码
			 * C name 客户名称 -- py 客户名称的拼音字头
			 * D contact01 联系人
			 * E tel01 联系人固话
			 * F qq01 联系人QQ号
			 * G mobile01 联系人手机
			 * H contact02 备用联系人
			 * I tel02 备用联系人固话
			 * J qq02 备用联系人QQ号
			 * K mobile02 备用联系人手机
			 * L address 地址
			 * M init_receivables 期初应收账款
			 * N init_receivables_dt 期初应收账款截止日期
			 * O address_shipping 发货地址
			 * P address_receipt 收货地址
			 * Q bank_name 开户行
			 * R bank_account 开户行账号
			 * S tax_number 税号
			 * T fax 传真
			 * U note 备注
			 */
			// 从第2行获取数据
			for($currentRow = 2; $currentRow <= $allRow; $currentRow ++) {
				// 数据坐标
				$index_category = 'A' . $currentRow;
				$index_code = 'B' . $currentRow;
				$index_name = 'C' . $currentRow;
				$index_contact01 = 'D' . $currentRow;
				$index_tel01 = 'E' . $currentRow;
				$index_qq01 = 'F' . $currentRow;
				$index_mobile01 = 'G' . $currentRow;
				$index_contact02 = 'H' . $currentRow;
				$index_tel02 = 'I' . $currentRow;
				$index_qq02 = 'J' . $currentRow;
				$index_mobile02 = 'K' . $currentRow;
				$index_address = 'L' . $currentRow;
				$index_init_receivables = 'M' . $currentRow;
				$index_init_receivables_dt = 'N' . $currentRow;
				$index_address_shipping = 'O' . $currentRow;
				$index_address_receipt = 'P' . $currentRow;
				$index_bank_name = 'Q' . $currentRow;
				$index_bank_account = 'R' . $currentRow;
				$index_tax_number = 'S' . $currentRow;
				$index_fax = 'T' . $currentRow;
				$index_note = 'U' . $currentRow;
				// 读取到的数据，保存到数组$arr中
				$category = $currentSheet->getCell($index_category)->getValue();
				$code = $currentSheet->getCell($index_code)->getValue();
				$name = $currentSheet->getCell($index_name)->getValue();
				$contact01 = $currentSheet->getCell($index_contact01)->getValue();
				$tel01 = $currentSheet->getCell($index_tel01)->getValue();
				$qq01 = $currentSheet->getCell($index_qq01)->getValue();
				$mobile01 = $currentSheet->getCell($index_mobile01)->getValue();
				$contact02 = $currentSheet->getCell($index_contact02)->getValue();
				$tel02 = $currentSheet->getCell($index_tel02)->getValue();
				$qq02 = $currentSheet->getCell($index_qq02)->getValue();
				$mobile02 = $currentSheet->getCell($index_mobile02)->getValue();
				$address = $currentSheet->getCell($index_address)->getValue();
				$init_receivables = $currentSheet->getCell($index_init_receivables)->getValue();
				$init_receivables_dt = $currentSheet->getCell($index_init_receivables_dt)->getValue();
				$address_shipping = $currentSheet->getCell($index_address_shipping)->getValue();
				$address_receipt = $currentSheet->getCell($index_address_receipt)->getValue();
				$bank_name = $currentSheet->getCell($index_bank_name)->getValue();
				$bank_account = $currentSheet->getCell($index_bank_account)->getValue();
				$tax_number = $currentSheet->getCell($index_tax_number)->getValue();
				$fax = $currentSheet->getCell($index_fax)->getValue();
				$note = $currentSheet->getCell($index_note)->getValue();
				
				// 如果为空则直接读取下一条记录
				if (! $category || ! $code || ! $name)
					continue;
				
				$categoryId = null;
				
				if ($categories["{$category}"]) {
					$categoryId = $categories["{$category}"];
				} else {
					$sql = "select id, code from t_customer_category where code = '%s' ";
					$data = $db->query($sql, $category);
					if (! $data) {
						// 新增分类
						continue;
					} else {
						$categoryId = $data[0]["id"];
					}
					$categories += array(
							"{$category}" => "{$categoryId}"
					);
				}
				
				// 新增
				// 检查商品编码是否唯一
				$sql = "select 1 from t_customer where code = '%s' ";
				$data = $db->query($sql, $code);
				if ($data) {
					$message .= "编码为 [{$code}] 的客户已经存在; \r\n";
					continue;
				}
				
				$id = $idGen->newId();
				$py = $ps->toPY($name);
				
				// Sql
				$insertSql .= $dataSql;
				// 数据参数加入
				array_push($params, $id, $categoryId, $code, $name, $py, $contact01, $qq01, $tel01, 
						$mobile01, $contact02, $qq02, $tel02, $mobile02, $address, $init_receivables, 
						$init_receivables_dt, $address_shipping, $address_receipt, $bank_name, 
						$bank_account, $tax_number, $fax, $note, $dataOrg);
			}
			
			$db->execute(rtrim($insertSql, ','), $params);
			
			$log = "导入方式新增客户;{$dataFile}";
			$bs->insertBizlog($log, "基础数据-客户");
		} catch ( Exception $e ) {
			$success = false;
			$message = $e;
		}
		
		$result = array(
				"msg" => $message,
				"success" => $success
		);
		return $result;
	}
}
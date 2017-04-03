<?php

namespace Home\Service;

use Home\DAO\PWBillDAO;

/**
 * 采购入库Service
 *
 * @author 李静波
 */
class PWBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "采购入库";

	/**
	 * 获得采购入库单主表列表
	 */
	public function pwbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new PWBillDAO($this->db());
		return $dao->pwbillList($params);
	}

	/**
	 * 获得采购入库单商品明细记录列表
	 */
	public function pwBillDetailList($pwbillId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = array(
				"id" => $pwbillId
		);
		
		$dao = new PWBillDAO($this->db());
		return $dao->pwBillDetailList($params);
	}

	/**
	 * 新建或编辑采购入库单
	 */
	public function editPWBill($json) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$id = $bill["id"];
		
		$db = $this->db();
		
		$db->startTrans();
		
		$dao = new PWBillDAO($db);
		
		$log = null;
		
		if ($id) {
			// 编辑采购入库单
			
			$rc = $dao->updatePWBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			
			$log = "编辑采购入库单: 单号 = {$ref}";
		} else {
			// 新建采购入库单
			
			$bill["companyId"] = $this->getCompanyId();
			$bill["loginUserId"] = $this->getLoginUserId();
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			
			$rc = $dao->addPWBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$pobillRef = $bill["pobillRef"];
			if ($pobillRef) {
				// 从采购订单生成采购入库单
				$log = "从采购订单(单号：{$pobillRef})生成采购入库单: 单号 = {$ref}";
			} else {
				// 手工新建采购入库单
				$log = "新建采购入库单: 单号 = {$ref}";
			}
		}
		
		// 同步库存账中的在途库存
		$rc = $dao->updateAfloatInventoryByPWBill($bill);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得某个采购入库单的信息
	 */
	public function pwBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$params["loginUserName"] = $this->getLoginUserName();
		$params["companyId"] = $this->getCompanyId();
		
		$dao = new PWBillDAO($this->db());
		return $dao->pwBillInfo($params);
	}

	/**
	 * 删除采购入库单
	 */
	public function deletePWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new PWBillDAO($db);
		$params = array(
				"id" => $id
		);
		$rc = $dao->deletePWBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$ref = $params["ref"];
		$log = "删除采购入库单: 单号 = {$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交采购入库单
	 */
	public function commitPWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = $this->db();
		$db->startTrans();
		
		$params = array(
				"id" => $id,
				"loginUserId" => $this->getLoginUserId()
		);
		
		$dao = new PWBillDAO($db);
		
		$rc = $dao->commitPWBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		
		// 业务日志
		$log = "提交采购入库单: 单号 = {$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 采购入库单生成pdf文件
	 */
	public function pdf($params) {
		if ($this->isNotOnline()) {
			return;
		}
		
		$bs = new BizConfigService();
		$productionName = $bs->getProductionName();
		
		$ref = $params["ref"];
		
		$dao = new PWBillDAO($this->db());
		
		$bill = $dao->getDataForPDF($params);
		if (! $bill) {
			return;
		}
		
		$ps = new PDFService();
		$pdf = $ps->getInstance();
		$pdf->SetTitle("采购入库单，单号：{$ref}");
		
		$pdf->setHeaderFont(Array(
				"stsongstdlight",
				"",
				16
		));
		
		$pdf->setFooterFont(Array(
				"stsongstdlight",
				"",
				14
		));
		
		$pdf->SetHeaderData("", 0, $productionName, "采购入库单");
		
		$pdf->SetFont("stsongstdlight", "", 10);
		$pdf->AddPage();
		
		/**
		 * 注意：
		 * TCPDF中，用来拼接HTML的字符串需要用单引号，否则HTML中元素的属性就不会被解析
		 */
		$html = '
				<table>
					<tr><td colspan="2">单号：' . $ref . '</td></tr>
					<tr><td colspan="2">供应商：' . $bill["supplierName"] . '</td></tr>
					<tr><td>业务日期：' . $bill["bizDT"] . '</td><td>入库仓库:' . $bill["warehouseName"] . '</td></tr>
					<tr><td>业务员：' . $bill["bizUserName"] . '</td><td></td></tr>
					<tr><td colspan="2">采购货款:' . $bill["goodsMoney"] . '</td></tr>
				</table>
				';
		$pdf->writeHTML($html);
		
		$html = '<table border="1" cellpadding="1">
					<tr><td>商品编号</td><td>商品名称</td><td>规格型号</td><td>数量</td><td>单位</td>
						<td>采购单价</td><td>采购金额</td>
					</tr>
				';
		foreach ( $bill["items"] as $v ) {
			$html .= '<tr>';
			$html .= '<td>' . $v["goodsCode"] . '</td>';
			$html .= '<td>' . $v["goodsName"] . '</td>';
			$html .= '<td>' . $v["goodsSpec"] . '</td>';
			$html .= '<td align="right">' . $v["goodsCount"] . '</td>';
			$html .= '<td>' . $v["unitName"] . '</td>';
			$html .= '<td align="right">' . $v["goodsPrice"] . '</td>';
			$html .= '<td align="right">' . $v["goodsMoney"] . '</td>';
			$html .= '</tr>';
		}
		
		$html .= "";
		
		$html .= '</table>';
		$pdf->writeHTML($html, true, false, true, false, '');
		
		$pdf->Output("$ref.pdf", "I");
	}
}
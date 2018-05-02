<?php

namespace Home\Service;

use Home\DAO\SOBillDAO;

/**
 * 销售订单Service
 *
 * @author 李静波
 */
class SOBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "销售订单";

	/**
	 * 获得销售订单主表信息列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function sobillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$dao = new SOBillDAO($this->db());
		return $dao->sobillList($params);
	}

	/**
	 * 获得销售订单的信息
	 */
	public function soBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$params["loginUserName"] = $this->getLoginUserName();
		$params["companyId"] = $this->getCompanyId();
		
		$dao = new SOBillDAO($this->db());
		return $dao->soBillInfo($params);
	}

	/**
	 * 新增或编辑销售订单
	 */
	public function editSOBill($json) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = $this->db();
		
		$db->startTrans();
		
		$dao = new SOBillDAO($db);
		
		$id = $bill["id"];
		
		$log = null;
		
		$bill["companyId"] = $this->getCompanyId();
		
		if ($id) {
			// 编辑
			
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->updateSOBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			
			$log = "编辑销售订单，单号：{$ref}";
		} else {
			// 新建销售订单
			
			$bill["loginUserId"] = $this->getLoginUserId();
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			
			$rc = $dao->addSOBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$log = "新建销售订单，单号：{$ref}";
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得销售订单的明细信息
	 */
	public function soBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["companyId"] = $this->getCompanyId();
		
		$dao = new SOBillDAO($this->db());
		return $dao->soBillDetailList($params);
	}

	/**
	 * 删除销售订单
	 */
	public function deleteSOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = $this->db();
		
		$db->startTrans();
		
		$dao = new SOBillDAO($db);
		$rc = $dao->deleteSOBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		$log = "删除销售订单，单号：{$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 审核销售订单
	 */
	public function commitSOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = $this->db();
		
		$db->startTrans();
		
		$dao = new SOBillDAO($db);
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$rc = $dao->commitSOBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$ref = $params["ref"];
		$log = "审核销售订单，单号：{$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 取消销售订单审核
	 */
	public function cancelConfirmSOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = $this->db();
		
		$db->startTrans();
		
		$dao = new SOBillDAO($db);
		$rc = $dao->cancelConfirmSOBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$ref = $params["ref"];
		$log = "取消审核销售订单，单号：{$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 销售订单生成pdf文件
	 */
	public function pdf($params) {
		if ($this->isNotOnline()) {
			return;
		}
		
		$bs = new BizConfigService();
		$productionName = $bs->getProductionName();
		
		$ref = $params["ref"];
		
		$dao = new SOBillDAO($this->db());
		
		$bill = $dao->getDataForPDF($params);
		if (! $bill) {
			return;
		}
		
		// 记录业务日志
		$log = "销售订单(单号：$ref)生成PDF文件";
		$bls = new BizlogService($this->db());
		$bls->insertBizlog($log, $this->LOG_CATEGORY);
		
		ob_start();
		
		$ps = new PDFService();
		$pdf = $ps->getInstance();
		$pdf->SetTitle("销售订单，单号：{$ref}");
		
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
		
		$pdf->SetHeaderData("", 0, $productionName, "销售订单");
		
		$pdf->SetFont("stsongstdlight", "", 10);
		$pdf->AddPage();
		
		/**
		 * 注意：
		 * TCPDF中，用来拼接HTML的字符串需要用单引号，否则HTML中元素的属性就不会被解析
		 */
		$html = '
				<table>
					<tr><td colspan="2">单号：' . $ref . '</td></tr>
					<tr><td colspan="2">客户：' . $bill["customerName"] . '</td></tr>
					<tr><td>业务日期：' . $bill["bizDT"] . '</td><td>交货地址:' . $bill["dealAddress"] . '</td></tr>
					<tr><td>业务员：' . $bill["bizUserName"] . '</td><td></td></tr>
					<tr><td colspan="2">销售金额:' . $bill["saleMoney"] . '</td></tr>
				</table>
				';
		$pdf->writeHTML($html);
		
		$html = '<table border="1" cellpadding="1">
					<tr><td>商品编号</td><td>商品名称</td><td>规格型号</td><td>数量</td><td>单位</td>
						<td>单价</td><td>销售金额</td>
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
		
		ob_end_clean();
		
		$pdf->Output("$ref.pdf", "I");
	}

	/**
	 * 获得打印销售订单的数据
	 *
	 * @param array $params        	
	 */
	public function getSOBillDataForLodopPrint($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["companyId"] = $this->getCompanyId();
		
		$dao = new SOBillDAO($this->db());
		return $dao->getSOBillDataForLodopPrint($params);
	}
}
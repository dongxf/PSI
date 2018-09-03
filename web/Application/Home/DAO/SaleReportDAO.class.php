<?php

namespace Home\DAO;

/**
 * 销售报表 DAO
 *
 * @author 李静波
 */
class SaleReportDAO extends PSIBaseExDAO {

	/**
	 * 销售日报表(按商品汇总) - 查询数据
	 *
	 * @param array $params        	
	 */
	public function saleDayByGoodsQueryData($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$dt = $params["dt"];
		
		$sort = $params["sort"];
		$sortProperty = "goods_code";
		$sortDirection = "ASC";
		if ($sort) {
			$sortJSON = json_decode(html_entity_decode($sort), true);
			if ($sortJSON) {
				$sortProperty = strtolower($sortJSON[0]["property"]);
				if ($sortProperty == strtolower("goodsCode")) {
					$sortProperty = "goods_code";
				} else if ($sortProperty == strtolower("saleMoney")) {
					$sortProperty = "sale_money";
				} else if ($sortProperty == strtolower("saleCount")) {
					$sortProperty = "sale_count";
				} else if ($sortProperty == strtolower("rejMoney")) {
					$sortProperty = "rej_money";
				} else if ($sortProperty == strtolower("rejCount")) {
					$sortProperty = "rej_count";
				}
				
				$sortDirection = strtoupper($sortJSON[0]["direction"]);
				if ($sortDirection != "ASC" && $sortDirection != "DESC") {
					$sortDirection = "ASC";
				}
			}
		}
		
		$result = [];
		
		// 创建临时表保存数据
		$sql = "CREATE TEMPORARY TABLE psi_sale_report (
					biz_dt datetime,
					goods_id varchar(255), goods_code varchar(255), goods_name varchar(255), goods_spec varchar(255), 
					unit_name varchar(255), sale_money decimal(19,2), sale_count decimal(19,8),
					rej_money decimal(19,2), rej_count decimal(19, 8), m decimal(19,2), c decimal(19,8),
					profit decimal(19,2), rate decimal(19, 2)
				)";
		$db->execute($sql);
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name
				from t_goods g, t_goods_unit u
				where g.unit_id = u.id and g.id in(
					select distinct d.goods_id
					from t_ws_bill w, t_ws_bill_detail d
					where w.id = d.wsbill_id and w.bizdt = '%s' and w.bill_status >= 1000
						and w.company_id = '%s'
					union
					select distinct d.goods_id
					from t_sr_bill s, t_sr_bill_detail d
					where s.id = d.srbill_id and s.bizdt = '%s' and s.bill_status = 1000
						and s.company_id = '%s'
					)
					order by g.code";
		$items = $db->query($sql, $dt, $companyId, $dt, $companyId);
		
		foreach ( $items as $v ) {
			$goodsId = $v["id"];
			$goodsCode = $v["code"];
			$goodsName = $v["name"];
			$goodsSpec = $v["spec"];
			$unitName = $v["unit_name"];
			$goodsId = $v["id"];
			
			$sql = "select sum(d.goods_money) as goods_money, sum(d.inventory_money) as inventory_money,
						sum(convert(d.goods_count, $fmt)) as goods_count
					from t_ws_bill w, t_ws_bill_detail d
					where w.id = d.wsbill_id and w.bizdt = '%s' and d.goods_id = '%s'
					and w.bill_status >= 1000 and w.company_id = '%s' ";
			$data = $db->query($sql, $dt, $goodsId, $companyId);
			$saleCount = $data[0]["goods_count"];
			if (! $saleCount) {
				$saleCount = 0;
			}
			$saleMoney = $data[0]["goods_money"];
			if (! $saleMoney) {
				$saleMoney = 0;
			}
			$saleInventoryMoney = $data[0]["inventory_money"];
			if (! $saleInventoryMoney) {
				$saleInventoryMoney = 0;
			}
			
			$sql = "select sum(convert(d.rejection_goods_count, $fmt)) as rej_count,
						sum(d.rejection_sale_money) as rej_money,
						sum(d.inventory_money) as rej_inventory_money
					from t_sr_bill s, t_sr_bill_detail d
					where s.id = d.srbill_id and s.bizdt = '%s' and d.goods_id = '%s'
						and s.bill_status = 1000 and s.company_id = '%s' ";
			$data = $db->query($sql, $dt, $goodsId, $companyId);
			$rejCount = $data[0]["rej_count"];
			if (! $rejCount) {
				$rejCount = 0;
			}
			$rejSaleMoney = $data[0]["rej_money"];
			if (! $rejSaleMoney) {
				$rejSaleMoney = 0;
			}
			$rejInventoryMoney = $data[0]["rej_inventory_money"];
			if (! $rejInventoryMoney) {
				$rejInventoryMoney = 0;
			}
			
			$c = $saleCount - $rejCount;
			$m = $saleMoney - $rejSaleMoney;
			$c = number_format($c, $dataScale, ".", "");
			$profit = $saleMoney - $rejSaleMoney - $saleInventoryMoney + $rejInventoryMoney;
			$rate = 0;
			if ($m > 0) {
				$rate = $profit / $m * 100;
			}
			
			$sql = "insert into psi_sale_report (biz_dt, goods_code, goods_name, goods_spec, unit_name, 
						sale_money, sale_count, rej_money, rej_count, m, c, profit, rate)
					values ('%s', '%s', '%s', '%s', '%s', 
							%f, %f, %f, %f, %f, %f, %f, %f)";
			$db->execute($sql, $dt, $goodsCode, $goodsName, $goodsSpec, $unitName, $saleMoney, 
					$saleCount, $rejSaleMoney, $rejCount, $m, $c, $profit, $rate);
		}
		
		$sql = "select biz_dt, goods_code, goods_name, goods_spec, unit_name,
					sale_money, convert(sale_count, $fmt) as sale_count, rej_money, 
					convert(rej_count, $fmt) as rej_count, m, convert(c, $fmt) as c, profit, rate 
				from psi_sale_report
				order by %s %s
				limit %d, %d
				";
		$data = $db->query($sql, $sortProperty, $sortDirection, $start, $limit);
		foreach ( $data as $v ) {
			$result[] = [
					"bizDT" => $this->toYMD($v["biz_dt"]),
					"goodsCode" => $v["goods_code"],
					"goodsName" => $v["goods_name"],
					"goodsSpec" => $v["goods_spec"],
					"saleCount" => $v["sale_count"],
					"unitName" => $v["unit_name"],
					"saleMoney" => $v["sale_money"],
					"rejCount" => $v["rej_count"],
					"rejMoney" => $v["rej_money"],
					"c" => $v["c"],
					"m" => $v["m"],
					"profit" => $v["profit"],
					"rate" => $v["rate"] == 0 ? null : sprintf("%0.2f", $v["rate"]) . "%"
			];
		}
		
		$sql = "select count(*) as cnt
				from t_goods g, t_goods_unit u
				where g.unit_id = u.id and g.id in(
					select distinct d.goods_id
					from t_ws_bill w, t_ws_bill_detail d
					where w.id = d.wsbill_id and w.bizdt = '%s' and w.bill_status >= 1000
						and w.company_id = '%s'
					union
					select distinct d.goods_id
					from t_sr_bill s, t_sr_bill_detail d
					where s.id = d.srbill_id and s.bizdt = '%s' and s.bill_status = 1000
						and s.company_id = '%s'
					)
				";
		$data = $db->query($sql, $dt, $companyId, $dt, $companyId);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
}
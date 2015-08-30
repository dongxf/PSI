<?php

namespace Home\Service;

/**
 * 现金Service
 *
 * @author 李静波
 */
class CashService extends PSIBaseService {

	public function cashList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$dtFrom = $params["dtFrom"];
		$dtTo = $params["dtTo"];
		
		$db = M();
		$result = array();
		$sql = "select biz_date, in_money, out_money, balance_money
				from t_cash
				where biz_date >= '%s' and biz_date <= '%s' 
				order by biz_date
				limit %d, %d ";
		$data = $db->query($sql, $dtFrom, $dtTo, $start, $limit);
		foreach ( $data as $i => $v ) {
			$result[$i]["bizDT"] = $this->toYMD($v["biz_date"]);
			$result[$i]["inMoney"] = $v["in_money"];
			$result[$i]["outMoney"] = $v["out_money"];
			$result[$i]["balanceMoney"] = $v["balance_money"];
		}
		
		$sql = "select count(*) as cnt
				from t_cash
				where biz_date >= '%s' and biz_date <= '%s' ";
		$data = $db->query($sql, $dtFrom, $dtTo);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
}
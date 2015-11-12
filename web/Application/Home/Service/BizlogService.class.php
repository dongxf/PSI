<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 业务日志Service
 *
 * @author 李静波
 */
class BizlogService extends PSIBaseService {

	/**
	 * 返回日志列表
	 */
	public function logList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		
		$sql = "select b.id, u.login_name, u.name, b.ip, b.info, b.date_created, 
					b.log_category, b.ip_from 
				from t_biz_log b, t_user u
				where b.user_id = u.id ";
		$queryParams = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::BIZ_LOG, "b", $queryParams);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= " order by b.date_created desc
				limit %d, %d ";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		
		$data = $db->query($sql, $queryParams);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["loginName"] = $v["login_name"];
			$result[$i]["userName"] = $v["name"];
			$result[$i]["ip"] = $v["ip"];
			$result[$i]["ipFrom"] = $v["ip_from"];
			$result[$i]["content"] = $v["info"];
			$result[$i]["dt"] = $v["date_created"];
			$result[$i]["logCategory"] = $v["log_category"];
		}
		
		$sql = "select count(*) as cnt 
				from t_biz_log b, t_user u
				where b.user_id = u.id";
		$queryParams = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::BIZ_LOG, "b", $queryParams);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"logs" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 记录业务日志
	 *
	 * @param string $log
	 *        	日志内容
	 * @param string $category
	 *        	日志分类
	 */
	public function insertBizlog($log, $category = "系统") {
		try {
			$us = new UserService();
			if ($us->getLoginUserId() == null) {
				return;
			}
			
			$dataOrg = $us->getLoginUserDataOrg();
			
			$ip = session("PSI_login_user_ip");
			if ($ip == null || $ip == "") {
				$ip = $this->getClientIP();
			}
			
			$ipFrom = session("PSI_login_user_ip_from");
			
			$sql = "insert into t_biz_log (user_id, info, ip, date_created, log_category, data_org, ip_from) 
					values ('%s', '%s', '%s',  now(), '%s', '%s', '%s')";
			M()->execute($sql, $us->getLoginUserId(), $log, $ip, $category, $dataOrg, $ipFrom);
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
}
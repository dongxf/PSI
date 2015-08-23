<?php

namespace Home\Service;

/**
 * 业务日志Service
 *
 * @author 李静波
 */
class BizlogService extends PSIBaseService {

	public function logList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		
		$sql = "select b.id, u.login_name, u.name, b.ip, b.info, b.date_created, b.log_category 
				from t_biz_log b, t_user u
				where b.user_id = u.id
				order by b.date_created desc
				limit %d, %d ";
		$data = $db->query($sql, $start, $limit);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["loginName"] = $v["login_name"];
			$result[$i]["userName"] = $v["name"];
			$result[$i]["ip"] = $v["ip"];
			$result[$i]["content"] = $v["info"];
			$result[$i]["dt"] = $v["date_created"];
			$result[$i]["logCategory"] = $v["log_category"];
		}
		
		$sql = "select count(*) as cnt 
				from t_biz_log b, t_user u
				where b.user_id = u.id";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		
		return array(
				"logs" => $result,
				"totalCount" => $cnt
		);
	}

	public function insertBizlog($log, $category = "系统") {
		try {
			$us = new UserService();
			if ($us->getLoginUserId() == null) {
				return;
			}
			
			$sql = "insert into t_biz_log (user_id, info, ip, date_created, log_category) 
					values ('%s', '%s', '%s',  now(), '%s')";
			M()->execute($sql, $us->getLoginUserId(), $log, $this->getClientIP(), $category);
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

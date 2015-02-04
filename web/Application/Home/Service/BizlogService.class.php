<?php

namespace Home\Service;

/**
 * 业务日志Service
 *
 * @author 李静波
 */
class BizlogService {

    public function logList($params) {
        $page = $params["page"];
        $start = $params["start"];
        $limit = $params["limit"];

        $sql = "select b.id, u.login_name, u.name, b.ip, b.info, b.date_created, b.log_category "
                . " from t_biz_log b, t_user u"
                . " where b.user_id = u.id"
                . " order by b.date_created desc"
                . " limit " . $start . ", " . $limit;
        $data = M()->query($sql);
        $result = array();

        foreach ($data as $i => $v) {
            $result[$i]["id"] = $v["id"];
            $result[$i]["loginName"] = $v["login_name"];
            $result[$i]["userName"] = $v["name"];
            $result[$i]["ip"] = $v["ip"];
            $result[$i]["content"] = $v["info"];
            $result[$i]["dt"] = $v["date_created"];
            $result[$i]["logCategory"] = $v["log_category"];
        }

        return $result;
    }

    public function logTotalCount() {
        $sql = "select count(*) as cnt "
                . " from t_biz_log b, t_user u"
                . " where b.user_id = u.id";
        $data = M()->query($sql);
        return $data[0]["cnt"];
    }

    public function insertBizlog($log, $category = "系统") {
        try {
            $us = new UserService();
			if ($us->getLoginUserId() == null) {
				return;
			}

            $sql = "insert into t_biz_log (user_id, info, ip, date_created, log_category) "
                    . " values ('%s', '%s', '%s',  now(), '%s')";
            M()->execute($sql, $us->getLoginUserId(), $log, $this->getClientIP(), $category);
        } catch (Exception $ex) {
            // TODO log 
        }
    }

	private function getClientIP() {
		$xRealIP = $_SERVER["X-REAL-IP"];
		if ($xRealIP) {
			return $xRealIP;
		} else {
			return get_client_ip();
		}
	}
}

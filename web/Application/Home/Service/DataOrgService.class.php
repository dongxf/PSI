<?php

namespace Home\Service;

/**
 * 数据域Service
 *
 * @author 李静波
 */
class DataOrgService extends PSIBaseService {

	public function buildSQL($fid, $tableName, $queryParams) {
		$us = new UserService();
		$userDataOrg = $us->getLoginUserDataOrg();
		
		$dataOrgList = $us->getDataOrgForFId($fid);
		if (count($dataOrgList) == 0) {
			return null; // 没有数据域
		}
		
		$result = " ( ";
		foreach ( $dataOrgList as $i => $dataOrg ) {
			if ($dataOrg == "*") {
				return "";
			}
			
			if ($i > 0) {
				$result .= " or ";
			}
			
			if ($dataOrg == "#") {
				$result .= $tableName . ".data_org = '%s' ";
				$queryParams[] = $userDataOrg;
				
				continue;
			}
			
			$result .= "left(" . $tableName . ".data_org, %d) = '%s' ";
			$queryParams[] = strlen($dataOrg);
			$queryParams[] = $dataOrg;
		}
		
		$result .= " ) ";
		
		return $result;
	}
}
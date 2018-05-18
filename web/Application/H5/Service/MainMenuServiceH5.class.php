<?php

namespace H5\Service;

use Home\Service\PSIBaseExService;
use Home\DAO\MainMenuDAOH5;

/**
 * 主菜单Service for H5
 *
 * @author 李静波
 */
class MainMenuServiceH5 extends PSIBaseExService {

	public function mainMenuItems() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"]=$this->getLoginUserId();
		
		$dao = new MainMenuDAOH5($this->db());
		return $dao->mainMenuItems($params);
	}
}
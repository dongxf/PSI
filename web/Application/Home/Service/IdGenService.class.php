<?php
namespace Home\Service;

/**
 * 生成UUIDService
 *
 * @author 李静波
 */
class IdGenService {
	public function newId() {
		$data = M()->query("select UUID() as uuid");
		if (!$data) {
			return strtoupper(uniqid());
		} else {
			return strtoupper($data[0]["uuid"]);
		}
	}
}

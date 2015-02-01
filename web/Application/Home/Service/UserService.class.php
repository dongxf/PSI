<?php

namespace Home\Service;

/**
 * 用户Service
 *
 * @author 李静波
 */
class UserService extends PSIBaseService {

	public function hasPermission($fid = null) {
		$result = session("loginUserId") != null;
		if (!result) {
			return false;
		}

		$idList = array("-9997", "-9999", "-9996");
		if ($fid == null || in_array($fid, $idList)) {
			return $result;
		}

		$userId = $this->getLoginUserId();
		$sql = "select count(*) as cnt from "
				. " t_role_user ru, t_role_permission rp, t_permission p "
				. " where ru.user_id = '%s' and ru.role_id = rp.role_id "
				. "     and rp.permission_id = p.id and p.fid = '%s' ";
		$data = M()->query($sql, $userId, $fid);

		return $data[0]["cnt"] > 0;
	}

	public function getLoginUserId() {
		return session("loginUserId");
	}

	public function getLoginUserName() {
		$sql = "select name from t_user where id = '%s' ";

		$data = M()->query($sql, $this->getLoginUserId());

		if ($data) {
			return $data[0]["name"];
		} else {
			return "";
		}
	}

	public function getLoginName() {
		$sql = "select login_name from t_user where id = '%s' ";

		$data = M()->query($sql, $this->getLoginUserId());

		if ($data) {
			return $data[0]["login_name"];
		} else {
			return "";
		}
	}

	public function doLogin($loginName, $password) {
		$sql = "select id from t_user where login_name = '%s' and password = '%s' and enabled = 1";

		$user = M()->query($sql, $loginName, md5($password));

		if ($user) {
			session("loginUserId", $user[0]["id"]);

			(new BizlogService())->insertBizlog("登录系统");
			return $this->ok();
		} else {
			return $this->bad("用户名或者密码错误");
		}
	}

	public function allOrgs() {
		$sql = "select id, name,  org_code, full_name "
				. " from t_org where parent_id is null order by org_code";
		$db = M();
		$orgList1 = $db->query($sql);
		$result = array();

		// 第一级组织
		foreach ($orgList1 as $i => $org1) {
			$result[$i]["id"] = $org1["id"];
			$result[$i]["text"] = $org1["name"];
			$result[$i]["orgCode"] = $org1["org_code"];
			$result[$i]["fullName"] = $org1["full_name"];

			// 第二级
			$sql = "select id, name,  org_code, full_name "
					. " from t_org where parent_id = '%s' order by org_code";
			$orgList2 = $db->query($sql, $org1["id"]);

			$c2 = array();
			foreach ($orgList2 as $j => $org2) {
				$c2[$j]["id"] = $org2["id"];
				$c2[$j]["text"] = $org2["name"];
				$c2[$j]["orgCode"] = $org2["org_code"];
				$c2[$j]["fullName"] = $org2["full_name"];
				$c2[$j]["expanded"] = true;

				// 第三级
				$sql = "select id, name,  org_code, full_name "
						. " from t_org where parent_id = '%s' order by org_code";
				$orgList3 = $db->query($sql, $org2["id"]);
				$c3 = array();
				foreach ($orgList3 as $k => $org3) {
					$c3[$k]["id"] = $org3["id"];
					$c3[$k]["text"] = $org3["name"];
					$c3[$k]["orgCode"] = $org3["org_code"];
					$c3[$k]["fullName"] = $org3["full_name"];
					$c3[$k]["children"] = array();
					$c3[$k]["leaf"] = true;
				}

				$c2[$j]["children"] = $c3;
				$c2[$j]["leaf"] = count($c3) == 0;
			}

			$result[$i]["children"] = $c2;
			$result[$i]["leaf"] = count($orgList2) == 0;
			$result[$i]["expanded"] = true;
		}

		return $result;
	}

	public function users($orgId) {
		$sql = "select id, login_name,  name, enabled, org_code from t_user where org_id = '%s' ";

		$data = M()->query($sql, $orgId);

		$result = array();

		foreach ($data as $key => $value) {
			$result[$key]["id"] = $value["id"];
			$result[$key]["loginName"] = $value["login_name"];
			$result[$key]["name"] = $value["name"];
			$result[$key]["enabled"] = $value["enabled"];
			$result[$key]["orgCode"] = $value["org_code"];
		}

		return $result;
	}

	public function editOrg($id, $name, $parentId, $orgCode) {
		if ($id) {
			// 编辑
			if ($parentId == $id) {
				return $this->bad("上级组织不能是自身");
			}

			if ($parentId == "root") {
				$parentId = null;
			}

			if ($parentId == null) {
				$sql = "update t_org set name = '%s', full_name = '%s', org_code = '%s', parent_id = null"
						. " where id = '%s' ";
				M()->execute($sql, $name, $name, $orgCode, $id);
			} else {
				$db = M();

				$tempParentId = $parentId;
				while ($tempParentId != null) {
					$sql = "select parent_id from t_org where id = '%s' ";
					$d = $db->query($sql, $tempParentId);
					if ($d) {
						$tempParentId = $d[0]["parent_id"];

						if ($tempParentId == $id) {
							return $this->bad("不能选择下级组织作为上级组织");
						}
					} else {
						$tempParentId = null;
					}
				}

				$sql = "select full_name from t_org where id = '%s' ";
				$data = $db->query($sql, $parentId);
				if ($data) {
					$parentFullName = $data[0]["full_name"];

					$sql = "update t_org set name = '%s', full_name = '%s', org_code = '%s', parent_id = '%s' "
							. " where id = '%s' ";
					$db->execute($sql, $name, $parentFullName . "\\" . $name, $orgCode, $parentId, $id);
				} else {
					return $this->bad("上级组织不存在");
				}
			}

			return $this->OK($id);
		} else {
			// 新增
			$idGenService = new IdGenService();
			$id = $idGenService->newId();

			$db = M();
			$sql = "select full_name from t_org where id = '%s' ";
			$parentOrg = $db->query($sql, $parentId);
			$fullName = "";
			if (!$parentOrg) {
				$parentId = null;
				$fullName = $name;
			} else {
				$fullName = $parentOrg[0]["full_name"] . "\\" . $name;
			}

			if ($parentId == null) {
				$sql = "insert into t_org (id, name, full_name, org_code, parent_id) "
						. " values ('%s', '%s', '%s', '%s', null)";

				$db->execute($sql, $id, $name, $fullName, $orgCode);
			} else {
				$sql = "insert into t_org (id, name, full_name, org_code, parent_id) "
						. " values ('%s', '%s', '%s', '%s', '%s')";

				$db->execute($sql, $id, $name, $fullName, $orgCode, $parentId);
			}
		}
		
		return $this->ok($id);
	}

	public function orgParentName($id) {
		$db = M();

		$data = $db->query("select parent_id from t_org where id = '%s' ", $id);

		if ($data) {
			$parentId = $data[0]["parent_id"];

			if ($parentId == null) {
				return "";
			} else {
				$data = $db->query("select full_name from t_org where id = '%s' ", $parentId);

				if ($data) {
					$result["parentOrgName"] = $data[0]["full_name"];
					return $result;
				} else {
					return "";
				}
			}
		} else {
			return "";
		}
	}

	public function deleteOrg($id) {
		$db = M();
		$sql = "select count(*) as cnt from t_org where parent_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("当前组织机构还有下级组织，不能删除");
		}

		$sql = "select count(*) as cnt from t_user where org_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("当前组织机构还有用户，不能删除");
		}

		$sql = "delete from t_org where id = '%s' ";
		$db->execute($sql, $id);

		return $this->ok();
	}

	public function editUser($params) {
		$id = $params["id"];
		$loginName = $params["loginName"];
		$name = $params["name"];
		$orgCode = $params["orgCode"];
		$orgId = $params["orgId"];
		$enabled = $params["enabled"];

		$py = (new PinyinService())->toPY($name);

		if ($id) {
			// 修改
			// 检查登录名是否被使用
			$db = M();
			$sql = "select count(*) as cnt from t_user where login_name = '%s' and id <> '%s' ";
			$data = $db->query($sql, $loginName, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("登录名 [$loginName] 已经存在");
			}

			// 检查组织机构是否存在
			$sql = "select count(*) as cnt from t_org where id = '%s' ";
			$data = $db->query($sql, $orgId);
			$cnt = $data[0]["cnt"];
			if ($cnt != 1) {
				return $this->bad("组织机构不存在");
			}

			$sql = "update t_user "
					. " set login_name = '%s', name = '%s', org_code = '%s', "
					. "       org_id = '%s', enabled = %d, py = '%s' "
					. " where id = '%s' ";
			$db->execute($sql, $loginName, $name, $orgCode, $orgId, $enabled, 
					$py, $id);
		} else {
			// 新建
			// 检查登录名是否被使用
			$db = M();
			$sql = "select count(*) as cnt from t_user where login_name = '%s' ";
			$data = $db->query($sql, $loginName);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("登录名 [$loginName] 已经存在");
			}

			// 检查组织机构是否存在
			$sql = "select count(*) as cnt from t_org where id = '%s' ";
			$data = $db->query($sql, $orgId);
			$cnt = $data[0]["cnt"];
			if ($cnt != 1) {
				return $this->bad("组织机构不存在");
			}

			// 新增用户的默认密码
			$password = md5("123456");

			$idGen = new IdGenService();
			$id = $idGen->newId();

			$sql = "insert into t_user (id, login_name, name, org_code, org_id, enabled, password, py) "
					. " values ('%s', '%s', '%s', '%s', '%s', %d, '%s', '%s') ";
			$db->execute($sql, $id, $loginName, $name, $orgCode, $orgId, 
					$enabled, $password, $py);
		}
		
		return $this->ok($id);
	}

	public function deleteUser($params) {
		$id = $params["id"];

		// TODO: 临时代码
		if ($id == "6C2A09CD-A129-11E4-9B6A-782BCBD7746B") {
			return $this->bad("不能删除系统管理员用户");
		}
		// TODO:　检查用户是否存在，以及是否能删除

		$sql = "delete from t_user where id = '%s' ";

		M()->execute($sql, $id);

		return $this->ok();
	}

	public function changePassword($params) {
		$id = $params["id"];
		$password = $params["password"];
		if (strlen($password) < 5) {
			return $this->bad("密码长度不能小于5位");
		}

		$sql = "update t_user "
				. " set password = '%s' "
				. " where id = '%s' ";
		M()->execute($sql, md5($password), $id);

		return $this->ok();
	}

	public function clearLoginUserInSession() {
		session("loginUserId", null);
	}

	public function changeMyPassword($params) {
		$userId = $params["userId"];
		$oldPassword = $params["oldPassword"];
		$newPassword = $params["newPassword"];

		if ($userId != $this->getLoginUserId()) {
			return $this->bad("服务器环境发生变化，请重新登录后再操作");
		}

		// 检验旧密码
		$db = M();
		$sql = "select count(*) as cnt from t_user where id = '%s' and password = '%s' ";
		$data = $db->query($sql, $userId, md5($oldPassword));
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("旧密码不正确");
		}

		if (strlen($newPassword) < 5) {
			return $this->bad("密码长度不能小于5位");
		}

		$sql = "update t_user "
				. " set password = '%s' "
				. " where id = '%s' ";
		$db->execute($sql, md5($newPassword), $userId);

		return $this->ok();
	}

	public function queryData($queryKey) {
		if (!$queryKey) {
			return [];
		}
		
		$sql = "select id, login_name, name from t_user "
				. " where login_name like '%s' or name like '%s' or py like '%s' "
				. " order by login_name "
				. " limit 20";
		$key = "%{$queryKey}%";
		$data = M()->query($sql, $key, $key, $key);
		$result = [];
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["loginName"] = $v["login_name"];
			$result[$i]["name"] = $v["name"];
		}
		return $result;
	}
}

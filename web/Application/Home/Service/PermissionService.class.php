<?php

namespace Home\Service;

/**
 * 权限 Service
 *
 * @author 李静波
 */
class PermissionService extends PSIBaseService {

	public function roleList() {
		$sql = "select id, name from t_role order by name";
		$data = M()->query($sql);

		return $data;
	}

	public function permissionList($roleId) {
		$sql = "select p.id, p.name"
				. " from t_role r, t_role_permission rp, t_permission p "
				. " where r.id = rp.role_id and r.id = '%s' and rp.permission_id = p.id "
				. " order by p.name";
		$data = M()->query($sql, $roleId);

		return $data;
	}

	public function userList($roleId) {
		$sql = "select u.id, u.login_name, u.name, org.full_name"
				. " from t_role r, t_role_user ru, t_user u, t_org org "
				. " where r.id = ru.role_id and r.id = '%s' and ru.user_id = u.id and u.org_id = org.id"
				. " order by org.full_name ";
		$data = M()->query($sql, $roleId);
		$result = array();

		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["orgFullName"] = $v["full_name"];
			$result[$i]["loginName"] = $v["login_name"];
		}

		return $result;
	}

	public function editRole($params) {
		$id = $params["id"];
		$name = $params["name"];
		$permissionIdList = $params["permissionIdList"];
		$userIdList = $params["userIdList"];

		$db = M();

		$pid = explode(",", $permissionIdList);
		$uid = explode(",", $userIdList);

		if ($id) {
			// 编辑
			$db->startTrans();
			try {
				$sql = "update t_role set name = '%s' where id = '%s' ";
				$db->execute($sql, $name, $id);

				$sql = "delete from t_role_permission where role_id = '%s' ";
				$db->execute($sql, $id);
				$sql = "delete from t_role_user where role_id = '%s' ";
				$db->execute($sql, $id);

				if ($pid) {
					foreach ($pid as $v) {
						$sql = "insert into t_role_permission (role_id, permission_id) "
								. " values ('%s', '%s')";
						$db->execute($sql, $id, $v);
					}
				}

				if ($uid) {
					foreach ($uid as $v) {
						$sql = "insert into t_role_user (role_id, user_id) "
								. " values ('%s', '%s') ";
						$db->execute($sql, $id, $v);
					}
				}

				$db->commit();
			} catch (Exception $exc) {
				$db->rollback();

				return $this->bad("数据库操作错误，请联系管理员");
			}
		} else {
			// 新增

			$idGen = new IdGenService();
			$id = $idGen->newId();

			$db->startTrans();
			try {
				$sql = "insert into t_role (id, name) values ('%s', '%s') ";
				$db->execute($sql, $id, $name);

				if ($pid) {
					foreach ($pid as $v) {
						$sql = "insert into t_role_permission (role_id, permission_id) "
								. " values ('%s', '%s')";
						$db->execute($sql, $id, $v);
					}
				}

				if ($uid) {
					foreach ($uid as $v) {
						$sql = "insert into t_role_user (role_id, user_id) "
								. " values ('%s', '%s') ";
						$db->execute($sql, $id, $v);
					}
				}

				$db->commit();
			} catch (Exception $exc) {
				$db->rollback();

				return $this->bad("数据库操作错误，请联系管理员");
			}
		}

		return $this->ok($id);
	}

	public function selectPermission($idList) {
		$list = explode(",", $idList);
		if (!$list) {
			return array();
		}

		$result = array();

		$sql = "select id, name"
				. " from t_permission"
				. " order by name";
		$data = M()->query($sql);

		$index = 0;

		foreach ($data as $v) {
			if (!in_array($v["id"], $list)) {
				$result[$index]["id"] = $v["id"];
				$result[$index]["name"] = $v["name"];

				$index++;
			}
		}

		return $result;
	}

	public function selectUsers($idList) {
		$list = explode(",", $idList);
		if (!$list) {
			return array();
		}

		$result = array();

		$sql = "select u.id, u.name, u.login_name, o.full_name"
				. " from t_user u, t_org o "
				. " where u.org_id = o.id "
				. " order by u.name";
		$data = M()->query($sql);

		$index = 0;

		foreach ($data as $v) {
			if (!in_array($v["id"], $list)) {
				$result[$index]["id"] = $v["id"];
				$result[$index]["name"] = $v["name"];
				$result[$index]["loginName"] = $v["login_name"];
				$result[$index]["orgFullName"] = $v["full_name"];

				$index++;
			}
		}

		return $result;
	}

	public function deleteRole($id) {
		$db = M();

		$db->startTrans();
		try {
			$sql = "delete from t_role_permission where role_id = '%s' ";
			$db->execute($sql, $id);

			$sql = "delete from t_role_user  where role_id = '%s' ";
			$db->execute($sql, $id);

			$sql = "delete from t_role where id = '%s' ";
			$db->execute($sql, $id);

			$db->commit();
		} catch (Exception $exc) {
			$db->rollback();

			return $this->bad("数据库错误，请联系管理员");
		}
		
		return $this->ok();
	}

}

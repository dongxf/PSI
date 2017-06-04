<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>, 李静波 <crm8000@qq.com>
// +----------------------------------------------------------------------
namespace Think\Session\Driver;

/**
 * 修补ThinkPHP在PHP7下的错误
 *
 *
 * 数据库方式Session驱动
 * CREATE TABLE think_session (
 * session_id varchar(255) NOT NULL,
 * session_expire int(11) NOT NULL,
 * session_data blob,
 * UNIQUE KEY `session_id` (`session_id`)
 * );
 */
class Db_psi {
	
	/**
	 * Session有效时间
	 */
	private $lifeTime = '';
	
	/**
	 *
	 * @var \mysqli
	 */
	private $mysqli = null;

	/**
	 * 打开Session
	 *
	 * @access public
	 * @param string $savePath        	
	 * @param mixed $sessName        	
	 */
	public function open($savePath, $sessName) {
		$this->lifeTime = 24 * 60 * 60; // 一天
		
		$this->createMySQLi();
		
		return $this->mysqli != null;
	}

	private function createMySQLi() {
		$hostName = C('DB_HOST');
		$portNumber = intval(C('DB_PORT'));
		$databaseName = C('DB_NAME');
		$userName = C('DB_USER');
		$password = C('DB_PWD');
		
		$this->mysqli = new \mysqli($hostName, $userName, $password, $databaseName, $portNumber);
	}

	/**
	 * 关闭Session
	 *
	 * @access public
	 */
	public function close() {
		$this->gc($this->lifeTime);
		
		if (! $this->mysqli) {
			return true;
		}
		
		return $this->mysqli->close();
	}

	/**
	 * 读取Session
	 *
	 * @access public
	 * @param string $sessID        	
	 */
	public function read($sessID) {
		if (! $this->mysqli) {
			$this->createMySQLi();
		}
		
		$sessID = $this->mysqli->real_escape_string($sessID);
		
		$currentTime = time();
		
		$sql = "select session_data as data	
				from think_session 
				where session_id = '{$sessID}' AND session_expire > {$currentTime} ";
		$res = $this->mysqli->query($sql);
		if ($res) {
			$row = $res->fetch_row();
			$data = $row[0];
			$res->free();
			
			return $data;
		} else {
			return "";
		}
	}

	/**
	 * 写入Session
	 *
	 * @access public
	 * @param string $sessID        	
	 * @param String $sessData        	
	 */
	public function write($sessID, $sessData) {
		if (! $this->mysqli) {
			$this->createMySQLi();
		}
		
		$expire = time() + $this->lifeTime;
		
		$sessID = $this->mysqli->real_escape_string($sessID);
		$sessData = $this->mysqli->real_escape_string($sessData);
		
		$sql = "REPLACE INTO think_session (session_id, session_expire, session_data) 
				VALUES( '$sessID', $expire,  '$sessData')";
		$this->mysqli->query($sql);
		
		return $this->mysqli->affected_rows == 1;
	}

	/**
	 * 删除Session
	 *
	 * @access public
	 * @param string $sessID        	
	 */
	public function destroy($sessID) {
		if (! $this->mysqli) {
			$this->createMySQLi();
		}
		
		$sql = "DELETE FROM think_session WHERE session_id = '$sessID'";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows)
			return true;
		else
			false;
	}

	/**
	 * Session 垃圾回收
	 *
	 * @access public
	 * @param string $sessMaxLifeTime        	
	 */
	public function gc($sessMaxLifeTime) {
		if (! $this->mysqli) {
			$this->createMySQLi();
		}
		
		$sql = "DELETE FROM think_session WHERE session_expire < " . time();
		$this->mysqli->query($sql);
		
		return $this->mysqli->affected_rows;
	}
}
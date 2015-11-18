<?php

function PSI_getHost() {
	// MoPaaS V1
	$host = getenv("MOPAAS_MYSQL22118_HOST");
	if ($host) {
		return $host;
	}
	
	return "localhost";
}

function PSI_getDBName() {
	$name = getenv("MOPAAS_MYSQL22118_NAME");
	if ($name) {
		return $name;
	}
	
	return "psi";
}

function PSI_getUser() {
	$user = getenv("MOPAAS_MYSQL22118_USER");
	if ($user) {
		return $user;
	}
	
	return "root";
}

function PSI_getPassword() {
	$password = getenv("MOPAAS_MYSQL22118_PASSWORD");
	if ($password) {
		return $password;
	}
	
	return "";
}

function PSI_getPort() {
	$port = getenv("MOPAAS_MYSQL22118_PORT");
	if ($port) {
		return $port;
	}
	
	return 3306;
}

return array(
		'URL_CASE_INSENSITIVE' => false,
		'SHOW_ERROR_MSG' => true,
		'DB_TYPE' => 'mysql', // 数据库类型
		'DB_HOST' => PSI_getHost(), // 服务器地址
		'DB_NAME' => PSI_getDBName(), // 数据库名
		'DB_USER' => PSI_getUser(), // 用户名
		'DB_PWD' => PSI_getPassword(), // 密码
		'DB_PORT' => PSI_getPort()
); // 端口


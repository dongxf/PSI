<?php

function PSI_getMoPaasV2MySQLConfig() {
	$services = getenv("VCAP_SERVICES");
	$services_json = json_decode($services, true);
	$mysql_config = $services_json["MySQL-5.5"][0]["credentials"];
	
	return $mysql_config;
}

function PSI_getHost() {
	// MoPaaS V1
	$host = getenv("MOPAAS_MYSQL22118_HOST");
	if ($host) {
		return $host;
	}
	
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["hostname"];
	}
	
	// 本地单机部署，发现写IP地址比localhost，数据库要快很多
	return "127.0.0.1";
}

function PSI_getDBName() {
	// MoPaaS V1
	$name = getenv("MOPAAS_MYSQL22118_NAME");
	if ($name) {
		return $name;
	}
	
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["name"];
	}
	
	return "psi";
}

function PSI_getUser() {
	// MoPaaS V1
	$user = getenv("MOPAAS_MYSQL22118_USER");
	if ($user) {
		return $user;
	}
	
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["user"];
	}
	
	return "root";
}

function PSI_getPassword() {
	// MoPaaS V1
	$password = getenv("MOPAAS_MYSQL22118_PASSWORD");
	if ($password) {
		return $password;
	}
	
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["password"];
	}
	
	return "";
}

function PSI_getPort() {
	// MoPaaS V1
	$port = getenv("MOPAAS_MYSQL22118_PORT");
	if ($port) {
		return $port;
	}
	
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["port"];
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


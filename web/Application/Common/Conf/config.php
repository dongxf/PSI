<?php

return array(
	'DB_TYPE'   => 'mysql', // 数据库类型
	'DB_HOST'   => getenv("PAAS") == "1" ? getenv("DB_HOST") : 'localhost', // 服务器地址
	'DB_NAME'   => getenv("PAAS") == "1" ? getenv("DB_NAME") :  'psi', // 数据库名
	'DB_USER'   => getenv("PAAS") == "1" ? getenv("DB_USER") : 'root', // 用户名
	'DB_PWD'    => getenv("PAAS") == "1" ? getenv("DB_PWD") : '', // 密码
	'DB_PORT'   => getenv("PAAS") == "1" ? getenv("DB_PORT") : 3306, // 端口
);

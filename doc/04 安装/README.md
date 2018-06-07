# PSI官方支持的部署环境

PSI作为一款SaaS软件，官方支持的部署环境是：MoPaaS ( https://www.mopaas.com/ )

----------

# 以下的部署文档仅作为私有部署适合的参考文档，不担保技术上的准确性

# 环境要求：PHP 7+, MySQL 5.5+

Linux下Nginx的配置是最常遇到的问题，解决办法见：

https://my.oschina.net/u/2525829/blog/532614

在Nginx下还需要配置好 `PathInfo`

参见：http://www.nginx.cn/426.html
最简单的方法是：php.ini中设置 `cgi.fix_pathinfo=1`

`如果实在是搞不定Nginx，可以用LNMPA：` https://lnmp.org/lnmpa.html

使用其他的环境，可以参考这个配置说明：

http://www.thinkphp.cn/topic/9728.html (wamp集成环境开启rewrite伪静态支持)

# phpstudy的部署问题

PSI在phpstudy的默认安装下不能正常启动，如果自己不能解决，请改用xampp部署


# 本地开发环境

本地开发环境推荐用xampp: https://www.apachefriends.org/zh_cn/index.html

IDE: Eclipse

ExtJS插件：http://www.spket.com/

# 百度网盘

里面有 XAMPP、Eclipse和ExtJS

链接：https://pan.baidu.com/s/1cFRzZGONN75AeemXCdHPlg 密码：8sq4
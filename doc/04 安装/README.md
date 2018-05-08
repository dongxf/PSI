# 环境要求：PHP 7+, MySQL 5.5+

Linux下Nginx的配置是最常遇到的问题，解决办法见：
https://my.oschina.net/u/2525829/blog/532614

lnmp.zip 就是上面地址网页的离线包。


在Nginx下还需要配置好 `PathInfo`
http://www.nginx.cn/426.html
Nginx_PathInfo.zip是上述网页的离线包
最简单的方法是：php.ini中设置 `cgi.fix_pathinfo=1`


使用其他的环境，可以参考这个配置说明：
http://www.thinkphp.cn/topic/9728.html (wamp集成环境开启rewrite伪静态支持)

# phpstudy的部署问题
PSI在phpstudy的默认安装下不能正常启动，如果自己不能解决，请改用xampp部署


# 本地开发环境

本地开发环境推荐用xampp: https://www.apachefriends.org/zh_cn/index.html

IDE: Eclipse

ExtJS插件：http://www.spket.com/
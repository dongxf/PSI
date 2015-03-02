Ext.define("PSI.Home.MainForm", {
    extend: "Ext.panel.Panel",

    border: 0,
    layout: "fit",
    bodyPadding: 5,
    autoScroll: true,
    items: [{
            border: 0,
            html: "<h1>欢迎使用开源进销存PSI</h1><br />\n\
<p>使用帮助请点击这里：<a href='http://my.oschina.net/u/134395/blog/374195' target='_blank'>http://my.oschina.net/u/134395/blog/374195</a></p> <br />\n\
<p>免费技术支持请联系：</p>" 
            	+ "<p>QQ：1569352868 " + '<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=1569352868&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:1569352868:51" alt="点击这里给我发消息" title="点击这里给我发消息"/></a>'
            	+ "<p>Email：1569352868@qq.com  <p>QQ群：414474186 " + '<a target="_blank" href="http://shang.qq.com/wpa/qunwpa?idkey=64808ce24f2a3186ccb1f37aad9ed591bcc4fb257d09749753aca98c6c73e400"><img border="0" src="http://pub.idqqimg.com/wpa/images/group.png" alt="开源进销存PSI" title="开源进销存PSI"></a>'
            	+ "</p>" +
"<p>微信公众号：<a target='_blank' href='http://static.oschina.net/uploads/space/2015/0301/162720_8BkY_134395.jpg'>opensource-psi 点击即可扫描二维码</a></p> <br />" +
		"<p>当前版本：" + PSI.Const.VERSION + "</p>"
    }],

    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    }
});
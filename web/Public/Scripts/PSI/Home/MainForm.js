Ext.define("PSI.Home.MainForm", {
    extend: "Ext.panel.Panel",

    border: 0,
    layout: "fit",
    bodyPadding: 5,
    autoScroll: true,
    items: [{
            border: 0,
            html: "<h1>欢迎使用开源进销存PSI</h1>\n\
<p>使用帮助请点击这里：<a href='http://my.oschina.net/u/134395/blog/374195' target='_blank'>http://my.oschina.net/u/134395/blog/374195</a></p>\n\
<p>如需技术支持，请联系：QQ：1569352868 Email：1569352868@qq.com  QQ群：414474186</p>\n\
<p>当前版本：" + PSI.Const.VERSION + "</p>"
    }],

    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    }
});
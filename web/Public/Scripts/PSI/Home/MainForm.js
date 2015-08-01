Ext.define("PSI.Home.MainForm", {
    extend: "Ext.panel.Panel",

    border: 0,
    layout: "fit",
    bodyPadding: 5,
    autoScroll: true,
    items: [{
            border: 0,
            html: "<h1>欢迎使用开源进销存PSI</h1><br /><p>当前版本：" + PSI.Const.VERSION + "</p>"
    }],

    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    }
});
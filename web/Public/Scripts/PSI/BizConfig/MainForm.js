Ext.define("PSI.BizConfig.MainForm", {
    extend: "Ext.panel.Panel",
    initComponent: function () {
        var me = this;

        Ext.apply(me, {
            border: 0,
            layout: "border",
            tbar: [
                {
                    text: "关闭", iconCls: "PSI-button-exit", handler: function () {
                        location.replace(PSI.Const.BASE_URL);
                    }
                }
            ],
            items: [
                {
                    region: "center", layout: "fit", xtype: "panel", border: 0,
                    items: []
                }
            ]
        });

        me.callParent(arguments);
    }
});
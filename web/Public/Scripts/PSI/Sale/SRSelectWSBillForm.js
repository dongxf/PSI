Ext.define("PSI.Sale.SRSelectWSBillForm", {
    extend: "Ext.window.Window",
    config: {
        parentForm: null
    },
    initComponent: function () {
        var me = this;
        Ext.apply(me, {title: "选择销售出库单",
            modal: true,
            onEsc: Ext.emptyFn,
            width: 800,
            height: 500,
            layout: "border",
            items: [{
                    region: "center",
                    border: 0,
                    bodyPadding: 10,
                    layout: "fit",
                    items: []
                },
                {
                    region: "north",
                    border: 0,
                    layout: {
                        type: "table",
                        columns: 2
                    },
                    height: 100,
                    bodyPadding: 10,
                    items: [
                    ]
                }],
            listeners: {
                show: {
                    fn: me.onWndShow,
                    scope: me
                }
            },
            buttons: [{
                    text: "确定",
                    iconCls: "PSI-button-ok",
                    formBind: true,
                    handler: me.onOK,
                    scope: me
                }, {
                    text: "取消", handler: function () {
                        me.close();
                    }, scope: me
                }]
        });

        me.callParent(arguments);
    },
    onWndShow: function () {
        var me = this;
    },
    // private
    onOK: function () {
        var me = this;
        me.close();
    }
});
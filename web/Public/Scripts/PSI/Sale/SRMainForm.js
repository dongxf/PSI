Ext.define("PSI.Sale.SRMainForm", {
    extend: "Ext.panel.Panel",
    border: 0,
    layout: "border",
    initComponent: function () {
        var me = this;

        Ext.apply(me, {
            tbar: [{
                    text: "新建销售退货入库单",
                    iconCls: "PSI-button-add",
                    scope: me,
                    handler: me.onAddSRBill
                }, "-", {
                    text: "编辑销售退货入库单",
                    iconCls: "PSI-button-edit",
                    scope: me,
                    handler: me.onEditSRBill
                }, "-", {
                    text: "删除销售退货入库单",
                    iconCls: "PSI-button-delete",
                    scope: me,
                    handler: me.onDeleteSRBill
                }, "-", {
                    text: "提交入库",
                    iconCls: "PSI-button-commit",
                    scope: me,
                    handler: me.onCommit
                }, "-", {
                    text: "关闭",
                    iconCls: "PSI-button-exit",
                    handler: function () {
                        location.replace(PSI.Const.BASE_URL);
                    }
                }],
            items: [{
                    region: "north",
                    height: "30%",
                    split: true,
                    layout: "fit",
                    border: 0,
                    items: []
                }, {
                    region: "center",
                    layout: "fit",
                    border: 0,
                    items: []
                }]
        });

        me.callParent(arguments);

        me.refreshBillGrid();
    },
    refreshBillGrid: function (id) {
    },
    onAddSRBill: function () {
    },
    onEditSRBill: function () {
    },
    onDeleteSRBill: function () {
    },
    onCommit: function () {
    }
});
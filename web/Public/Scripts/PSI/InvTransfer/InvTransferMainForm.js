// 库间调拨 - 主界面
Ext.define("PSI.InvTransfer.InvTransferMainForm", {
    extend: "Ext.panel.Panel",
    border: 0,
    layout: "border",
    initComponent: function () {
        var me = this;

        Ext.apply(me, {
            tbar: [{
                    text: "新建调拨单",
                    iconCls: "PSI-button-add",
                    scope: me,
                    handler: me.onAddBill
                }, "-", {
                    text: "编辑调拨单",
                    iconCls: "PSI-button-edit",
                    scope: me,
                    handler: me.onEditBill
                }, "-", {
                    text: "删除调拨单",
                    iconCls: "PSI-button-delete",
                    scope: me,
                    handler: me.onDeleteBill
                }, "-", {
                    text: "提交调拨单",
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

    },
    
    // 新增调拨单
    onAddBill: function () {
    	PSI.MsgBox.showInfo("TODO");
    },
    
    // 编辑调拨单
    onEditBill: function () {
    	PSI.MsgBox.showInfo("TODO");
    },
    
    // 删除调拨单
    onDeleteBill: function () {
    	PSI.MsgBox.showInfo("TODO");
    },
    
    // 提交调拨单
    onCommit: function () {
    	PSI.MsgBox.showInfo("TODO");
    }
});
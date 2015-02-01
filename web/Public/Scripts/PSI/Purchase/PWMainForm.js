Ext.define("PSI.Purchase.PWMainForm", {
    extend: "Ext.panel.Panel",
    border: 0,
    layout: "border",
    initComponent: function () {
        var me = this;

        Ext.define("PSIPWBill", {
            extend: "Ext.data.Model",
            fields: ["id", "ref", "bizDate", "supplierName", "warehouseName", "inputUserName",
                "bizUserName", "billStatus", "amount"]
        });
        var storePWBill = Ext.create("Ext.data.Store", {
            autoLoad: false,
            model: "PSIPWBill",
            data: []
        });

        var gridPWBill = Ext.create("Ext.grid.Panel", {
            columnLines: true,
            columns: [
                {header: "状态", dataIndex: "billStatus", menuDisabled: true, sortable: false, width: 60},
                {header: "入库单号", dataIndex: "ref", width: 110, menuDisabled: true, sortable: false},
                {header: "业务日期", dataIndex: "bizDate", menuDisabled: true, sortable: false},
                {header: "供应商", dataIndex: "supplierName", width: 200, menuDisabled: true, sortable: false},
                {header: "采购金额", dataIndex: "amount", menuDisabled: true, sortable: false, align: "right", xtype: "numbercolumn", width: 80},
                {header: "入库仓库", dataIndex: "warehouseName", menuDisabled: true, sortable: false},
                {header: "业务员", dataIndex: "bizUserName", menuDisabled: true, sortable: false},
                {header: "录单人", dataIndex: "inputUserName", menuDisabled: true, sortable: false}
            ],
            store: storePWBill,
            listeners: {
                select: {
                    fn: me.onPWBillGridSelect,
                    scope: me
                },
                itemdblclick: {
                    fn: me.onEditPWBill,
                    scope: me
                }
            }
        });

        Ext.define("PSIPWBillDetail", {
            extend: "Ext.data.Model",
            fields: ["id", "goodsCode", "goodsName", "goodsSpec", "unitName", "goodsCount",
                "goodsMoney", "goodsPrice"]
        });
        var storePWBillDetail = Ext.create("Ext.data.Store", {
            autoLoad: false,
            model: "PSIPWBillDetail",
            data: []
        });

        var gridPWBillDetail = Ext.create("Ext.grid.Panel", {
            title: "采购入库单明细",
            columnLines: true,
            columns: [
                Ext.create("Ext.grid.RowNumberer", {text: "序号", width: 30}),
                {header: "商品编码", dataIndex: "goodsCode", menuDisabled: true, sortable: false, width: 60},
                {header: "商品名称", dataIndex: "goodsName", menuDisabled: true, sortable: false, width: 120},
                {header: "规格型号", dataIndex: "goodsSpec", menuDisabled: true, sortable: false},
                {header: "采购数量", dataIndex: "goodsCount", menuDisabled: true, sortable: false, align: "right"},
                {header: "单位", dataIndex: "unitName", menuDisabled: true, sortable: false, width: 60},
                {header: "采购单价", dataIndex: "goodsPrice", menuDisabled: true, sortable: false, align: "right", xtype: "numbercolumn", width: 60},
                {header: "采购金额", dataIndex: "goodsMoney", menuDisabled: true, sortable: false, align: "right", xtype: "numbercolumn", width: 80}
            ],
            store: storePWBillDetail,
            listeners: {
                itemdblclick: {
                    fn: me.onEditPWBillDetail,
                    scope: me
                }
            }
        });

        Ext.apply(me, {
            tbar: [
                {
                    text: "新建采购入库单", iconCls: "PSI-button-add", scope: me, handler: me.onAddPWBill
                }, "-", {
                    text: "编辑采购入库单", iconCls: "PSI-button-edit", scope: me, handler: me.onEditPWBill
                }, "-", {
                    text: "删除采购入库单", iconCls: "PSI-button-delete", scope: me, handler: me.onDeletePWBill
                }, "-", {
                    text: "提交入库", iconCls: "PSI-button-commit", scope: me, handler: me.onCommit
                }, "-", {
                    text: "关闭", iconCls: "PSI-button-exit", handler: function () {
                        location.replace(PSI.Const.BASE_URL);
                    }
                }
            ],
            items: [{
                    region: "north", height: "30%",
                    split: true, layout: "fit", border: 0,
                    items: [gridPWBill]
                }, {
                    region: "center", layout: "fit", border: 0,
                    items: [gridPWBillDetail]
                }]
        });

        me.pwBillGrid = gridPWBill;
        me.pwBillDetailGrid = gridPWBillDetail;

        me.callParent(arguments);

        me.refreshPWBillGrid();
    },
    refreshPWBillGrid: function (id) {
        var gridDetail = this.pwBillDetailGrid;
        gridDetail.setTitle("采购入库单明细");
        gridDetail.getStore().removeAll();

        var grid = this.pwBillGrid;
        var el = grid.getEl() || Ext.getBody();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Purchase/pwbillList",
            method: "POST",
            callback: function (options, success, response) {
                var store = grid.getStore();

                store.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    store.add(data);

                    if (store.getCount() > 0) {
                        if (id) {
                            var r = store.findExact("id", id);
                            if (r != -1) {
                                grid.getSelectionModel().select(r);
                            }
                        } else {
                            grid.getSelectionModel().select(0);
                        }
                    }
                }

                el.unmask();
            }
        });
    },
    onAddPWBill: function () {
        var form = Ext.create("PSI.Purchase.PWEditForm", {
            parentForm: this
        });
        form.show();
    },
    onEditPWBill: function () {
        var item = this.pwBillGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("没有选择要编辑的采购入库单");
            return;
        }
        var pwBill = item[0];

        var form = Ext.create("PSI.Purchase.PWEditForm", {
            parentForm: this,
            entity: pwBill
        });
        form.show();
    },
    onDeletePWBill: function () {
        var me = this;
        var item = me.pwBillGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要删除的采购入库单");
            return;
        }
        
        var pwBill = item[0];
        var store = me.pwBillGrid.getStore();
        var index = store.findExact("id", pwBill.get("id"));
        index--;
        var preIndex = null;
        var preItem = store.getAt(index);
        if (preItem) {
            preIndex = preItem.get("id");
        }

        var info = "请确认是否删除采购入库单: <span style='color:red'>" + pwBill.get("ref") + "</span>";
        var me = this;
        PSI.MsgBox.confirm(info, function () {
            var el = Ext.getBody();
            el.mask("正在删除中...");
            Ext.Ajax.request({
                url: PSI.Const.BASE_URL + "Home/Purchase/deletePWBill",
                method: "POST",
                params: {id: pwBill.get("id")},
                callback: function (options, success, response) {
                    el.unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.showInfo("成功完成删除操作", function () {
                                me.refreshPWBillGrid(preIndex);
                            });
                        } else {
                            PSI.MsgBox.showInfo(data.msg);
                        }
                    } else {
                        PSI.MsgBox.showInfo("网络错误", function () {
                            window.location.reload();
                        });
                    }
                }
            });
        });
    },
    onPWBillGridSelect: function () {
        this.refreshPWBillDetailGrid();
    },
    refreshPWBillDetailGrid: function (id) {
        var me = this;
        me.pwBillDetailGrid.setTitle("采购入库单明细");
        var grid = me.pwBillGrid;
        var item = me.pwBillGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            return;
        }
        var pwBill = item[0];

        var grid = me.pwBillDetailGrid;
        grid.setTitle("单号: " + pwBill.get("ref") + " 供应商: " + pwBill.get("supplierName") + " 入库仓库: " + pwBill.get("warehouseName"));
        var el = grid.getEl();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Purchase/pwBillDetailList",
            params: {pwBillId: pwBill.get("id")},
            method: "POST",
            callback: function (options, success, response) {
                var store = grid.getStore();

                store.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    store.add(data);

                    if (store.getCount() > 0) {
                        if (id) {
                            var r = store.findExact("id", id);
                            if (r != -1) {
                                grid.getSelectionModel().select(r);
                            }
                        }
                    }
                }

                el.unmask();
            }
        });
    },
    onCommit: function () {
        var item = this.pwBillGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("没有选择要提交的采购入库单");
            return;
        }
        var pwBill = item[0];

        var detailCount = this.pwBillDetailGrid.getStore().getCount();
        if (detailCount == 0) {
            PSI.MsgBox.showInfo("当前采购入库单没有录入商品明细，不能提交");
            return;
        }

        var info = "请确认是否提交单号: <span style='color:red'>" + pwBill.get("ref") + "</span> 的采购入库单?";
        var me = this;
        PSI.MsgBox.confirm(info, function () {
            var el = Ext.getBody();
            el.mask("正在提交中...");
            Ext.Ajax.request({
                url: PSI.Const.BASE_URL + "Home/Purchase/commitPWBill",
                method: "POST",
                params: {id: pwBill.get("id")},
                callback: function (options, success, response) {
                    el.unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.showInfo("成功完成提交操作", function () {
                                me.refreshPWBillGrid();
                            });
                        } else {
                            PSI.MsgBox.showInfo(data.msg);
                        }
                    } else {
                        PSI.MsgBox.showInfo("网络错误", function () {
                            window.location.reload();
                        });
                    }
                }
            });
        });
    }
});
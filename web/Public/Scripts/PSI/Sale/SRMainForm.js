// 采购退货入库 - 主界面
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
                    items: [me.getSRGrid()]
                }, {
                    region: "center",
                    layout: "fit",
                    border: 0,
                    items: [me.getSRDetailGrid()]
                }]
        });

        me.callParent(arguments);

        me.refreshSRBillGrid();
    },
    refreshSRBillGrid: function (id) {
        var gridDetail = this.getSRDetailGrid();
        gridDetail.setTitle("销售退货入库单明细");
        gridDetail.getStore().removeAll();
        Ext.getCmp("pagingToobar").doRefresh();
        this.__lastId = id;

    },
    
    // 新增销售退货入库单
    onAddSRBill: function () {
        var form = Ext.create("PSI.Sale.SREditForm", {
            parentForm: this
        });
        form.show();
    },
    
    // 编辑销售退货入库单
    onEditSRBill: function () {
        PSI.MsgBox.showInfo("正在开发中...");
    },
    
    // 删除销售退货入库单
    onDeleteSRBill: function () {
    	var me = this;
        var item = me.getSRGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要删除的销售退货入库单");
            return;
        }
        var bill = item[0];

        var info = "请确认是否删除销售退货入库单: <span style='color:red'>" + bill.get("ref")
                + "</span>";
        var me = this;
        PSI.MsgBox.confirm(info, function () {
            var el = Ext.getBody();
            el.mask("正在删除中...");
            Ext.Ajax.request({
                url: PSI.Const.BASE_URL + "Home/Sale/deleteSRBill",
                method: "POST",
                params: {
                    id: bill.get("id")
                },
                callback: function (options, success, response) {
                    el.unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.showInfo("成功完成删除操作", function () {
                                me.refreshSRBillGrid();
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
    
    // 提交销售退货入库单
    onCommit: function () {
        PSI.MsgBox.showInfo("正在开发中...");
    },
    
    getSRGrid: function () {
        var me = this;
        if (me.__srGrid) {
            return me.__srGrid;
        }

        var modelName = "PSISRBill";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["id", "ref", "bizDate", "customerName", "warehouseName",
                "inputUserName", "bizUserName", "billStatus", "amount"]
        });
        var store = Ext.create("Ext.data.Store", {
            autoLoad: false,
            model: modelName,
            data: [],
            pageSize: 20,
            proxy: {
                type: "ajax",
                actionMethods: {
                    read: "POST"
                },
                url: PSI.Const.BASE_URL + "Home/Sale/srbillList",
                reader: {
                    root: 'dataList',
                    totalProperty: 'totalCount'
                }
            }
        });
        store.on("load", function (e, records, successful) {
            if (successful) {
                me.gotoSRBillGridRecord(me.__lastId);
            }
        });

        me.__srGrid = Ext.create("Ext.grid.Panel", {
            border: 0,
            columnLines: true,
            columns: [{
                    header: "状态",
                    dataIndex: "billStatus",
                    menuDisabled: true,
                    sortable: false,
                    width: 60,
                    renderer: function(value) {
                    	return value == "待入库" ? "<span style='color:red'>" + value + "</span>" : value;
                    }
                }, {
                    header: "单号",
                    dataIndex: "ref",
                    width: 110,
                    menuDisabled: true,
                    sortable: false
                }, {
                    header: "业务日期",
                    dataIndex: "bizDate",
                    menuDisabled: true,
                    sortable: false
                }, {
                    header: "客户",
                    dataIndex: "customerName",
                    width: 200,
                    menuDisabled: true,
                    sortable: false
                }, {
                    header: "退货金额",
                    dataIndex: "amount",
                    menuDisabled: true,
                    sortable: false,
                    align: "right",
                    xtype: "numbercolumn",
                    width: 80
                }, {
                    header: "入库仓库",
                    dataIndex: "warehouseName",
                    menuDisabled: true,
                    sortable: false
                }, {
                    header: "业务员",
                    dataIndex: "bizUserName",
                    menuDisabled: true,
                    sortable: false
                }, {
                    header: "录单人",
                    dataIndex: "inputUserName",
                    menuDisabled: true,
                    sortable: false
                }],
            listeners: {
                select: {
                    fn: me.onSRBillGridSelect,
                    scope: me
                },
                itemdblclick: {
                    fn: me.onEditSRBill,
                    scope: me
                }
            },
            store: store,
            tbar: [{
                    id: "pagingToobar",
                    xtype: "pagingtoolbar",
                    border: 0,
                    store: store
                }, "-", {
                    xtype: "displayfield",
                    value: "每页显示"
                }, {
                    id: "comboCountPerPage",
                    xtype: "combobox",
                    editable: false,
                    width: 60,
                    store: Ext.create("Ext.data.ArrayStore", {
                        fields: ["text"],
                        data: [["20"], ["50"], ["100"], ["300"], ["1000"]]
                    }),
                    value: 20,
                    listeners: {
                        change: {
                            fn: function () {
                                storeWSBill.pageSize = Ext.getCmp("comboCountPerPage").getValue();
                                storeWSBill.currentPage = 1;
                                Ext.getCmp("pagingToobar").doRefresh();
                            },
                            scope: me
                        }
                    }
                }, {
                    xtype: "displayfield",
                    value: "条记录"
                }]
        });

        return me.__srGrid;
    },
    getSRDetailGrid: function() {
        var me = this;
        if (me.__srDetailGrid) {
            return me.__srDetailGrid;
        }
        
        var modelName = "PSISRBillDetail";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["id", "goodsCode", "goodsName", "goodsSpec", "unitName",
                "goodsCount", "goodsMoney", "goodsPrice", "rejCount", "rejPrice", "rejSaleMoney"]
        });
        var store = Ext.create("Ext.data.Store", {
            autoLoad: false,
            model: modelName,
            data: []
        });

        me.__srDetailGrid = Ext.create("Ext.grid.Panel", {
            title: "销售退货入库单明细",
            columnLines: true,
            columns: [Ext.create("Ext.grid.RowNumberer", {
                    text: "序号",
                    width: 30
                }), {
                    header: "商品编码",
                    dataIndex: "goodsCode",
                    menuDisabled: true,
                    sortable: false,
                    width: 60
                }, {
                    header: "商品名称",
                    dataIndex: "goodsName",
                    menuDisabled: true,
                    sortable: false,
                    width: 120
                }, {
                    header: "规格型号",
                    dataIndex: "goodsSpec",
                    menuDisabled: true,
                    sortable: false
                }, {
                    header: "退货数量",
                    dataIndex: "rejCount",
                    menuDisabled: true,
                    sortable: false,
                    align: "right"
                }, {
                    header: "单位",
                    dataIndex: "unitName",
                    menuDisabled: true,
                    sortable: false,
                    width: 60
                }, {
                    header: "退货单价",
                    dataIndex: "rejPrice",
                    menuDisabled: true,
                    sortable: false,
                    align: "right",
                    xtype: "numbercolumn",
                    width: 60
                }, {
                    header: "退货金额",
                    dataIndex: "rejSaleMoney",
                    menuDisabled: true,
                    sortable: false,
                    align: "right",
                    xtype: "numbercolumn",
                    width: 80
                }],
            store: store
        });

        return me.__srDetailGrid;
    },
    gotoSRBillGridRecord: function (id) {
        var me = this;
        var grid = me.getSRGrid();
        grid.getSelectionModel().deselectAll();
        var store = grid.getStore();
        if (id) {
            var r = store.findExact("id", id);
            if (r != -1) {
                grid.getSelectionModel().select(r);
            } else {
                grid.getSelectionModel().select(0);
            }
        } else {
            grid.getSelectionModel().select(0);
        }
    },
    onSRBillGridSelect: function() {
    	this.freshSRBillDetailGrid();
    },
    freshSRBillDetailGrid: function(id) {
        var me = this;
        me.getSRDetailGrid().setTitle("销售退货入库单明细");
        var grid = me.getSRGrid();
        var item = grid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            return;
        }
        var bill = item[0];

        grid = me.getSRDetailGrid();
        grid.setTitle("单号: " + bill.get("ref") + " 客户: "
                + bill.get("customerName") + " 入库仓库: "
                + bill.get("warehouseName"));
        var el = grid.getEl();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Sale/srBillDetailList",
            params: {
                billId: bill.get("id")
            },
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
    }
});
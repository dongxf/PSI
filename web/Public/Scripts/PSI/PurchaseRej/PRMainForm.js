// 采购退货出库 - 主界面
Ext.define("PSI.PurchaseRej.PRMainForm", {
    extend: "Ext.panel.Panel",
    border: 0,
    layout: "border",
    initComponent: function () {
        var me = this;

        Ext.apply(me, {
            tbar: [{
                    text: "新建采购退货出库单",
                    iconCls: "PSI-button-add",
                    scope: me,
                    handler: me.onAddBill
                }, "-", {
                    text: "编辑采购退货出库单",
                    iconCls: "PSI-button-edit",
                    scope: me,
                    handler: me.onEditBill
                }, "-", {
                    text: "删除采购退货出库单",
                    iconCls: "PSI-button-delete",
                    scope: me,
                    handler: me.onDeleteBill
                }, "-", {
                    text: "提交采购退货出库单",
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
                    items: [me.getMainGrid()]
                }, {
                    region: "center",
                    layout: "fit",
                    border: 0,
                    items: [me.getDetailGrid()]
                }]
        });

        me.callParent(arguments);

        me.refreshMainGrid();
    },
    
    refreshMainGrid: function (id) {
    	var me = this;
        var gridDetail = me.getDetailGrid();
        gridDetail.setTitle("采购退货出库单明细");
        gridDetail.getStore().removeAll();
        Ext.getCmp("pagingToobar").doRefresh();
        me.__lastId = id;
    },
    
    // 新增采购退货出库单
    onAddBill: function () {
    	var me = this;
    	var form = Ext.create("PSI.PurchaseRej.PREditForm", {
    		parentForm: me
    	});
    	form.show();
    },
    
    // 编辑采购退货出库单
    onEditBill: function () {
    	PSI.MsgBox.showInfo("TODO");
    },
    
    // 删除采购退货出库单
    onDeleteBill: function () {
    	PSI.MsgBox.showInfo("TODO");
    },
    
    // 提交采购退货出库单
    onCommit: function () {
    	PSI.MsgBox.showInfo("TODO");
    },
    
    getMainGrid: function() {
        var me = this;
        if (me.__mainGrid) {
            return me.__mainGrid;
        }

        var modelName = "PSIITBill";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["id", "ref", "bizDT",  "warehouseName", "supplierName",
                "inputUserName", "bizUserName", "billStatus", "rejMoney"]
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
                url: PSI.Const.BASE_URL + "Home/PurchaseRej/prbillList",
                reader: {
                    root: 'dataList',
                    totalProperty: 'totalCount'
                }
            }
        });
        store.on("load", function (e, records, successful) {
            if (successful) {
                me.gotoMainGridRecord(me.__lastId);
            }
        });

        me.__mainGrid = Ext.create("Ext.grid.Panel", {
            border: 0,
            columnLines: true,
            columns: [{
                    header: "状态",
                    dataIndex: "billStatus",
                    menuDisabled: true,
                    sortable: false,
                    width: 60,
                    renderer: function(value) {
                    	return value == "待出库" ? "<span style='color:red'>" + value + "</span>" : value;
                    }
                }, {
                    header: "单号",
                    dataIndex: "ref",
                    width: 110,
                    menuDisabled: true,
                    sortable: false
                }, {
                    header: "业务日期",
                    dataIndex: "bizDT",
                    menuDisabled: true,
                    sortable: false
                },{
                    header: "供应商",
                    dataIndex: "supplierName",
                    menuDisabled: true,
                    sortable: false,
                    width: 150
                }, {
                    header: "出库仓库",
                    dataIndex: "warehouseName",
                    menuDisabled: true,
                    sortable: false,
                    width: 150
                }, {
                	header: "退货金额", 
                	dataIndex: "rejMoney", 
                	menuDisabled: true, 
                	sortable: false, 
                	align: "right", 
                	xtype: "numbercolumn", 
                	width: 150
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
                    fn: me.onMainGridSelect,
                    scope: me
                },
                itemdblclick: {
                    fn: me.onEditBill,
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

        return me.__mainGrid;
    },
    
    getDetailGrid: function() {
        var me = this;
        if (me.__detailGrid) {
            return me.__detailGrid;
        }
        
        var modelName = "PSIITBillDetail";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["id", "goodsCode", "goodsName", "goodsSpec", "unitName", 
                     "rejCount", "rejPrice", "rejMoney"]
        });
        var store = Ext.create("Ext.data.Store", {
            autoLoad: false,
            model: modelName,
            data: []
        });

        me.__detailGrid = Ext.create("Ext.grid.Panel", {
            title: "采购退货出库单明细",
            columnLines: true,
            columns: [Ext.create("Ext.grid.RowNumberer", {
                    text: "序号",
                    width: 30
                }), {
                    header: "商品编码",
                    dataIndex: "goodsCode",
                    menuDisabled: true,
                    sortable: false,
                    width: 120
                }, {
                    header: "商品名称",
                    dataIndex: "goodsName",
                    menuDisabled: true,
                    sortable: false,
                    width: 200
                }, {
                    header: "规格型号",
                    dataIndex: "goodsSpec",
                    menuDisabled: true,
                    sortable: false,
                    width: 200
                }, {
                    header: "退货数量",
                    dataIndex: "rejCount",
                    menuDisabled: true,
                    sortable: false,
                    align: "right",
                    width: 150
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
                    width: 150,
                    xtype: "numbercolumn"
                }, {
                    header: "退货金额",
                    dataIndex: "rejMoney",
                    menuDisabled: true,
                    sortable: false,
                    align: "right",
                    width: 150,
                    xtype: "numbercolumn"
                }],
            store: store
        });

        return me.__detailGrid;
    },
    
    gotoMainGridRecord: function (id) {
        var me = this;
        var grid = me.getMainGrid();
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
    
    onMainGridSelect: function() {
    	this.refreshDetailGrid();
    },
    
    refreshDetailGrid: function (id) {
        var me = this;
        me.getDetailGrid().setTitle("采购退货出库单明细");
        var grid = me.getMainGrid();
        var item = grid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            return;
        }
        var bill = item[0];

        grid = me.getDetailGrid();
        grid.setTitle("单号: " + bill.get("ref")  + " 出库仓库: "
                + bill.get("warehouseName"));
        var el = grid.getEl();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/PurchaseRej/prBillDetailList",
            params: {
                id: bill.get("id")
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
    },
    
    gotoMainGridRecord: function (id) {
        var me = this;
        var grid = me.getMainGrid();
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
    }
});
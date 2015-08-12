// 销售月报表(按仓库汇总)
Ext.define("PSI.Report.SafetyInventoryForm", {
    extend: "Ext.panel.Panel",
    
    border: 0,
    
    layout: "border",

    initComponent: function () {
        var me = this;

        Ext.apply(me, {
            tbar: [{
            	text: "查询",
            	iconCls: "PSI-button-refresh",
            	handler: me.onQuery,
            	scope: me
            	},
                {
                    text: "关闭", iconCls: "PSI-button-exit", handler: function () {
                        location.replace(PSI.Const.BASE_URL);
                    }
                }
            ],
            items: [{
                    	region: "center", layout: "fit", border: 0,
                    	items: [me.getMainGrid()]
            }]
        });

        me.callParent(arguments);
    },
    
    getMainGrid: function() {
    	var me = this;
    	if (me.__mainGrid) {
    		return me.__mainGrid;
    	}
    	
    	var modelName = "PSIReportSafetyInventory";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["bizDT", "warehouseCode", "warehouseName", "saleMoney",
                "rejMoney", "m", "profit", "rate"]
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
                url: PSI.Const.BASE_URL + "Home/Report/safetyInventoryQueryData",
                reader: {
                    root: 'dataList',
                    totalProperty: 'totalCount'
                }
            }
        });

        me.__mainGrid = Ext.create("Ext.grid.Panel", {
        	viewConfig: {
                enableTextSelection: true
            },
            border: 0,
            columnLines: true,
            columns: [
                {xtype: "rownumberer"},
                {header: "月份", dataIndex: "bizDT", menuDisabled: true, sortable: false, width: 80},
                {header: "仓库编码", dataIndex: "warehouseCode", menuDisabled: true, sortable: false},
                {header: "仓库", dataIndex: "warehouseName", menuDisabled: true, sortable: false, width: 200},
                {header: "销售出库金额", dataIndex: "saleMoney", menuDisabled: true, sortable: false,
                	align: "right", xtype: "numbercolumn"},
                {header: "退货入库金额", dataIndex: "rejMoney", menuDisabled: true, sortable: false,
                	align: "right", xtype: "numbercolumn"},
                {header: "净销售金额", dataIndex: "m", menuDisabled: true, sortable: false,
                	align: "right", xtype: "numbercolumn"},
                {header: "毛利", dataIndex: "profit", menuDisabled: true, sortable: false,
                	align: "right", xtype: "numbercolumn"},
                {header: "毛利率", dataIndex: "rate", menuDisabled: true, sortable: false, align: "right"}
            ],
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
                                store.pageSize = Ext.getCmp("comboCountPerPage").getValue();
                                store.currentPage = 1;
                                Ext.getCmp("pagingToobar").doRefresh();
                            },
                            scope: me
                        }
                    }
                }, {
                    xtype: "displayfield",
                    value: "条记录"
                }],
            listeners: {
            }
        });
        
        return me.__mainGrid;
    },
    
    onQuery: function() {
    	this.refreshMainGrid();
    },
    
    refreshMainGrid: function (id) {
        Ext.getCmp("pagingToobar").doRefresh();
    }
});
Ext.define("PSI.Home.MainForm", {
    extend: "Ext.panel.Panel",

    border: 0,
    bodyPadding: 5,
    
    initComponent: function () {
        var me = this;
        
        Ext.apply(me, {
        	layout: "hbox",
            items: [{
            	region: "west", flex: 1, layout: "vbox", border: 0,
            	items: [
            	        {
            	        	flex: 1,
            	        	width: "100%",
            	        	height: 240,
            	        	margin: "5",
            	        	header: {
            	                title: "<span style='font-size:120%'>销售看板</span>",
            	                iconCls: "PSI-portal-sale",
            	                height: 40
            	            },
        	                layout: "fit",
        	                items: [
    	                        me.getSaleGrid()
        	                ]
            	        },
            	        {
            	        	header: {
            	                title: "<span style='font-size:120%'>采购看板</span>",
            	                iconCls: "PSI-portal-purchase",
            	                height: 40
            	            },
            	        	flex: 1,
            	        	width: "100%",
            	        	height: 240,
            	        	margin: "5",
            	        	layout: "fit",
            	        	items: [me.getPurchaseGrid()]
            	        }
            	]
            },{
            	flex: 1, layout: "vbox", border: 0,
            	items: [
            	        {
            	        	header: {
            	                title: "<span style='font-size:120%'>库存看板</span>",
            	                iconCls: "PSI-portal-inventory",
            	                height: 40
            	            },
            	        	flex: 1,
            	        	width: "100%",
            	        	height: 240,
            	        	margin: "5",
            	        	layout: "fit",
            	        	items: [me.getInventoryGrid()]
            	        },
            	        {
            	        	header: {
            	                title: "<span style='font-size:120%'>资金看板</span>",
            	                iconCls: "PSI-portal-money",
            	                height: 40
            	            },
            	        	flex: 1,
            	        	width: "100%",
            	        	height: 240,
            	        	margin: "5",
            	        	layout: "fit",
            	        	items: [
            	        	        me.getMoneyGrid()
            	        	]
            	        }]
            }]
        });

        me.callParent(arguments);
        
        me.queryInventoryData();
    },
    
    getSaleGrid: function() {
    	var me = this;
    	if (me.__saleGrid) {
    		return me.__saleGrid;
    	}
    	
    	var modelName = "PSIPortalSale";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["month", "saleMoney", "profit", "rate"]
        });

        me.__saleGrid = Ext.create("Ext.grid.Panel", {
            viewConfig: {
                enableTextSelection: true
            },
            columnLines: true,
            border: 0,
            columns: [
                {header: "月份", dataIndex: "month", width: 80, menuDisabled: true, sortable: false},
                {header: "销售额", dataIndex: "saleMoney", width: 120, menuDisabled: true, sortable: false},
                {header: "毛利", dataIndex: "profit", width: 120, menuDisabled: true, sortable: false},
                {header: "毛利率", dataIndex: "rate", menuDisabled: true, sortable: false}
            ],
            store: Ext.create("Ext.data.Store", {
                model: modelName,
                autoLoad: false,
                data: []
            })
        });

        return me.__saleGrid;
    },
    
    getPurchaseGrid: function() {
    	var me = this;
    	if (me.__purchaseGrid) {
    		return me.__purchaseGrid;
    	}
    	
    	var modelName = "PSIPortalPurchase";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["month", "purchaseMoney"]
        });

        me.__purchaseGrid = Ext.create("Ext.grid.Panel", {
            viewConfig: {
                enableTextSelection: true
            },
            columnLines: true,
            border: 0,
            columns: [
                {header: "月份", dataIndex: "month", width: 80, menuDisabled: true, sortable: false},
                {header: "采购额", dataIndex: "purchaseMoney", width: 120, menuDisabled: true, sortable: false}
            ],
            store: Ext.create("Ext.data.Store", {
                model: modelName,
                autoLoad: false,
                data: []
            })
        });

        return me.__purchaseGrid;
    },

    getInventoryGrid: function() {
    	var me = this;
    	if (me.__inventoryGrid) {
    		return me.__inventoryGrid;
    	}
    	
    	var modelName = "PSIPortalInventory";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["warehouseName", "inventoryMoney", "siCount"]
        });

        me.__inventoryGrid = Ext.create("Ext.grid.Panel", {
            viewConfig: {
                enableTextSelection: true
            },
            columnLines: true,
            border: 0,
            columns: [
                {header: "仓库", dataIndex: "warehouseName", width: 160, menuDisabled: true, sortable: false},
                {header: "存货金额", dataIndex: "inventoryMoney", width: 160, 
                	menuDisabled: true, sortable: false, align: "right", xtype: "numbercolumn"},
                {header: "库存低于安全库存量商品种类数", dataIndex: "siCount", width: 180, 
                		menuDisabled: true, sortable: false, align: "right", xtype: "numbercolumn", format: "0"}
            ],
            store: Ext.create("Ext.data.Store", {
                model: modelName,
                autoLoad: false,
                data: []
            })
        });

        return me.__inventoryGrid;
    },

    getMoneyGrid: function() {
    	var me = this;
    	if (me.__moneyGrid) {
    		return me.__moneyGrid;
    	}
    	
    	var modelName = "PSIPortalMoney";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["item", "balanceMoney", "money30", "money30to60", "money60to90", "money90"]
        });

        me.__moneyGrid = Ext.create("Ext.grid.Panel", {
            viewConfig: {
                enableTextSelection: true
            },
            columnLines: true,
            border: 0,
            columns: [
                {header: "款项", dataIndex: "item", width: 80, menuDisabled: true, sortable: false},
                {header: "当期余额", dataIndex: "balanceMoney", width: 120, menuDisabled: true, sortable: false},
                {header: "账龄30天内", dataIndex: "money30", width: 120, menuDisabled: true, sortable: false},
                {header: "账龄30-60天", dataIndex: "money30to60", menuDisabled: true, sortable: false},
                {header: "账龄60-90天", dataIndex: "money60to90", menuDisabled: true, sortable: false},
                {header: "账龄大于90天", dataIndex: "money90", menuDisabled: true, sortable: false}
            ],
            store: Ext.create("Ext.data.Store", {
                model: modelName,
                autoLoad: false,
                data: []
            })
        });

        return me.__moneyGrid;
    },
    
    queryInventoryData: function() {
    	var me = this;
        var grid = me.getInventoryGrid();
        var el = grid.getEl() || Ext.getBody();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Portal/inventoryPortal",
            method: "POST",
            callback: function (options, success, response) {
                var store = grid.getStore();
                store.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    store.add(data);
                }

                el.unmask();
            }
        });
    }
});
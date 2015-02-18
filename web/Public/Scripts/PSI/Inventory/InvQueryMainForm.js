Ext.define("PSI.Inventory.InvQueryMainForm", {
	extend : "Ext.panel.Panel",

	border : 0,
	layout : "border",

	initComponent : function() {
		var me = this;

		Ext.define("PSIWarehouse", {
			extend : "Ext.data.Model",
			fields : [ "id", "code", "name" ]
		});

		Ext.define("PSIInventory", {
			extend : "Ext.data.Model",
			fields : [ "id", "goodsId", "goodsCode", "goodsName", "goodsSpec",
					"unitName", "inCount", "inPrice", "inMoney", "outCount",
					"outPrice", "outMoney", "balanceCount", "balancePrice",
					"balanceMoney" ]
		});

		Ext.define("PSIInventoryDetail", {
			extend : "Ext.data.Model",
			fields : [ "id", "goodsCode", "goodsName", "goodsSpec", "unitName",
					"inCount", "inPrice", "inMoney", "outCount", "outPrice",
					"outMoney", "balanceCount", "balancePrice", "balanceMoney",
					"bizDT", "bizUserName", "refType", "refNumber" ]
		});

		Ext.apply(me, {
			tbar : [ {
				text : "关闭",
				iconCls : "PSI-button-exit",
				handler : function() {
					location.replace(PSI.Const.BASE_URL);
				}
			} ],
			items : [ {
				region : "west",
				layout : "fit",
				border : 0,
				width : 200,
				split : true,
				items : [ me.getWarehouseGrid() ]
			}, {
				region : "center",
				layout : "border",
				items : [ {
					region : "center",
					layout : "fit",
					border : 0,
					items : [ me.getInvertoryGrid() ]
				}, {
					title : "明细账",
					region : "south",
					height : "50%",
					split : true,
					layout : "fit",
					border : 0,
					items : [ me.getInvertoryDetailGrid() ]
				} ]
			} ]
		});

		me.callParent(arguments);

		me.refreshWarehouseGrid();
	},

	getWarehouseGrid : function() {
		var me = this;
		if (me.__warehouseGrid) {
			return me.__warehouseGrid;
		}

		me.__warehouseGrid = Ext.create("Ext.grid.Panel", {
			title : "仓库",
			columnLines : true,
			columns : [ {
				header : "仓库编码",
				dataIndex : "code",
				menuDisabled : true,
				sortable : false,
				width : 60
			}, {
				header : "仓库名称",
				dataIndex : "name",
				menuDisabled : true,
				sortable : false,
				flex : 1
			} ],
			store : Ext.create("Ext.data.Store", {
				model : "PSIWarehouse",
				autoLoad : false,
				data : []
			}),
			listeners : {
				select : {
					fn : me.onWarehouseGridSelect,
					scope : me
				}
			}
		});

		return me.__warehouseGrid;
	},

	refreshWarehouseGrid : function() {
		var grid = this.getWarehouseGrid();
		var el = grid.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/Inventory/warehouseList",
			method : "POST",
			callback : function(options, success, response) {
				var store = grid.getStore();

				store.removeAll();

				if (success) {
					var data = Ext.JSON.decode(response.responseText);
					store.add(data);
				}

				el.unmask();
			}
		});
	},

	getInvertoryGrid : function() {
		var me = this;
		if (me.__invertoryGrid) {
			return me.__invertoryGrid;
		}

		me.__invertoryGrid = Ext.create("Ext.grid.Panel", {
			border : 0,
			columnLines : true,
			columns : [ {
				header : "商品编码",
				dataIndex : "goodsCode",
				menuDisabled : true,
				sortable : false
			}, {
				header : "商品名称",
				dataIndex : "goodsName",
				menuDisabled : true,
				sortable : false
			}, {
				header : "规格型号",
				dataIndex : "goodsSpec",
				menuDisabled : true,
				sortable : false
			}, {
				header : "商品单位",
				dataIndex : "unitName",
				menuDisabled : true,
				sortable : false,
				width : 60
			}, {
				header : "入库数量",
				align : "right",
				dataIndex : "inCount",
				menuDisabled : true,
				sortable : false
			}, {
				header : "平均入库单价",
				align : "right",
				xtype : "numbercolumn",
				dataIndex : "inPrice",
				menuDisabled : true,
				sortable : false
			}, {
				header : "入库总金额",
				align : "right",
				xtype : "numbercolumn",
				dataIndex : "inMoney",
				menuDisabled : true,
				sortable : false
			}, {
				header : "出库数量",
				align : "right",
				dataIndex : "outCount",
				menuDisabled : true,
				sortable : false
			}, {
				header : "平均出库单价",
				align : "right",
				xtype : "numbercolumn",
				dataIndex : "outPrice",
				menuDisabled : true,
				sortable : false
			}, {
				header : "出库总金额",
				align : "right",
				xtype : "numbercolumn",
				dataIndex : "outMoney",
				menuDisabled : true,
				sortable : false
			}, {
				header : "余额数量",
				align : "right",
				dataIndex : "balanceCount",
				menuDisabled : true,
				sortable : false
			}, {
				header : "余额平均单价",
				align : "right",
				xtype : "numbercolumn",
				dataIndex : "balancePrice",
				menuDisabled : true,
				sortable : false
			}, {
				header : "余额总金额",
				align : "right",
				xtype : "numbercolumn",
				dataIndex : "balanceMoney",
				menuDisabled : true,
				sortable : false
			} ],
			store : Ext.create("Ext.data.Store", {
				model : "PSIInventory",
				autoLoad : false,
				data : []
			}),
			listeners : {
				select : {
					fn : me.onInvertoryGridSelect,
					scope : me
				}
			}
		});

		return me.__invertoryGrid;
	},

	getWarehouseIdParam : function() {
		var item = this.getWarehouseGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return null;
		}

		var warehouse = item[0];
		return warehouse.get("id");
	},

	getGoodsIdParam : function() {
		var item = this.getInvertoryGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return null;
		}

		var invertory = item[0];
		return invertory.get("goodsId");
	},

	getInvertoryDetailGrid : function() {
		var me = this;
		if (me.__invertoryDetailGrid) {
			return me.__invertoryDetailGrid;
		}

		var store = Ext.create("Ext.data.Store", {
			model : "PSIInventoryDetail",
			pageSize: 20,
			proxy : {
				type : "ajax",
				actionMethods : {
					read : "POST"
				},
				url : PSI.Const.BASE_URL + "Home/Inventory/invertoryDetailList",
				reader : {
					root : 'details',
					totalProperty : 'totalCount'
				}
			},
			autoLoad : false,
			data : []
		});

		store.on("beforeload", function() {
			Ext.apply(store.proxy.extraParams, {
				warehouseId : me.getWarehouseIdParam(),
				goodsId : me.getGoodsIdParam(),
				dtFrom : Ext.Date.format(Ext.getCmp("dtFrom").getValue(),
						"Y-m-d"),
				dtTo : Ext.Date.format(Ext.getCmp("dtTo").getValue(), "Y-m-d")
			});
		});

		me.__invertoryDetailGrid = Ext.create("Ext.grid.Panel", {
			viewConfig: {
		        enableTextSelection: true
		    },
			tbar : [ {
				xtype : "displayfield",
				value : "业务日期 从"
			}, {
				id : "dtFrom",
				xtype : "datefield",
				format : "Y-m-d",
				width : 90
			}, {
				xtype : "displayfield",
				value : " 到 "
			}, {
				id : "dtTo",
				xtype : "datefield",
				format : "Y-m-d",
				width : 90,
				value : new Date()
			}, {
				text : "查询",
				iconCls : "PSI-button-refresh",
				handler : me.onQuery,
				scope : me
			}, {
				xtype : "pagingtoolbar",
				store : store
			} ],
			columnLines : true,
			columns : [ Ext.create("Ext.grid.RowNumberer", {
				text : "序号",
				width : 30
			}), {
				header : "商品编码",
				dataIndex : "goodsCode",
				menuDisabled : true,
				sortable : false
			}, {
				header : "商品名称",
				dataIndex : "goodsName",
				menuDisabled : true,
				sortable : false
			}, {
				header : "规格型号",
				dataIndex : "goodsSpec",
				menuDisabled : true,
				sortable : false
			}, {
				header : "商品单位",
				dataIndex : "unitName",
				menuDisabled : true,
				sortable : false,
				width : 60
			}, {
				header : "入库数量",
				dataIndex : "inCount",
				align : "right",
				menuDisabled : true,
				sortable : false
			}, {
				header : "平均入库单价",
				dataIndex : "inPrice",
				align : "right",
				xtype : "numbercolumn",
				menuDisabled : true,
				sortable : false
			}, {
				header : "入库总金额",
				dataIndex : "inMoney",
				align : "right",
				xtype : "numbercolumn",
				menuDisabled : true,
				sortable : false
			}, {
				header : "出库数量",
				dataIndex : "outCount",
				align : "right",
				menuDisabled : true,
				sortable : false
			}, {
				header : "平均出库单价",
				dataIndex : "outPrice",
				align : "right",
				xtype : "numbercolumn",
				menuDisabled : true,
				sortable : false
			}, {
				header : "出库总金额",
				dataIndex : "outMoney",
				align : "right",
				xtype : "numbercolumn",
				menuDisabled : true,
				sortable : false
			}, {
				header : "余额数量",
				dataIndex : "balanceCount",
				align : "right",
				menuDisabled : true,
				sortable : false
			}, {
				header : "余额平均单价",
				dataIndex : "balancePrice",
				align : "right",
				xtype : "numbercolumn",
				menuDisabled : true,
				sortable : false
			}, {
				header : "余额总金额",
				dataIndex : "balanceMoney",
				align : "right",
				xtype : "numbercolumn",
				menuDisabled : true,
				sortable : false
			}, {
				header : "业务日期",
				dataIndex : "bizDT",
				menuDisabled : true,
				sortable : false,
				width: 80
			}, {
				header : "业务员",
				dataIndex : "bizUserName",
				menuDisabled : true,
				sortable : false,
				width: 80
			}, {
				header : "业务类型",
				dataIndex : "refType",
				menuDisabled : true,
				sortable : false,
				width: 80
			}, {
				header : "业务单号",
				dataIndex : "refNumber",
				menuDisabled : true,
				sortable : false,
				width: 120
			} ],
			store : store
		});

		var dt = new Date();
		dt.setDate(dt.getDate() - 7);
		Ext.getCmp("dtFrom").setValue(dt);

		return me.__invertoryDetailGrid;
	},

	onWarehouseGridSelect : function() {
		this.refreshInvertoryGrid()
	},

	refreshInvertoryGrid : function() {
        this.getInvertoryDetailGrid().getStore().removeAll();
        
		var item = this.getWarehouseGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}

		var warehouse = item[0];

		var grid = this.getInvertoryGrid();
		grid.setTitle("仓库 [" + warehouse.get("name") + "] 的总账");

		var el = grid.getEl();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/Inventory/invertoryList",
			params : {
				warehouseId : warehouse.get("id")
			},
			method : "POST",
			callback : function(options, success, response) {
				var store = grid.getStore();

				store.removeAll();

				if (success) {
					var data = Ext.JSON.decode(response.responseText);

					store.add(data);

					if (store.getCount() > 0) {
						grid.getSelectionModel().select(0);
					}
				}

				el.unmask();
			}
		});
	},

	onInvertoryGridSelect : function() {
		this.getInvertoryDetailGrid().getStore().loadPage(1);
	},

	onQuery : function() {
		var dtTo = Ext.getCmp("dtTo").getValue();
		if (dtTo == null) {
			Ext.getCmp("dtTo").setValue(new Date());
		}

		var dtFrom = Ext.getCmp("dtFrom").getValue();
		if (dtFrom == null) {
			var dt = new Date();
			dt.setDate(dt.getDate() - 7);
			Ext.getCmp("dtFrom").setValue(dt);
		}

		this.getInvertoryDetailGrid().getStore().loadPage(1);
	}
});
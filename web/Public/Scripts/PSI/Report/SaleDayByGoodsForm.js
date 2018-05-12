/**
 * 销售日报表(按商品汇总)
 */
Ext.define("PSI.Report.SaleDayByGoodsForm", {
	extend : "PSI.AFX.BaseMainExForm",

	initComponent : function() {
		var me = this;

		var store = me.getMainGrid().getStore();

		Ext.apply(me, {
					tbar : [{
								id : "pagingToobar",
								cls : "PSI-toolbox",
								xtype : "pagingtoolbar",
								border : 0,
								store : store
							}, "-", {
								xtype : "displayfield",
								value : "每页显示"
							}, {
								id : "comboCountPerPage",
								cls : "PSI-toolbox",
								xtype : "combobox",
								editable : false,
								width : 60,
								store : Ext.create("Ext.data.ArrayStore", {
											fields : ["text"],
											data : [["20"], ["50"], ["100"],
													["300"], ["1000"]]
										}),
								value : 20,
								listeners : {
									change : {
										fn : function() {
											store.pageSize = Ext
													.getCmp("comboCountPerPage")
													.getValue();
											store.currentPage = 1;
											Ext.getCmp("pagingToobar")
													.doRefresh();
										},
										scope : me
									}
								}
							}, {
								xtype : "displayfield",
								value : "条记录"
							}, "-", {
								id : "editQueryDT",
								cls : "PSI-toolbox",
								xtype : "datefield",
								format : "Y-m-d",
								labelAlign : "right",
								labelSeparator : "",
								labelWidth : 60,
								fieldLabel : "业务日期",
								value : new Date()
							}, " ", {
								text : "查询",
								iconCls : "PSI-button-refresh",
								handler : me.onQuery,
								scope : me
							}, "-", {
								text : "重置查询条件",
								handler : me.onClearQuery,
								scope : me
							}, "-", {
								text : "关闭",
								handler : function() {
									me.closeWindow();
								}
							}],
					items : [{
								region : "center",
								layout : "border",
								border : 0,
								items : [{
											region : "center",
											layout : "fit",
											border : 0,
											items : [me.getMainGrid()]
										}, {
											region : "south",
											layout : "fit",
											height : 100,
											items : [me.getSummaryGrid()]
										}]
							}]
				});

		me.callParent(arguments);
	},

	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSIReportSaleDayByGoods";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["bizDT", "goodsCode", "goodsName", "goodsSpec",
							"saleCount", "unitName", "saleMoney", "rejCount",
							"rejMoney", "c", "m", "profit", "rate"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : [],
					pageSize : 20,
					proxy : {
						type : "ajax",
						actionMethods : {
							read : "POST"
						},
						url : PSI.Const.BASE_URL
								+ "Home/Report/saleDayByGoodsQueryData",
						reader : {
							root : 'dataList',
							totalProperty : 'totalCount'
						}
					}
				});
		store.on("beforeload", function() {
					store.proxy.extraParams = me.getQueryParam();
				});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
					cls : "PSI",
					viewConfig : {
						enableTextSelection : true
					},
					border : 0,
					columnLines : true,
					columns : [{
								xtype : "rownumberer"
							}, {
								header : "业务日期",
								dataIndex : "bizDT",
								menuDisabled : true,
								sortable : false,
								width : 80
							}, {
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
								header : "销售出库数量",
								dataIndex : "saleCount",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}, {
								header : "计量单位",
								dataIndex : "unitName",
								menuDisabled : true,
								sortable : false,
								width : 60
							}, {
								header : "销售出库金额",
								dataIndex : "saleMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "退货入库数量",
								dataIndex : "rejCount",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}, {
								header : "退货入库金额",
								dataIndex : "rejMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "净销售数量",
								dataIndex : "c",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}, {
								header : "净销售金额",
								dataIndex : "m",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "毛利",
								dataIndex : "profit",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "毛利率",
								dataIndex : "rate",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}],
					store : store
				});

		return me.__mainGrid;
	},

	getSummaryGrid : function() {
		var me = this;
		if (me.__summaryGrid) {
			return me.__summaryGrid;
		}

		var modelName = "PSIReportSaleDayByGoodsSummary";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["bizDT", "saleMoney", "rejMoney", "m", "profit",
							"rate"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__summaryGrid = Ext.create("Ext.grid.Panel", {
					cls : "PSI",
					header : {
						height : 30,
						title : me.formatGridHeaderTitle("日销售汇总")
					},
					viewConfig : {
						enableTextSelection : true
					},
					border : 0,
					columnLines : true,
					columns : [{
								header : "业务日期",
								dataIndex : "bizDT",
								menuDisabled : true,
								sortable : false,
								width : 80
							}, {
								header : "销售出库金额",
								dataIndex : "saleMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "退货入库金额",
								dataIndex : "rejMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "净销售金额",
								dataIndex : "m",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "毛利",
								dataIndex : "profit",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "毛利率",
								dataIndex : "rate",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}],
					store : store
				});

		return me.__summaryGrid;
	},

	onQuery : function() {
		this.refreshMainGrid();
		this.refreshSummaryGrid();
	},

	refreshSummaryGrid : function() {
		var me = this;
		var grid = me.getSummaryGrid();
		var el = grid.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL
							+ "Home/Report/saleDayByGoodsSummaryQueryData",
					params : me.getQueryParam(),
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

	onClearQuery : function() {
		var me = this;

		Ext.getCmp("editQueryDT").setValue(new Date());

		me.onQuery();
	},

	getQueryParam : function() {
		var me = this;

		var result = {};

		var dt = Ext.getCmp("editQueryDT").getValue();
		if (dt) {
			result.dt = Ext.Date.format(dt, "Y-m-d");
		}

		return result;
	},

	refreshMainGrid : function(id) {
		Ext.getCmp("pagingToobar").doRefresh();
	}
});
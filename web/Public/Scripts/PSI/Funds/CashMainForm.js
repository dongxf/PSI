// 现金收支查询界面
Ext.define("PSI.Funds.CashMainForm", {
	extend : "Ext.panel.Panel",

	border : 0,
	layout : "border",

	initComponent : function() {
		var me = this;

		Ext.apply(me, {
			tbar : [ 
			{
				xtype : "displayfield",
				value : "业务日期 从"
			}, {
				id : "dtFrom",
				xtype : "datefield",
				format : "Y-m-d",
				width : 100
			}, {
				xtype : "displayfield",
				value : " 到 "
			}, {
				id : "dtTo",
				xtype : "datefield",
				format : "Y-m-d",
				width : 100,
				value : new Date()
			}, {
				text : "查询",
				iconCls : "PSI-button-refresh",
				handler : me.onQuery,
				scope : me
			}, "-", {
				text : "关闭",
				iconCls : "PSI-button-exit",
				handler : function() {
					location.replace(PSI.Const.BASE_URL);
				}
			} ],
			layout : "border",
			border : 0,
			items : [ {
				region : "center",
				layout : "fit",
				border : 0,
				items : [ me.getMainGrid() ]
			}, {
				region : "south",
				layout : "fit",
				border : 0,
				split : true,
				height : "50%",
				items : [ me.getDetailGrid() ]
			} ]

		});

		me.callParent(arguments);
	},
	
	getMainGrid: function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		Ext.define("PSIPay", {
			extend : "Ext.data.Model",
			fields : [ "id", "caId", "code", "name", "payMoney", "actMoney",
					"balanceMoney" ]
		});

		var store = Ext.create("Ext.data.Store", {
			model : "PSIPay",
			pageSize : 20,
			proxy : {
				type : "ajax",
				actionMethods : {
					read : "POST"
				},
				url : PSI.Const.BASE_URL + "Home/Funds/cashList",
				reader : {
					root : 'dataList',
					totalProperty : 'totalCount'
				}
			},
			autoLoad : false,
			data : []
		});

		store.on("beforeload", function() {
			Ext.apply(store.proxy.extraParams, {
			});
		});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
			bbar : [ {
				xtype : "pagingtoolbar",
				store : store
			} ],
			columnLines : true,
			columns : [ {
				header : "编码",
				dataIndex : "code",
				menuDisabled : true,
				sortable : false
			}, {
				header : "名称",
				dataIndex : "name",
				menuDisabled : true,
				sortable : false,
				width: 300
			}, {
				header : "应付金额",
				dataIndex : "payMoney",
				menuDisabled : true,
				sortable : false,
				align : "right",
				xtype : "numbercolumn",
				width: 160
			}, {
				header : "已付金额",
				dataIndex : "actMoney",
				menuDisabled : true,
				sortable : false,
				align : "right",
				xtype : "numbercolumn",
				width: 160
			}, {
				header : "未付金额",
				dataIndex : "balanceMoney",
				menuDisabled : true,
				sortable : false,
				align : "right",
				xtype : "numbercolumn",
				width: 160
			} ],
			store : store,
			listeners : {
				select : {
					fn : me.onPayGridSelect,
					scope : me
				}
			}
		});

		return me.__mainGrid;

	},
	
	getDetailGrid: function() {
		var me = this;
		if (me.__detailGrid) {
			return me.__detailGrid;
		}

		Ext.define("PSIPayDetail", {
			extend : "Ext.data.Model",
			fields : [ "id", "payMoney", "actMoney", "balanceMoney", "refType",
					"refNumber", "bizDT", "dateCreated" ]
		});

		var store = Ext.create("Ext.data.Store", {
			model : "PSIPayDetail",
			pageSize : 20,
			proxy : {
				type : "ajax",
				actionMethods : {
					read : "POST"
				},
				url : PSI.Const.BASE_URL + "Home/Funds/cashDetailList",
				reader : {
					root : 'dataList',
					totalProperty : 'totalCount'
				}
			},
			autoLoad : false,
			data : []
		});

		store.on("beforeload", function() {
			Ext.apply(store.proxy.extraParams, {
			});
		});

		me.__detailGrid = Ext.create("Ext.grid.Panel", {
			title : "现金支付流水明细",
			bbar : [ {
				xtype : "pagingtoolbar",
				store : store
			} ],
			columnLines : true,
			columns : [ {
				header : "业务类型",
				dataIndex : "refType",
				menuDisabled : true,
				sortable : false,
				width: 120
			}, {
				header : "单号",
				dataIndex : "refNumber",
				menuDisabled : true,
				sortable : false,
				width : 120,
				renderer: function(value, md, record) {
					return "<a href='" + PSI.Const.BASE_URL + "Home/Bill/viewIndex?fid=2024&refType=" 
						+ encodeURIComponent(record.get("refType")) 
						+ "&ref=" + encodeURIComponent(record.get("refNumber")) + "' target='_blank'>" + value + "</a>";
				}
			}, {
				header : "业务日期",
				dataIndex : "bizDT",
				menuDisabled : true,
				sortable : false
			}, {
				header : "应付金额",
				dataIndex : "payMoney",
				menuDisabled : true,
				sortable : false,
				align : "right",
				xtype : "numbercolumn"
			}, {
				header : "已付金额",
				dataIndex : "actMoney",
				menuDisabled : true,
				sortable : false,
				align : "right",
				xtype : "numbercolumn"
			}, {
				header : "未付金额",
				dataIndex : "balanceMoney",
				menuDisabled : true,
				sortable : false,
				align : "right",
				xtype : "numbercolumn"
			},{
				header : "创建时间",
				dataIndex : "dateCreated",
				menuDisabled : true,
				sortable : false,
				width: 140
			} ],
			store : store
		});

		return me.__detailGrid;

	},
	
	onQuery : function() {
		var me = this;
		me.getDetailGrid().getStore().removeAll();
		
		me.getMainGrid().getStore().loadPage(1);
	},
	
	onMainGridSelect: function() {
		this.getDetailGrid().getStore().loadPage(1);
    }
});
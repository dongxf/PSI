/**
 * 表单视图开发助手 - 主页面
 */
Ext.define("PSI.FormView.DevMainForm", {
	extend : "PSI.AFX.BaseMainExForm",

	initComponent : function() {
		var me = this;

		Ext.apply(me, {
					tbar : me.getToolbarCmp(),
					items : [{
								region : "center",
								layout : "fit",
								items : me.getMainGrid()
							}]
				});

		me.callParent(arguments);

		me.refreshMainGrid();
	},

	getToolbarCmp : function() {
		var me = this;
		return [{
					text : "查看视图",
					handler : me.onView,
					scope : me
				}, "-", {
					text : "关闭",
					handler : function() {
						me.closeWindow();
					},
					scope : me
				}];
	},

	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSIFormView";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "name"]
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
						url : me.URL("Home/FormView/fvListForDev"),
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
					border : 1,
					columnLines : true,
					columns : [{
								xtype : "rownumberer",
								width : 50
							}, {
								header : "视图名称",
								dataIndex : "name",
								width : 400,
								menuDisabled : true,
								sortable : false
							}],
					store : store,
					bbar : ["->", {
								id : "pagingToobar",
								xtype : "pagingtoolbar",
								border : 0,
								store : store
							}, "-", {
								xtype : "displayfield",
								value : "每页显示"
							}, {
								id : "comboCountPerPage",
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
							}]
				});

		return me.__mainGrid;
	},

	refreshMainGrid : function(id) {
		var me = this;

		Ext.getCmp("pagingToobar").doRefresh();
	},

	getQueryParam : function() {
		return {};
	},

	onView : function() {
		var me = this;

		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("请选择要查看的表单视图");
			return;
		}

		var formView = item[0];

		var url = me.URL(Ext.String.format("Home/FormView/devView?id={0}",
				formView.get("id")));
		window.open(url);
	}
});
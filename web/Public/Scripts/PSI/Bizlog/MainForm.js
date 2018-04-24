/**
 * 业务日志 - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.Bizlog.MainForm", {
	extend : "PSI.AFX.BaseOneGridMainForm",

	config : {
		unitTest : "0"
	},

	/**
	 * 重载父类方法
	 */
	afxGetToolbarCmp : function() {
		var me = this;

		var buttons = [{
					id : "pagingToobar",
					xtype : "pagingtoolbar",
					border : 0,
					store : me.getMainGrid().getStore()
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
								data : [["20"], ["50"], ["100"], ["300"],
										["1000"]]
							}),
					value : 20,
					listeners : {
						change : {
							fn : function() {
								store.pageSize = Ext
										.getCmp("comboCountPerPage").getValue();
								store.currentPage = 1;
								Ext.getCmp("pagingToobar").doRefresh();
							},
							scope : me
						}
					}
				}, {
					xtype : "displayfield",
					value : "条记录"
				}, "-", {
					text : "刷新",
					handler : me.onRefresh,
					scope : me,
					iconCls : "PSI-button-refresh"
				}, "-", {
					text : "帮助",
					handler : function() {
						window.open(me.URL("/Home/Help/index?t=bizlog"));
					}
				}, "-", {
					text : "关闭",
					handler : function() {
						me.closeWindow();
					}
				}, "->", {
					text : "一键升级数据库",
					iconCls : "PSI-button-database",
					scope : me,
					handler : me.onUpdateDatabase
				}];

		if (me.getUnitTest() == "1") {
			buttons.push("-", {
						text : "单元测试",
						handler : me.onUnitTest,
						scope : me
					});
		}

		return buttons;
	},

	/**
	 * 重载父类方法
	 */
	afxGetMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSI_Bizlog_MainForm_PSILog";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "loginName", "userName", "ip", "ipFrom",
							"content", "dt", "logCategory"],
					idProperty : "id"
				});
		var store = Ext.create("Ext.data.Store", {
					model : modelName,
					pageSize : 20,
					proxy : {
						type : "ajax",
						actionMethods : {
							read : "POST"
						},
						url : me.URL("Home/Bizlog/logList"),
						reader : {
							root : 'logs',
							totalProperty : 'totalCount'
						}
					},
					autoLoad : true
				});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
					cls : "PSI",
					viewConfig : {
						enableTextSelection : true
					},
					loadMask : true,
					border : 0,
					columnLines : true,
					columns : [Ext.create("Ext.grid.RowNumberer", {
										text : "序号",
										width : 50
									}), {
								text : "登录名",
								dataIndex : "loginName",
								width : 60,
								menuDisabled : true,
								sortable : false
							}, {
								text : "姓名",
								dataIndex : "userName",
								width : 80,
								menuDisabled : true,
								sortable : false
							}, {
								text : "IP",
								dataIndex : "ip",
								width : 120,
								menuDisabled : true,
								sortable : false,
								renderer : function(value, md, record) {
									return "<a href='http://www.baidu.com/s?wd="
											+ encodeURIComponent(value)
											+ "' target='_blank'>"
											+ value
											+ "</a>";
								}
							}, {
								text : "IP所属地",
								dataIndex : "ipFrom",
								width : 200,
								menuDisabled : true,
								sortable : false
							}, {
								text : "日志分类",
								dataIndex : "logCategory",
								width : 150,
								menuDisabled : true,
								sortable : false
							}, {
								text : "日志内容",
								dataIndex : "content",
								flex : 1,
								menuDisabled : true,
								sortable : false
							}, {
								text : "日志记录时间",
								dataIndex : "dt",
								width : 140,
								menuDisabled : true,
								sortable : false
							}],
					store : store
				});

		return me.__mainGrid;
	},

	/**
	 * 刷新
	 */
	onRefresh : function() {
		Ext.getCmp("pagingToobar").doRefresh();
		this.focus();
	},

	/**
	 * 升级数据库
	 */
	onUpdateDatabase : function() {
		var me = this;

		PSI.MsgBox.confirm("请确认是否升级数据库？", function() {
			var el = Ext.getBody();
			el.mask("正在升级数据库，请稍等......");
			Ext.Ajax.request({
						url : PSI.Const.BASE_URL + "Home/Bizlog/updateDatabase",
						method : "POST",
						callback : function(options, success, response) {
							el.unmask();

							if (success) {
								var data = Ext.JSON
										.decode(response.responseText);
								if (data.success) {
									PSI.MsgBox.showInfo("成功升级数据库", function() {
												me.onRefresh();
											});
								} else {
									PSI.MsgBox.showInfo(data.msg);
								}
							} else {
								PSI.MsgBox.showInfo("网络错误", function() {
											window.location.reload();
										});
							}
						}
					});
		});
	},

	onUnitTest : function() {
		var url = PSI.Const.BASE_URL + "UnitTest";
		window.open(url);
	}
});
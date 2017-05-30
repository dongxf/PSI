/**
 * Unit Test - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.UnitTest.MainForm", {
			extend : "PSI.AFX.BaseMainExForm",
			border : 0,

			/**
			 * 初始化组件
			 */
			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							tbar : me.getToolbarCmp(),
							layout : "border",
							items : [{
										region : "center",
										layout : "fit",
										border : 0,
										items : [me.getMainGrid()]
									}]
						});

				me.callParent(arguments);
			},

			getToolbarCmp : function() {
				var me = this;

				return [{
							text : "开始测试",
							iconCls : "PSI-button-commit",
							handler : me.onStartUnitTest,
							scope : me
						}, {
							text : "关闭",
							iconCls : "PSI-button-exit",
							handler : function() {
								window.close();
							}
						}];
			},

			getMainGrid : function() {
				var me = this;
				if (me.__mainGrid) {
					return me.__mainGrid;
				}

				var modelName = "PSIUnitTestResult";

				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "name", "result", "msg"]
						});

				var store = Ext.create("Ext.data.Store", {
							autoLoad : false,
							model : modelName,
							data : []
						});

				me.__mainGrid = Ext.create("Ext.grid.Panel", {
							viewConfig : {
								enableTextSelection : true
							},
							title : "单元测试结果",
							columnLines : true,
							border : 0,
							columns : [Ext.create("Ext.grid.RowNumberer", {
												text : "序号",
												width : 30
											}), {
										header : "名称",
										dataIndex : "name",
										menuDisabled : true,
										sortable : false,
										width : 300
									}, {
										header : "结果",
										dataIndex : "result",
										menuDisabled : true,
										sortable : false,
										width : 300
									}, {
										header : "信息",
										dataIndex : "msg",
										menuDisabled : true,
										sortable : false,
										flex : 1
									}],
							store : store
						});

				return me.__mainGrid;
			},

			/**
			 * 开始单元测试
			 */
			onStartUnitTest : function() {
				var me = this;

				me.showInfo("TODO");
			}
		});
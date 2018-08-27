/**
 * 会计科目 - 主界面
 */
Ext.define("PSI.Subject.MainForm", {
			extend : "PSI.AFX.BaseMainExForm",

			/**
			 * 初始化组件
			 */
			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							tbar : me.getToolbarCmp(),
							items : [{
										region : "center",
										xtype : "panel",
										layout : "fit",
										border : 0,
										html : "<h1>本模块待开发</h1>",
										items : []
									}]
						});

				me.callParent(arguments);
			},

			getToolbarCmp : function() {
				var me = this;

				return [{
							text : "关闭",
							handler : function() {
								me.closeWindow();
							}
						}];
			}
		});
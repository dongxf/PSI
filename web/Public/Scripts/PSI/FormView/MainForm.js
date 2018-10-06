/**
 * 表单视图 - 主界面
 */
Ext.define("PSI.FormView.MainForm", {
			extend : "Ext.panel.Panel",

			config : {
				formViewId : null
			},

			initComponent : function() {
				var me = this;

				Ext.apply(me, {
					border:0,
					tbar: [{text:"Close"}]
				});

				me.callParent(arguments);
			}
		});
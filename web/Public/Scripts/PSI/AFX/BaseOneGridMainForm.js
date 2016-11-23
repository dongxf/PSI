/**
 * 只有一个Grid的主界面基类
 */
Ext.define("PSI.AFX.BaseOneGridMainForm", {
			extend : "PSI.AFX.BaseMainForm",

			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							items : [{
										region : "center",
										xtype : "panel",
										layout : "fit",
										border : 0,
										items : [me.getMainGrid()]
									}]
						});

				me.callParent(arguments);
			},

			getMainGrid : function() {
				var me = this;
				return me.afxGetMainGrid();
			},

			afxGetMainGrid : function() {
				return null;
			}
		});
/**
 * 主界面基类
 */
Ext.define("PSI.AFX.BaseMainForm", {
			extend : "Ext.panel.Panel",

			border : 0,

			layout : "border",

			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							tbar : me.afxGetToolbarCmp()
						});

				me.callParent(arguments);
			},

			URL : function(url) {
				return PSI.Const.BASE_URL + url;
			},

			afxGetToolbarCmp : function() {
				return [];
			}
		});
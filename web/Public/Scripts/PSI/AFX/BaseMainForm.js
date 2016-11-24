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

				me.afxInitComponent();
			},

			URL : function(url) {
				return PSI.Const.BASE_URL + url;
			},

			decodeJSON : function(str) {
				return Ext.JSON.decode(str);
			},

			afxGetToolbarCmp : function() {
				return [];
			},

			afxInitComponent : function() {
			}
		});
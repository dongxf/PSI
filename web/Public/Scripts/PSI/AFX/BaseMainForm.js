/**
 * 主界面基类
 */
Ext.define("PSI.AFX.BaseMainForm", {
			extend : "Ext.panel.Panel",

			URL : function(url) {
				return PSI.Const.BASE_URL + url;
			}
		});
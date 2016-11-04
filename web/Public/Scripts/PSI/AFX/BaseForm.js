/**
 * PSI 窗体基类
 */
Ext.define("PSI.AFX.BaseForm", {
			extend : 'Ext.window.Window',

			modal : true,
			resizable : false,
			onEsc : Ext.emptyFn,

			URL : function(url) {
				return PSI.Const.BASE_URL + url;
			}
		});
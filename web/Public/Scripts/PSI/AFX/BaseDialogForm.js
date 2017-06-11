/**
 * PSI 对话框窗体基类
 */
Ext.define("PSI.AFX.BaseDialogForm", {
			extend : 'Ext.window.Window',

			config : {
				parentForm : null,
				entity : null
			},

			modal : true,
			resizable : false,
			onEsc : Ext.emptyFn,

			URL : function(url) {
				return PSI.Const.BASE_URL + url;
			},

			decodeJSON : function(str) {
				return Ext.JSON.decode(str);
			},

			tip : function(info) {
				PSI.MsgBox.tip(info);
			},

			showInfo : function(info, func) {
				PSI.MsgBox.showInfo(info, func);
			},

			confirm : function(confirmInfo, funcOnYes) {
				PSI.MsgBox.confirm(confirmInfo, funcOnYes);
			},

			ajax : function(r) {
				if (!r.method) {
					r.method = "POST";
				}
				Ext.Ajax.request(r);
			},

			formatTitle : function(title) {
				return "<span style='font-size:160%'>" + title + "</span>";
			}
		});
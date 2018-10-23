//
// 账样字段显示次序
//
Ext.define("PSI.Subject.FmtColShowOrderEditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		var entity = me.getEntity();

		var buttons = [];

		var btn = {
			text : "保存",
			formBind : true,
			iconCls : "PSI-button-ok",
			handler : function() {
				me.onOK(false);
			},
			scope : me
		};
		buttons.push(btn);

		var btn = {
			text : "关闭",
			handler : function() {
				me.close();
			},
			scope : me
		};
		buttons.push(btn);

		var t = "设置字段显示次序";
		var f = "edit-form-update.png";
		var logoHtml = "<img style='float:left;margin:10px 20px 0px 10px;width:48px;height:48px;' src='"
				+ PSI.Const.BASE_URL
				+ "Public/Images/"
				+ f
				+ "'></img>"
				+ "<h2 style='color:#196d83'>"
				+ t
				+ "</h2>"
				+ "<p style='color:#196d83'>标记 <span style='color:red;font-weight:bold'>*</span>的是必须录入数据的字段</p>";
		Ext.apply(me, {
					header : {
						title : me.formatTitle(PSI.Const.PROD_NAME),
						height : 40
					},
					width : 1000,
					height : 340,
					layout : "border",
					listeners : {
						show : {
							fn : me.onWndShow,
							scope : me
						},
						close : {
							fn : me.onWndClose,
							scope : me
						}
					},
					items : [{
								region : "north",
								height : 90,
								border : 0,
								html : logoHtml
							}, {
								region : "center",
								border : 0,
								items : [],
								buttons : buttons
							}]
				});

		me.callParent(arguments);
	},

	/**
	 * 保存
	 */
	onOK : function(thenAdd) {
		var me = this;

	},

	onWindowBeforeUnload : function(e) {
		return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
	},

	onWndClose : function() {
		var me = this;

		Ext.get(window).un('beforeunload', me.onWindowBeforeUnload);

		if (me.__lastId) {
			if (me.getParentForm()) {
				me.getParentForm().refreshFmtColsGrid()
			}
		}
	},

	onWndShow : function() {
		var me = this;

		Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);

		var id = me.getEntity().get("id");

		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		var r = {
			url : me.URL("Home/Subject/fmtGridColsList"),
			params : {
				id : id
			},
			callback : function(options, success, response) {
				el.unmask();

				if (success) {
					var data = me.decodeJSON(response.responseText);

				} else {
					me.showInfo("网络错误")
				}
			}
		};

		me.ajax(r);
	}
});
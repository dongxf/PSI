Ext.define("PSI.Warehouse.EditOrgForm", {
	extend : "Ext.window.Window",
	config : {
		parentForm : null
	},
	initComponent : function() {
		var me = this;

		var buttons = [];

		buttons.push({
			text : "确定",
			formBind : true,
			iconCls : "PSI-button-ok",
			handler : function() {
				me.onOK(false);
			},
			scope : me
		}, {
			text : "取消",
			handler : function() {
				me.close();
			},
			scope : me
		});

		Ext.apply(me, {
			title : "添加组织机构",
			modal : true,
			onEsc : Ext.emptyFn,
			width : 500,
			height : 400,
			layout : "fit",
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
			items : [],
			buttons : buttons
		});

		me.callParent(arguments);
	},
	// private
	onOK : function() {
		var me = this;
		me.close();
	},
	onWndClose : function() {
	},
	onWndShow : function() {
	}
});
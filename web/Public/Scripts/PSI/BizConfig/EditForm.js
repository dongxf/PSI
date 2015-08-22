// 业务设置 - 编辑设置项目
Ext.define("PSI.BizConfig.EditForm", {
	extend : "Ext.window.Window",
	config : {
		parentForm : null
	},
	initComponent : function() {
		var me = this;

		var buttons = [];

		buttons.push({
			text : "保存",
			formBind : true,
			iconCls : "PSI-button-ok",
			handler : function() {
				me.onOK();
			},
			scope : me
		}, {
			text : "取消",
			handler : function() {
				me.close();
			},
			scope : me
		},{
            text: "帮助",
            iconCls: "PSI-help",
            handler: function () {
                window.open("http://my.oschina.net/u/134395/blog/378538");
            }
        });
		
		var modelName = "PSIWarehouse";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["id", "name"]
        });

        var storePW = Ext.create("Ext.data.Store", {
            model: modelName,
            autoLoad: false,
            fields : [ "id", "name" ],
            data: []
        });
        me.__storePW = storePW;
        var storeWS = Ext.create("Ext.data.Store", {
            model: modelName,
            autoLoad: false,
            fields : [ "id", "name" ],
            data: []
        });
        me.__storeWS = storeWS;

		Ext.apply(me, {
			title : "业务设置",
			modal : true,
			onEsc : Ext.emptyFn,
			width : 400,
			height : 330,
			layout : "fit",
			items : [ {
				id : "editForm",
				xtype : "form",
				layout : "form",
				height : "100%",
				bodyPadding : 5,
				defaultType : 'textfield',
				fieldDefaults : {
					labelWidth : 60,
					labelAlign : "right",
					labelSeparator : "",
					msgTarget : 'side'
				},
				items : [ {
					id : "editName1003-01",
					xtype : "displayfield"
				}, {
					id : "editValue1003-01",
					xtype : "combo",
					queryMode : "local",
					editable : false,
					valueField : "id",
					store : Ext.create("Ext.data.ArrayStore", {
						fields : [ "id", "text" ],
						data : [ [ "0", "仓库不需指定组织机构" ], [ "1", "仓库需指定组织机构" ] ]
					}),
					name : "value1003-01"
				},{
					id : "editName2001-01",
					xtype : "displayfield"
				}, {
					id : "editValue2001-01",
					xtype : "combo",
					queryMode : "local",
					editable : false,
					valueField : "id",
					displayField: "name",
					store : storePW,
					name : "value2001-01"
				}, {
					id : "editName2002-01",
					xtype : "displayfield"
				}, {
					id : "editValue2002-01",
					xtype : "combo",
					queryMode : "local",
					editable : false,
					valueField : "id",
					store : Ext.create("Ext.data.ArrayStore", {
						fields : [ "id", "text" ],
						data : [ [ "0", "不允许编辑销售单价" ], [ "1", "允许编辑销售单价" ] ]
					}),
					name : "value2002-01"
				}, {
					id : "editName2002-02",
					xtype : "displayfield"
				}, {
					id : "editValue2002-02",
					xtype : "combo",
					queryMode : "local",
					editable : false,
					valueField : "id",
					displayField: "name",
					store : storeWS,
					name : "value2002-02"
				} ],
				buttons : buttons
			} ],
			listeners : {
				close : {
					fn : me.onWndClose,
					scope : me
				},
				show : {
					fn : me.onWndShow,
					scope : me
				}
			}
		});

		me.callParent(arguments);
	},
	// private
	onOK : function(thenAdd) {
		var me = this;
		var f = Ext.getCmp("editForm");
		var el = f.getEl();
		el.mask(PSI.Const.SAVING);
		f.submit({
			url : PSI.Const.BASE_URL + "Home/BizConfig/edit",
			method : "POST",
			success : function(form, action) {
				el.unmask();
				me.__saved = true;
				PSI.MsgBox.showInfo("数据保存成功", function() {
					me.close();
				});
			},
			failure : function(form, action) {
				el.unmask();
				PSI.MsgBox.showInfo(action.result.msg);
			}
		});
	},
	onWndClose : function() {
		var me = this;
		if (me.__saved) {
			me.getParentForm().refreshGrid();
		}
	},
	onWndShow : function() {
		var me = this;
		me.__saved = false;

		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/BizConfig/allConfigsWithExtData",
			method : "POST",
			callback : function(options, success, response) {
				if (success) {
					var data = Ext.JSON.decode(response.responseText);
					me.__storePW.add(data.extData.warehouse);
					me.__storeWS.add(data.extData.warehouse);

					for (var i = 0; i < data.dataList.length; i++) {
						var item = data.dataList[i];
						var editName = Ext.getCmp("editName" + item.id);
						if (editName) {
							editName.setValue(item.name);
						}
						var editValue = Ext.getCmp("editValue" + item.id);
						if (editValue) {
							editValue.setValue(item.value);
						}
					}
				} else {
					PSI.MsgBox.showInfo("网络错误");
				}

				el.unmask();
			}
		});
	}
});
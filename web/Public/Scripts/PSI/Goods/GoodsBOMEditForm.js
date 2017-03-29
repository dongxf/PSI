/**
 * 商品构成 - 新增或编辑界面
 */
Ext.define("PSI.Goods.GoodsBOMEditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	config : {
		goods : null
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		var goods = me.getGoods();

		var entity = me.getEntity();

		me.adding = entity == null;

		var buttons = [];
		if (!entity) {
			var btn = {
				text : "保存并继续新增",
				formBind : true,
				handler : function() {
					me.onOK(true);
				},
				scope : me
			};

			buttons.push(btn);
		}

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
			text : entity == null ? "关闭" : "取消",
			handler : function() {
				me.close();
			},
			scope : me
		};
		buttons.push(btn);

		Ext.apply(me, {
			title : entity == null ? "新增商品构成" : "编辑商品构成",
			width : 520,
			height : 370,
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
			items : [{
				id : "PSI_Goods_GoodsBOMEditForm_editForm",
				xtype : "form",
				layout : {
					type : "table",
					columns : 1
				},
				height : "100%",
				bodyPadding : 5,
				defaultType : 'textfield',
				fieldDefaults : {
					labelAlign : "right",
					labelSeparator : "",
					msgTarget : 'side',
					margin : "5"
				},
				items : [{
							xtype : "hidden",
							name : "id",
							value : entity == null ? null : entity.get("id")
						}, {
							fieldLabel : "商品编码",
							width : 470,
							readOnly : true,
							value : goods.get("code")
						}, {
							fieldLabel : "品名",
							width : 470,
							readOnly : true,
							value : goods.get("name")
						}, {
							fieldLabel : "规格型号",
							readOnly : true,
							width : 470,
							value : goods.get("spec")
						}, {
							fieldLabel : "商品单位",
							readOnly : true,
							value : goods.get("unitName")
						}, {
							id : "PSI_Goods_GoodsBOMEditForm_editSubGoodsCode",
							fieldLabel : "子商品编码",
							width : 470,
							allowBlank : false,
							blankText : "没有输入子商品",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							xtype : "psi_subgoodsfield",
							parentGoodsId : me.goods.get("id"),
							listeners : {
								specialkey : {
									fn : me.onEditSubGoodsSpecialKey,
									scope : me
								}
							}
						}, {
							fieldLabel : "子商品名称",
							width : 470,
							readOnly : true,
							id : "PSI_Goods_GoodsBOMEditForm_editSubGoodsName"
						}, {
							fieldLabel : "子商品规格型号",
							readOnly : true,
							width : 470,
							id : "PSI_Goods_GoodsBOMEditForm_editSubGoodsSpec"
						}, {
							id : "PSI_Goods_GoodsBOMEditForm_editSubGoodsCount",
							xtype : "numberfield",
							fieldLabel : "子商品数量",
							allowDecimals : false,
							hideTrigger : true,
							name : "subGoodsCount"
						}, {
							fieldLabel : "子商品单位",
							readOnly : true,
							id : "PSI_Goods_GoodsBOMEditForm_editSubGoodsUnitName"
						}, {
							xtype : "hidden",
							id : "PSI_Goods_GoodsBOMEditForm_editSubGoodsId",
							name : "subGoodsId"
						}],
				buttons : buttons
			}]
		});

		me.callParent(arguments);

		me.editForm = Ext.getCmp("PSI_Goods_GoodsBOMEditForm_editForm");

		me.editSubGoods = Ext.getCmp("PSI_Goods_GoodsBOMEditForm_editSubGoods");
		me.editSubGoodsCount = Ext
				.getCmp("PSI_Goods_GoodsBOMEditForm_editSubGoodsCount");
		me.editSubGoodsId = Ext
				.getCmp("PSI_Goods_GoodsBOMEditForm_editSubGoodsId");
		me.editSubGoodsCode = Ext
				.getCmp("PSI_Goods_GoodsBOMEditForm_editSubGoodsCode");
		me.editSubGoodsSpec = Ext
				.getCmp("PSI_Goods_GoodsBOMEditForm_editSubGoodsSpec");
		me.editSubGoodsUnitName = Ext
				.getCmp("PSI_Goods_GoodsBOMEditForm_editSubGoodsUnitName");
	},

	/**
	 * 保存
	 */
	onOK : function(thenAdd) {
		var me = this;
		var f = me.editForm;
		var el = f.getEl();
		el.mask(PSI.Const.SAVING);
		var sf = {
			url : me.URL("/Home/Goods/editGoodsBOM"),
			method : "POST",
			success : function(form, action) {
				me.__lastId = action.result.id;

				el.unmask();

				PSI.MsgBox.tip("数据保存成功");
				me.focus();
				if (thenAdd) {
					me.clearEdit();
				} else {
					me.close();
				}
			},
			failure : function(form, action) {
				el.unmask();
				PSI.MsgBox.showInfo(action.result.msg, function() {
							me.editSubGoods.focus();
						});
			}
		};
		f.submit(sf);
	},

	onEditCodeSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			var editName = me.editName;
			editName.focus();
			editName.setValue(editName.getValue());
		}
	},

	onEditNameSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			var f = me.editForm;
			if (f.getForm().isValid()) {
				me.onOK(me.adding);
			}
		}
	},

	clearEdit : function() {
		var me = this;
		me.editCode.focus();

		var editors = [me.editCode, me.editName];
		for (var i = 0; i < editors.length; i++) {
			var edit = editors[i];
			edit.setValue(null);
			edit.clearInvalid();
		}
	},

	onWndClose : function() {
		var me = this;
		if (me.__lastId) {
			if (me.getParentForm()) {
				me.getParentForm().freshGrid(me.__lastId);
			}
		}
	},

	onWndShow : function() {
		var me = this;
		var editCode = me.editCode;
		editCode.focus();
		editCode.setValue(editCode.getValue());
	}
});
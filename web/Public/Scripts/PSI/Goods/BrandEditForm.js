/**
 * 新增或编辑商品品牌
 */
Ext.define("PSI.Goods.BrandEditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;
		var entity = me.getEntity();

		var title = entity == null ? "新增商品品牌" : "编辑商品品牌";
		title = me.formatTitle(title);
		var iconCls = entity === null ? "SI-button-add" : "SI-button-edit";

		Ext.apply(me, {
			header : {
				title : title,
				height : 40,
				iconCls : iconCls
			},
			width : 400,
			height : 150,
			layout : "fit",
			items : [{
				id : "PSI_Goods_BrandEditForm_editForm",
				xtype : "form",
				layout : {
					type : "table",
					columns : 1
				},
				height : "100%",
				bodyPadding : 5,
				defaultType : 'textfield',
				fieldDefaults : {
					labelWidth : 50,
					labelAlign : "right",
					labelSeparator : "",
					msgTarget : 'side'
				},
				items : [{
							xtype : "hidden",
							name : "id",
							value : entity === null ? null : entity.get("id")
						}, {
							id : "PSI_Goods_BrandEditForm_editName",
							fieldLabel : "品牌",
							allowBlank : false,
							blankText : "没有输入品牌",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							name : "name",
							value : entity === null ? null : entity.get("text"),
							listeners : {
								specialkey : {
									fn : me.onEditNameSpecialKey,
									scope : me
								}
							},
							width : 370
						}, {
							id : "PSI_Goods_BrandEditForm_editParentBrand",
							xtype : "PSI_parent_brand_editor",
							parentItem : me,
							fieldLabel : "上级品牌",
							listeners : {
								specialkey : {
									fn : me.onEditParentBrandSpecialKey,
									scope : me
								}
							},
							width : 370
						}, {
							id : "PSI_Goods_BrandEditForm_editParentBrandId",
							xtype : "hidden",
							name : "parentId",
							value : entity === null ? null : entity
									.get("parentId")
						}],
				buttons : [{
							text : "确定",
							formBind : true,
							iconCls : "PSI-button-ok",
							handler : me.onOK,
							scope : me
						}, {
							text : "取消",
							handler : function() {
								PSI.MsgBox.confirm("请确认是否取消操作?", function() {
											me.close();
										});
							},
							scope : me
						}]
			}],
			listeners : {
				show : {
					fn : me.onEditFormShow,
					scope : me
				}
			}
		});

		me.callParent(arguments);

		me.editForm = Ext.getCmp("PSI_Goods_BrandEditForm_editForm");

		me.editName = Ext.getCmp("PSI_Goods_BrandEditForm_editName");
		me.editParentBrand = Ext
				.getCmp("PSI_Goods_BrandEditForm_editParentBrand");
		me.editParentBrandId = Ext
				.getCmp("PSI_Goods_BrandEditForm_editParentBrandId");
	},

	onEditFormShow : function() {
		var me = this;

		me.editName.focus();

		var entity = me.getEntity();
		if (entity === null) {
			return;
		}

		me.getEl().mask("数据加载中...");
		Ext.Ajax.request({
					url : me.URL("/Home/Goods/brandParentName"),
					method : "POST",
					params : {
						id : entity.get("id")
					},
					callback : function(options, success, response) {
						me.getEl().unmask();
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							me.editParentBrand.setValue(data.parentBrandName);
							me.editParentBrandId.setValue(data.parentBrandId);
							me.editName.setValue(data.name);
						}
					}
				});
	},

	setParentBrand : function(data) {
		var me = this;

		me.editParentBrand.setValue(data.fullName);
		me.editParentBrandId.setValue(data.id);
	},

	onOK : function() {
		var me = this;
		var f = me.editForm;
		var el = f.getEl();
		el.mask("数据保存中...");
		f.submit({
					url : me.URL("/Home/Goods/editBrand"),
					method : "POST",
					success : function(form, action) {
						el.unmask();
						me.close();
						if (me.getParentForm()) {
							me.getParentForm().refreshGrid();
						}
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg, function() {
									me.editName.focus();
								});
					}
				});
	},

	onEditNameSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			me.editParentBrand.focus();
		}
	},

	onEditParentBrandSpecialKey : function(field, e) {
		var me = this;
		if (e.getKey() == e.ENTER) {
			if (me.editForm.getForm().isValid()) {
				me.onOK();
			}
		}
	}
});
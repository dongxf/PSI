/**
 * 供应商分类 - 新增或编辑界面
 */
Ext.define("PSI.Supplier.CategoryEditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	initComponent : function() {
		var me = this;
		var entity = me.getEntity();
		me.adding = entity == null;
		me.__lastId = entity == null ? null : entity.get("id");

		var buttons = [];
		if (!entity) {
			buttons.push({
						text : "保存并继续新增",
						formBind : true,
						handler : function() {
							me.onOK(true);
						},
						scope : me
					});
		}

		buttons.push({
					text : "保存",
					formBind : true,
					iconCls : "PSI-button-ok",
					handler : function() {
						me.onOK(false);
					},
					scope : me
				}, {
					text : entity == null ? "关闭" : "取消",
					handler : function() {
						me.close();
					},
					scope : me
				});

		var title = entity == null ? "新增供应商分类" : "编辑供应商分类";
		title = me.formatTitle(title);
		var iconCls = entity == null ? "PSI-button-add" : "PSI-button-edit";

		Ext.apply(me, {
			header : {
				title : title,
				height : 40,
				iconCls : iconCls
			},
			width : 400,
			height : 160,
			layout : "fit",
			items : [{
						id : "PSI_Supplier_CategoryEditForm_editForm",
						xtype : "form",
						layout : {
							type : "table",
							columns : 1
						},
						height : "100%",
						bodyPadding : 5,
						defaultType : 'textfield',
						fieldDefaults : {
							labelWidth : 60,
							labelAlign : "right",
							labelSeparator : "",
							msgTarget : 'side',
							width : 370,
							margin : "5"
						},
						items : [{
									xtype : "hidden",
									name : "id",
									value : entity == null ? null : entity
											.get("id")
								}, {
									id : "PSI_Supplier_CategoryEditForm_editCode",
									fieldLabel : "分类编码",
									allowBlank : false,
									blankText : "没有输入分类编码",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "code",
									value : entity == null ? null : entity
											.get("code"),
									listeners : {
										specialkey : {
											fn : me.onEditCodeSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_Supplier_CategoryEditForm_editName",
									fieldLabel : "分类名称",
									allowBlank : false,
									blankText : "没有输入分类名称",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "name",
									value : entity == null ? null : entity
											.get("name"),
									listeners : {
										specialkey : {
											fn : me.onEditNameSpecialKey,
											scope : me
										}
									}
								}],
						buttons : buttons
					}],
			listeners : {
				show : {
					fn : me.onWndShow,
					scope : me
				},
				close : {
					fn : me.onWndClose,
					scope : me
				}
			}
		});

		me.callParent(arguments);

		me.editForm = Ext.getCmp("PSI_Supplier_CategoryEditForm_editForm");

		me.editCode = Ext.getCmp("PSI_Supplier_CategoryEditForm_editCode");
		me.editName = Ext.getCmp("PSI_Supplier_CategoryEditForm_editName");
	},

	onOK : function(thenAdd) {
		var me = this;
		var f = me.editForm;
		var el = f.getEl();
		el.mask(PSI.Const.SAVING);
		f.submit({
					url : me.URL("/Home/Supplier/editCategory"),
					method : "POST",
					success : function(form, action) {
						el.unmask();
						PSI.MsgBox.tip("数据保存成功");
						me.focus();
						me.__lastId = action.result.id;
						if (thenAdd) {
							var editCode = me.editCode;
							editCode.setValue(null);
							editCode.clearInvalid();
							editCode.focus();

							var editName = me.editName;
							editName.setValue(null);
							editName.clearInvalid();
						} else {
							me.close();
						}
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg, function() {
									me.editCode.focus();
								});
					}
				});
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
				me.editCode.focus();
				me.onOK(me.adding);
			}
		}
	},

	onWndClose : function() {
		var me = this;
		if (me.__lastId) {
			if (me.getParentForm()) {
				me.getParentForm().freshCategoryGrid(me.__lastId);
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
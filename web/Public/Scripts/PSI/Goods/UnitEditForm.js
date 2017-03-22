/**
 * 商品计量单位 - 新增或编辑界面
 */
Ext.define("PSI.Goods.UnitEditForm", {
			extend : "PSI.AFX.BaseDialogForm",

			/**
			 * 初始化组件
			 */
			initComponent : function() {
				var me = this;
				var entity = me.getEntity();
				me.adding = entity == null;

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

				Ext.apply(me, {
							title : entity == null ? "新增商品计量单位" : "编辑商品计量单位",
							width : 400,
							height : 110,
							layout : "fit",
							items : [{
								id : "PSI_Goods_UnitEditForm_editForm",
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
									id : "PSI_Goods_UnitEditForm_editName",
									fieldLabel : "计量单位",
									allowBlank : false,
									blankText : "没有输入计量单位",
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

				me.editForm = Ext.getCmp("PSI_Goods_UnitEditForm_editForm");
				me.editName = Ext.getCmp("PSI_Goods_UnitEditForm_editName");
			},

			onOK : function(thenAdd) {
				var me = this;
				var f = me.editForm;
				var el = f.getEl();
				el.mask(PSI.Const.SAVING);
				f.submit({
							url : me.URL("/Home/Goods/editUnit"),
							method : "POST",
							success : function(form, action) {
								el.unmask();
								me.__lastId = action.result.id;
								PSI.MsgBox.tip("数据保存成功");
								me.focus();
								if (thenAdd) {
									var editName = me.editName;
									editName.focus();
									editName.setValue(null);
									editName.clearInvalid();
								} else {
									me.close();
								}
							},
							failure : function(form, action) {
								el.unmask();
								PSI.MsgBox.showInfo(action.result.msg,
										function() {
											me.editName.focus();
										});
							}
						});
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

				var editName = me.editName;
				editName.focus();
				editName.setValue(editName.getValue());
			}
		});
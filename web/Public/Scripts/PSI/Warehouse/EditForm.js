/**
 * 仓库 - 新增或编辑界面
 */
Ext.define("PSI.Warehouse.EditForm", {
			extend : "PSI.AFX.BaseForm",

			/**
			 * 初始化组件
			 */
			initComponent : function() {
				var me = this;

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
							title : entity == null ? "新增仓库" : "编辑仓库",
							width : 400,
							height : 140,
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
								id : "editForm",
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
									id : "editCode",
									fieldLabel : "仓库编码",
									allowBlank : false,
									blankText : "没有输入仓库编码",
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
									id : "editName",
									fieldLabel : "仓库名称",
									allowBlank : false,
									blankText : "没有输入仓库名称",
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
							}]
						});

				me.callParent(arguments);

				me.editForm = Ext.getCmp("editForm");

				me.editCode = Ext.getCmp("editCode");
				me.editName = Ext.getCmp("editName");
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
					url : me.URL("/Home/Warehouse/editWarehouse"),
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
									Ext.getCmp("editCode").focus();
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
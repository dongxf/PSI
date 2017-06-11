/**
 * 修改用户密码
 */
Ext.define("PSI.User.ChangeUserPasswordForm", {
	extend : "PSI.AFX.BaseDialogForm",

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;
		var entity = me.getEntity();

		Ext.apply(me, {
			header : {
				title : me.formatTitle("修改用户登录密码"),
				height : 40,
				iconCls : "PSI-button-change-password"
			},
			width : 400,
			height : 200,
			layout : "fit",
			items : [{
				id : "PSI_User_ChangeUserPasswordForm_editForm",
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
					width : 370
				},
				items : [{
							xtype : "hidden",
							name : "id",
							value : entity.id
						}, {
							fieldLabel : "登录名",
							value : entity.loginName,
							xtype : "displayfield"
						}, {
							fieldLabel : "姓名",
							value : entity.name,
							xtype : "displayfield"
						}, {
							id : "PSI_User_ChangeUserPasswordForm_editPassword",
							fieldLabel : "密码",
							allowBlank : false,
							blankText : "没有输入密码",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							inputType : "password",
							name : "password",
							listeners : {
								specialkey : {
									fn : me.onEditPasswordSpecialKey,
									scope : me
								}
							}
						}, {
							id : "PSI_User_ChangeUserPasswordForm_editConfirmPassword",
							fieldLabel : "确认密码",
							allowBlank : false,
							blankText : "没有输入确认密码",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							inputType : "password",
							listeners : {
								specialkey : {
									fn : me.onEditConfirmPasswordSpecialKey,
									scope : me
								}
							}
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
								me.close();
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

		me.editPassword = Ext
				.getCmp("PSI_User_ChangeUserPasswordForm_editPassword");
		me.editConfirmPassword = Ext
				.getCmp("PSI_User_ChangeUserPasswordForm_editConfirmPassword");
		me.editForm = Ext.getCmp("PSI_User_ChangeUserPasswordForm_editForm");
	},

	onEditFormShow : function() {
		var me = this;
		me.editPassword.focus();
	},

	/**
	 * 修改密码
	 */
	onOK : function() {
		var me = this;
		var pass = me.editPassword.getValue();
		var pass2 = me.editConfirmPassword.getValue();
		if (pass != pass2) {
			me.showInfo("输入的密码和确认密码不一致，请重新输入", function() {
						me.editPassword.focus();
					});

			return;
		}

		var f = me.editForm;
		var el = f.getEl();
		el.mask("数据保存中...");
		f.submit({
					url : me.URL("Home/User/changePassword"),
					method : "POST",
					success : function(form, action) {
						el.unmask();
						me.showInfo("成功修改密码", function() {
									me.close();
								});
					},
					failure : function(form, action) {
						el.unmask();
						me.showInfo(action.result.msg);
					}
				});
	},

	onEditPasswordSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			me.editConfirmPassword.focus();
		}
	},

	onEditConfirmPasswordSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			if (me.editForm.getForm().isValid()) {
				me.onOK();
			}
		}
	}
});
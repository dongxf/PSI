/**
 * 银行账户 - 新增或编辑界面
 */
Ext.define("PSI.Bank.EditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	config : {
		company : null
	},

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

		var t = entity == null ? "新增银行账户" : "编辑银行账户";
		var f = entity == null
				? "edit-form-create.png"
				: "edit-form-update.png";
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
					width : 400,
					height : 310,
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
								id : "PSI_Bank_EditForm_editForm",
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
									xtype : "hidden",
									name : "companyId",
									value : me.getCompany().get("id")
								}, {
									fieldLabel : "组织机构",
									xtype : "displayfield",
									value : me.getCompany().get("name")
								}, {
									id : "PSI_Bank_EditForm_editBankName",
									fieldLabel : "银行",
									allowBlank : false,
									blankText : "没有输入银行名称",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "bankName",
									value : entity == null ? null : entity
											.get("bankName"),
									listeners : {
										specialkey : {
											fn : me.onEditBankNameSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_Bank_EditForm_editBankNumber",
									fieldLabel : "账号",
									allowBlank : false,
									blankText : "没有输入账号",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "bankNumber",
									value : entity == null ? null : entity
											.get("bankNumber"),
									listeners : {
										specialkey : {
											fn : me.onEditBankNumberSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_Bank_EditForm_editMemo",
									fieldLabel : "备注",
									name : "memo",
									value : entity == null ? null : entity
											.get("memo"),
									listeners : {
										specialkey : {
											fn : me.onEditMemoSpecialKey,
											scope : me
										}
									}
								}],
								buttons : buttons
							}]
				});

		me.callParent(arguments);

		me.editForm = Ext.getCmp("PSI_Bank_EditForm_editForm");

		me.editBankName = Ext.getCmp("PSI_Bank_EditForm_editBankName");
		me.editBankNumber = Ext.getCmp("PSI_Bank_EditForm_editBankNumber");
		me.editMemo = Ext.getCmp("PSI_Bank_EditForm_editMemo");
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
			url : me.URL("/Home/Bank/editBank"),
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
							me.editBankName.focus();
						});
			}
		};
		f.submit(sf);
	},

	onEditBankNameSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			me.editBankNumber.focus();
			me.editBankNumber.setValue(me.editBankNumber.getValue());
		}
	},

	onEditBankNumberSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			me.editMemo.focus();
			me.editMemo.setValue(me.editMemo.getValue());
		}
	},

	onEditMemoSpecialKey : function(field, e) {
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
		me.editBankName.focus();

		var editors = [me.editBankName, me.editBankNumber, me.editMemo];
		for (var i = 0; i < editors.length; i++) {
			var edit = editors[i];
			edit.setValue(null);
			edit.clearInvalid();
		}
	},

	onWindowBeforeUnload : function(e) {
		return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
	},

	onWndClose : function() {
		var me = this;

		Ext.get(window).un('beforeunload', me.onWindowBeforeUnload);

		if (me.__lastId) {
			if (me.getParentForm()) {
				me.getParentForm().refreshMainGrid(me.__lastId);
			}
		}
	},

	onWndShow : function() {
		var me = this;

		Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);

		me.editBankName.focus();
		me.editBankName.setValue(me.editBankName.getValue());
	}
});
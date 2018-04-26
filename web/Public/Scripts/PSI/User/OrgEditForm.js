/**
 * 新增或编辑组织机构
 */
Ext.define("PSI.User.OrgEditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;
		var entity = me.getEntity();

		var title = entity == null ? "新增组织机构" : "编辑组织机构";
		title = me.formatTitle(title);

		var iconCls = entity == null ? "PSI-button-add" : "PSI-button-edit";

		Ext.apply(me, {
			header : {
				title : title,
				height : 40,
				iconCls : iconCls
			},
			width : 400,
			height : 200,
			layout : "fit",
			items : [{
				id : "PSI_User_OrgEditForm_editForm",
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
					msgTarget : 'side'
				},
				items : [{
							xtype : "hidden",
							name : "id",
							value : entity === null ? null : entity.get("id")
						}, {
							id : "PSI_User_OrgEditForm_editName",
							fieldLabel : "名称",
							allowBlank : false,
							blankText : "没有输入名称",
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
							id : "PSI_User_OrgEditForm_editParentOrg",
							xtype : "PSI_parent_org_editor",
							parentItem : me,
							fieldLabel : "上级组织",
							listeners : {
								specialkey : {
									fn : me.onEditParentOrgSpecialKey,
									scope : me
								}
							},
							width : 370
						}, {
							id : "PSI_User_OrgEditForm_editParentOrgId",
							xtype : "hidden",
							name : "parentId",
							value : entity === null ? null : entity
									.get("parentId")
						}, {
							id : "PSI_User_OrgEditForm_editOrgCode",
							fieldLabel : "编码",
							allowBlank : false,
							blankText : "没有输入编码",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							name : "orgCode",
							value : entity === null ? null : entity
									.get("orgCode"),
							listeners : {
								specialkey : {
									fn : me.onEditOrgCodeSpecialKey,
									scope : me
								}
							},
							width : 370
						}, {
							xtype : "displayfield",
							value : "上级组织机构为空的时候，该组织机构是公司"
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
								me.confirm("请确认是否取消操作?", function() {
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
				},
				close : {
					fn : me.onWndClose,
					scope : me
				}
			}
		});

		me.callParent(arguments);

		me.editParentOrg = Ext.getCmp("PSI_User_OrgEditForm_editParentOrg");
		me.editParentOrgId = Ext.getCmp("PSI_User_OrgEditForm_editParentOrgId");
		me.editName = Ext.getCmp("PSI_User_OrgEditForm_editName");
		me.editOrgCode = Ext.getCmp("PSI_User_OrgEditForm_editOrgCode");

		me.editForm = Ext.getCmp("PSI_User_OrgEditForm_editForm");
	},

	onWindowBeforeUnload : function(e) {
		return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
	},

	onWndClose : function() {
		var me = this;

		Ext.get(window).un('beforeunload', me.onWindowBeforeUnload);
	},

	onEditFormShow : function() {
		var me = this;

		Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);

		me.editName.focus();

		var entity = me.getEntity();
		if (entity === null) {
			return;
		}
		var el = me.getEl() || Ext.getBody();
		el.mask("数据加载中...");
		me.ajax({
					url : me.URL("Home/User/orgParentName"),
					params : {
						id : entity.get("id")
					},
					callback : function(options, success, response) {
						el.unmask();
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							me.editParentOrg.setValue(data.parentOrgName);
							me.editParentOrgId.setValue(data.parentOrgId);
							me.editName.setValue(data.name);
							me.editOrgCode.setValue(data.orgCode);
						}
					}
				});
	},

	setParentOrg : function(data) {
		var me = this;
		me.editParentOrg.setValue(data.fullName);
		me.editParentOrgId.setValue(data.id);
	},

	onOK : function() {
		var me = this;
		var f = me.editForm;
		var el = f.getEl();
		el.mask("数据保存中...");
		f.submit({
					url : me.URL("Home/User/editOrg"),
					method : "POST",
					success : function(form, action) {
						el.unmask();
						me.close();
						me.getParentForm().freshOrgGrid();
					},
					failure : function(form, action) {
						el.unmask();
						me.showInfo(action.result.msg, function() {
									me.editName.focus();
								});
					}
				});
	},

	onEditNameSpecialKey : function(field, e) {
		var me = this;
		if (e.getKey() == e.ENTER) {
			me.editParentOrg.focus();
		}
	},

	onEditParentOrgSpecialKey : function(field, e) {
		var me = this;
		if (e.getKey() == e.ENTER) {
			me.editOrgCode.focus();
		}
	},

	onEditOrgCodeSpecialKey : function(field, e) {
		var me = this;
		if (e.getKey() == e.ENTER) {
			if (me.editForm.getForm().isValid()) {
				me.onOK();
			}
		}
	}
});
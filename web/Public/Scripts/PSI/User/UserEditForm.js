Ext.define("PSI.User.UserEditForm", {
    extend: "Ext.window.Window",

    config: {
        parentForm: null,
        entity: null
    },

    initComponent: function () {
        var me = this;

        var entity = me.getEntity();

        Ext.apply(me, {
            title: entity === null ? "新增用户" : "编辑用户",
            modal: true,
            onEsc: Ext.emptyFn,
            width: 400,
            height: 220,
            layout: "fit",
            defaultFocus: "editLoginName",
            items: [{
                id: "editForm",
                xtype: "form",
                layout: "form",
                height: "100%",
                bodyPadding: 5,
                defaultType: 'textfield',
                fieldDefaults: {
                    labelWidth: 60,
                    labelAlign: "right",
                    labelSeparator: "",
                    msgTarget: 'side'
                },
                items: [{
                    xtype: "hidden",
                    name: "id",
                    value: entity === null ? null : entity.id
                }, {
                    id: "editLoginName",
                    fieldLabel: "登录名",
                    allowBlank: false,
                    blankText: "没有输入登录名",
                    beforeLabelTextTpl: PSI.Const.REQUIRED,
                    name: "loginName",
                    value: entity === null ? null : entity.loginName,
                    listeners: {
                        specialkey: {
                            fn: me.onEditLoginNameSpecialKey,
                            scope: me
                        }
                    }
                }, {
                    id: "editName",
                    fieldLabel: "姓名",
                    allowBlank: false,
                    blankText: "没有输入姓名",
                    beforeLabelTextTpl: PSI.Const.REQUIRED,
                    name: "name",
                    value: entity === null ? null : entity.name,
                    listeners: {
                        specialkey: {
                            fn: me.onEditNameSpecialKey,
                            scope: me
                        }
                    }
                }, {
                    id: "editOrgCode",
                    fieldLabel: "编码",
                    allowBlank: false,
                    blankText: "没有输入编码",
                    beforeLabelTextTpl: PSI.Const.REQUIRED,
                    name: "orgCode",
                    value: entity === null ? null : entity.orgCode,
                    listeners: {
                        specialkey: {
                            fn: me.onEditOrgCodeSpecialKey,
                            scope: me
                        }
                    }
                }, {
                    id: "editOrgName",
                    xtype: "PSI_org_editor",
                    fieldLabel: "所属组织",
                    allowBlank: false,
                    blankText: "没有选择组织机构",
                    beforeLabelTextTpl: PSI.Const.REQUIRED,
                    parentItem: this,
                    value: entity === null ? null : entity.orgName,
                    listeners: {
                        specialkey: {
                            fn: me.onEditOrgNameSpecialKey,
                            scope: me
                        }
                    }
                }, {
                    id: "editOrgId",
                    xtype: "hidden",
                    name: "orgId",
                    value: entity === null ? null : entity.orgId
                }, {
                    xtype: "radiogroup",
                    fieldLabel: "能否登录",
                    columns: 2,
                    items: [
                        {
                            boxLabel: "允许登录", name: "enabled", inputValue: true,
                            checked: entity === null ? true : entity.enabled == 1
                        },
                        {
                            boxLabel: "<span style='color:red'>禁止登录</span>",
                            name: "enabled", inputValue: false,
                            checked: entity === null ? false : entity.enabled != 1
                        }
                    ]
                }],
                buttons: [{
                    text: "确定",
                    formBind: true,
                    iconCls: "PSI-button-ok",
                    handler: me.onOK,
                    scope: me
                }, {
                    text: "取消", handler: function () {
                        PSI.MsgBox.confirm("请确认是否取消操作?", function () {
                            me.close();
                        });
                    }, scope: me
                }]
            }]
        });

        me.callParent(arguments);
    },

    setOrg: function (data) {
        var editOrgName = Ext.getCmp("editOrgName");
        editOrgName.setValue(data.fullName);

        var editOrgId = Ext.getCmp("editOrgId");
        editOrgId.setValue(data.id);
    },

    // private
    onOK: function () {
        var me = this;
        var f = Ext.getCmp("editForm");
        var el = f.getEl();
        el.mask("数据保存中...");
        f.submit({
            url: PSI.Const.BASE_URL + "Home/User/editUser",
            method: "POST",
            success: function (form, action) {
                el.unmask();
                PSI.MsgBox.showInfo("数据保存成功", function () {
                    me.close();
                    me.getParentForm().freshUserGrid();
                });
            },
            failure: function (form, action) {
                el.unmask();
                PSI.MsgBox.showInfo(action.result.msg, function () {
                    Ext.getCmp("editName").focus();
                });
            }
        });
    },

    onEditLoginNameSpecialKey: function (field, e) {
        if (e.getKey() === e.ENTER) {
            Ext.getCmp("editName").focus();
        }
    },

    onEditNameSpecialKey: function (field, e) {
        if (e.getKey() === e.ENTER) {
            Ext.getCmp("editOrgCode").focus();
        }
    },

    onEditOrgCodeSpecialKey: function (field, e) {
        if (e.getKey() === e.ENTER) {
            Ext.getCmp("editOrgName").focus();
        }
    },

    onEditOrgNameSpecialKey: function (field, e) {
        if (e.getKey() === e.ENTER) {
            var f = Ext.getCmp("editForm");
            if (f.getForm().isValid()) {
                this.onOK();
            }
        }
    }
});
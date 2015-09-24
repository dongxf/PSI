// 登录界面
Ext.define("PSI.User.LoginForm", {
    extend: 'Ext.window.Window',
    
    config: {
        demoInfo: "",
        productionName: ""
    },

    modal: true,
    closable: false,
    resizable: false,
    onEsc: Ext.emptyFn,
    width: 400,
    layout: "fit",
    defaultFocus: Ext.util.Cookies.get("PSI_user_login_name") ? "editPassword" : "editLoginName",

    initComponent: function () {
        var me = this;

        Ext.apply(me, {
            height: me.getDemoInfo() == "" ? 140 : 200,
            header: {
                title: "<span style='font-size:120%'>登录 - " + me.getProductionName() + "</span>",
                iconCls: "PSI-login",
                height: 40
            },
            items: [{
                id: "loginForm",
                xtype: "form",
                layout: {
					type : "table",
					columns : 1
				},
                height: "100%",
                border: 0,
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
                	name: "fromDevice",
                	value: "web"
                },{
                    id: "editLoginName",
                    width: 370,
                    fieldLabel: "登录名",
                    allowBlank: false,
                    blankText: "没有输入登录名",
                    beforeLabelTextTpl: PSI.Const.REQUIRED,
                    name: "loginName",
                    value: Ext.util.Cookies.get("PSI_user_login_name"),
                    listeners: {
                        specialkey: function (field, e) {
                            if (e.getKey() === e.ENTER) {
                                Ext.getCmp("editPassword").focus();
                            }
                        }
                    }
                }, {
                    id: "editPassword",
                    fieldLabel: "密码",
                    allowBlank: false,
                    blankText: "没有输入密码",
                    beforeLabelTextTpl: PSI.Const.REQUIRED,
                    inputType: "password",
                    name: "password",
                    width: 370,
                    listeners: {
                        specialkey: function (field, e) {
                            if (e.getKey() === e.ENTER) {
                                if (Ext.getCmp("loginForm").getForm().isValid()) {
                                    me.onOK();
                                }
                            }
                        }
                    }
                },{
                    xtype: "displayfield",
                    value: me.getDemoInfo()
                }],
                buttons: [{
                    text: "登录",
                    formBind: true,
                    handler: me.onOK,
                    scope: me,
                    iconCls: "PSI-button-ok"
                },{
                    text: "帮助",
                    iconCls: "PSI-help",
                    handler: function() {
                        window.open("http://www.kancloud.cn/crm8000/psi_help/login");
                    }
                }]
            }]
        });

        me.callParent(arguments);
    },

    onOK: function () {
        var me = this;

        var loginName = Ext.getCmp("editLoginName").getValue();
        var f = Ext.getCmp("loginForm");
        var el = f.getEl() || Ext.getBody();
        el.mask("系统登录中...");
        f.getForm().submit({
            url: PSI.Const.BASE_URL + "Home/User/loginPOST",
            method: "POST",
            success: function (form, action) {
                Ext.util.Cookies.set("PSI_user_login_name", encodeURIComponent(loginName),
                    Ext.Date.add(new Date(), Ext.Date.YEAR, 1));

                location.replace(PSI.Const.BASE_URL);
            },
            failure: function (form, action) {
                el.unmask();
                PSI.MsgBox.showInfo(action.result.msg, function () {
                    var editPassword = Ext.getCmp("editPassword");
                    editPassword.setValue(null);
                    editPassword.clearInvalid();
                    editPassword.focus();
                });
            }
        });
    }
});
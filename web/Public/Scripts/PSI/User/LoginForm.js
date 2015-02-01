Ext.define("PSI.User.LoginForm", {
    extend: 'Ext.window.Window',

    header: {
        title: "<span style='font-size:120%'>登录 - PSI</span>",
        iconCls: "PSI-login",
        height: 40
    },
    modal: true,
    closable: false,
    onEsc: Ext.emptyFn,
    width: 400,
    height: 140,
    layout: "fit",
    defaultFocus: Ext.util.Cookies.get("PSI_user_login_name") ? "editPassword" : "editLoginName",

    initComponent: function () {
        var me = this;

        Ext.apply(me, {
            items: [{
                id: "loginForm",
                xtype: "form",
                layout: "form",
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
                    id: "editLoginName",
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
                    listeners: {
                        specialkey: function (field, e) {
                            if (e.getKey() === e.ENTER) {
                                if (Ext.getCmp("loginForm").getForm().isValid()) {
                                    me.onOK();
                                }
                            }
                        }
                    }
                }],
                buttons: [{
                    text: "登录",
                    formBind: true,
                    handler: me.onOK,
                    iconCls: "PSI-button-ok"
                },{
                    text: "帮助",
                    iconCls: "PSI-help",
                    handler: function() {
                        window.open("http://my.oschina.net/u/134395/blog/373981");
                    }
                }]
            }]
        });

        me.callParent(arguments);
    },

    getPostURL: function() {
    	return PSI.Const.BASE_URL + "Home/User/loginPOST";
    },
    
    // private
    onOK: function () {
        var me = this;

        var loginName = Ext.getCmp("editLoginName").getValue();
        var f = Ext.getCmp("loginForm");
        var el = f.getEl() || Ext.getBody();
        el.mask("系统登录中...");
        f.getForm().submit({
            url: me.getPostURL(),
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
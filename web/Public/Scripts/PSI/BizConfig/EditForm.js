Ext.define("PSI.BizConfig.EditForm", {
    extend: "Ext.window.Window",
    config: {
        parentForm: null
    },
    initComponent: function () {
        var me = this;

        var buttons = [];

        buttons.push({
            text: "保存",
            formBind: true,
            iconCls: "PSI-button-ok",
            handler: function () {
                me.onOK();
            }, scope: me
        }, {
            text: "取消", handler: function () {
                me.close();
            }, scope: me
        });

        Ext.apply(me, {
            title: "业务设置",
            modal: true,
            onEsc: Ext.emptyFn,
            width: 400,
            height: 130,
            layout: "fit",
            items: [
                {
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
                    items: [
                        {
                            fieldLabel: "设置项",
                            xtype: "displayfield",
                            value: ""
                        }, {
                            id: "editValue2002-01",
                            fieldLabel: "值",
                            name: "value2002-01"
                        }
                    ],
                    buttons: buttons
                }],
            listeners: {
                close: {
                    fn: me.onWndClose,
                    scope: me
                },
                show: {
                    fn: me.onWndShow,
                    scope: me
                }
            }
        });

        me.callParent(arguments);
    },
    // private
    onOK: function (thenAdd) {
        var me = this;
        var f = Ext.getCmp("editForm");
        var el = f.getEl();
        el.mask(PSI.Const.SAVING);
        f.submit({
            url: PSI.Const.BASE_URL + "Home/BizConfig/edit",
            method: "POST",
            success: function (form, action) {
                el.unmask();
                me.__saved = true;
                PSI.MsgBox.info("数据保存成功", function() {
                    me.close();    
                });
            },
            failure: function (form, action) {
                el.unmask();
                PSI.MsgBox.showInfo(action.result.msg);
            }
        });
    },
    onWndClose: function () {
        var me = this;
        if (me.__saved) {
            me.getParentForm().refreshGrid();
        }
    },
    onWndShow: function () {
        var me = this;
        me.__saved = false;
    }
});
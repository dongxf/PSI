Ext.define("PSI.MsgBox", {
    statics: {
        showInfo: function (info, func) {
            Ext.Msg.show({
                title: "提示",
                msg: info,
                icon: Ext.Msg.INFO,
                buttons: Ext.Msg.OK,
                modal: true,
                fn: function () {
                    if (func) {
                        func();
                    }
                }
            });
        },
        confirm: function (confirmInfo, funcOnYes) {
            Ext.Msg.show({
                title: "提示",
                msg: confirmInfo,
                icon: Ext.Msg.QUESTION,
                buttons: Ext.Msg.YESNO,
                modal: true,
                defaultFocus: "no",
                fn: function (id) {
                    if (id === "yes" && funcOnYes) {
                        funcOnYes();
                    }
                }
            });
        },
        tip: function (info) {
            var wnd = Ext.create("Ext.window.Window", {
                modal: false,
                onEsc: Ext.emptyFn,
                width: 300,
                height: 100,
                header: false,
                laytout: "fit",
                border: 0,
                items: [
                    {
                        xtype: "container",
                        html: "<h3>提示</h3><p>" + info + "</p>"
                    }
                ]
            });

            wnd.showAt(document.body.clientWidth / 2 - 150, 0);

            Ext.Function.defer(function () {
                wnd.hide();
                wnd.close();
            }, 2000);
        }
    }
});
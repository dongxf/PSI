Ext.define("PSI.Goods.GoodsTUEditForm", {
    extend: "Ext.window.Window",
    config: {
        parentForm: null,
        entity: null
    },
    initComponent: function () {
        var me = this;
        var entity = me.getEntity();

        Ext.define("PSIGoodsUnit", {
            extend: "Ext.data.Model",
            fields: ["id", "name"]
        });

        var unitStore = Ext.create("Ext.data.Store", {
            model: "PSIGoodsUnit",
            autoLoad: false,
            data: []
        });
        me.unitStore = unitStore;

        me.adding = entity == null;

        var buttons = [];
        if (!entity) {
            buttons.push({
                text: "保存并继续新增",
                formBind: true,
                handler: function () {
                    me.onOK(true);
                },
                scope: me
            });
        }

        buttons.push({
            text: "保存",
            formBind: true,
            iconCls: "PSI-button-ok",
            handler: function () {
                me.onOK(false);
            }, scope: me
        }, {
            text: entity == null ? "关闭" : "取消", handler: function () {
                me.close();
            }, scope: me
        });

        var categoryStore = me.getParentForm().categoryGrid.getStore();
        var selectedCategory = me.getParentForm().categoryGrid.getSelectionModel().getSelection();
        var defaultCategoryId = null;
        if (selectedCategory != null && selectedCategory.length > 0) {
            defaultCategoryId = selectedCategory[0].get("id");
        }

        Ext.apply(me, {
            title: entity == null ? "新增商品" : "编辑商品",
            modal: true,
            onEsc: Ext.emptyFn,
            width: 400,
            height: 230,
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
                            xtype: "hidden",
                            name: "id",
                            value: entity == null ? null : entity.get("id")
                        },
                        {
                            id: "editCategory",
                            xtype: "combo",
                            fieldLabel: "商品分类",
                            allowBlank: false,
                            blankText: "没有输入商品分类",
                            beforeLabelTextTpl: PSI.Const.REQUIRED,
                            valueField: "id",
                            displayField: "name",
                            store: categoryStore,
                            queryMode: "local",
                            editable: false,
                            value: defaultCategoryId,
                            name: "categoryId",
                            listeners: {
                                specialkey: {
                                    fn: me.onEditSpecialKey,
                                    scope: me
                                }
                            }
                        },
                        {
                            id: "editCode",
                            fieldLabel: "商品编码",
                            allowBlank: false,
                            blankText: "没有输入商品编码",
                            beforeLabelTextTpl: PSI.Const.REQUIRED,
                            name: "code",
                            value: entity == null ? null : entity.get("code"),
                            listeners: {
                                specialkey: {
                                    fn: me.onEditSpecialKey,
                                    scope: me
                                }
                            }
                        },
                        {
                            id: "editName",
                            fieldLabel: "品名",
                            allowBlank: false,
                            blankText: "没有输入品名",
                            beforeLabelTextTpl: PSI.Const.REQUIRED,
                            name: "name",
                            value: entity == null ? null : entity.get("name"),
                            listeners: {
                                specialkey: {
                                    fn: me.onEditSpecialKey,
                                    scope: me
                                }
                            }
                        },
                        {
                            id: "editSpec",
                            fieldLabel: "规格型号",
                            name: "spec",
                            value: entity == null ? null : entity.get("spec"),
                            listeners: {
                                specialkey: {
                                    fn: me.onEditSpecialKey,
                                    scope: me
                                }
                            }
                        },
                        {
                            id: "editUnit",
                            xtype: "combo",
                            fieldLabel: "计量单位",
                            allowBlank: false,
                            blankText: "没有输入计量单位",
                            beforeLabelTextTpl: PSI.Const.REQUIRED,
                            valueField: "id",
                            displayField: "name",
                            store: unitStore,
                            queryMode: "local",
                            editable: false,
                            name: "unitId",
                            listeners: {
                                specialkey: {
                                    fn: me.onEditSpecialKey,
                                    scope: me
                                }
                            }
                        }, {
                            fieldLabel: "销售价",
                            allowBlank: false,
                            blankText: "没有输入销售价",
                            beforeLabelTextTpl: PSI.Const.REQUIRED,
                            xtype: "numberfield",
                            hideTrigger: true,
                            name: "salePrice",
                            id: "editSalePrice",
                            value: entity == null ? null : entity.get("salePrice"),
                            listeners: {
                                specialkey: {
                                    fn: me.onEditSalePriceSpecialKey,
                                    scope: me
                                }
                            }
                        }
                    ],
                    buttons: buttons
                }],
            listeners: {
                show: {
                    fn: me.onWndShow,
                    scope: me
                },
                close: {
                    fn: me.onWndClose,
                    scope: me
                }
            }
        });

        me.callParent(arguments);

        me.__editorList = ["editCategory", "editCode", "editName", "editSpec",
            "editUnit", "editSalePrice"];
    },
    onWndShow: function () {
        var me = this;
        var editCode = Ext.getCmp("editCode");
        editCode.focus();
        editCode.setValue(editCode.getValue());

        var el = me.getEl();
        var unitStore = me.unitStore;
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Goods/allUnits",
            method: "POST",
            callback: function (options, success, response) {
                unitStore.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    unitStore.add(data);
                }

                el.unmask();

                if (!me.adding) {
                    Ext.getCmp("editCategory").setValue(me.getEntity().get("categoryId"));
                    Ext.getCmp("editUnit").setValue(me.getEntity().get("unitId"));
                } else {
                    if (unitStore.getCount() > 0) {
                        var unitId = unitStore.getAt(0).get("id");
                        Ext.getCmp("editUnit").setValue(unitId);
                    }
                }
            }
        });
    },
    // private
    onOK: function (thenAdd) {
        var me = this;
        var f = Ext.getCmp("editForm");
        var el = f.getEl();
        el.mask(PSI.Const.SAVING);
        f.submit({
            url: PSI.Const.BASE_URL + "Home/Goods/editGoods",
            method: "POST",
            success: function (form, action) {
                el.unmask();
                me.__lastId = action.result.id;
                me.getParentForm().__lastId = me.__lastId;

                PSI.MsgBox.tip("数据保存成功");
                me.focus();

                if (thenAdd) {
                    me.clearEdit();
                } else {
                    me.close();
                    me.getParentForm().freshGoodsGrid();
                }
            },
            failure: function (form, action) {
                el.unmask();
                PSI.MsgBox.showInfo(action.result.msg, function () {
                    Ext.getCmp("editCode").focus();
                });
            }
        });
    },
    onEditSpecialKey: function (field, e) {
        if (e.getKey() === e.ENTER) {
            var me = this;
            var id = field.getId();
            for (var i = 0; i < me.__editorList.length; i++) {
                var editorId = me.__editorList[i];
                if (id === editorId) {
                    var edit = Ext.getCmp(me.__editorList[i + 1]);
                    edit.focus();
                    edit.setValue(edit.getValue());
                }
            }
        }
    },
    onEditSalePriceSpecialKey: function (field, e) {
        if (e.getKey() == e.ENTER) {
            var f = Ext.getCmp("editForm");
            if (f.getForm().isValid()) {
                var me = this;
                me.onOK(me.adding);
            }
        }
    },
    clearEdit: function () {
        Ext.getCmp("editCode").focus();

        var editors = [Ext.getCmp("editCode"), Ext.getCmp("editName"), Ext.getCmp("editSpec"),
            Ext.getCmp("editSalePrice")];
        for (var i = 0; i < editors.length; i++) {
            var edit = editors[i];
            edit.setValue(null);
            edit.clearInvalid();
        }
    },
    onWndClose: function () {
        var me = this;
        me.getParentForm().__lastId = me.__lastId;
        me.getParentForm().freshGoodsGrid();
    }
});
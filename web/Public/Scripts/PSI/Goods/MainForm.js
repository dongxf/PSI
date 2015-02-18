Ext.define("PSI.Goods.MainForm", {
    extend: "Ext.panel.Panel",
    initComponent: function () {
        var me = this;

        Ext.define("PSIGoodsCategory", {
            extend: "Ext.data.Model",
            fields: ["id", "code", "name"]
        });

        var categoryGrid = Ext.create("Ext.grid.Panel", {
            title: "商品分类",
            forceFit: true,
            columnLines: true,
            columns: [
                {header: "编码", dataIndex: "code", flex: 1, menuDisabled: true, sortable: false},
                {header: "类别", dataIndex: "name", flex: 1, menuDisabled: true, sortable: false}
            ],
            store: Ext.create("Ext.data.Store", {
                model: "PSIGoodsCategory",
                autoLoad: false,
                data: []
            }),
            listeners: {
                select: {
                    fn: me.onCategoryGridSelect,
                    scope: me
                },
                itemdblclick: {
                    fn: me.onEditCategory,
                    scope: me
                }
            }
        });
        me.categoryGrid = categoryGrid;

        Ext.define("PSIGoods", {
            extend: "Ext.data.Model",
            fields: ["id", "code", "name", "spec", "unitId", "unitName", "categoryId", "salePrice"]
        });

        var store = Ext.create("Ext.data.Store", {
            autoLoad: false,
            model: "PSIGoods",
            data: [],
            pageSize: 20,
            proxy: {
                type: "ajax",
                actionMethods: {
                    read: "POST"
                },
                url: PSI.Const.BASE_URL + "Home/Goods/goodsList",
                reader: {
                    root: 'goodsList',
                    totalProperty: 'totalCount'
                }
            }
        });

        store.on("beforeload", function () {
            var item = me.categoryGrid.getSelectionModel().getSelection();
            var categoryId;
            if (item == null || item.length != 1) {
                categoryId = null;
            }

            categoryId = item[0].get("id");

            Ext.apply(store.proxy.extraParams, {
                categoryId: categoryId
            });
        });
        store.on("load", function (e, records, successful) {
            if (successful) {
                me.gotoGoodsGridRecord(me.__lastId);
            }
        });

        var goodsGrid = Ext.create("Ext.grid.Panel", {
            viewConfig: {
                enableTextSelection: true
            },
            title: "商品列表",
            bbar: [{
                    id: "pagingToolbar",
                    border: 0,
                    xtype: "pagingtoolbar",
                    store: store
                }, "-", {
                    xtype: "displayfield",
                    value: "每页显示"
                }, {
                    id: "comboCountPerPage",
                    xtype: "combobox",
                    editable: false,
                    width: 60,
                    store: Ext.create("Ext.data.ArrayStore", {
                        fields: ["text"],
                        data: [["20"], ["50"], ["100"], ["300"], ["1000"]]
                    }),
                    value: 20,
                    listeners: {
                        change: {
                            fn: function () {
                                store.pageSize = Ext.getCmp("comboCountPerPage").getValue();
                                store.currentPage = 1;
                                Ext.getCmp("pagingToolbar").doRefresh();
                            },
                            scope: me
                        }
                    }
                }, {
                    xtype: "displayfield",
                    value: "条记录"
                }],
            columnLines: true,
            columns: [
                Ext.create("Ext.grid.RowNumberer", {text: "序号", width: 30}),
                {header: "商品编码", dataIndex: "code", menuDisabled: true, sortable: false},
                {header: "品名", dataIndex: "name", menuDisabled: true, sortable: false, width: 300},
                {header: "规格型号", dataIndex: "spec", menuDisabled: true, sortable: false, width: 200},
                {header: "计量单位", dataIndex: "unitName", menuDisabled: true, sortable: false, width: 60},
                {header: "销售价", dataIndex: "salePrice", menuDisabled: true, sortable: false, align: "right", xtype: "numbercolumn"}
            ],
            store: store,
            listeners: {
                itemdblclick: {
                    fn: me.onEditGoods,
                    scope: me
                }
            }
        });

        me.goodsGrid = goodsGrid;

        Ext.apply(me, {
            border: 0,
            layout: "border",
            tbar: [
                {text: "新增商品分类", iconCls: "PSI-button-add", handler: me.onAddCategory, scope: me},
                {text: "编辑商品分类", iconCls: "PSI-button-edit", handler: me.onEditCategory, scope: me},
                {text: "删除商品分类", iconCls: "PSI-button-delete", handler: me.onDeleteCategory, scope: me}, "-",
                {text: "新增商品", iconCls: "PSI-button-add-detail", handler: me.onAddGoods, scope: me},
                {text: "修改商品", iconCls: "PSI-button-edit-detail", handler: me.onEditGoods, scope: me},
                {text: "删除商品", iconCls: "PSI-button-delete-detail", handler: me.onDeleteGoods, scope: me}, "-",
                {
                    text: "帮助",
                    iconCls: "PSI-help",
                    handler: function() {
                        window.open("http://my.oschina.net/u/134395/blog/374778");
                    }
                },
                "-",
                {
                    text: "关闭", iconCls: "PSI-button-exit", handler: function () {
                        location.replace(PSI.Const.BASE_URL);
                    }
                }
            ],
            items: [
                {
                    region: "center", xtype: "panel", layout: "fit", border: 0,
                    items: [goodsGrid]
                },
                {
                    xtype: "panel",
                    region: "west",
                    layout: "fit",
                    width: 300,
                    minWidth: 200,
                    maxWidth: 350,
                    split: true,
                    border: 0,
                    items: [categoryGrid]
                }
            ]
        });

        me.callParent(arguments);

        me.freshCategoryGrid(null, true);
    },
    onAddCategory: function () {
        var form = Ext.create("PSI.Goods.CategoryEditForm", {
            parentForm: this
        });

        form.show();
    },
    onEditCategory: function () {
        var item = this.categoryGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要编辑的商品分类");
            return;
        }

        var category = item[0];

        var form = Ext.create("PSI.Goods.CategoryEditForm", {
            parentForm: this,
            entity: category
        });

        form.show();
    },
    onDeleteCategory: function () {
        var me = this;
        var item = me.categoryGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要删除的商品分类");
            return;
        }

        var category = item[0];

        var store = me.categoryGrid.getStore();
        var index = store.findExact("id", category.get("id"));
        index--;
        var preIndex = null;
        var preItem = store.getAt(index);
        if (preItem) {
            preIndex = preItem.get("id");
        }

        var info = "请确认是否删除商品分类: <span style='color:red'>" + category.get("name") + "</span>";
        var me = this;
        PSI.MsgBox.confirm(info, function () {
            var el = Ext.getBody();
            el.mask("正在删除中...");
            Ext.Ajax.request({
                url: PSI.Const.BASE_URL + "Home/Goods/deleteCategory",
                method: "POST",
                params: {id: category.get("id")},
                callback: function (options, success, response) {
                    el.unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.tip("成功完成删除操作")
                            me.freshCategoryGrid(preIndex);
                        } else {
                            PSI.MsgBox.showInfo(data.msg);
                        }
                    } else {
                        PSI.MsgBox.showInfo("网络错误", function () {
                            window.location.reload();
                        });
                    }
                }

            });
        });
    },
    freshCategoryGrid: function (id) {
        var grid = this.categoryGrid;
        var el = grid.getEl() || Ext.getBody();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Goods/allCategories",
            method: "POST",
            callback: function (options, success, response) {
                var store = grid.getStore();

                store.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    store.add(data);

                    if (id) {
                        var r = store.findExact("id", id);
                        if (r != -1) {
                            grid.getSelectionModel().select(r);
                        }
                    } else {
                        grid.getSelectionModel().select(0);
                    }
                }

                el.unmask();
            }
        });
    },
    freshGoodsGrid: function () {
        var me = this;
        var item = me.categoryGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            var grid = me.goodsGrid;
            grid.setTitle("商品列表");
            return;
        }

        Ext.getCmp("pagingToolbar").doRefresh()
    },
    // private
    onCategoryGridSelect: function () {
        var me = this;
        me.goodsGrid.getStore().currentPage = 1;
        
        me.freshGoodsGrid();
    },
    onAddGoods: function () {
        if (this.categoryGrid.getStore().getCount() == 0) {
            PSI.MsgBox.showInfo("没有商品分类，请先新增商品分类");
            return;
        }

        var form = Ext.create("PSI.Goods.GoodsEditForm", {
            parentForm: this
        });

        form.show();
    },
    onEditGoods: function () {
        var item = this.categoryGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择商品分类");
            return;
        }

        var category = item[0];

        var item = this.goodsGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要编辑的商品");
            return;
        }

        var goods = item[0];
        goods.set("categoryId", category.get("id"));
        var form = Ext.create("PSI.Goods.GoodsEditForm", {
            parentForm: this,
            entity: goods
        });

        form.show();
    },
    onDeleteGoods: function () {
        var me = this;
        var item = me.goodsGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要删除的商品");
            return;
        }

        var goods = item[0];

        var store = me.goodsGrid.getStore();
        var index = store.findExact("id", goods.get("id"));
        index--;
        var preItem = store.getAt(index);
        if (preItem) {
            me.__lastId = preItem.get("id");
        }


        var info = "请确认是否删除商品: <span style='color:red'>" + goods.get("name")
                + " " + goods.get("spec") + "</span>";

        PSI.MsgBox.confirm(info, function () {
            var el = Ext.getBody();
            el.mask("正在删除中...");
            Ext.Ajax.request({
                url: PSI.Const.BASE_URL + "Home/Goods/deleteGoods",
                method: "POST",
                params: {id: goods.get("id")},
                callback: function (options, success, response) {
                    el.unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.tip("成功完成删除操作");
                            me.freshGoodsGrid();
                        } else {
                            PSI.MsgBox.showInfo(data.msg);
                        }
                    } else {
                        PSI.MsgBox.showInfo("网络错误", function () {
                            window.location.reload();
                        });
                    }
                }

            });
        });
    },
    gotoCategoryGridRecord: function (id) {
        var me = this;
        var grid = me.categoryGrid;
        var store = grid.getStore();
        if (id) {
            var r = store.findExact("id", id);
            if (r != -1) {
                grid.getSelectionModel().select(r);
            } else {
                grid.getSelectionModel().select(0);
            }
        }
    },
    gotoGoodsGridRecord: function (id) {
        var me = this;
        var grid = me.goodsGrid;
        var store = grid.getStore();
        if (id) {
            var r = store.findExact("id", id);
            if (r != -1) {
                grid.getSelectionModel().select(r);
            } else {
                grid.getSelectionModel().select(0);
            }
        }
    }
});
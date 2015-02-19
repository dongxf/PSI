Ext.define("PSI.Customer.MainForm", {
    extend: "Ext.panel.Panel",
    border: 0,
    layout: "border",
    initComponent: function () {
        var me = this;
        Ext.define("PSICustomerCategory", {
            extend: "Ext.data.Model",
            fields: ["id", "code", "name", {name: "cnt", type: "int"}]
        });

        var categoryGrid = Ext.create("Ext.grid.Panel", {
            title: "客户分类",
            features: [{ftype: "summary"}],
            forceFit: true,
            columnLines: true,
            columns: [
                {header: "类别编码", dataIndex: "code", width: 60, menuDisabled: true, sortable: false},
                {header: "类别", dataIndex: "name", flex: 1, menuDisabled: true, sortable: false,
                    summaryRenderer: function () {
                        return "客户个数合计";
                    }},
                {header: "客户个数", dataIndex: "cnt", width: 80, menuDisabled: true, sortable: false,
                    summaryType: "sum"}
            ],
            store: Ext.create("Ext.data.Store", {
                model: "PSICustomerCategory",
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

        Ext.define("PSICustomer", {
            extend: "Ext.data.Model",
            fields: ["id", "code", "name", "contact01", "tel01", "mobile01", "qq01",
                "contact02", "tel02", "mobile02", "qq02", "categoryId", "initReceivables",
                "initReceivablesDT"]
        });

        var store = Ext.create("Ext.data.Store", {
            autoLoad: false,
            model: "PSICustomer",
            data: [],
            pageSize: 20,
            proxy: {
                type: "ajax",
                actionMethods: {
                    read: "POST"
                },
                url: PSI.Const.BASE_URL + "Home/Customer/customerList",
                reader: {
                    root: 'customerList',
                    totalProperty: 'totalCount'
                }
            },
            listeners: {
                beforeload: {
                    fn: function () {
                        var item = me.categoryGrid.getSelectionModel().getSelection();
                        var categoryId;
                        if (item == null || item.length != 1) {
                            categoryId = null;
                        }

                        categoryId = item[0].get("id");

                        Ext.apply(store.proxy.extraParams, {
                            categoryId: categoryId
                        });
                    },
                    scope: me
                },
                load: {
                    fn: function (e, records, successful) {
                        if (successful) {
                            me.refreshCategoryCount();
                            me.gotoCustomerGridRecord(me.__lastId);
                        }
                    },
                    scope: me
                }
            }
        });

        var customerGrid = Ext.create("Ext.grid.Panel", {
            viewConfig: {
                enableTextSelection: true
            },
            title: "客户列表",
            columnLines: true,
            columns: [
                Ext.create("Ext.grid.RowNumberer", {text: "序号", width: 30}),
                {header: "客户编码", dataIndex: "code", menuDisabled: true, sortable: false},
                {header: "客户名称", dataIndex: "name", menuDisabled: true, sortable: false, width: 300},
                {header: "联系人", dataIndex: "contact01", menuDisabled: true, sortable: false},
                {header: "手机", dataIndex: "mobile01", menuDisabled: true, sortable: false},
                {header: "固话", dataIndex: "tel01", menuDisabled: true, sortable: false},
                {header: "QQ", dataIndex: "qq01", menuDisabled: true, sortable: false},
                {header: "备用联系人", dataIndex: "contact02", menuDisabled: true, sortable: false},
                {header: "备用联系人手机", dataIndex: "mobile02", menuDisabled: true, sortable: false},
                {header: "备用联系人固话", dataIndex: "tel02", menuDisabled: true, sortable: false},
                {header: "备用联系人QQ", dataIndex: "qq02", menuDisabled: true, sortable: false},
                {header: "应收期初余额", dataIndex: "initReceivables", align: "right", xtype: "numbercolumn", menuDisabled: true, sortable: false},
                {header: "应收期初余额日期", dataIndex: "initReceivablesDT", menuDisabled: true, sortable: false}
            ],
            store: store,
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
            listeners: {
                itemdblclick: {
                    fn: me.onEditCustomer,
                    scope: me
                }
            }
        });

        me.customerGrid = customerGrid;

        Ext.apply(me, {
            tbar: [
                {text: "新增客户分类", iconCls: "PSI-button-add", handler: me.onAddCategory, scope: me},
                {text: "编辑客户分类", iconCls: "PSI-button-edit", handler: me.onEditCategory, scope: me},
                {text: "删除客户分类", iconCls: "PSI-button-delete", handler: me.onDeleteCategory, scope: me}, "-",
                {text: "新增客户", iconCls: "PSI-button-add-detail", handler: me.onAddCustomer, scope: me},
                {text: "修改客户", iconCls: "PSI-button-edit-detail", handler: me.onEditCustomer, scope: me},
                {text: "删除客户", iconCls: "PSI-button-delete-detail", handler: me.onDeleteCustomer, scope: me}, "-",
                {
                    text: "帮助",
                    iconCls: "PSI-help",
                    handler: function () {
                        window.open("http://my.oschina.net/u/134395/blog/374871");
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
                    items: [customerGrid]
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

        me.freshCategoryGrid();
    },
    onAddCategory: function () {
        var form = Ext.create("PSI.Customer.CategoryEditForm", {
            parentForm: this
        });

        form.show();
    },
    onEditCategory: function () {
        var item = this.categoryGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要编辑的客户分类");
            return;
        }

        var category = item[0];

        var form = Ext.create("PSI.Customer.CategoryEditForm", {
            parentForm: this,
            entity: category
        });

        form.show();
    },
    onDeleteCategory: function () {
        var me = this;
        var item = me.categoryGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要删除的客户分类");
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


        var info = "请确认是否删除客户分类: <span style='color:red'>" + category.get("name") + "</span>";
        var me = this;
        PSI.MsgBox.confirm(info, function () {
            var el = Ext.getBody();
            el.mask("正在删除中...");
            Ext.Ajax.request({
                url: PSI.Const.BASE_URL + "Home/Customer/deleteCategory",
                method: "POST",
                params: {id: category.get("id")},
                callback: function (options, success, response) {
                    el.unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.tip("成功完成删除操作");
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
            url: PSI.Const.BASE_URL + "Home/Customer/categoryList",
            method: "POST",
            callback: function (options, success, response) {
                var store = grid.getStore();

                store.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    store.add(data);

                    if (store.getCount() > 0) {
                        if (id) {
                            var r = store.findExact("id", id);
                            if (r != -1) {
                                grid.getSelectionModel().select(r);
                            }
                        } else {
                            grid.getSelectionModel().select(0);
                        }
                    }
                }

                el.unmask();
            }
        });
    },
    freshCustomerGrid: function (id) {
        var item = this.categoryGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            var grid = this.customerGrid;
            grid.setTitle("客户列表");
            return;
        }

        var category = item[0];

        var grid = this.customerGrid;
        grid.setTitle("属于分类 [" + category.get("name") + "] 的客户");

        this.__lastId = id;
        Ext.getCmp("pagingToolbar").doRefresh()
    },
    // private
    onCategoryGridSelect: function () {
        this.freshCustomerGrid();
    },
    onAddCustomer: function () {
        if (this.categoryGrid.getStore().getCount() == 0) {
            PSI.MsgBox.showInfo("没有客户分类，请先新增客户分类");
            return;
        }

        var form = Ext.create("PSI.Customer.CustomerEditForm", {
            parentForm: this
        });

        form.show();
    },
    onEditCustomer: function () {
        var item = this.categoryGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("没有选择客户分类");
            return;
        }
        var category = item[0];

        var item = this.customerGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要编辑的客户");
            return;
        }

        var customer = item[0];
        customer.set("categoryId", category.get("id"));
        var form = Ext.create("PSI.Customer.CustomerEditForm", {
            parentForm: this,
            entity: customer
        });

        form.show();
    },
    onDeleteCustomer: function () {
        var me = this;
        var item = me.customerGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要删除的客户");
            return;
        }

        var customer = item[0];

        var store = me.customerGrid.getStore();
        var index = store.findExact("id", customer.get("id"));
        index--;
        var preIndex = null;
        var preItem = store.getAt(index);
        if (preItem) {
            preIndex = preItem.get("id");
        }


        var info = "请确认是否删除客户: <span style='color:red'>" + customer.get("name") + "</span>";
        var me = this;
        PSI.MsgBox.confirm(info, function () {
            var el = Ext.getBody();
            el.mask("正在删除中...");
            Ext.Ajax.request({
                url: PSI.Const.BASE_URL + "Home/Customer/deleteCustomer",
                method: "POST",
                params: {id: customer.get("id")},
                callback: function (options, success, response) {
                    el.unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.tip("成功完成删除操作");
                            me.freshCustomerGrid(preIndex);
                        } else {
                            PSI.MsgBox.showInfo(data.msg);
                        }
                    }
                }

            });
        });
    },
    gotoCustomerGridRecord: function (id) {
        var me = this;
        var grid = me.customerGrid;
        var store = grid.getStore();
        if (id) {
            var r = store.findExact("id", id);
            if (r != -1) {
                grid.getSelectionModel().select(r);
            } else {
                grid.getSelectionModel().select(0);
            }
        } else {
            grid.getSelectionModel().select(0);
        }
    },
    refreshCategoryCount: function() {
        var me = this;
        var item = me.categoryGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            return;
        }

        var category = item[0];
        category.set("cnt", me.customerGrid.getStore().getTotalCount());
        me.categoryGrid.getStore().commitChanges();
    }

});
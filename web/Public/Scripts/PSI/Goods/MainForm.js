// 商品 - 主界面
Ext.define("PSI.Goods.MainForm", {
    extend: "Ext.panel.Panel",
    
    initComponent: function () {
        var me = this;

        var modelName = "PSIGoods";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["id", "code", "name", "spec", "unitId", "unitName", "categoryId", "salePrice",
                     "purchasePrice", "barCode", "memo"]
        });

        var store = Ext.create("Ext.data.Store", {
            autoLoad: false,
            model: modelName,
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
            store.proxy.extraParams = me.getQueryParam();
        });
        store.on("load", function (e, records, successful) {
            if (successful) {
                me.refreshCategoryCount();
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
                {header: "销售价", dataIndex: "salePrice", menuDisabled: true, sortable: false, align: "right", xtype: "numbercolumn"},
                {header: "建议采购价", dataIndex: "purchasePrice", menuDisabled: true, sortable: false, align: "right", xtype: "numbercolumn"},
                {header: "条形码", dataIndex: "barCode", menuDisabled: true, sortable: false},
                {header: "备注", dataIndex: "memo", menuDisabled: true, sortable: false, width: 300}
            ],
            store: store,
            listeners: {
                itemdblclick: {
                    fn: me.onEditGoods,
                    scope: me
                },
                select: {
                	fn: me.onGoodsSelect,
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
                {text: "导入商品", iconCls: "PSI-button-add", handler: me.onImportGoods, scope: me}, "-",
                {text: "修改商品", iconCls: "PSI-button-edit-detail", handler: me.onEditGoods, scope: me},
                {text: "删除商品", iconCls: "PSI-button-delete-detail", handler: me.onDeleteGoods, scope: me}, "-",
                {text: "设置商品安全库存", iconCls: "PSI-button-view", handler: me.onSafetyInventory, scope: me}, "-",
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
            items: [{
                    	region: "north",
                    	border: 0,
                    	height: 60,
                    	title: "查询条件",
                    	collapsible: true,
                    	layout : {
        					type : "table",
        					columns : 5
        				},
                    	items: [{
                    		id: "editQueryCode",
        					labelWidth : 60,
        					labelAlign : "right",
        					labelSeparator : "",
        					fieldLabel : "商品编码",
        					margin: "5, 0, 0, 0",
        					xtype : "textfield",
        					listeners: {
                                specialkey: {
                                    fn: me.onQueryEditSpecialKey,
                                    scope: me
                                }
                            }
        				},{
        					id: "editQueryName",
        					labelWidth : 60,
        					labelAlign : "right",
        					labelSeparator : "",
        					fieldLabel : "品名",
        					margin: "5, 0, 0, 0",
        					xtype : "textfield",
        					listeners: {
                                specialkey: {
                                    fn: me.onQueryEditSpecialKey,
                                    scope: me
                                }
                            }
        				},{
        					id: "editQuerySpec",
        					labelWidth : 60,
        					labelAlign : "right",
        					labelSeparator : "",
        					fieldLabel : "规格型号",
        					margin: "5, 0, 0, 0",
        					xtype : "textfield",
        					listeners: {
                                specialkey: {
                                    fn: me.onQueryEditSpecialKey,
                                    scope: me
                                }
                            }
        				},{
        					id: "editQueryBarCode",
        					labelWidth : 60,
        					labelAlign : "right",
        					labelSeparator : "",
        					fieldLabel : "条形码",
        					margin: "5, 0, 0, 0",
        					xtype : "textfield",
        					listeners: {
                                specialkey: {
                                    fn: me.onLastQueryEditSpecialKey,
                                    scope: me
                                }
                            }
        				}, {
        					xtype: "container",
        					items: [{
        						xtype: "button",
        						text: "查询",
        						width: 100,
        						iconCls: "PSI-button-refresh",
        						margin: "5, 0, 0, 20",
        						handler: me.onQuery,
        						scope: me
        					},{
        						xtype: "button",
        						text: "清空查询条件",
        						width: 100,
        						iconCls: "PSI-button-cancel",
        						margin: "5, 0, 0, 5",
        						handler: me.onClearQuery,
        						scope: me
        					}]
        				}]
                    },{
                    	region: "center", layout: "border",
                    	items: [{
                            region: "center", xtype: "panel", layout: "border", border: 0,
                            items: [{
                            	region: "center", layout: "fit", border: 0,
                            	items: [goodsGrid]
                            }, {
                            	region: "south", layout: "fit", border: 0,
                            	height: 200, split: true,
                            	items: [me.getSIGrid()]
                            }]
                        }, {
                            xtype: "panel",
                            region: "west",
                            layout: "fit",
                            width: 300,
                            minWidth: 200,
                            maxWidth: 350,
                            split: true,
                            border: 0,
                            items: [me.getCategoryGrid()]
                        }]
                    }]
        });

        me.callParent(arguments);
        
        me.__queryEditNameList = ["editQueryCode", "editQueryName", "editQuerySpec", "editQueryBarCode"];

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
    	var me = this;
        var grid = me.getCategoryGrid();
        var el = grid.getEl() || Ext.getBody();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Goods/allCategories",
            method: "POST",
            params: me.getQueryParam(),
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
        var item = me.getCategoryGrid().getSelectionModel().getSelection();
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
        me.getSIGrid().setTitle("商品安全库存");
        me.getSIGrid().getStore().removeAll();
        
        me.goodsGrid.getStore().currentPage = 1;
        
        me.freshGoodsGrid();
    },
    onAddGoods: function () {
        if (this.getCategoryGrid().getStore().getCount() == 0) {
            PSI.MsgBox.showInfo("没有商品分类，请先新增商品分类");
            return;
        }

        var form = Ext.create("PSI.Goods.GoodsEditForm", {
            parentForm: this
        });

        form.show();
    },
    onEditGoods: function () {
        var item = this.getCategoryGrid().getSelectionModel().getSelection();
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
        var grid = me.getCategoryGrid();
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
    },
    refreshCategoryCount: function() {
        var me = this;
        var item = me.getCategoryGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            return;
        }

//        var category = item[0];
//        category.set("cnt", me.goodsGrid.getStore().getTotalCount());
//        me.categoryGrid.getStore().commitChanges();
    },
    
    onQueryEditSpecialKey: function (field, e) {
        if (e.getKey() === e.ENTER) {
            var me = this;
            var id = field.getId();
            for (var i = 0; i < me.__queryEditNameList.length - 1; i++) {
                var editorId = me.__queryEditNameList[i];
                if (id === editorId) {
                    var edit = Ext.getCmp(me.__queryEditNameList[i + 1]);
                    edit.focus();
                    edit.setValue(edit.getValue());
                }
            }
        }
    },
    
    onLastQueryEditSpecialKey: function (field, e) {
        if (e.getKey() === e.ENTER) {
        	this.onQuery();
        }
    },

    getQueryParam: function() {
    	var me = this;
        var item = me.getCategoryGrid().getSelectionModel().getSelection();
        var categoryId;
        if (item == null || item.length != 1) {
            categoryId = null;
        } else {
        	categoryId = item[0].get("id");	
        }

        var result = {
        	categoryId: categoryId
        };
        
        var code = Ext.getCmp("editQueryCode").getValue();
        if (code) {
        	result.code = code;
        }
        
        var name = Ext.getCmp("editQueryName").getValue();
        if (name) {
        	result.name = name;
        }
        
        var spec = Ext.getCmp("editQuerySpec").getValue();
        if (spec) {
        	result.spec = spec;
        }
        
        var barCode = Ext.getCmp("editQueryBarCode").getValue();
        if (barCode) {
        	result.barCode = barCode;
        }
        
        return result;
    },

    onQuery: function() {
    	this.freshCategoryGrid();
    },
    
    onClearQuery: function() {
    	var nameList = this.__queryEditNameList;
    	for (var i = 0; i < nameList.length; i++) {
    		var name = nameList[i];
    		var edit = Ext.getCmp(name);
    		if (edit) {
    			edit.setValue(null);
    		}
    	}
    	
    	this.onQuery();
    },
    
    getSIGrid: function() {
    	var me = this;
    	if (me.__siGrid) {
    		return me.__siGrid;
    	}

    	var modelName = "PSIGoodsSafetyInventory";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["id", "warehouseCode", "warehouseName", "safetyInventory", "inventoryCount", "unitName",
                     "inventoryUpper"]
        });

        me.__siGrid = Ext.create("Ext.grid.Panel", {
            viewConfig: {
                enableTextSelection: true
            },
            title: "商品安全库存",
            columnLines: true,
            columns: [
                {header: "仓库编码", dataIndex: "warehouseCode", width: 80, menuDisabled: true, sortable: false},
                {header: "仓库名称", dataIndex: "warehouseName", width: 100, menuDisabled: true, sortable: false},
                {header: "库存上限", dataIndex: "inventoryUpper", width: 120, menuDisabled: true, 
                	sortable: false, align: "right", xtype: "numbercolumn",
    				format: "0"},
                {header: "安全库存量", dataIndex: "safetyInventory", width: 120, menuDisabled: true, 
                	sortable: false, align: "right", xtype: "numbercolumn",
    				format: "0"},
                {header: "当前库存", dataIndex: "inventoryCount", width: 120, 
                		menuDisabled: true, sortable: false, align: "right", xtype: "numbercolumn",
        				format: "0"},
                {header: "计量单位", dataIndex: "unitName", width: 80, menuDisabled: true, sortable: false}
            ],
            store: Ext.create("Ext.data.Store", {
                model: modelName,
                autoLoad: false,
                data: []
            }),
            listeners: {
            	itemdblclick: {
            		fn: me.onSafetyInventory,
            		scope: me
            	}
            }
        });

        return me.__siGrid;
    },
    
    onGoodsSelect: function() {
        var me = this;
        var item = me.goodsGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
        	me.getSIGrid().setTitle("商品安全库存");
            return;
        }

        var goods = item[0];
        var info = goods.get("code") + " " + goods.get("name") + " " + goods.get("spec");

        var grid = me.getSIGrid();
    	grid.setTitle("商品[" + info + "]的安全库存");
    	
        var el = grid.getEl() || Ext.getBody();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Goods/goodsSafetyInventoryList",
            method: "POST",
            params: {
            	id: goods.get("id")
            },
            callback: function (options, success, response) {
                var store = grid.getStore();

                store.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    store.add(data);
                }

                el.unmask();
            }
        });
    },
    
    onSafetyInventory: function() {
        var me = this;
        var item = me.goodsGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
        	PSI.MsgBox.showInfo("请选择要设置安全库存的商品");
            return;
        }

        var goods = item[0];
    	
        var form = Ext.create("PSI.Goods.SafetyInventoryEditForm", {
        	parentForm: me,
        	entity: goods
        });
        
        form.show();
    },

    onImportGoods: function () {
        var form = Ext.create("PSI.Goods.GoodsImportForm", {
            parentForm: this
        });

        form.show();
    },
    
    getCategoryGrid: function() {
    	var me = this;
    	if (me.__categoryGrid) {
    		return me.__categoryGrid;
    	}

    	var modelName = "PSIGoodsCategory";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["id", "text", "fullName", "code", "leaf", "children"]
        });

        var store = Ext.create("Ext.data.TreeStore", {
            model: modelName,
            proxy: {
                type: "ajax",
                actionMethods: {
                    read: "POST"
                },
                url: PSI.Const.BASE_URL + "Home/Goods/allCategories"
            }
        });

        store.on("load", me.onCategoryStoreLoad, me);

        me.__categoryGrid = Ext.create("Ext.tree.Panel", {
            title: "商品分类",
            store: store,
            rootVisible: false,
            useArrows: true,
            viewConfig: {
                loadMask: true
            },
            columns: {
                defaults: {
                    sortable: false,
                    menuDisabled: true,
                    draggable: false
                },
                items: [{
                        xtype: "treecolumn",
                        text: "分类",
                        dataIndex: "text",
                        width: 220
                    }, {
                        text: "编码",
                        dataIndex: "code",
                        width: 100
                    }]
            },
            listeners: {
            	select: {
            		fn: function (rowModel, record) {
                        me.onCategoryTreeNodeSelect(record);
                    },
                    scope: me
            	}
            }
        });
        
        me.categoryGrid = me.__categoryGrid;
        
        return me.__categoryGrid;
    },
    
    onCategoryStoreLoad: function () {
    	var me = this;
        var tree = me.getCategoryGrid();
        var root = tree.getRootNode();
        if (root) {
            var node = root.firstChild;
            if (node) {
                //me.onOrgTreeNodeSelect(node);
            }
        }
    },
    
    onCategoryTreeNodeSelect: function(record) {
    	if (!record) {
    		return;
    	}
    	
    	this.onCategoryGridSelect();
    }
});
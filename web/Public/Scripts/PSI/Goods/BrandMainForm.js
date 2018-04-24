/**
 * 商品品牌 - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.Goods.BrandMainForm", {
			extend : "PSI.AFX.BaseOneGridMainForm",

			/**
			 * 重载父类方法
			 */
			afxGetToolbarCmp : function() {
				var me = this;
				return [{
							text : "新增品牌",
							handler : me.onAddBrand,
							scope : me
						}, {
							text : "编辑品牌",
							handler : me.onEditBrand,
							scope : me
						}, {
							text : "删除品牌",
							handler : me.onDeleteBrand,
							scope : me
						}, "-", {
							text : "刷新",
							iconCls : "PSI-button-refresh",
							handler : me.onRefreshGrid,
							scope : me
						}, "-", {
							text : "帮助",
							iconCls : "PSI-help",
							handler : function() {
								window.open(me
										.URL("/Home/Help/index?t=goodsBrand"));
							}
						}, "-", {
							text : "关闭",
							iconCls : "PSI-button-exit",
							handler : function() {
								me.closeWindow();
							}
						}];
			},

			/**
			 * 重载父类方法
			 */
			afxGetMainGrid : function() {
				var me = this;
				if (me.__mainGrid) {
					return me.__mainGrid;
				}

				var modelName = "PSIGoodsBrand";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "text", "fullName", "leaf",
									"children"]
						});

				var store = Ext.create("Ext.data.TreeStore", {
							model : modelName,
							proxy : {
								type : "ajax",
								actionMethods : {
									read : "POST"
								},
								url : me.URL("Home/Goods/allBrands")
							}
						});

				me.__mainGrid = Ext.create("Ext.tree.Panel", {
							cls : "PSI",
							border : 0,
							store : store,
							rootVisible : false,
							useArrows : true,
							viewConfig : {
								loadMask : true
							},
							columns : {
								defaults : {
									sortable : false,
									menuDisabled : true,
									draggable : false
								},
								items : [{
											xtype : "treecolumn",
											text : "品牌",
											dataIndex : "text",
											flex : 1
										}, {
											text : "全名",
											dataIndex : "fullName",
											flex : 2
										}]
							}
						});

				return me.__mainGrid;
			},

			/**
			 * 重载父类方法
			 */
			afxRefreshGrid : function(id) {
				var me = this;
				var store = me.getMainGrid().getStore();
				store.load();
			},

			/**
			 * 新增商品品牌
			 */
			onAddBrand : function() {
				var me = this;
				var form = Ext.create("PSI.Goods.BrandEditForm", {
							parentForm : me
						});
				form.show();
			},

			/**
			 * 编辑商品品牌
			 */
			onEditBrand : function() {
				var me = this;
				var item = me.getMainGrid().getSelectionModel().getSelection();
				if (item == null || item.length != 1) {
					PSI.MsgBox.showInfo("请选择要编辑的商品品牌");
					return;
				}

				var brand = item[0];

				var form = Ext.create("PSI.Goods.BrandEditForm", {
							parentForm : me,
							entity : brand
						});

				form.show();
			},

			/**
			 * 删除商品品牌
			 */
			onDeleteBrand : function() {
				var me = this;
				var item = me.getMainGrid().getSelectionModel().getSelection();
				if (item == null || item.length != 1) {
					PSI.MsgBox.showInfo("请选择要删除的商品品牌");
					return;
				}

				var brand = item[0];
				var info = "请确认是否删除商品品牌: <span style='color:red'>"
						+ brand.get("text") + "</span>";
				var confimFunc = function() {
					var el = Ext.getBody();
					el.mask("正在删除中...");
					var r = {
						url : me.URL("Home/Goods/deleteBrand"),
						method : "POST",
						params : {
							id : brand.get("id")
						},
						callback : function(options, success, response) {
							el.unmask();

							if (success) {
								var data = Ext.JSON
										.decode(response.responseText);
								if (data.success) {
									PSI.MsgBox.tip("成功完成删除操作")
									me.refreshGrid();
								} else {
									PSI.MsgBox.showInfo(data.msg);
								}
							} else {
								PSI.MsgBox.showInfo("网络错误", function() {
											window.location.reload();
										});
							}
						}
					};
					Ext.Ajax.request(r);
				};
				PSI.MsgBox.confirm(info, confimFunc);
			},

			onRefreshGrid : function() {
				var me = this;
				me.refreshGrid();
			}
		});
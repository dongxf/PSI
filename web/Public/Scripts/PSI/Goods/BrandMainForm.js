/**
 * 商品品牌 - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.Goods.BrandMainForm", {
			extend : "Ext.panel.Panel",

			/**
			 * 初始化组件
			 */
			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							border : 0,
							layout : "border",
							tbar : [{
										text : "新增品牌",
										iconCls : "PSI-button-add",
										handler : me.onAddBrand,
										scope : me
									}, {
										text : "编辑品牌",
										iconCls : "PSI-button-edit",
										handler : me.onEditBrand,
										scope : me
									}, {
										text : "删除品牌",
										iconCls : "PSI-button-delete",
										handler : me.onDeleteBrand,
										scope : me
									}, "-", {
										text : "关闭",
										iconCls : "PSI-button-exit",
										handler : function() {
											location
													.replace(PSI.Const.BASE_URL);
										}
									}],
							items : [{
										region : "center",
										xtype : "panel",
										layout : "fit",
										border : 0,
										items : [me.getGrid()]
									}]
						});

				me.callParent(arguments);

				me.freshGrid();
			},

			/**
			 * 新增商品品牌
			 */
			onAddBrand : function() {
				PSI.MsgBox.showInfo("TODO");
			},

			/**
			 * 编辑商品品牌
			 */
			onEditBrand : function() {
				PSI.MsgBox.showInfo("TODO");
			},

			/**
			 * 删除商品品牌
			 */
			onDeleteBrand : function() {
				PSI.MsgBox.showInfo("TODO");
			},

			/**
			 * 刷新Grid
			 */
			freshGrid : function(id) {
			},

			getGrid : function() {
				var me = this;
				if (me.__grid) {
					return me.__grid;
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
								url : PSI.Const.BASE_URL
										+ "Home/Goods/allBrands"
							}
						});

				me.__grid = Ext.create("Ext.tree.Panel", {
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
											width : 500
										}]
							}
						});

				return me.__grid;
			}
		});
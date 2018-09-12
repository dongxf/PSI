/**
 * 自定义字段 - 上级科目字段
 */
Ext.define("PSI.Subject.ParentSubjectField", {
			extend : "Ext.form.field.Trigger",
			alias : "widget.PSI_parent_subject_field",

			config : {
				showModal : false
			},

			initComponent : function() {
				var me = this;

				me.enableKeyEvents = true;

				me.callParent(arguments);

				me.on("keydown", function(field, e) {
							if (e.getKey() === e.BACKSPACE) {
								e.preventDefault();
								return false;
							}

							if (e.getKey() !== e.ENTER) {
								me.onTriggerClick(e);
							}
						});

				me.on({
							render : function(p) {
								p.getEl().on("dblclick", function() {
											me.onTriggerClick();
										});
							},
							single : true
						});
			},

			onTriggerClick : function(e) {
				var me = this;

				var modelName = "PSISubjectParentField";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "code", "name", "category", "leaf",
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
										+ "Home/Subject/parentSubjectList"
							}
						});

				var tree = Ext.create("Ext.tree.Panel", {
							cls : "PSI",
							store : store,
							rootVisible : false,
							useArrows : true,
							viewConfig : {
								loadMask : true
							},
							columns : {
								defaults : {
									flex : 1,
									sortable : false,
									menuDisabled : true,
									draggable : false
								},
								items : [{
											xtype : "treecolumn",
											text : "科目码",
											dataIndex : "code",
											width : 200
										}, {
											text : "科目名称",
											dataIndex : "name",
											width : 200
										}, {
											text : "分类",
											dataIndex : "category",
											width : 80,
											renderer : function(value) {
												if (value == 1) {
													return "资产";
												} else if (value == 2) {
													return "负债";
												} else if (value == 4) {
													return "所有者权益";
												} else if (value == 5) {
													return "成本";
												} else if (value == 6) {
													return "损益";
												} else {
													return "";
												}
											}
										}]
							}
						});
				tree.on("itemdblclick", me.onOK, me);
				me.tree = tree;

				var wnd = Ext.create("Ext.window.Window", {
							title : "选择上级组织",
							modal : me.getShowModal(),
							header : false,
							border : 0,
							width : 400,
							height : 300,
							layout : "fit",
							items : tree,
							buttons : [{
										text : "确定",
										handler : this.onOK,
										scope : this
									}, {
										text : "取消",
										handler : function() {
											wnd.close();
										}
									}]
						});
				wnd.on("close", function() {
							me.focus();
						});
				if (!me.getShowModal()) {
					wnd.on("deactivate", function() {
								wnd.close();
							});
				}

				me.wnd = wnd;
				wnd.showBy(me);
			},

			onOK : function() {
				var me = this;
				var tree = me.tree;
				var item = tree.getSelectionModel().getSelection();

				if (item === null || item.length !== 1) {
					PSI.MsgBox.showInfo("没有选择上级科目");

					return;
				}

				var data = item[0].data;
			}
		});
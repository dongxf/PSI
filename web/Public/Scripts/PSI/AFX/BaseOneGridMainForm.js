/**
 * 只有一个Grid的主界面基类
 */
Ext.define("PSI.AFX.BaseOneGridMainForm", {
			extend : "PSI.AFX.BaseMainForm",

			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							items : [{
										region : "center",
										xtype : "panel",
										layout : "fit",
										border : 0,
										items : [me.getMainGrid()]
									}]
						});

				me.callParent(arguments);

				me.freshGrid();
			},

			// public
			getMainGrid : function() {
				var me = this;
				return me.afxGetMainGrid();
			},

			// public
			gotoGridRecord : function(id) {
				var me = this;
				var grid = me.getMainGrid();
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

			// public
			freshGrid : function(id) {
				var me = this;
				var grid = me.getMainGrid();
				var el = grid.getEl() || Ext.getBody();
				el.mask(PSI.Const.LOADING);
				Ext.Ajax.request({
							url : me.URL(me.afxGetRefreshGridURL()),
							method : "POST",
							callback : function(options, success, response) {
								var store = grid.getStore();

								store.removeAll();

								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);
									store.add(data);

									me.gotoGridRecord(id);
								}

								el.unmask();
							}
						});
			},

			// protected
			afxGetMainGrid : function() {
				return null;
			},

			// protected
			afxGetRefreshGridURL : function() {
				return null;
			}
		});
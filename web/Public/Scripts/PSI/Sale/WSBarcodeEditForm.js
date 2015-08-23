Ext.define("PSI.Sale.WSBarcodeEditForm", {
    extend: "Ext.window.Window",
    config: {
        parentForm: null
    },
    
    initComponent: function () {
        var me = this;

        Ext.apply(me, {
            title: "条码录入",
            modal: true,
            onEsc: Ext.emptyFn,
            width: 400,
            height: 110,
            layout: "fit",
            defaultFocus: "editBarCode",
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
                            id: "editBarCode",
                            fieldLabel: "条形码",
                            listeners: {
                                specialkey: {
                                    fn: me.onEditSpecialKey,
                                    scope: me
                                }
                            }
                        }
                    ],
                    buttons: [{
                            	  text: "关闭", handler: function () {
                            		  me.close();
                            	  }, scope: me
                              }]
                }
            ],
            listeners: {
                show: {
                    fn: me.onWndShow,
                    scope: me
                }
            }
        });

        me.callParent(arguments);
    },
    
    onEditSpecialKey: function (field, e) {
    	var me = this;
    	
        if (e.getKey() == e.ENTER) {
        	var barCode = Ext.getCmp("editBarCode").getValue();
        	if (barCode) {
        		me.queryGoodsInfo(barCode);
        	}
        }
    },

    onWndShow: function() {
        var editName = Ext.getCmp("editBarCode");
        editName.focus();
    },
    
    queryGoodsInfo: function(barCode) {
    	var me = this;
    	
        var el = Ext.getBody();
        el.mask("查询中...");
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Goods/queryGoodsInfoByBarcode",
            method: "POST",
            params: {
                barcode: barCode
            },
            callback: function (options, success, response) {
                el.unmask();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    if (data.success) {
                    	var goods = {
                    			goodsId: data.id,
                    			goodsCode: data.code,
                    			goodsName: data.name,
                    			goodsSpec: data.spec,
                    			unitName: data.unitName,
                    			goodsCount: 1,
                    			goodsPrice: data.salePrice,
                    			goodsMoney: data.salePrice
                    	};
                    	me.getParentForm().addGoodsByBarCode(goods);
                    	var edit = Ext.getCmp("editBarCode");
                    	edit.setValue(null);
                    	PSI.MsgBox.tip("成功新增商品");
                    	edit.focus();
                    } else {
                    	var edit = Ext.getCmp("editBarCode");
                    	edit.setValue(null);
                        PSI.MsgBox.showInfo(data.msg);
                    }
                } else {
                    PSI.MsgBox.showInfo("网络错误");
                }
            }

        });
    }
});
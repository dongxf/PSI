var app = new Framework7({
			root : '#app',
			id : 'com.gitee.crm8000.psi',
			name : 'PSI',
			theme : 'auto',
			data : function() {
				return {
					PSI : {
						productionName : productionName,
						demoLoginInfo : demoLoginInfo,
						baseURI : baseURI,
						userIsLoggedIn : userIsLoggedIn
					}
				};
			},
			methods : {},
			routes : routesPSI,
			dialog : {
				buttonOk : "确定",
				buttonCancel : "取消",
				title : productionName
			},
			calendar : {
				monthNames : ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月",
						"九月", "十月", "十一月", "十二月"],
				dayNamesShort : ["日", "一", "二", "三", "四", "五", "六"],
				firstDay : 0,
				dateFormat : "yyyy-mm-dd"
			}
		});

var mainView = app.views.create('.view-main', {
			url : '/'
		});
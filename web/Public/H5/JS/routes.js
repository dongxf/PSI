function toURL(url) {
	return baseURI + "Public/H5/Pages/" + url;
}

// 主页面
routesPSI = [{
			path : '/',
			async : function(routeTo, routeFrom, resolve, reject) {
				if (app.data.PSI.userIsLoggedIn) {
					var url = app.data.PSI.baseURI
							+ "H5/MainMenu/mainMenuItems";

					app.preloader.show();

					app.request.post(url, {}, function(data) {
								app.preloader.hide();
								resolve({
											componentUrl : toURL("home.html")
										}, {
											context : {
												mainMenu : data
											}
										});
							}, function() {
								app.preloader.hide();
								app.dialog.alert("网络错误");
								reject();
							}, "json");

				} else {
					resolve({
								componentUrl : toURL("login.html")
							});

				}
			}
		}];

routesPSI.push({
			path : '/sobillQuery/',
			async : function(routeTo, routeFrom, resolve, reject) {
				if (app.data.PSI.userIsLoggedIn) {
					resolve({
								componentUrl : toURL("Sale/sobillQuery.html")
							});

				} else {
					resolve({
								componentUrl : toURL("login.html")
							});

				}
			}
		});

// 销售订单列表
routesPSI.push({
	path : '/sobillList/',
	async : function(routeTo, routeFrom, resolve, reject) {
		if (app.data.PSI.userIsLoggedIn) {
			var url = app.data.PSI.baseURI + "H5/Sale/sobillList";

			app.preloader.show();

			app.request.post(url, {
						billStatus : routeTo.context.billStatus,
						ref : routeTo.context.ref,
						receivingType : routeTo.context.receivingType,
						fromDT : routeTo.context.fromDT,
						toDT : routeTo.context.toDT,
						customerId : routeTo.context.customerId,
						page : routeTo.context.currentPage
					}, function(data) {
						app.preloader.hide();
						var ctx = routeTo.context;
						ctx.billList = data.dataList;
						ctx.totalPage = parseInt(data.totalPage);

						resolve({
									componentUrl : toURL("Sale/sobillList.html")
								}, {
									context : ctx
								});
					}, function() {
						app.preloader.hide();
						app.dialog.alert("网络错误");
						reject();
					}, "json");

		} else {
			resolve({
						componentUrl : toURL("login.html")
					});

		}
	}
});

// 某个销售订单详情页面
routesPSI.push({
	path : '/sobillDetail/:id',
	async : function(routeTo, routeFrom, resolve, reject) {
		if (app.data.PSI.userIsLoggedIn) {
			var url = app.data.PSI.baseURI + "H5/Sale/sobillDetail";

			app.preloader.show();

			app.request.post(url, {
						id : routeTo.params.id
					}, function(data) {
						app.preloader.hide();
						resolve({
									componentUrl : toURL("Sale/sobillDetail.html")
								}, {
									context : {
										bill : data
									}
								});
					}, function() {
						app.preloader.hide();
						app.dialog.alert("网络错误");
						reject();
					}, "json");

		} else {
			resolve({
						componentUrl : toURL("login.html")
					});

		}
	}
});

// 关于
routesPSI.push({
			path : '/about/',
			url : toURL("about.html")
		});

// Default route (404 page). MUST BE THE LAST
routesPSI.push({
			path : '(.*)',
			url : toURL("404.html")
		});

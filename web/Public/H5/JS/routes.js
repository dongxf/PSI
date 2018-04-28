routesPSI = [{
			path : '/',
			async : function(routeTo, routeFrom, resolve, reject) {
				if (true) {
					resolve({
								url : './Public/H5/Pages/home.html'
							});
				} else {
					resolve({
								url : './Public/H5/Pages/login.html'
							});

				}
			}

		}];

routesPSI.push({
			path : '/about/',
			url : './Public/H5/Pages/about.html'
		});

// Default route (404 page). MUST BE THE LAST
routesPSI.push({
			path : '(.*)',
			url : './Public/H5/Pages/404.html'
		});

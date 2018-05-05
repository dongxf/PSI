function toURL(url) {
	return baseURI + "Public/H5/Pages/" + url;
}

routesPSI = [{
			path : '/',
			async : function(routeTo, routeFrom, resolve, reject) {
				if (userIsLoggedIn) {
					resolve({
								url : toURL("home.html")
							});
				} else {
					resolve({
								componentUrl : toURL("login.html")
							});

				}
			}

		}];

routesPSI.push({
			path : '/about/',
			url : toURL("about.html")
		});

// Default route (404 page). MUST BE THE LAST
routesPSI.push({
			path : '(.*)',
			url : toURL("404.html")
		});

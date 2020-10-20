class url {
	/** opoink official domain website  */
	opoinkBaseUrl = 'https://www.opoink.com';

	/** will hold the query param from url */
	query = {};

	/**
	 * parse the url here so that the param will be available
	 */
	constructor(){
		this.query = {};
		let search = this.getSearch().replace('?', '');
		let queries = search.split("&");

		queries.forEach(q => {
			let _q = q.split("=");
			if(_q.length == 2){
				this.query[_q[0]] = _q[1];
			}
		});

	}

	/**
	 * return the protocal from the current url
	 */
	getProtocol(){
		return window.location.protocol;
	};

	/**
	 * return the host from the current url
	 */
	getHost(){
		return window.location.host;
	};

	/**
	 * return the path name of the current url
	 */
	getPathname(){
		return window.location.pathname;
	};

	/**
	 * return the query param of the current url
	 */
	getSearch(){
		return window.location.search;
	};

	/**
	 * return the route from the current url
	 */
	getRoute(){
		let pathArray = this.getPathname().substring(1).split('/');
		if(typeof pathArray[0] != 'undefined'){
			return pathArray[0].toLowerCase();
		} else {
			return '';
		}
	};

	/**
	 * return the system url
	 * @param {*} path 
	 * @param {*} isFullUrl 
	 */
	getUrl(path="", isFullUrl=false){
		if(isFullUrl){
			return this.getProtocol() + "//" + this.getHost() + '/' + this.getRoute() + path;
		} else {
			return "/" + this.getRoute() + path;
		}
	};

	/**
	 * redirect in the new location
	 * @param {*} location 
	 * @param {*} isFullUrl 
	 */
	redirect(location, isFullUrl=false){
		window.location.href = this.getUrl(location, isFullUrl) ;
	};

	/**
	 * get the current url param
	 * @param {*} param 
	 */
	getParam(param){
		if(typeof this.query[param] != 'undefined'){
			return this.query[param];
		}
	}
}
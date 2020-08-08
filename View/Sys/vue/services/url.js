{
	getProtocol(){
		return window.location.protocol;
	},
	getHost(){
		return window.location.host;
	},
	getPathname(){
		return window.location.pathname;
	},
	getSearch(){
		return window.location.search;
	},
	getRoute(){
		let pathArray = this.getPathname().substring(1).split('/');
		if(typeof pathArray[0] != 'undefined'){
			return pathArray[0];
		} else {
			return '';
		}
	},
	getUrl(path=""){
		return "/" + this.getRoute() + path;
	},
	redirect(location){
		window.location = location;
	}
}
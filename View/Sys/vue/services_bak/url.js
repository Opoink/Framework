{
	opoinkBaseUrl: 'https://www.opoink.com',
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
			return pathArray[0].toLowerCase();
		} else {
			return '';
		}
	},
	getUrl(path="", isFullUrl=false){
		if(isFullUrl){
			return this.getProtocol() + "//" + this.getHost() + '/' + this.getRoute() + path;
		} else {
			return "/" + this.getRoute() + path;
		}
	},
	redirect(location, isFullUrl=false){
		window.location.href = this.getUrl(location, isFullUrl) ;
	}
}
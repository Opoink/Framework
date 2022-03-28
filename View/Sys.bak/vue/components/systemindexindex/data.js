class systemindexindex {
	announcements = null;
	requestParams = {
		page: 1
	};
	init(){
		this.getAnnouncements();
	};
	getAnnouncements(){
		let url = _vue.url.opoinkBaseUrl + '/opoink/rest/api/v1/announcements' + _vue.request.buildQuery(this.requestParams);
		_vue.request.makeRequest(url, '', 'GET')
		.then(a => {
			if(!a.error && a.result){
				this.announcements = a.result.data;
			} else {
				this.announcements = [];
			}
		});
	};
}
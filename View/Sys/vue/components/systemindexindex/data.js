{
	announcements: null,
	init(){
		this.getAnnouncements();
	},
	getAnnouncements(){
		_vue.request.makeRequest(_vue.url.opoinkBaseUrl + '/opoink/rest/api/v1/announcements', '', 'GET')
		.then(a => {
			if(!a.error && a.result){
				this.announcements = a.result;
			}
		})
	}
}
{
	settings: {
		domains: null,
		admin: null,
		system_url: null,
		mode: null,
		cache: null,
		images: null,
	},
	init(){
		_vue.request.makeRequest('/'+_vue.url.getRoute()+'/settings?settings=1', '', 'GET')
		.then(settings => {
			if(!settings.error && settings.result){
				this.setSetting(settings.result);
			}
		});
	},
	setSetting(settings){
		this.settings.domains = settings.domains.join(', ');
		this.settings.admin = settings.admin;
		this.settings.system_url = settings.system_url;
		this.settings.mode = settings.mode;
		this.settings.cache = settings.cache;
		this.settings.images = settings.images.join(', ');
	}
}
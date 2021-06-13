class systemsettingsindex {
	settingFetched = false;
	settings = {
		domains: null,
		admin: null,
		system_url: null,
		mode: null,
		cache: null,
		images: null,
	};
	init(){
		this.getSettings();
	};
	getSettings(){
		_vue.request.makeRequest('/'+_vue.url.getRoute()+'/settings?settings=1', '', 'GET')
		.then(settings => {
			if(!settings.error && settings.result){
				this.setSetting(settings.result);
			}
		});
	};
	setSetting(settings){
		this.settings.domains = settings.domains.join(', ');
		this.settings.admin = settings.admin;
		this.settings.system_url = settings.system_url;
		this.settings.mode = settings.mode;
		this.settings.cache = settings.cache;
		this.settings.images = settings.images.join(', ');
		this.settings.auth = settings.auth;
		this.settings.sys_g_recaptcha = settings.sys_g_recaptcha ? settings.sys_g_recaptcha.status : 0;
		this.settings.g_recaptcha_key = settings.sys_g_recaptcha ? settings.sys_g_recaptcha.key : '';
		this.settings.g_recaptcha_secret = settings.sys_g_recaptcha ? settings.sys_g_recaptcha.secret : '';


		this.settingFetched = true;
	};
	onSubmit(e){
		e.preventDefault();
		_vue.loader.text = 'Fetching form key...';
		_vue.request.getFormKey().then(formkey => {
			if(formkey){
				this.settings['form_key'] = formkey;

				_vue.loader.text = 'Saving settings...';
				_vue.request.makeRequest('/'+_vue.url.getRoute()+'/settings', this.settings, 'POST')
				.then(save => {
					if(!save.error && save.result){
						let sec = 3;
						_vue.loader.isLoading = true;
						setInterval(f => {
							_vue.loader.text = 'Reload in '+sec+' sec...';
							if(sec <= 1){
								let url = _vue.url.getProtocol()+'//'+_vue.url.getHost()+'/system'+save.result.system_url+'/settings';
								window.location.href = url;
							}
							sec--;
						}, 1000);
					}
				});
			}
		});
	};
}
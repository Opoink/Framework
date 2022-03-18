if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class settingsIndexIndex {

	mainHeader;
	loader;
	settingFetched = false;
	settings = {
		domains: null,
		admin: null,
		system_url: null,
		mode: null,
		cache: null,
		images: null,
	};

	request;
	url;

	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];
	}

	init(){
		setTimeout(f => {
			this.mainHeader = window['_vue']['mainheader-component'];
			this.mainHeader.pageTitle = 'Settings';
			this.loader = window['_vue']['loader-component'];

			this.getSettings();
		}, 500);
		
	}

	getSettings(){
		this.request.makeRequest('/'+this.url.getRoute()+'/settings?settings=1', '', 'GET')
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
		this.settings.mailer = settings.mailer;
		if(!settings.mailer){
			this.settings.mailer = {
				use_phpmailer: "0",
				debug: "0",
				host: "",
				auth: "",
				username: "",
				password: "",
				smpt_secure: "",
				port: "",
			}
		}
		this.settings.sys_g_recaptcha = settings.sys_g_recaptcha ? settings.sys_g_recaptcha.status : 0;
		this.settings.g_recaptcha_key = settings.sys_g_recaptcha ? settings.sys_g_recaptcha.key : '';
		this.settings.g_recaptcha_secret = settings.sys_g_recaptcha ? settings.sys_g_recaptcha.secret : '';

		this.settingFetched = true;
	};
	onSubmit(e){
		e.preventDefault();
		this.loader.text = 'Fetching form key...';
		this.request.getFormKey().then(formkey => {
			if(formkey){
				this.settings['form_key'] = formkey;

				this.loader.text = 'Saving settings...';
				this.request.makeRequest('/'+this.url.getRoute()+'/settings', this.settings, 'POST')
				.then(save => {
					if(!save.error && save.result){
						let sec = 3;
						this.loader.isLoading = true;
						let reload = setInterval(f => {
							this.loader.text = 'Reload in '+sec+' sec...';
							if(sec <= 1){
								let url = this.url.getProtocol()+'//'+this.url.getHost()+'/system'+save.result.system_url+'/settings';
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

window['_vue']['settings-index-index-component'] = new settingsIndexIndex();

let settingsIndexIndexComponent = Vue.component('settings-index-index-component', {
	data: function(){
		return {
			vue: window['_vue']['settings-index-index-component'] 
		}
	},
	beforeRouteEnter: function(to, from, next) {
		let data = window['_vue']['settings-index-index-component'];
		data.init();
		next();
	},
	template: `{{template}}`
});

vueRouter.addRoutes([{ 
	path: '/'+sysUrl+'/settings', 
	component: settingsIndexIndexComponent, 
	name: 'settings-index-index' 
}]);


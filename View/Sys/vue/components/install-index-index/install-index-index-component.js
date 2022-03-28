if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class installIndexIndex {
	buttonName = {
		'step1': 'Agree and Continue',
		'step2': 'Check Now',
		'step3': 'Save Database',
		'step4': 'Save Account',
		'step5': 'Save Link And Auth Key'
	};
	requirement = {
		phpver: {
			message: 'Php Version',
			passed: null
		},
		memlimit: {
			message: 'Memory limit',
			passed: null
		},
		writabledir: {
			message: 'Writable Directory',
			passed: null
		}
	};
	form = {
		database: {
			host: 'localhost',
			user: 'root',
			name: '',
			password: '',
			prefix: '',
			error: '',
			form_key: ''
		},
		sysuser: {
			id: '',
			firstname: '',
			lastname: '',
			email: '',
			password: '',
			retypepassword: '',
			error: '',
			form_key: ''
		},
		urls: {
			admin_url: '',
			system_url: '',
			auth_key: '',
			auth_secret: '',
			error: '',
			form_key: ''
		}
	};

	currentStep = 1;

	request = null;
	url = null;
	loader = null;
	toast = null;
	loginComponent = null;

	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];
	}

	init(){
		setTimeout(f => {
			this.loader = window['_vue']['loader-component'];
			this.toast = window['_vue']['toast-component'];
			this.loginComponent = window['_vue']['login-index-index-component']
		}, 500);
	}

	getSystemUrl(){
		return 'system' + this.form.urls.system_url;
	};
	goTo(step){
		this.loader.isLoading = true;
		if(this.currentStep == 1){
			this.currentStep = 2;
			this.loader.isLoading = false;
		}
		else if(this.currentStep == 2){
			this.checkRequirements('phpver').then(phpver => {
				if(phpver.passed){
					this.checkRequirements('memlimit').then(memlimit => {
						if(memlimit.passed){
							this.checkRequirements('writabledir').then(writabledir => {
								if(writabledir.passed){
									this.request.makeRequest('/'+window.sysUrl+'/install/formkey', '').then(formkey => {
										if(!formkey.error && formkey.result){
											let jsonData = {
												form_key: formkey.result.formKey
											}
											this.request.makeRequest('/'+window.sysUrl+'/install/database?getdb=1', jsonData, 'POST')
											.then(database => {
												if(!database.error && database.result){
													this.form.database.host = database.result.host;
													this.form.database.user = database.result.username;
													this.form.database.name = database.result.database;
													this.form.database.password = database.result.password;
													this.form.database.prefix = database.result.table_prefix;
												}

												this.loader.isLoading = false;
												this.currentStep = 3;
											});
										}
									});
								}
							});
						}
					});
				}
			});
		}
		else if(this.currentStep == 3){
			this.request.makeRequest('/'+window.sysUrl+'/install/formkey', '').then(formkey => {
				if(!formkey.error && formkey.result){
					this.form.database.form_key = formkey.result.formKey;
					this.request.makeRequest('/'+window.sysUrl+'/install/database', this.form.database).then(database => {
						if(!database.error && database.result){
							this.request.makeRequest('/'+window.sysUrl+'/install/saveadmin?getadmin=1&form_key='+formkey.result.formKey, '', 'GET').then(sysuser => {
								if(!sysuser.error && sysuser.result){
									this.form.sysuser.id = sysuser.result.id
									this.form.sysuser.firstname = sysuser.result.firstname
									this.form.sysuser.lastname = sysuser.result.lastname
									this.form.sysuser.email = sysuser.result.email
								}
								this.loader.isLoading = false;
								this.currentStep = 4;
							});
						} else {
							this.loader.isLoading = false;
							this.form.database.error = database.error.responseText;
						}
					});
				}
			});
		}
		else if(this.currentStep == 4){
			this.request.makeRequest('/'+window.sysUrl+'/install/formkey', '').then(formkey => {
				if(!formkey.error && formkey.result){
					this.form.sysuser.form_key = formkey.result.formKey;
					this.form.sysuser.error = '';
					this.request.makeRequest('/'+window.sysUrl+'/install/saveadmin', this.form.sysuser).then(sysuser => {
						if(!sysuser.error && sysuser.result){
							this.currentStep = 5;
							this.form.urls.admin_url = '_' + this.randomPassword(4).toLowerCase();
							this.form.urls.system_url = '_' + this.randomPassword(4).toLowerCase();
							this.form.urls.auth_key = this.randomPassword(150, 10, 10, 10, 4);
							this.form.urls.auth_secret = this.randomPassword(150, 10, 10, 10, 4);
						} else {
							this.form.sysuser.error = sysuser.error.responseText;
						}
						this.loader.isLoading = false;
					});
				}
			});
		}
		else if(this.currentStep == 5){
			this.request.makeRequest('/'+window.sysUrl+'/install/formkey', '').then(formkey => {
				if(!formkey.error && formkey.result){
					this.form.urls.form_key = formkey.result.formKey;
					this.form.urls.error = '';
					this.request.makeRequest('/'+window.sysUrl+'/install/saveadminurl', this.form.urls).then(saveadminurl => {
						this.loader.isLoading = true;
						if(!saveadminurl.error && saveadminurl.result){
							this.beforeInstallBmodule();
						} else {
							if(saveadminurl.error.status == 401 && saveadminurl.error.responseText == 'The system was already installed.'){
								this.beforeInstallBmodule();
							}
							this.form.urls.error = saveadminurl.error.responseText;
						}
					});
				}
			});
		}
	};
	beforeInstallBmodule(){
		setTimeout(f => {
			this.loader.text = 'Signing in your account...';

			this.loginComponent.form.email = this.form.sysuser.email;
			this.loginComponent.form.password = this.form.sysuser.password;
			
			this.loginComponent.login(false, {isredirect: 0}, this.getSystemUrl())
			.then(loggedIn => {
				if(loggedIn){
					this.loader.text = 'Installing Opoink/Bmodule';
					this.loader.isLoading = true;
					setTimeout(f => {
						this.installBmodule().then(installbmodule => {
							this.currentStep = 6;
							this.loader.isLoading = false;
						});
					}, 1000);
				}
			});
		}, 1000);
	};
	installBmodule(){
		return new Promise(bmod => {
			this.request.makeRequest('/'+this.getSystemUrl()+'/install/formkey', '').then(formkey => {
				if(!formkey.error && formkey.result){
					let jsonData = {
						form_key: formkey.result.formKey
					};
					this.request.makeRequest('/'+this.getSystemUrl()+'/install/opoinkbmodule', jsonData).then(installbmodule => {
						if(!installbmodule.error && installbmodule.result){
							this.loader.text = installbmodule.result.message;
							setTimeout(f => {
								bmod(installbmodule.result);
							},3000);
						} else {
							this.loader.text = installbmodule.error.responseText;
							setTimeout(f => {
								bmod(null);
							},3000);
						}
					});
				}
			});
		});
	};
	checkRequirements(type){
		return new Promise(req => {
			let jsonData = {
				check: type
			}
			this.requirement[type].passed = 'checking';
			this.request.makeRequest('/'+sysUrl+'/install/requirement', jsonData).then(resolve => {
				if(!resolve.error && resolve.result){
					this.requirement[type] = resolve.result;
					req(resolve.result);
				} else {
					req(resolve.error);
				}
			});
		});
	};
	// makeRequest(url, jsonData, type = 'POST'){
	// 	return new Promise(request => {
	// 		let ajaxData = {
	// 			url: url,
	// 			method: type,
	// 			data: {},
	// 			contentType: "application/json; charset=utf-8",
	// 			dataType: "json",
	// 			beforeSend: f => {
	// 			},
	// 			success: f => {
	// 				request({error: null,result: f});
	// 			},
	// 			error: error => {
	// 				request({error: error,result: null});
	// 			}
	// 		}

	// 		if(jsonData){
	// 			ajaxData.data = JSON.stringify(jsonData);
	// 		}

	// 		$.ajax(ajaxData);
	// 	});
	// };
	getUrl(){
		return window.location.protocol + "//" + window.location.host;
	};

	randomPassword(len = 8, minUpper = 0, minLower = 0, minNumber = -1, minSpecial = -1) {
		let chars = String.fromCharCode(...Array(127).keys()).slice(33),//chars
			A2Z = String.fromCharCode(...Array(91).keys()).slice(65),//A-Z
			a2z = String.fromCharCode(...Array(123).keys()).slice(97),//a-z
			zero2nine = String.fromCharCode(...Array(58).keys()).slice(48),//0-9
			specials = chars.replace(/\w/g, '')
		if (minSpecial < 0) chars = zero2nine + A2Z + a2z
		if (minNumber < 0) chars = chars.replace(zero2nine, '')
		let minRequired = minSpecial + minUpper + minLower + minNumber
		let rs = [].concat(
			Array.from({length: minSpecial ? minSpecial : 0}, () => specials[Math.floor(Math.random() * specials.length)]),
			Array.from({length: minUpper ? minUpper : 0}, () => A2Z[Math.floor(Math.random() * A2Z.length)]),
			Array.from({length: minLower ? minLower : 0}, () => a2z[Math.floor(Math.random() * a2z.length)]),
			Array.from({length: minNumber ? minNumber : 0}, () => zero2nine[Math.floor(Math.random() * zero2nine.length)]),
			Array.from({length: Math.max(len, minRequired) - (minRequired ? minRequired : 0)}, () => chars[Math.floor(Math.random() * chars.length)]),
		)
		return rs.sort(() => Math.random() > Math.random()).join('')
	};
}

window['_vue']['install-index-index-component'] = new installIndexIndex();

let installIndexIndexComponent = Vue.component('install-index-index-component', {
	data: function(){
		return {
			vue: window['_vue']['install-index-index-component'] 
		}
	},
	beforeRouteEnter: function(to, from, next) {
		let data = window['_vue']['install-index-index-component'];
		data.init();
		next();
	},
	template: `{{template}}`
});

vueRouter.addRoutes([{ 
	path: '/'+sysUrl+'/install', 
	component: installIndexIndexComponent, 
	name: 'install-index-index' 
}]);
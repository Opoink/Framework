{
	buttonName: {
		'step1': 'Agree and Continue',
		'step2': 'Check Now',
		'step3': 'Save Database',
		'step4': 'Save Account',
		'step5': 'Save Link And Auth Key'
	},
	requirement: {
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
	},
	form: {
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
	},
	currentStep: 1,
	goTo(step){
		if(this.currentStep == 1){
			this.currentStep = 2;
		}
		else if(this.currentStep == 2){
			this.checkRequirements('phpver').then(phpver => {
				if(phpver.passed){
					this.checkRequirements('memlimit').then(memlimit => {
						if(memlimit.passed){
							this.checkRequirements('writabledir').then(writabledir => {
								if(writabledir.passed){
									this.makeRequest('/system/install/formkey', '').then(formkey => {
										if(!formkey.error && formkey.result){
											let jsonData = {
												form_key: formkey.result.formKey
											}
											this.makeRequest('/system/install/database/getdb/1', jsonData, 'POST')
											.then(database => {
												if(!database.error && database.result){
													this.form.database.host = database.result.host;
													this.form.database.user = database.result.username;
													this.form.database.name = database.result.database;
													this.form.database.password = database.result.password;
													this.form.database.prefix = database.result.table_prefix;
												}
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
			this.makeRequest('/system/install/formkey', '').then(formkey => {
				if(!formkey.error && formkey.result){
					this.form.database.form_key = formkey.result.formKey;
					this.makeRequest('/system/install/database', this.form.database).then(database => {
						if(!database.error && database.result){
							this.makeRequest('/system/install/saveadmin/getadmin/1/form_key/'+formkey.result.formKey, '', 'GET').then(sysuser => {
								if(!sysuser.error && sysuser.result){
									this.form.sysuser.id = sysuser.result.id
									this.form.sysuser.firstname = sysuser.result.firstname
									this.form.sysuser.lastname = sysuser.result.lastname
									this.form.sysuser.email = sysuser.result.email
								}
								this.currentStep = 4;
							});
						} else {
							this.form.database.error = database.error.responseText;
						}
					});
				}
			});
		}
		else if(this.currentStep == 4){
			this.makeRequest('/system/install/formkey', '').then(formkey => {
				if(!formkey.error && formkey.result){
					this.form.sysuser.form_key = formkey.result.formKey;
					this.form.sysuser.error = '';
					this.makeRequest('/system/install/saveadmin', this.form.sysuser).then(sysuser => {
						if(!sysuser.error && sysuser.result){
							this.currentStep = 5;
							this.form.urls.admin_url = '_' + this.passwordGenerator(4, false);
							this.form.urls.system_url = '_' + this.passwordGenerator(4, false);
							this.form.urls.auth_key = '_' + this.passwordGenerator(150, true);
							this.form.urls.auth_secret = '_' + this.passwordGenerator(150, true);
						} else {
							this.form.sysuser.error = sysuser.error.responseText;
						}
					});
				}
			});
		}
		else if(this.currentStep == 5){
			this.makeRequest('/system/install/formkey', '').then(formkey => {
				if(!formkey.error && formkey.result){
					this.form.urls.form_key = formkey.result.formKey;
					this.form.urls.error = '';
					this.makeRequest('/system/install/saveadminurl', this.form.urls).then(saveadminurl => {
						if(!saveadminurl.error && saveadminurl.result){
							this.currentStep = 6;
						} else {
							this.form.urls.error = saveadminurl.error.responseText;
						}
					});
				}
			});
		}
	},
	checkRequirements(type){
		return new Promise(req => {
			let jsonData = {
				check: type
			}
			this.requirement[type].passed = 'checking';
			this.makeRequest('/system/install/requirement', jsonData).then(resolve => {
				if(!resolve.error && resolve.result){
					this.requirement[type] = resolve.result;
					req(resolve.result);
				} else {
					req(resolve.error);
				}
			});
		});
	},
	makeRequest(url, jsonData, type = 'POST'){
		return new Promise(request => {
			let ajaxData = {
				url: url,
				method: type,
				data: {},
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				beforeSend: f => {
				},
				success: f => {
					request({error: null,result: f});
				},
				error: error => {
					request({error: error,result: null});
				}
			}

			if(jsonData){
				ajaxData.data = JSON.stringify(jsonData);
			}

			$.ajax(ajaxData);
		});
	},
	getUrl(){
		return window.location.protocol + "//" + window.location.host;
	},
	passwordGenerator( len, isPunctuation ) {
		let length = (len)?(len):(10);
		let string = "abcdefghijklmnopqrstuvwxyz"; //to upper 
		let numeric = '0123456789';
		let punctuation = '!@#$%^&*()_+~`|}{[]\:;?><,./-=';
		let password = "";
		let character = "";
		let crunch = true;
		while( password.length<length ) {
			entity1 = Math.ceil(string.length * Math.random()*Math.random());
			entity2 = Math.ceil(numeric.length * Math.random()*Math.random());
			if(isPunctuation){
				entity3 = Math.ceil(punctuation.length * Math.random()*Math.random());
			}
			hold = string.charAt( entity1 );
			hold = (password.length%2==0)?(hold.toUpperCase()):(hold);
			character += hold;
			character += numeric.charAt( entity2 );
			if(isPunctuation){
				character += punctuation.charAt( entity3 );
			}
			password = character;
		}
		password=password.split('').sort(function(){return 0.5-Math.random()}).join('');
		return password.substr(0,len);
	}
}
{
	buttonName: {
		'step1': 'Agree and Continue',
		'step2': 'Check Now',
		'step3': 'Save Database',
		'step4': 'Save Account'
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
			firstname: 'asd',
			lastname: 'cvb',
			email: 'asd',
			password: 'asd',
			retypepassword: 'vxcv',
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
							console.log('database database database', database.result);
							this.currentStep = 4;
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
					this.makeRequest('/system/install/saveadmin', this.form.sysuser).then(sysuser => {
						console.log('sysuser sysuser sysuser', sysuser);
						// if(!database.error && database.result){
						// 	this.currentStep = 4;
						// } else {
						// 	this.form.database.error = database.error.responseText;
						// }
					});
					console.log(this.form.sysuser);
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
	}
}
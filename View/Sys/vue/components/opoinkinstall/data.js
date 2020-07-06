{
	buttonName: {
		'step1': 'Agree and Continue',
		'step2': 'Check Now',
		'step3': 'Save Database'
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
		}
	},
	currentStep: 1,
	goTo: function(step){
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
									this.currentStep = 3;
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
						} else {
							this.form.database.error = database.error.responseText;
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
	makeRequest: function(url, jsonData, type = 'POST'){
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
					console.log('error error', error);
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
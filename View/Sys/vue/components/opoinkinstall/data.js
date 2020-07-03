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
	},
	checkRequirements(type){
		return new Promise(req => {
			let jsonData = {
				check: type
			}
			this.requirement[type].passed = 'checking';
			this.makeRequest('/system/install/requirement', jsonData).then(resolve => {
				if(resolve){
					this.requirement[type] = resolve;
				}
				req(resolve);
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
					request(f);
				},
				error: error => {
					request(null);
				}
			}

			if(jsonData){
				ajaxData.data = JSON.stringify(jsonData);
			}

			$.ajax(ajaxData);
		});
	}
}
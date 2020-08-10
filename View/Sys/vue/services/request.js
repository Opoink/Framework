{
	getFormKey(){
		return new Promise(fk => {
			_vue.request.makeRequest('/'+_vue.url.getRoute()+'/install/formkey', '', 'GET')
			.then(formkey => {
				if(!formkey.error && formkey.result){
					fk(formkey.result.formKey);
				} else {
					fk(null);
				}
			});
		});
	},
	makeRequest(url, jsonData, type = 'POST', _dataType='json', _contentType='application/json; charset=utf-8'){
		_vue.loader.isLoading = true;
		return new Promise(request => {
			let ajaxData = {
				url: url,
				method: type,
				data: {},
				contentType: _contentType,
				dataType: _dataType,
				beforeSend: f => {
				},
				success: f => {
					request({error: null,result: f});
				},
				error: error => {
					request({error: error,result: null});
				},
				complete: complete => {
					_vue.loader.isLoading = false;
				}
			}

			if(jsonData){
				ajaxData.data = JSON.stringify(jsonData);
			}

			jQuery.ajax(ajaxData);
		});
	}
}
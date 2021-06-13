class request {
	contentType = 'application/json; charset=utf-8';
	dataType = 'json';
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
	};
	/**
	 * set the url param for the request
	 * @param params object the list of param to be added into the url
	 */
	buildQuery(params){
		let keys = Object.keys(params);
		let paramArray = [];
		for (let key of keys) {
			if(params[key] != null){
				paramArray.push(key + '=' + params[key]);
			}
		}
		if(paramArray.length){
			return '?' + paramArray.join('&');
		} else {
			return '';
		}
	};

	/**
	 * set the url param for the request
	 * @url
	 * @jsonData
	 * @type
	 * @loader
	 */
	makeRequest(url, jsonData, type = 'POST', loader = false){
		return new Promise(request => {
			let ajaxData = {
				url: url,
				method: type,
				data: {},
				contentType: this.contentType,
				dataType: this.dataType,
				beforeSend: f => {
					if(loader){
						_vue.loader.isLoading = true;
					}
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
	};
}
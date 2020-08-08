{
	makeRequest(url, jsonData, type = 'POST'){
		_vue.loader.isLoading = true;
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
				},
				complete: complete => {
					_vue.loader.isLoading = false;
				}
			}

			if(jsonData){
				ajaxData.data = JSON.stringify(jsonData);
			}

			$.ajax(ajaxData);
		});
	}
}
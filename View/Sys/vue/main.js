Object.byString = function(o, s) {
	s = s.replace(/\[(\w+)\]/g, '.$1'); // convert indexes to properties
	s = s.replace(/^\./, '');           // strip a leading dot
	var a = s.split('.');
	for (var i = 0, n = a.length; i < n; ++i) {
		var k = a[i];
		if (k in o) {
			o = o[k];
		} else {
			return;
		}
	}
	return o;
}

var opoink_system = {};

class url {
	/** opoink official domain website  */
	opoinkBaseUrl = 'https://www.opoink.com';

	/** will hold the query param from url */
	query = {};

	/**
	 * parse the url here so that the param will be available
	 */
	constructor(){
		this.query = {};
		let search = this.getSearch().replace('?', '');
		let queries = search.split("&");

		queries.forEach(q => {
			let _q = q.split("=");
			if(_q.length == 2){
				this.query[_q[0]] = _q[1];
			}
		});

	}

	/**
	 * return the protocal from the current url
	 */
	getProtocol(){
		return window.location.protocol;
	};

	/**
	 * return the host from the current url
	 */
	getHost(){
		return window.location.host;
	};

	/**
	 * return the path name of the current url
	 */
	getPathname(){
		return window.location.pathname;
	};

	/**
	 * return the query param of the current url
	 */
	getSearch(){
		return window.location.search;
	};

	/**
	 * return the route from the current url
	 */
	getRoute(){
		let pathArray = this.getPathname().substring(1).split('/');
		if(typeof pathArray[0] != 'undefined'){
			return pathArray[0].toLowerCase();
		} else {
			return '';
		}
	};

	/**
	 * return the system url
	 * @param {*} path 
	 * @param {*} isFullUrl 
	 */
	getUrl(path="", isFullUrl=false){
		if(isFullUrl){
			return this.getProtocol() + "//" + this.getHost() + '/' + this.getRoute() + path;
		} else {
			return "/" + this.getRoute() + path;
		}
	};

	/**
	 * redirect in the new location
	 * @param {*} location 
	 * @param {*} isFullUrl 
	 */
	redirect(location, isFullUrl=false){
		window.location.href = this.getUrl(location, isFullUrl) ;
	};

	/**
	 * get the current url param
	 * @param {*} param 
	 */
	getParam(param){
		// if(typeof this.query[param] != 'undefined'){
		// 	return this.query[param];
		// }
		if(param){
            if(typeof window.vueRouter.currentRoute.params[param] != 'undefined'){
                return window.vueRouter.currentRoute.params[param];
            } else {
                return null;
            }
        } else {
            return window.vueRouter.currentRoute.params;
        }
	}
	getQuery(param){
        if(param){
            if(typeof window.vueRouter.currentRoute.query[param] != 'undefined'){
                return window.vueRouter.currentRoute.query[param];
            } else {
                return null;
            }
        } else {
            return window.vueRouter.currentRoute.query;
        }
    }

	navigateTo(path){
        if(window.vueRouter.currentRoute != path){
            window.vueRouter.push(path);
        }
    }
}
opoink_system['_url'] = new url();

class request {
	contentType = 'application/json; charset=utf-8';
	dataType = 'json';
	getFormKey(){
		return new Promise(fk => {
			this.makeRequest('/'+window.opoink_system['_url'].getRoute()+'/install/formkey', '', 'GET')
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
	 * deserialize nested form field serielized by jquery
	 * @param {*} $str 
	 * return JSON object
	 */
	stringToJson(str){
		str = decodeURIComponent(str);
		let params = str.split('&');

		let jsonData = {};
		params.forEach(param => {
			let _param = param.split('=');
			let key = _param[0];
			let val = '';
			if(typeof _param[1] != 'undefined'){
				val = _param[1];
			}
			// key = key.split(']');
			// key = key.join('');

			/**
			 * credits to 
			 * Alexander Higgins 
			 * at https://stackoverflow.com/questions/44916365/how-to-create-a-nested-object-json-of-a-form-input-values-based-on-the-input-n
			 */
			let nameParts  = key.split('.');
			let prefix = '';
			let stack = jsonData;
			for (let index = 0; index < nameParts.length - 1; index++) {
				prefix = nameParts[index];

				if (!stack[prefix]) {
					stack[prefix] = {};
				}
				stack = stack[prefix];
			}
			prefix = nameParts[nameParts.length - 1];
			if (stack[prefix]) {
				let newVal = stack[prefix] + ',' + val;
			  stack[prefix] += newVal;
			} else {
			  stack[prefix] = val;
			}
		});
		return jsonData;
	}

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
						// _vue.loader.isLoading = true;
					}
				},
				success: f => {
					request({error: null,result: f});
				},
				error: error => {
					request({error: error,result: null});
				},
				complete: complete => {
					// _vue.loader.isLoading = false;
				}
			}

			if(jsonData){
				ajaxData.data = JSON.stringify(jsonData);
			}

			$.ajax(ajaxData);
		});
	};
}
opoink_system['_request'] = new request();


var vueRouter = new VueRouter({
	mode: 'history',
	routes: []
});
Vue.use(VueRouter);

new Vue({
	data: () => {
		return {
		}
	},
	beforeMount: () => {
	},
	mounted: () => {
	},
	router: vueRouter
}).$mount('#app-root');
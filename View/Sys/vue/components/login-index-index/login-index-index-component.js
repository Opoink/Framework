if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class loginIndexIndex {
	url = window['_url'];
	request = window['_request'];

	formView = 'login'; /* either login || lost-password */
	form = {
		email: '',
		password: ''
	};
	sendingForgotPasswordEmail = false;

	changeView(view){
		this.formView = view
	};
	onSubmit(e){
		e.preventDefault();
	};
	login(redirect = true, params = null, route=null){
		return new Promise(login_result => {
			if(this.validateEmail(this.form.email)){
				if(this.form.password.length){
					let qureyParams = '';
					if(params){
						qureyParams = this.request.buildQuery(params);
					}
					let _route = this.url.getRoute();
					if(route){
						_route = route;
					}
					console.log('qureyParams qureyParams', qureyParams);

					this.request.makeRequest('/'+_route+'/login' + qureyParams, this.form)
					.then(login => {
						if(!login.error && login.result){
							window._vue['toast-component'].add(login.result.message, 'Login Success');
							if(redirect){
								this.url.redirect('/');
							}
							login_result(true);
						} else {
							if(login.error.responseText == "You’re already logged in."){
								login_result(true);
							} else {
								window._vue['toast-component'].add(login.error.responseText, 'Error');
								login_result(false);
							}
						}
					});
				} else {
					window._vue['toast-component'].add('Please enter your password.', 'Error');
					login_result(false);
				}
			} else {
				window._vue['toast-component'].add('Invalid email address format.', 'Error',8000000);
				login_result(false);
			}
		});
	};
	lostPassword(){
		// if(this.validateEmail(this.form.email)){
		// 	let jsonData = {
		// 		email: this.form.email
		// 	}

		// 	this.sendingForgotPasswordEmail = true;
		// 	_vue.request.makeRequest('/'+_vue.url.getRoute()+'/login/forgetpassword', jsonData)
		// 	.then(fp => {
		// 		if(!fp.error && fp.result){
		// 			_vue.toast.add(fp.result.message, 'Email Sent');
		// 			this.changeView('login');
		// 		} else {
		// 			_vue.toast.add(fp.error.responseText, 'Error');
		// 		}
		// 		this.sendingForgotPasswordEmail = false;
		// 	});
		// } else {
		// 	_vue.toast.add('Invalid email address format.', 'Error');
		// }
	};
	validateEmail(email) {
	  const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	  return re.test(email);
	}
}

window['_vue']['login-index-index-component'] = new loginIndexIndex();

let loginIndexIndexComponent = Vue.component('login-index-index-component', {
	data: function(){
		return {
			vue: window['_vue']['login-index-index-component'] 
		}
	},
	template: '{{template}}'
});

vueRouter.addRoutes([{ 
	path: '/system/login', 
	component: loginIndexIndexComponent, 
	name: 'login-index-index' 
}]);

console.log('window', Vue);
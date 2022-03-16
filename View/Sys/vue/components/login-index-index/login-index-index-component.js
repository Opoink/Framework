if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class loginIndexIndex {
	url;
	request;
	toast;

	formView = 'login'; /* either login || lost-password */
	form = {
		email: '',
		password: ''
	};
	sendingForgotPasswordEmail = false;

	constructor(){
		this.url = window['opoink_system']['_url'];
		this.request = window['opoink_system']['_request'];
		setTimeout(f => {
			this.toast = window._vue["toast-component"];
		}, 500);
	}

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
					this.request.makeRequest('/'+_route+'/login' + qureyParams, this.form)
					.then(login => {
						if(!login.error && login.result){
							this.toast.add(login.result.message, 'Login Success');
							if(redirect){
								this.url.redirect('/');
							}
							login_result(true);
						} else {
							if(login.error.responseText == "You’re already logged in."){
								login_result(true);
							} else {
								this.toast.add(login.error.responseText, 'Error');
								login_result(false);
							}
						}
					});
				} else {
					this.toast.add('Please enter your password.', 'Error');
					login_result(false);
				}
			} else {
				this.toast.add('Invalid email address format.', 'Error');
				login_result(false);
			}
		});
	};
	lostPassword(){
		if(this.validateEmail(this.form.email)){
			let jsonData = {
				email: this.form.email
			}

			this.sendingForgotPasswordEmail = true;
			this.request.makeRequest('/'+this.url.getRoute()+'/login/forgetpassword', jsonData)
			.then(fp => {
				if(!fp.error && fp.result){
					this.toast.add(fp.result.message, 'Email Sent');
					this.changeView('login');
				} else {
					this.toast.add(fp.error.responseText, 'Error');
				}
				this.sendingForgotPasswordEmail = false;
			});
		} else {
			this.toast.add('Invalid email address format.', 'Error');
		}
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
	template: `{{template}}`
});

vueRouter.addRoutes([{ 
	path: '/'+sysUrl+'/login', 
	component: loginIndexIndexComponent, 
	name: 'login-index-index' 
}]);
class opoinkloginindex {
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
		return new Promise(loggedin => {
			if(this.validateEmail(this.form.email)){
				if(this.form.password.length){
					let qureyParams = '';
					if(params){
						qureyParams = _vue.request.buildQuery(params);
					}
					let _route = _vue.url.getRoute();
					if(route){
						_route = route;
					}
					_vue.request.makeRequest('/'+_route+'/login' + qureyParams, this.form)
					.then(login => {
						if(!login.error && login.result){
							_vue.toast.add(login.result.message, 'Login Success');
							if(redirect){
								_vue.url.redirect('/');
							}
							loggedin(true);
						} else {
							if(login.error.responseText == "You’re already logged in."){
								loggedin(true);
							} else {
								_vue.toast.add(login.error.responseText, 'Error');
								loggedin(false);
							}
						}
					});
				} else {
					_vue.toast.add('Please enter your password.', 'Error');
					loggedin(false);
				}
			} else {
				_vue.toast.add('Invalid email address format.', 'Error');
				loggedin(false);
			}
		});
	};
	lostPassword(){
		if(this.validateEmail(this.form.email)){
			let jsonData = {
				email: this.form.email
			}

			this.sendingForgotPasswordEmail = true;
			_vue.request.makeRequest('/'+_vue.url.getRoute()+'/login/forgetpassword', jsonData)
			.then(fp => {
				if(!fp.error && fp.result){
					_vue.toast.add(fp.result.message, 'Email Sent');
					this.changeView('login');
				} else {
					_vue.toast.add(fp.error.responseText, 'Error');
				}
				this.sendingForgotPasswordEmail = false;
			});
		} else {
			_vue.toast.add('Invalid email address format.', 'Error');
		}
	};
	validateEmail(email) {
	  const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	  return re.test(email);
	}
}
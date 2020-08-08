{
	formView: 'login', /* either login || lost-password */
	form: {
		email: '',
		password: ''
	},
	changeView(view){
		this.formView = view
	},
	onSubmit(e){
		e.preventDefault();
	},
	login(){
		if(this.validateEmail(this.form.email)){
			if(this.form.password.length){
				_vue.request.makeRequest('/'+_vue.url.getRoute()+'/login', this.form)
				.then(login => {
					if(!login.error && login.result){
						_vue.toast.add(login.result.message, 'Login Success');
						_vue.url.redirect('/'+_vue.url.getRoute());
					} else {
						_vue.toast.add('Invalid login creadentials.', 'Error');
					}
				});
			} else {
				_vue.toast.add('Please enter your password.', 'Error');
			}
		} else {
			_vue.toast.add('Invalid email address format.', 'Error');
		}
	},
	lostPassword(){
		if(this.validateEmail(this.form.email)){
			let jsonData = {
				email: this.form.email
			}
			_vue.request.makeRequest('/'+_vue.url.getRoute()+'/login/forgetpassword', jsonData)
			.then(fp => {
				console.log(fp);
				if(!fp.error && fp.result){
					_vue.toast.add(fp.result.message, 'Email Sent');
				} else {
					_vue.toast.add(fp.error.responseText, 'Error');
				}
			});
		} else {
			_vue.toast.add('Invalid email address format.', 'Error');
		}
	},
	validateEmail(email) {
	  const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	  return re.test(email);
	}
}
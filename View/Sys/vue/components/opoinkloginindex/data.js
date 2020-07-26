{
	formView: 'login', /* either login || lost-password */
	form: {
		email: '',
		password: ''
	},
	changeView(view){
		this.formView = view
	},
	onSubmit(){
		console.log('this.form', this.form);
	}
}
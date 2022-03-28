class systemmodulecreate {
	
	request = null;
	toast = null;
	url = null;
	loader = null;

	form = {
		vendor_name: '',
		module_name: '',
		module_ver: ''
	}

	init(){
		this.request = _vue.request;
		this.toast = _vue.toast;
		this.url = _vue.url;
		this.loader = _vue.loader;
	}

	/**
	 * process the creation of new module
	 */
	save(){
		if(!this.form.vendor_name){
			this.toast.add('Please enter a vendor name to create a new module.', 'Error')
		}
		else if(!this.form.module_name){
			this.toast.add('Please enter a module name.', 'Error')
		}
		else {
			if(!this.form.module_ver){
				this.form.module_ver = '0.0.0';
			}

			this.loader.isLoading = true;
			this.loader.text = 'Creating new module.';
			
			this.request.getFormKey().then(formkey => {
				this.form['form_key'] = formkey;
				let url = '/' + this.url.getRoute() + '/module/save';
				this.request.makeRequest(url, this.form, 'POST', true)
				.then(newmod => {
					if(!newmod.error && newmod.result){
						this.url.redirect('/module');
					} else if(newmod.error && !newmod.result){
						this.toast.add(newmod.error.responseText, 'Error')
					}
				});
			});
		}
	}
}
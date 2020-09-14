class systemmoduleedit {
	
	request = null;
	toast = null;
	url = null;
	loader = null;

	current_ver = null;
	form = {
		vendor_name: '',
		module_name: '',
		module_ver: '',
		save: 'save'
	}

	init(){
		this.request = _vue.request;
		this.toast = _vue.toast;
		this.url = _vue.url;
		this.loader = _vue.loader;

		this.form.vendor_name = this.url.getParam('vendor');
		this.form.module_name = this.url.getParam('module');
		this.form.module_ver = this.url.getParam('version');
		this.current_ver = this.url.getParam('version');
	}

	/**
	 * update the module
	 */
	save(){
		if(!this.form.module_ver){
			this.form.module_ver = this.current_ver;
		}

		this.loader.isLoading = true;
		this.loader.text = 'Saving module version.';
			
		this.request.getFormKey().then(formkey => {
			this.form['form_key'] = formkey;
			let url = '/' + this.url.getRoute() + '/module/update';
			this.request.makeRequest(url, this.form, 'POST', true)
			.then(mod => {
				console.log(mod)
				if(!mod.error && mod.result){
					this.url.redirect('/module/edit?vendor='+this.form.vendor_name+'&module='+this.form.module_name+'&version='+this.form.module_ver);
				} else if(mod.error && !mod.result){
					this.toast.add(mod.error.responseText, 'Error');
				}
			});
		});
	}
}
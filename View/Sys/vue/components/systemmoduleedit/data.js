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

	/**
	 * the value of the controller form field
	 */
	controllerForm = {
		controller_route_regex: false,
		controller_route: '',
		controller_controller_regex: false,
		controller_controller: '',
		controller_action_regex: false,
		controller_action: '',
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
				if(!mod.error && mod.result){
					this.url.redirect('/module/edit?vendor='+this.form.vendor_name+'&module='+this.form.module_name+'&version='+this.form.module_ver);
				} else if(mod.error && !mod.result){
					this.toast.add(mod.error.responseText, 'Error');
				}
			});
		});
	}

	/**
	 * make an API request to creat new controller
	 */
	createController(){
		console.log('this.controllerForm this.controllerForm', this.controllerForm);

		this.loader.isLoading = true;
		this.loader.text = 'Creating new controller.';

		let jsonData = {
			vendor_name: this.form.vendor_name,
			module_name: this.form.module_name,
			controller_route: this.controllerForm.controller_route,
			controller_controller: this.controllerForm.controller_controller,
			controller_action: this.controllerForm.controller_action
		};

		if(this.controllerForm.controller_route_regex && !this.controllerForm.controller_route){
			this.toast.add('If you will use route as regex, you have to enter your expression into route\'s field', 'Error');
		}
		else if(this.controllerForm.controller_controller_regex && !this.controllerForm.controller_controller) {
			this.toast.add('If you will use controller as regex, you have to enter your expression into controller\'s field', 'Error');
		}
		else if(this.controllerForm.controller_action_regex && !this.controllerForm.controller_action) {
			this.toast.add('If you will use action as regex, you have to enter your expression into action\'s field', 'Error');
		} else {
			if(this.controllerForm.controller_route_regex){
				jsonData['controller_route_regex'] = 'yes';
			}
			if(this.controllerForm.controller_controller_regex){
				jsonData['controller_controller_regex'] = 'yes';
			}
			if(this.controllerForm.controller_action_regex){
				jsonData['controller_action_regex'] = 'yes';
			}
			let url = '/' + this.url.getRoute() + '/module/addcontroller';
			this.request.makeRequest(url, jsonData, 'POST', true)
			.then(mod => {
				if(!mod.error && mod.result){
					
					this.controllerForm.controller_route_regex = false;
					this.controllerForm.controller_route = '';
					this.controllerForm.controller_controller_regex = false;
					this.controllerForm.controller_controller = '';
					this.controllerForm.controller_action_regex = false;
					this.controllerForm.controller_action = '';

					this.toast.add(mod.result.message, 'Success');
				} else if(mod.error && !mod.result){
					this.toast.add(mod.error.responseText, 'Error');
				}
				this.loader.isLoading = false;
			});
		}
	}

	/**
	 * this will reset controller form depending on what is the given param
	 * @param {*} type either route || controller
	 */
	/**regexSelected(type){
		if(type == 'route'){
			this.controllerForm.controller_controller_regex = false;
			this.controllerForm.controller_controller = '';
			this.controllerForm.controller_action_regex = false;
			this.controllerForm.controller_action = '';
		}
		else if(type == 'controller'){
			this.controllerForm.controller_action_regex = false;
			this.controllerForm.controller_action = '';
		}

		console.log('regexSelected regexSelected', type)
	}*/
}
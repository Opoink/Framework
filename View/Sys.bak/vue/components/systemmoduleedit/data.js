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

	controllerType = 'RCA';

	/**
	 * the value of the controller form field
	 */
	controllerForm = {
		extend_to_class: '',
		controller_type: 'public',
		controller_route_regex: false,
		controller_route: '',
		controller_controller_regex: false,
		controller_controller: '',
		controller_action_regex: false,
		controller_action: '',
		controller_pattern: '',
		controller_request_method: '*'
	}

	init(){
		this.request = _vue.request;
		this.toast = _vue.toast;
		this.url = _vue.url;
		this.loader = _vue.loader;

		this.resetControllerForm();

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
		if(this.controllerType == 'RCA'){
			this.createControllerRCA();
		} else if(this.controllerType == 'PATTERN') {
			this.createControllerPATTERN();
		}
	}
	/**
	 * make an API request to creat new controller
	 */
	createControllerRCA(){
		let jsonData = {
			type: this.controllerType,
			extend_to_class: this.controllerForm.extend_to_class,
			controller_type: this.controllerForm.controller_type,
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
			this.loader.isLoading = true;
			this.loader.text = 'Creating new controller.';
			if(this.controllerForm.controller_route_regex){
				jsonData['controller_route_regex'] = 'yes';
			}
			if(this.controllerForm.controller_controller_regex){
				jsonData['controller_controller_regex'] = 'yes';
			}
			if(this.controllerForm.controller_action_regex){
				jsonData['controller_action_regex'] = 'yes';
			}
			this.requestToCreate(jsonData);
		}
	}

	requestToCreate(jsonData){
		let url = '/' + this.url.getRoute() + '/module/addcontroller';
		this.request.makeRequest(url, jsonData, 'POST', true)
		.then(mod => {
			if(!mod.error && mod.result){
				this.resetControllerForm();
				this.toast.add(mod.result.message, 'Success');
			} else if(mod.error && !mod.result){
				this.toast.add(mod.error.responseText, 'Error');
			}
			this.loader.isLoading = false;
		});
	}

	resetControllerForm(){
		this.controllerForm.extend_to_class = '\\Of\\Controller\\Controller';
		this.controllerForm.controller_type = 'public';
		this.controllerForm.controller_route_regex = false;
		this.controllerForm.controller_route = '';
		this.controllerForm.controller_controller_regex = false;
		this.controllerForm.controller_controller = '';
		this.controllerForm.controller_action_regex = false;
		this.controllerForm.controller_action = '';
		this.controllerForm.controller_pattern = '';
		this.controllerForm.controller_request_method = '*';
	}

	createControllerPATTERN(){
		let jsonData = {
			type: this.controllerType,
			controller_pattern: this.controllerForm.controller_pattern,
			vendor_name: this.form.vendor_name,
			module_name: this.form.module_name,
			controller_request_method: this.controllerForm.controller_request_method,
			extend_to_class: this.controllerForm.extend_to_class
		};
		this.requestToCreate(jsonData);
	}

	setControllerType(type){
		this.resetControllerForm();
		this.controllerType = type;
	}
}
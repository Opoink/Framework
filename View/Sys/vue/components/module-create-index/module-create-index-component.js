if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class moduleCreateIndex {
	request = null;
	toast = null;
	url = null;
	loader = null;

	form = {
		vendor_name: '',
		module_name: '',
		module_ver: ''
	}

	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];
		setTimeout(f => {
			this.toast = window['_vue']['toast-component'];
			this.loader = window['_vue']['loader-component'];
		}, 500);
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

window['_vue']['module-create-index-component'] = new moduleCreateIndex();

let moduleCreateIndexComponent = Vue.component('module-create-index-component', {
	data: function(){
		return {
			vue: window['_vue']['module-create-index-component'] 
		}
	},
	beforeRouteEnter: function(to, from, next) {
		document.title = 'Create Modules';
		next();
	},
	template: `{{template}}`
});

vueRouter.addRoutes([{ 
	path: '/'+sysUrl+'/module/create', 
	component: moduleCreateIndexComponent, 
	name: 'module-create-index' 
}]);


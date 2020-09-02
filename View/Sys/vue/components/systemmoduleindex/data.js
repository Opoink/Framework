class systemmoduleindex {

	/**
	 * this is the list of the module that was already in 
	 * <installation_dir>/App/Ext disrectory
	 */
	modules  = {
		installed: [],
		uninstalled: []
	};

	/**
	 * this are the selected module that are ready for installation, upgrade or uninstallation
	 */
	modForm = {
		installed: [],
		uninstalled: []
	};

	init(){
		this.getModules();
	};

	/**
	 * make a server request tot get the list of modules
	 */
	getModules(){
		let url = '/' + _vue.url.getRoute() + '/module' + _vue.request.buildQuery({modules: 1});
		_vue.request.makeRequest(url, '', 'GET')
		.then(a => {
			if(!a.error && a.result){
				this.modules.installed = this.convertModuleIntoArray(a.result.installed);
				this.modules.uninstalled = this.convertModuleIntoArray(a.result.uninstalled);
			}
		});
	};

	/**
	 * convert the list of module into an array
	 * @param {*} modules 
	 */
	convertModuleIntoArray(modules){

		let mods = this.objectToArray(modules);
		mods.forEach(mod => {
			/**
			 * the type typeof array and an object will result same "object"
			 * while the array have the property of length and object don't
			 */
			if(typeof mod.value.controllers.length == 'undefined'){
				mod.value.controllers = this.objectToArray(mod.value.controllers);

				mod.value.controllers.forEach(con => {
					if(typeof con.value == 'string'){
						con['isRegex'] = false;
					} else {
						con['isRegex'] = true;
					}
				});
				
			}
		});
		
		return mods;
	};

	/**
	 * convert an object into an array, set the object key as one of the values
	 * @param {*} obj 
	 */
	objectToArray(obj){
		let keys = Object.keys(obj);
		let mods = [];
		for (let key of keys) {
			if(key, obj.hasOwnProperty(key)){
				let data = {
					key: key,
					value: obj[key],
				}
				mods.push(data);
			}
		}
		return mods;
	};

	/**
	 * install all module that is checked in the UI
	 */
	installModules(){
		if(this.modForm.uninstalled.length > 0){
			_vue.loader.text = 'Fetching form key...';
			_vue.request.getFormKey().then(formkey => {
				if(formkey){
					let jsonData = {
						'form_key': formkey,
						'availableModule': this.modForm.uninstalled
					}
					let url = '/' + _vue.url.getRoute() + '/module/install';
					_vue.request.makeRequest(url, jsonData, 'POST')
					.then(a => {
						console.log('installModules installModules', a);
	
						// if(!a.error && a.result){
						// 	this.modules.installed = this.convertModuleIntoArray(a.result.installed);
						// 	this.modules.uninstalled = this.convertModuleIntoArray(a.result.uninstalled);
						// }
					});
	
				}
			});
		} else {
			_vue.toast.add('Please select atleast one module to install');
		}
	};

	/**
	 * upgrade all selected module
	 * and reset all selected after
	 */
	upgradeModules(){
		console.log(this.modForm.installed);

		this.modForm.installed = [];
	};
}
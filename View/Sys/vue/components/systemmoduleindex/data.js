class systemmoduleindex {

	/** url service */
	url = null

	/**
	 * this is the list of the module that was already in 
	 * <installation_dir>/App/Ext disrectory
	 */
	modules = {
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

	/** hold the list of on-going and done task */
	installTasks = [];

	/** either to display the finish button or not  */
	moduleTaskFinishButton = false;

	/** tell what action the modal start button will do */
	modalAction = '';

	/** tell if the process of module is currently being done or not */
	initializing = false;

	constructor(){
	}

	init(){
		this.getModules();
		this.url = _vue.url;
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
	 * open the modal for task
	 * @param {*} action the action of the modal to perform 
	 */
	openModal(action){
		this.modalAction = action;
		this.installTasks = [];
		this.initializing = false;

		jQuery.noConflict();
		$('#modInstallModal').modal({
			backdrop: 'static',
			keyboard: false
		});
		this.moduleTaskFinishButton = true;
	}

	/**
	 * install all module that is checked in the UI
	 */
	installModules(){
		if(this.modForm.uninstalled.length > 0){
			this.moduleTaskFinishButton = false;
			this.initializing = true;
			this.installModulesAssync(this.modForm.uninstalled, 0).then(() => {
				this.getModules();
				this.moduleTaskFinishButton = true;
				this.modForm.installed = [];
				this.modForm.uninstalled = [];
			});
		} else {
			_vue.toast.add('Please select atleast one module to install');
		}
	};

	/**
	 * we need to wait for each module to finish its install
	 * before firing a new one
	 * @param {*} mods is the light of module that was being proccess
	 * @param {*} index is the key of the array
	 */
	installModulesAssync(mods, index){
		return new Promise(install => {
			if(typeof mods[index] != 'undefined'){
				this.installTasks.push('Installing ' + mods[index] + '...');

				_vue.loader.text = 'Fetching form key...';
				_vue.request.getFormKey().then(formkey => {
					if(formkey){
						let jsonData = {
							'form_key': formkey,
							'availableModule': [mods[index]]
						}
						let url = '/' + _vue.url.getRoute() + '/module/install';
						_vue.request.makeRequest(url, jsonData, 'POST')
						.then(a => {
							setTimeout(f => {
								if(!a.error && a.result){
									this.installTasks.push(mods[index] + ' successfully installed...');
								} else {
									this.installTasks.push('Failed to install ' + mods[index] + '...');
									this.installTasks.push('Check this module if already saved on your database, if it is you may want to delete it first.');
									this.installTasks.push('Check the permission of etc directory and make sure that it is writable.');
								}
								let newIndex = index + 1;
								if(typeof mods[newIndex] != 'undefined'){
									this.installTasks.push("-------------------------------------");
									install(this.installModulesAssync(mods, newIndex));
								} else {
									install(true);
								}
							}, 3000);
						});
					} else {
						this.installTasks.push('Failed, can\'t get a form key...');
						install(false);
					}
				});
			} else {
				install(false);
			}
		});
	}

	/**
	 * upgrade all selected module
	 * and reset all selected after
	 */
	upgradeModules(action){
		if(this.modForm.installed.length > 0){
			this.moduleTaskFinishButton = false;
			this.initializing = true;
			this.updateModuleAssync(this.modForm.installed, 0, action).then(() => {
				this.getModules();
				this.moduleTaskFinishButton = true;
				this.modForm.installed = [];
				this.modForm.uninstalled = [];
			});
		} else {
			_vue.toast.add('Please select atleast one module to upgrade');
		}
	}

	/**
	 * we need to wait for each module to finish its install
	 * before firing a new one
	 * @param {*} mods is the light of module that was being proccess
	 * @param {*} index is the key of the array
	 */
	updateModuleAssync(mods, index, action){
		return new Promise(install => {
			if(typeof mods[index] != 'undefined'){
				if(action == 'upgrade'){
					this.installTasks.push('Upgrading ' + mods[index] + '...');
				}
				else if(action == 'uninstall'){
					this.installTasks.push('Uninstalling module ' + mods[index] + '...');
					this.installTasks.push('The file structure and the database included in this module will not be removed. You will have to remove it manually.');
				}

				_vue.request.getFormKey().then(formkey => {
					if(formkey){
						let jsonData = {
							'form_key': formkey,
							'action': action,
							'intalledModule': [mods[index]]
						}
						let url = '/' + _vue.url.getRoute() + '/module/action';
						_vue.request.makeRequest(url, jsonData, 'POST')
						.then(a => {
							setTimeout(f => {
								if(!a.error && a.result){
									a.result.message.forEach(msg => {
										this.installTasks.push(msg);
									});
								} else {
									this.installTasks.push('Failed to upgrade ' + mods[index] + '...');
								}
								let newIndex = index + 1;
								if(typeof mods[newIndex] != 'undefined'){
									this.installTasks.push("-------------------------------------");
									install(this.updateModuleAssync(mods, newIndex, action));
								} else {
									install(true);
								}
							}, 3000);
						});
					} else {
						this.installTasks.push('Failed, can\'t get a form key...');
						install(false);
					}
				});
			} else {
				install(false);
			}
		});
	}
}
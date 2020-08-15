class systemmoduleindex {

	modules  = {
		installed: [],
		uninstalled: []
	};

	init(){
		console.log('systemmoduleindex systemmoduleindex');
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
				this.modules.uninstalled = this.convertModuleIntoArray(a.result.uninstalled);
				console.log('this.modules.uninstalled', this.modules.uninstalled)
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
		
		console.log('mods mods mods', mods);
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
}
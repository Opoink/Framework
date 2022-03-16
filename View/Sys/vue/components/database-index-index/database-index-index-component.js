if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class databaseIndexIndex {
	/** url service */
	url = null;
	request = null;
	mainHeader = null;
	loader = null;
	toast = null;

	/**
	 * all database tables from installed modules only
	 * tables from not installed modules are not included
	 */
	alltables = [];

	selectedModule = null;
	selectedTableName = null;
	selectedTableValue = null;

	/**
	 * the current selected table fields
	 */
	selectedTableFields = null;
	selectedTableField = null;

	formField = {
		name: '',
		type: '',
		length: '',
		default: '',
		default_value: '',
		attributes: '',
		collation: '',
		old_name: '',
		primary: false
	}
	formFieldSaveAndInstall = false;


	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];
	}

	init(){
		this.selectedTableName = null;
		this.selectedTableValue = null;

		setTimeout(f => {
			this.mainHeader = window['_vue']['mainheader-component'];
			this.mainHeader.pageTitle = 'Opoink Modules';
			this.loader = window['_vue']['loader-component'];
			this.toast = window['_vue']['toast-component'];
			this.getModuleTables();
		}, 500);
	}

	/**
	 * this method will call an API
	 * the result all table from the instable modules
	 */
	getModuleTables(){
		let url = '/' + this.url.getRoute() + '/database?alltables=1';
		this.request.makeRequest(url, '', 'GET', true)
		.then(result => {
			if(!result.error && result.result){
				this.alltables = result.result;
			} else if(result.error && !result.result){
				this.toast.add(result.error.responseText, 'Error');
			}
		});
	}

	/**
	 * get table row from API
	 * @param {*} module 
	 * @param {*} tablename 
	 */
	setTableRows(module, tablename, tableValue){
		this.selectedTableName = tablename;
		this.selectedTableValue = tablename;
		this.selectedModule = module;

		let url = '/' + this.url.getRoute() + '/database?module=' + module + '&tablename=' + tablename;
		this.request.makeRequest(url, '', 'GET', true)
		.then(result => {
			if(!result.error && result.result){
				this.selectedTableFields = result.result;
			} else if(result.error && !result.result){
				this.toast.add(result.error.responseText, 'Error');
			}
		});
	}

	setField(field){
		this.selectedTableField = field;
		this.formFieldSaveAndInstall = false;
		this.setFormField();
	}

	setFormField(){
		$.each(this.formField, (key, value) => {
			if(key == 'default'){
				this.formField[key] = 'NONE';
				if(typeof this.selectedTableField[key] != 'undefined'){
					if(this.selectedTableField[key] === 'CURRENT_TIMESTAMP'){
						this.formField[key] = this.selectedTableField[key];
					}
					else if(this.selectedTableField[key] === null) {
						this.formField[key] = 'NULL';
					}
					else {
						this.formField[key] = 'USER_DEFINED';
					}
				}
			}
			else {
				this.formField[key] = '';
				if(typeof this.selectedTableField[key] != 'undefined'){
					this.formField[key] = this.selectedTableField[key];
				}
			}
		});

		this.formField.old_name = this.selectedTableField.name;
	}

	saveField(e){
		e.preventDefault();

		let jsonData = {
			module: this.selectedModule,
			tablename: this.selectedTableName,
			save_and_install: this.formFieldSaveAndInstall,
			fields: [this.formField]
		}
		this.request.getFormKey().then(formkey => {
			jsonData['form_key'] = formkey;

			let url = '/' + this.url.getRoute() + '/database/savefield';
			this.request.makeRequest(url, jsonData, 'POST', true).then(result => {
				if(!result.error && result.result){
					console.log('saveField saveField', result.result);
					this.selectedTableFields = result.result;
				} else if(result.error && !result.result){
					this.toast.add(result.error.responseText, 'Error');
				}
			});
		});
	}
}

window['_vue']['database-index-index-component'] = new databaseIndexIndex();

let databaseIndexIndexComponent = Vue.component('database-index-index-component', {
	data: function(){
		return {
			vue: window['_vue']['database-index-index-component'] 
		}
	},
	beforeRouteEnter: function(to, from, next) {
		let data = window['_vue']['database-index-index-component'];
		data.init();
		next();
	},
	template: `{{template}}`
});

vueRouter.addRoutes([{ 
	path: '/'+sysUrl+'/database', 
	component: databaseIndexIndexComponent, 
	name: 'database-index-index' 
}]);


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

	/**
	 * this form is for creating and updating database field(column)
	 */
	formField = {
		name: '',
		type: '',
		length: '',
		default: '',
		default_value: '',
		attributes: '',
		collation: '',
		old_name: '',
		primary: false,
		comment: '',
		after: ''
	}
	formFieldDefaultValue = '';
	formFieldSaveAndInstall = false;
	formFieldDropCheck = false;
	formFieldRemoveOnJson = false;

	/**
	 * this form is for creating new table for JSON schema
	 */
	newTableForm = {
		tablename: '',
		primary_key: '',
		collation: 'utf8_general_ci',
		storage_engine: ''
	}

	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];
	}

	init(){
		this.selectedTableFields = null;
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

	resetForm(){
		this.formField = {
			name: '',
			type: '',
			length: '',
			default: '',
			default_value: '',
			attributes: '',
			collation: '',
			old_name: '',
			primary: false,
			comment: '',
			after: ''
		}
	}

	resetNewTableForm(){
		newTableForm = {
			tablename: '',
			primary_key: '',
			collation: 'utf8_general_ci',
			storage_engine: ''
		}
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
	setTableRows(module, tablename, tableValue=null){
		this.selectedTableName = tablename;
		this.selectedTableValue = tablename;
		this.selectedModule = module;
		this.selectedTableValue = null;

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
		if(field){
			this.setFormField();
		} else {
			this.resetForm();
		}
	}

	setFormField(){
		this.formField.default = 'NONE';
		this.formFieldDefaultValue = '';

		$.each(this.formField, (key, value) => {
			if(key == 'default'){
				if(typeof this.selectedTableField.default != 'undefined'){
					if(this.selectedTableField.default === 'CURRENT_TIMESTAMP'){
						this.formField.default = this.selectedTableField.default;
					}
					else if(this.selectedTableField.default === null) {
						this.formField.default = 'NULL';
					}
					else {
						this.formField.default = 'USER_DEFINED';
						this.formFieldDefaultValue = this.selectedTableField.default;
						console.log('formField formField formField', this.formFieldDefaultValue, this.selectedTableField.default);
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

		this.formField.default_value = this.formFieldDefaultValue;
		let jsonData = {
			module: this.selectedModule,
			tablename: this.selectedTableName,
			save_and_install: this.formFieldSaveAndInstall,
			fields: [this.formField]
		}

		this.loader.setLoader(true, 'Saving field...');

		this.request.getFormKey().then(formkey => {
			jsonData['form_key'] = formkey;

			let url = '/' + this.url.getRoute() + '/database/savefield';
			this.request.makeRequest(url, jsonData, 'POST', true).then(result => {
				if(!result.error && result.result){
					this.selectedTableFields = result.result;

					this.selectedTableFields.fields.forEach(field => {
						if(field.name == this.formField.name){
							this.setField(field);
							this.getModuleTables();
						}
					});

					this.toast.add('Field successfully saved.', 'Sucess');
					this.loader.reset();
				} else if(result.error && !result.result){
					this.toast.add(result.error.responseText, 'Error');
					this.loader.reset();
				}
			});
		});
	}

	dropField(e){
		e.preventDefault();

		let jsonData = {
			module: this.selectedModule,
			tablename: this.selectedTableName,
			fields: [this.formField],
			drop_check: this.formFieldDropCheck,
			remove_on_json: this.formFieldRemoveOnJson,
		}

		this.loader.setLoader(true, 'Dropping field...');

		this.request.getFormKey().then(formkey => {
			jsonData['form_key'] = formkey;
			let url = '/' + this.url.getRoute() + '/database/dropfield';
			this.request.makeRequest(url, jsonData, 'POST', true).then(result => {
				if(!result.error && result.result){
					if(typeof result.result.database_drop.column_success != 'undefined'){
						$.each(result.result.database_drop.column_success, (key, val) => {
							this.toast.add(val, 'Sucess');
						});
					}
					if(typeof result.result.database_drop.column_errors != 'undefined'){
						$.each(result.result.database_drop.column_errors, (key, val) => {
							this.toast.add(val, 'Error');
						});
					}
					if(typeof result.result.json_remove.success != 'undefined'){
						$.each(result.result.json_remove.success, (key, val) => {
							this.toast.add(val, 'Sucess');
						});
					}
					if(typeof result.result.json_remove.errors != 'undefined'){
						$.each(result.result.json_remove.errors, (key, val) => {
							this.toast.add(val, 'Error');
						});
					}
					
					this.setTableRows(this.selectedModule, this.selectedTableName);
					this.loader.reset();

				} else if(result.error && !result.result){
					this.toast.add(result.error.responseText, 'Error');
					this.loader.reset();
				}
			});
		});
	}

	/**
	 * create a new database table this will only create 
	 * the JSON schema and will not install the table directly
	 */
	createNewTable(e){
		e.preventDefault();

		let jsonData = {
			module: this.selectedModule,
			database_table: this.newTableForm
		}
		this.request.getFormKey().then(formkey => {
			jsonData['form_key'] = formkey;

			let url = '/' + this.url.getRoute() + '/database/newtablejsonschema';
			this.request.makeRequest(url, jsonData, 'POST', true).then(result => {
				if(!result.error && result.result){
					this.alltables = result.result;
					$('#newDatabaseTableModal').modal('hide');
					this.resetNewTableForm();
				}
				else {
					this.toast.add(result.error.responseText, 'Error');
				}
				this.loader.reset();
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


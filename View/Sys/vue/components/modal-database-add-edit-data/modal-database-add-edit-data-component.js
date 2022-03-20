if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class modalDatabaseAddEditDataComponent {

	request = null;
	url = null;
	loader = null;
	toast = null;

	databaseIndexIndexComponent = null;
	installDataSaveToDatabase = false

	formFields = {};
	targetFieldIndex = 'no';

	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];
	}

	init(){
		setTimeout(f => {
			this.loader = window['_vue']['loader-component'];
			this.toast = window['_vue']['toast-component'];
		}, 100);
	}

	resetFormFields(){
		this.formFields = {};
		this.targetFieldIndex = 'no';
		this.installDataSaveToDatabase = false;
	}

	/**
	 * set the form field based on the selected table
	 * and show field modal, set delay 100ms to ensure the 
	 * formFields model is set for later user during save
	 */
	setFormField(){
		this.resetFormFields();
		$.each(this.databaseIndexIndexComponent.selectedTableFields.fields, (key, val) => {
			this.formFields[val.name] = {
				value: '',
				option: {
					is_hashed: false,
					primary: typeof val.primary != 'undefined'  ? val.primary : false
				}
			};
		});
		console.log('setFormField setFormField', this.formFields);
		setTimeout(() => {
			$('#modalDatabaseAddEditData').modal('show');
		}, 100);
	}

	setEditFormField(data, index){
		this.resetFormFields();
		this.targetFieldIndex = index;
		$.each(data, (key, val) => {
			this.formFields[key] = {
				value: val.value,
				option: {
					is_hashed: false,
					primary: false
				}
			};
			if(typeof val.option != 'undefined'){
				if(typeof val.option.is_hashed != 'undefined'){
					this.formFields[key]['option']['is_hashed'] = val.option.is_hashed;
				}
				if(typeof val.option.primary != 'undefined'){
					this.formFields[key]['option']['primary'] = val.option.primary;
				}
			}
		});
		setTimeout(() => {
			$('#modalDatabaseAddEditData').modal('show');
		}, 100);
	}

	/**
	 * save the field into JSON files via API call
	 * @param {*} e 
	 */
	saveInsallData(e){
		e.preventDefault();


		let jsonData = {
			/** fields: this.request.stringToJson($(e.target).serialize()), */
			fields:  this.formFields,
			module: this.databaseIndexIndexComponent.selectedModule,
			tablename: this.databaseIndexIndexComponent.selectedTableName,
			save_to_database: this.installDataSaveToDatabase,
			target_field_index: this.targetFieldIndex
		}

		this.loader.setLoader(true, 'Saving installation data...');
		this.request.getFormKey().then(formkey => {
			jsonData['form_key'] = formkey;

			let url = '/' + this.url.getRoute() + '/database/saveinstalldata';
			this.request.makeRequest(url, jsonData, 'POST', true).then(result => {
				if(!result.error && result.result){	
					this.databaseIndexIndexComponent.setTableRows(
						this.databaseIndexIndexComponent.selectedModule, 
						this.databaseIndexIndexComponent.selectedTableName, 
						this.databaseIndexIndexComponent.selectedTableValue
					);

					$('#modalDatabaseAddEditData').modal('hide');
				} else if(result.error && !result.result){
					this.toast.add(result.error.responseText, 'Error');
				}
				this.loader.reset();
			});
		});
	}
}

window['_vue']['modal-database-add-edit-data-component'] = new modalDatabaseAddEditDataComponent();

Vue.component('modal-database-add-edit-data-component', {
	data: function(){
		return {
			vue: window['_vue']['modal-database-add-edit-data-component']
		}
	},
	mounted: function() {
		this.vue.databaseIndexIndexComponent = this.databaseIndexIndexComponent;
		this.vue.init();
	},
	props: ['databaseIndexIndexComponent'],
	template: `{{template}}`
});
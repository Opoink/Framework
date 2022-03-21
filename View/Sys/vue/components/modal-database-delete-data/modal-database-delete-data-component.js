if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class modalDatabaseDeleteDataComponent {

	request = null;
	url = null;
	loader = null;
	toast = null;

	databaseIndexIndexComponent = null;

	selectedData = {};

	formField = {
		target_field_index: 'no',
		remove_in_database: false
	}

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

	resetFormFields() {
		this.selectedData = {};
		this.formField = {
			target_field_index: 'no',
			remove_in_database: false
		}
	}

	setEditFormField(data, index){
		$.each(this.databaseIndexIndexComponent.selectedTableFields.fields, (key, val) => {
			if(typeof data[val.name] == 'undefined'){
				data[val.name] = {
					value: '',
					option: {
						is_hashed: false,
						primary: false
					}
				}
			}
		});
		this.selectedData = data;

		this.formField.target_field_index = index;
		$('#modalDatabaseDeleteInstallData').modal('show');
	}

	deleteInstallData(e){
		e.preventDefault();

		let jsonData = {
			module: this.databaseIndexIndexComponent.selectedModule,
			tablename: this.databaseIndexIndexComponent.selectedTableName,
			fields: this.selectedData,
			target_field_index: this.formField.target_field_index,
			remove_in_database: this.formField.remove_in_database
		}

		this.loader.setLoader(true, 'Deleting installation data...');
		this.request.getFormKey().then(formkey => {
			jsonData['form_key'] = formkey;

			let url = '/' + this.url.getRoute() + '/database/deleteinstalldata';

			this.request.makeRequest(url, jsonData, 'POST', true).then(result => {
				if(!result.error && result.result){	
					$.each(result.result, (key, val) => {
						this.toast.add(val, 'Success');
					});

					this.databaseIndexIndexComponent.setTableRows(
						this.databaseIndexIndexComponent.selectedModule, 
						this.databaseIndexIndexComponent.selectedTableName, 
						this.databaseIndexIndexComponent.selectedTableValue
					);

					this.resetFormFields();

					$('#modalDatabaseDeleteInstallData').modal('hide');
				} else if(result.error && !result.result){
					this.toast.add(result.error.responseText, 'Error');
				}
				this.loader.reset();
			});
		});

	}
}

window['_vue']['modal-database-delete-data-component'] = new modalDatabaseDeleteDataComponent();

Vue.component('modal-database-delete-data-component', {
	data: function(){
		return {
			vue: window['_vue']['modal-database-delete-data-component']
		}
	},
	mounted: function() {
		this.vue.databaseIndexIndexComponent = this.databaseIndexIndexComponent;
		this.vue.init();
	},
	props: ['databaseIndexIndexComponent'],
	template: `{{template}}`
});
if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class modalDatabaseAddEditDataComponent {

	request = null;
	databaseIndexIndexComponent = null;
	installDataSaveToDatabase = false

	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];
	}

	init(){
		setTimeout(f => {
			this.loader = window['_vue']['loader-component'];
			this.toast = window['_vue']['toast-component'];
		}, 500);
	}

	saveInsallData(e){
		e.preventDefault();

		let jsonData = {
			fields: this.request.stringToJson($(e.target).serialize()),
			module: this.databaseIndexIndexComponent.selectedModule,
			tablename: this.databaseIndexIndexComponent.selectedTableName,
			save_to_database: this.installDataSaveToDatabase
		}

		this.loader.setLoader(true, 'Saving installation data...');
		this.request.getFormKey().then(formkey => {
			jsonData['form_key'] = formkey;
			console.log(jsonData);

			let url = '/' + this.url.getRoute() + '/database/saveinstalldata';
			this.request.makeRequest(url, jsonData, 'POST', true).then(result => {
				if(!result.error && result.result){	
					console.log('saveInsallData saveInsallData saveInsallData', result.result);
		// 			this.selectedTableFields = result.result;
		// 			this.getModuleTables();
		// 			this.toast.add('Field successfully saved.', 'Sucess');
		// 			this.selectedTableValue.is_installed = true;

		// 			$('#saveDatabaseTableFieldsModal').modal('hide');
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
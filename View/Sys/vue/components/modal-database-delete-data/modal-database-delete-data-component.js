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
		targetFieldIndex: 'no',
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
			targetFieldIndex: 'no',
			remove_in_database: false
		}
		console.log('resetFormFields  resetFormFields');
	}

	setEditFormField(data, index){
		this.selectedData = data;
		this.formField.targetFieldIndex = index;
		console.log('setEditFormField setEditFormField', this.selectedData);
		$('#modalDatabaseDeleteInstallData').modal('show');
	}

	deleteInstallData(){

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
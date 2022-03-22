if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class commonDatabaseTableRelationViewComponent {

	url = null;
	request = null;
	loader = null;
	toast = null;

	formsRelation = [];

	databaseIndexIndex = null;

	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];

		setTimeout(f => {
			this.loader = window['_vue']['loader-component'];
			this.toast = window['_vue']['toast-component'];
		}, 500);
	}

	init(){
		this.formsRelation = [];
		this.addForm();
	}

	addForm(){
		this.formsRelation.push({
			tablename: this.databaseIndexIndex.selectedTableName,
			constraint_name: '',
			on_delete: 'ON DELETE RESTRICT',
			on_updated: 'ON UPDATE RESTRICT',
			column: '',
			reference_tablename: '',
			reference_column: '',
		});
	}

	saveConstraint(){
		let jsonData = {
			module: this.databaseIndexIndex.selectedModule,
			tablename: this.databaseIndexIndex.selectedTableName,
			constraints: []
		}

		$.each(this.formsRelation, (key, value) => {
			if(
				value.constraint_name != '' && 
				value.column != '' && 
				value.reference_tablename != '' && 
				value.reference_column != ''
			){
				value.tablename = this.databaseIndexIndex.selectedTableName;
				jsonData.constraints.push(value);
			}
		});

		this.loader.setLoader(true, 'Saving database relation...');
		this.request.getFormKey().then(formkey => {
			jsonData['form_key'] = formkey;
			let url = '/' + this.url.getRoute() + '/database/saveconstraint';
			this.request.makeRequest(url, jsonData, 'POST', true).then(result => {
				if(!result.error && result.result){
					// $('#databaseDropFieldModal').modal('hide');
					$.each(result.result.message, (key, value) => {
						this.toast.add(value, 'Success');
					});
					$.each(result.result.errors_message, (key, value) => {
						this.toast.add(value, 'Error');
					});

				} else if(result.error && !result.result){
					this.toast.add(result.error.responseText, 'Error');
				}
				this.loader.reset();
			});
		});
	}
}

window['_vue']['common-database-table-relation-view-component'] = new commonDatabaseTableRelationViewComponent();

Vue.component('common-database-table-relation-view-component', {
	data: function(){
		return {
			vue: window['_vue']['common-database-table-relation-view-component']
		}
	},
	mounted: function(){
		this.vue.databaseIndexIndex = window['_vue']['database-index-index-component'];
		this.vue.init();
	},
	template: `{{template}}`
});
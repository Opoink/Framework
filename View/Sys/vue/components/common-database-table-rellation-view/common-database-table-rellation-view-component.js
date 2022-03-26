if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class commonDatabaseTableRelationViewComponent {

	url = null;
	request = null;
	loader = null;
	toast = null;

	save_and_install = false;

	databaseIndexIndex = null;

	dropConstraintColumnForm = {
		column: null,
		drop_in_database: false,
		remove_in_json_file: false
	};

	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];

		setTimeout(f => {
			this.loader = window['_vue']['loader-component'];
			this.toast = window['_vue']['toast-component'];
		}, 500);
	}

	init(){
		console.log('init init init init');
		this.resetDropConstraintColumnForm();
	}

	addForm(){
		if(!this.databaseIndexIndex.selectedTableFields.table_relation){
			this.databaseIndexIndex.selectedTableFields.table_relation = [];
		}
		this.databaseIndexIndex.selectedTableFields.table_relation.push({
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
			save_and_install: this.save_and_install,
			constraints: []
		}

		$.each(this.databaseIndexIndex.selectedTableFields.table_relation, (key, value) => {
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
					$.each(result.result.message, (key, value) => {
						this.toast.add(value, 'Success');
					});
					$.each(result.result.errors_message, (key, value) => {
						this.toast.add(value, 'Error');
					});
					
					this.databaseIndexIndex.setTableRows(
						this.databaseIndexIndex.selectedModule, 
						this.databaseIndexIndex.selectedTableName, 
						this.databaseIndexIndex.selectedTableValue
					);
					this.resetSaveAndInstall();
					$('#modalDatabaseSaveRelationConfirmData').modal('hide');

				} else if(result.error && !result.result){
					this.toast.add(result.error.responseText, 'Error');
				}
				this.loader.reset();
			});
		});
	}

	resetSaveAndInstall(){
		this.save_and_install = false;
	}

	resetDropConstraintColumnForm(){
		this.dropConstraintColumnForm = {
			column: null,
			drop_in_database: false,
			remove_in_json_file: false
		};
	}
	
	/**
	 * set the column to drop constraint
	 */
	setDropConstraintColumn(column){
		this.resetDropConstraintColumnForm();
		this.dropConstraintColumnForm['column'] = column;
	}

	dropConstraint(){
		let jsonData = this.dropConstraintColumnForm;
		jsonData['module'] = this.databaseIndexIndex.selectedModule;

		this.loader.setLoader(true, 'Dropping database relation...');
		this.request.getFormKey().then(formkey => {
			jsonData['form_key'] = formkey;
			let url = '/' + this.url.getRoute() + '/database/dropconstraint';
			this.request.makeRequest(url, jsonData, 'POST', true).then(result => {
				if(!result.error && result.result){
					$.each(result.result.message, (key, value) => {
						this.toast.add(value, 'Success');
					});
					$.each(result.result.errors_message, (key, value) => {
						this.toast.add(value.message, 'Error');
					});

					this.databaseIndexIndex.setTableRows(
						this.databaseIndexIndex.selectedModule, 
						this.databaseIndexIndex.selectedTableName, 
						this.databaseIndexIndex.selectedTableValue
					);
					this.resetDropConstraintColumnForm();
					$('#modalDatabaseDropRelationConfirmData').modal('hide');

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
	beforeDestroy() {
		this.vue.init();
		// $('.common-database-tables-component .ptablename').unbind();
	},
	template: `{{template}}`
});
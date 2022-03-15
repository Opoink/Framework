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


	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];
	}

	init(){
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
		// this.request.getFormKey().then(formkey => {
		// 	this.form['form_key'] = formkey;
		// });
		let url = '/' + this.url.getRoute() + '/database?alltables=1';
		this.request.makeRequest(url, '', 'GET', true)
		.then(result => {
			if(!result.error && result.result){
				this.alltables = result.result;
				console.log(this.alltables);
			} else if(result.error && !result.result){
				this.toast.add(result.error.responseText, 'Error');
			}
		});
	}

	setTableRows($fields){
		console.log('setTableRows setTableRows', $fields);
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
	template: '{{template}}'
});

vueRouter.addRoutes([{ 
	path: '/'+sysUrl+'/database', 
	component: databaseIndexIndexComponent, 
	name: 'database-index-index' 
}]);


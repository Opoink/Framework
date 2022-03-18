if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

Vue.component('common-database-tables-component', {
	data: function(){
		return {
			vue: null
		}
	},
	mounted: function(){
		setTimeout(() => {
			/**
			 * we need to user database-index-index-component 
			 * as vue data provider here
			 * we will not give this component its own data provider
			 */
			this.vue = window['_vue']['database-index-index-component'];
		}, 100);
	},
	template: `{{template}}`
});
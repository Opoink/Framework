if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class indexIndexIndex {

	mainHeader;

	constructor(){
	}

	init(){
		setTimeout(f => {
			this.mainHeader = window['_vue']['mainheader-component'];
			this.mainHeader.pageTitle = 'Opoink Dashboard';
		}, 500);
	}
}

window['_vue']['index-index-index-component'] = new indexIndexIndex();

let indexIndexIndexComponent = Vue.component('index-index-index-component', {
	data: function(){
		return {
			vue: window['_vue']['index-index-index-component'] 
		}
	},
	beforeRouteEnter: function(to, from, next) {
		let data = window['_vue']['index-index-index-component'];
		data.init();
		next();
	},
	template: `{{template}}`
});

vueRouter.addRoutes([{ 
	path: '/'+sysUrl, 
	component: indexIndexIndexComponent, 
	name: 'index-index-index' 
}]);
if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class indexIndexIndex {

}

window['_vue']['index-index-index-component'] = new indexIndexIndex();

let indexIndexIndexComponent = Vue.component('index-index-index-component', {
	data: function(){
		return {
			vue: window['_vue']['index-index-index-component'] 
		}
	},
	template: '{{template}}'
});

vueRouter.addRoutes([{ 
	path: '/system', 
	component: indexIndexIndexComponent, 
	name: 'index-index-index' 
}]);

console.log('window', Vue);
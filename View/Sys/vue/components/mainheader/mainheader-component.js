Vue.component('mainheader', {
	props: ['mainheader'],
	template: '{{template}}'
});


if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class mainheader {
	pageTitle = '';
}

window['_vue']['mainheader-component'] = new mainheader();

Vue.component('mainheader-component', {
	data: function(){
		return {
			vue: window['_vue']['mainheader-component']
		}
	},
	template: `{{template}}`
});
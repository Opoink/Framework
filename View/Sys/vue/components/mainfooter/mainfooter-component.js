Vue.component('mainfooter', {
	props: ['mainfooter'],
	template: '{{template}}'
});




Vue.component('mainfooter', {
	props: ['mainfooter'],
	template: '{{template}}'
});


if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class mainfooterComponent {

	version = {
		app: 'opoink/opoink-app-2 v1.0.0',
		opoink_framework: 'opoink/framework v1.0.0',
		opoink_cli: 'opoink/cli v1.0.0',
		opoink_template: 'opoink/template v1.0.0',
		opoink_router: 'opoink/router v1.0.0',
	}

	constructor(){}

	getVersion(){
		return new Promise(resolve => {

		});
	}
}

window['_vue']['mainfooter-component'] = new mainfooterComponent();

Vue.component('mainfooter-component', {
	data: function(){
		return {
			vue: window['_vue']['mainfooter-component']
		}
	},
	beforeMount: function(){
	},
	template: `{{template}}`
});
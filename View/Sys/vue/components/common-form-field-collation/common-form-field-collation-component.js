if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class commonFormFieldCollationComponent {
	
}

window['_vue']['common-form-field-collation-component'] = new commonFormFieldCollationComponent();

Vue.component('common-form-field-collation-component', {
	data: function(){
		return {
			vue: window['_vue']['common-form-field-collation-component'] 
		}
	},
	props: ['formModel', 'formid'],
	template: `{{template}}`
});
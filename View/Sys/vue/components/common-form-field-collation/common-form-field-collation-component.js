if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class commonFormFieldCollationComponent {
	messages = [];
	add(message, type='Success', timeout=8000){
		if(message){
			this.messages.push({
				type: type, 
				message: message
			});
			setTimeout(f =>{
		      this.deleteMessage(0);
		    }, timeout);
		}
	};
	deleteMessage(key){
		this.messages.splice(key, 1);
	};
}

window['_vue']['common-form-field-collation-component'] = new commonFormFieldCollationComponent();

Vue.component('common-form-field-collation-component', {
	data: function(){
		return {
			toast: window['_vue']['common-form-field-collation-component'] 
		}
	},
	props: ['formModel', 'formid'],
	template: `{{template}}`
});
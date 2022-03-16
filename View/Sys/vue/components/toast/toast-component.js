if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class toast {
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

window['_vue']['toast-component'] = new toast();

Vue.component('toast-component', {
	data: function(){
		return {
			toast: window['_vue']['toast-component'] 
		}
	},
	template: `{{template}}`
});
if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class sidenav {
	
}

window['_vue']['sidenav-component'] = new sidenav();

Vue.component('sidenav-component', {
	data: function(){
		return {
			vue: window['_vue']['sidenav-component'],
			url: window['_url'],
			isShowChildNav: false,
			childToDisplay: '',
			showChildNav: function(childToDisplay){
				this.isShowChildNav = true;
				this.childToDisplay = childToDisplay;
			},
			closeChildNav(){
				this.isShowChildNav = false;
				this.childToDisplay = '';
			}
		}
	},
	template: '{{template}}'
});
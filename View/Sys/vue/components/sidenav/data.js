{
	isShowChildNav: false,
	childToDisplay: '',
	showChildNav(childToDisplay){
		this.isShowChildNav = true;
		this.childToDisplay = childToDisplay;
	},
	closeChildNav(){
		this.isShowChildNav = false;
		this.childToDisplay = '';
	}
}
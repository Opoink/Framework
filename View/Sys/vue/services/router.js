{
	path: null,
	sysControllers: [],
	init(){
		this.path = window.location.pathname;
		return this.setSystemController().getPageComponent();
	},
	getPageName(){
	},
	getRoute(){
		let pathArray = this.path.substring(1).split('/');
		if(typeof pathArray[0] != 'undefined'){
			return pathArray[0];
		} else {
			return 'system';
		}
	},
	setSystemController(){
		let sysRoute = this.getRoute();
		this.sysControllers[sysRoute + '_index_index'] = 'systemindexindex';
		this.sysControllers[sysRoute + '_login_index'] = 'opoinkloginindex';
		this.sysControllers[sysRoute + '_install_index'] = 'opoinkinstall';
		this.sysControllers[sysRoute + '_settings_index'] = 'systemsettingsindex';
		return this;
	},
	getPageComponent(){
		if(typeof this.sysControllers[pageConfig.name] != 'undefined'){
			return this.sysControllers[pageConfig.name];
		} else {
			return null;
		}
	}
}
class systemcacheindex {
	selectAllVall = 0;
	cache_services = {
		less: 0,
		deployed_files: 0,
		xml: 0,
		database: 0,
	};
	cache = {};
	init(){
		this.getCache();
	};
	getCache(){
		_vue.request.makeRequest('/'+_vue.url.getRoute()+'/cache/action', '', 'GET').then(status => {
			if(!status.error && status.result){
				this.cache = status.result;
			}
		});
	};
	selectAll(){
		this.cache_services.less = this.selectAllVall;
		this.cache_services.deployed_files = this.selectAllVall;
		this.cache_services.xml = this.selectAllVall;
		this.cache_services.database = this.selectAllVall;
	};
	openModalConfirm(){
		_vue.modalconfirm.modalTitle = 'Purge Cache';
		_vue.modalconfirm.modalContent = '<p class="fw-400">Are you sure you want to purge cache of the selected item/s?</p>';
		_vue.modalconfirm.modalAction = 'Purge Now';
		_vue.modalconfirm.show();
		_vue.modalconfirm.callback = (f => {
			this.purge()
		});
	};
	purge(){
		let toPurgeArray = [];
		if(this.cache_services.less){
			toPurgeArray.push('less');
		}
		if(this.cache_services.deployed_files){
			toPurgeArray.push('deployed_files');
		}
		if(this.cache_services.xml){
			toPurgeArray.push('xml');
		}
		if(this.cache_services.database){
			toPurgeArray.push('database');
		}
		this.purgeArray(toPurgeArray, 0);
	  	_vue.modalconfirm.hide();
	};
	purgeArray(array, index){
		let count = array.length - 1;
		if(index <= count ){
			this.purgeHelper(array[index]).then(p => {
				let n = index + 1;
				if(n <= count){
					this.purgeArray(array, n);
				} 
				if(index == count){
					_vue.loader.text = 'Loading new cache...';
					_vue.loader.isLoading = true;
					setTimeout(f => {
						this.getCache();
					}, 3000);
				}
			});
		}
	};
	purgeHelper(type){
		_vue.loader.text = 'Purging ' + type + ' cache...';
		return new Promise(p => {
			_vue.request.getFormKey().then(formkey => {
				let jsonData = {
					cache_services: {},
					form_key: formkey
				};

				jsonData.cache_services[type] = type;

				_vue.request.makeRequest('/'+_vue.url.getRoute()+'/cache/action', jsonData).then(purged => {
					if(!purged.error && purged.result){
						_vue.toast.add(purged.result.message);
					}
					p(purged);
				});
			});
		})
	}
}
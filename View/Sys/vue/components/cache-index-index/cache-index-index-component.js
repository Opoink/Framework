if(typeof window['_vue'] == 'undefined'){
	window['_vue'] = {};
}

class cacheIndexIndex {

	selectAllVall = 0;
	cache_services = {
		less: 0,
		deployed_files: 0,
		xml: 0,
		database: 0,
	};
	cache = {};

	mainHeader;
	request;
	url;
	toast;

	constructor(){
		this.request = window['opoink_system']['_request'];
		this.url = window['opoink_system']['_url'];
	}

	init(){
		setTimeout(f => {
			this.mainHeader = window['_vue']['mainheader-component'];
			this.mainHeader.pageTitle = 'Opoink System Cache';
			this.loader = window['_vue']['loader-component'];
			this.toast = window['_vue']['toast-component'];

			this.getCache()
		}, 500);
	}

	getCache(){
		this.request.makeRequest('/'+this.url.getRoute()+'/cache/action', '', 'GET').then(status => {
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
		$('#purge-chache-confirm').modal('show');
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
		$('#purge-chache-confirm').modal('hide');
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
					this.loader.text = 'Loading new cache...';
					this.loader.isLoading = true;
					setTimeout(f => {
						this.getCache();
						this.loader.reset();
					}, 3000);
				}
			});
		}
	};
	purgeHelper(type){
		this.loader.text = 'Purging ' + type + ' cache...';
		return new Promise(p => {
			this.request.getFormKey().then(formkey => {
				let jsonData = {
					cache_services: {},
					form_key: formkey
				};

				jsonData.cache_services[type] = type;

				this.request.makeRequest('/'+this.url.getRoute()+'/cache/action', jsonData).then(purged => {
					if(!purged.error && purged.result){
						this.toast.add(purged.result.message);
					}
					p(purged);
				});
			});
		})
	}
}

window['_vue']['cache-index-index-component'] = new cacheIndexIndex();

let cacheIndexIndexComponent = Vue.component('cache-index-index-component', {
	data: function(){
		return {
			vue: window['_vue']['cache-index-index-component'] 
		}
	},
	beforeRouteEnter: function(to, from, next) {
		document.title = 'System Cache';
		let data = window['_vue']['cache-index-index-component'];
		data.init();
		next();
	},
	template: `{{template}}`
});

vueRouter.addRoutes([{ 
	path: '/'+sysUrl+'/cache', 
	component: cacheIndexIndexComponent,
	name: 'cache-index-index' 
}]);
class modalconfirm {
	modalTitle = '';
	modalContent = '';
	modalClose = '';
	modalAction = '';
	callback = null;
	show(){
        jQuery.noConflict();
		$('#modalconfirmModalCenter').modal('show');
	};
	hide(){
        jQuery.noConflict();
		$('#modalconfirmModalCenter').modal('hide');
	};
}
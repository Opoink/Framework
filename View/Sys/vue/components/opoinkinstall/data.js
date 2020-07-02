{
	buttonName: {
		'step1': 'Agree and Continue',
		'step2': 'Check Now',
		'step3': 'Save Database'
	},
	currentStep: 1,
	goTo: function(step){
		this.currentStep = parseInt(step) + 1;
	}
}
{
	counter: 0,
	test: function(){
		return 'testing ' + this.counter;
	},
	add: function(){
		this.counter++;
	},
	minus: function(){
		this.counter--;
	}
}
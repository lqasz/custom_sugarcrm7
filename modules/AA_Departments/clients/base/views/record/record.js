({
	extendsFrom: "RecordView",

	initialize: function(options) {
		var self = this;

		if(App.user.id != "ada95982-6143-43d9-e3ae-540f494996bf" && App.user.id != "1") {
			$('<style>'+
	        '.subpanel-controls.btn-toolbar.pull-right { display: none; }'+
	    	'</style>').appendTo('head');
		}

	    this._super('initialize', [options]);
	    this.context.on('button:delete_button:click', this.deleteClicked, this);

	    if(self.model.attributes.id == "4d4281ae-106b-b8dd-d888-56a5b788ca8d") {
    		$('.record-cell[data-name="assigned_user_name"]').hide(0);
    	}
	},

	render: function() {
		this._super('render');
        $(document).find('.main-pane').addClass('hideFilterView');
	}
})

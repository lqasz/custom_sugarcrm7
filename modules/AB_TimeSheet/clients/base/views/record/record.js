({
  	extendsFrom: "RecordView",
  	teamMembers: [],
  	view: undefined,

	initialize: function(options) {
		this._super('initialize', [options]);

		this.view = options.context.get("action");
		var self = this;
		self.collection.on('data:sync:complete', function() {
			self.teamMembers = JSON.parse(self.model.get("subordinates_c"));
			self.model.trigger('rebuildFields');
		}, self);

		self.model.on('rebuildFields', function() {
			self.render();
		});
	},

	render: function() {
		this._super('render');

		console.info(this.view);
		if(!_.isEmpty(this.teamMembers)) {
			$('.record-cell[data-name="subordinates_c"]').html(this.rebuildSubordinatiesField());
		}
	},

	rebuildSubordinatiesField: function() {
		var self = this,
			string = '<div class="span12"><table>';
		
		_.each(self.teamMembers, function(el, user) {
			string += '<tr><td>';
			string += '<div class="ellipsis_inline span12">'+user+'</div>';
			string += '<div class="span12 first">';
			string += '<ul>';

			_.each(el, function(procent, projectNumber) {
				string += '<li class="span12 first"><div class="span7">'+projectNumber+'</div><div class="span4">'+procent+'</div></li>';
			});

			string += '</ul>';
			string += '</div>';
			string += '</td></tr>';
		});

		string += '</table></div>';

		return string;
	},

	editClicked: function() {
        this.view = "edit";
        this._super('editClicked');
    },

    cancelClicked: function() {
        this.view = "detail";
        this._super('cancelClicked');
    },
})
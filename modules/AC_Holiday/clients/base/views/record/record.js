({
  	extendsFrom: "RecordView",
  	disabledFields: [],
  	holidayView: undefined,

	initialize: function(options) {
		this._super('initialize', [options]);

		var self = this;
		self.holidayView = options.context.get("action");
		self.collection.on('data:sync:complete', function() {
			app.api.call('GET', "index.php?entryPoint=getData&getUserTeam=1&userID="+ app.user.get('id'), null,{
				success: _.bind(function(data) {					
					switch(data) {
	                	case 0:
	                		self.disabledFields.push('fa_c', 'supervisor', 'board', 'rejected_c', 'sick_leave', 'withdrawal_of_leave_c');
	                		break;
	                	case 1:
	                		self.disabledFields.push('fa_c', 'board', 'sick_leave', 'withdrawal_of_leave_c');
	                		break;
	                	case 2:
	                		if(app.user.get('id') != "ada95982-6143-43d9-e3ae-540f494996bf") { self.disabledFields.push('supervisor'); }
	                		self.disabledFields.push('board', 'rejected_c');
	                		break;
	                	case 3:
	                		self.disabledFields.push('fa_c', 'supervisor', 'sick_leave', 'withdrawal_of_leave_c');
	                		break;
	                }

	                if(self.holidayView == "edit") {
	                	self.disableFields();
	                }
				}, self)
			});
		}, self);
	},
	render: function() {
		this._super('render');
		$('span.record-edit-link-wrapper[data-name="team_name"]').parent().parent().addClass('hide');
        $(document).find('.main-pane').addClass('hideFilterView');
	},

	editClicked: function() {
		this._super('editClicked');
		this.disableFields();
	},

	disableFields: function() {
		if(!_.isEmpty(this.disabledFields)) {
        	_.each(this.disabledFields, function(el) {
        		var checked = (self.model.get(el)) ? "checked": "",
        			string = '<span class="normal index" data-fieldname="'+el+'" data-index="">'+
                     			'<span class="detail">'+
									'<input type="checkbox" '+checked+' disabled>'+
								'</span>'+
                            '</span>';

        		$('.record-cell[data-name="'+el+'"]').removeClass('edit');
        		$('.record-cell[data-name="'+el+'"]').children(".normal").replaceWith(string);
        	});
        }
	},
})
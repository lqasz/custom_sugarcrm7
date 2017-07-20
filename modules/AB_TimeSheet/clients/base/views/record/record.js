({
  	extendsFrom: "RecordView",
  	teamMembers: [],
  	view: undefined,
  	events: _.extend({}, this.events, {
        'change .slider': 'setSliderTextValue',
        'blur .slider-text': 'setSliderValue',
        'click a[name="cancel_button"]': 'cancelClicked',
        'click .add-project': 'addProjectRow',
        'keyup .project-name': 'searchProjectByName',
        'focus .project-name': 'searchProjectByName',
        'click .project-item': 'setProjectItem',
        // 'focusout .project-name': 'closeProjectList',
    }),

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

		if(!_.isEmpty(this.teamMembers)) {
			$('.record-cell[data-name="subordinates_c"]').html(this.rebuildSubordinatiesField());
		}
	},

	rebuildSubordinatiesField: function() {
		var self = this,
			string = '<div class="span12"><table class="span12">';
		
		_.each(self.teamMembers, function(el, user) {
			string += '<tr class="span12 first"><td>';
			string += '<div class="span12"><div class="ellipsis_inline span7">'+user+'</div>';
			
			if(self.view == "edit") { string += '<div class="span1 first"><a class="add-project"><i class="fa-plus fa"></i></a></div>'; }

			string += '</div><div class="span12 first">';
			string += '<ul class="project-list">';

			_.each(el, function(procent, projectName) {
				var projectText = "",
					procentText = "";

				if(self.view == "edit") {
					projectText = '<div class="span3 test"><input class="project-name" type="text" value="'+projectName+'" /><ul class="select2-results list-of-projects hide"></ul></div>';
					procentText = '<div class="span3"><input class="slider" type="range" value="'+procent+'" /></div><div class="span1"><input type="text" class="slider-text" value="'+procent+'" /></div>';
				} else {
					projectText = '<div class="span7 first">'+projectName+'</div>';
					procentText = '<div class="span4 first">'+procent+'%</div>';
				}

				string += '<li class="span12 first"><div class="span12 project-row">'+projectText+procentText+'</div></li>';
			});

			string += '</ul>';
			string += '</div>';
			string += '</td></tr>';
		});

		string += '</table></div>';
		return string;
	},

	addProjectRow: function(e) {
		var projectText = '<div class="span3"><input class="project-name" type="text" value="" /><ul class="select2-results list-of-projects hide"></ul></div>',
			procentText = '<div class="span3"><input class="slider" type="range" value="0" /></div><div class="span1"><input type="text" class="slider-text" value="0"/></div>',
			string = '<li class="span12 first"><div class="span12 project-row">'+projectText+procentText+'</div></li>';

		$(e.currentTarget).parent().parent().parent().find('.project-list').append(string);
	},

	setSliderTextValue: function(e) {
		var $element = $(e.currentTarget);
		$element.parent().parent().find('.slider-text').val($element.val());
	},

	setProjectItem: function(e) {
		e.preventDefault();
		e.stopPropagation();

		var $element = $(e.currentTarget);

		$element.parent().next('.project-name').val($element.text());
		$element.parent().addClass('hide');
	},

	closeProjectList: function(e) {
		var $list = $(e.currentTarget).find('.select2-results');

		$list.html("");
		$list.addClass('hide');
	},

	setSliderValue: function(e) {
		var $element = $(e.currentTarget);
		$element.parent().parent().find('.slider').val($element.val());
	},

	searchProjectByName: function(e) {
		var results = [],
			$input = $(e.currentTarget),
			toSearch = $input.val(),
			$list = $input.next('.select2-results');

		$list.html("");

		var objects = {
			"0": "170113",
			"1": "170050",
			"2": "170033",
			"3": "170023",
			"4": "170103",
			"5": "170022"
		};

		for(key in objects) {
			if(objects[key].indexOf(toSearch)!=-1) {
				results.push(objects[key]);
			}
		}

		if(!_.isEmpty(results)) {
			_.each(results, function(el) {
				var listItem = '<li class="project-item">'+el+'</li>';
				$list.append(listItem);
			});

			$input.addClass('big-element');
			$list.removeClass('hide');
		}
	},

	editClicked: function() {
        this.view = "edit";
        this.render();
        this._super('editClicked');
    },

    cancelClicked: function() {
        this.view = "detail";
        this.render();
        this._super('cancelClicked');
    },

    saveClicked: function() {
    	
    },
})
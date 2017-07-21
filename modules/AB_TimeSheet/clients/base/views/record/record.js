({
  	extendsFrom: "RecordView",
  	dataFetched: [],
  	updatedData: [],
  	listOfProjects: [],

  	view: undefined,
  	events: _.extend({}, this.events, {
        'change .slider': 'setSliderTextValue',
        'blur .slider-text': 'setSliderValue',
        'click a[name="cancel_button"]': 'cancelClicked',
        'click .add-project': 'addProjectRow',
        'keyup .project-name': 'searchProjectByName',
        'focus .project-name': 'searchProjectByName',
        'click .project-item': 'setProjectItem',
        'keyup .project-item': 'moveOverProjectItem',
        // 'focusout .project-name': 'closeProjectList',
    }),

	initialize: function(options) {
		this._super('initialize', [options]);

		this.view = options.context.get("action");
		var self = this;
		self.collection.on('data:sync:complete', function() {
			self.dataFetched = JSON.parse(self.model.get("subordinates_c"));

			// get all data from db
            app.api.call('GET', 'index.php?entryPoint=getData&getAllProjects=1', null,{
                success: _.bind(function(data) {
                    self.listOfProjects = data;
                    self.model.trigger('rebuildFields'); // trigger event in model
                })
            });
		}, self);

		self.model.on('rebuildFields', function() {
			self.render();
		});
	},

	render: function() {
		this._super('render');

		if(!_.isEmpty(this.dataFetched)) {
			$('.record-cell[data-name="subordinates_c"]').html(this.rebuildSubordinatiesField());
		}
	},

	rebuildSubordinatiesField: function() {
		var self = this,
			string = '<div class="span12"><table class="span12">';
		
		_.each(self.dataFetched, function(el, user) {
			string += '<tr class="span12 first"><td>';
			string += '<div class="span12" data-name="employee-name"><div class="ellipsis_inline span7">'+user+'</div>';
			
			if(self.view == "edit") { string += '<div class="span1 first"><a class="add-project"><i class="fa-plus fa"></i></a></div>'; }

			string += '</div><div class="span12 first">';
			string += '<ul class="project-list">';

			_.each(el, function(procent, projectName) {
				var projectText = "",
					procentText = "";

				if(self.view == "edit") {
					projectText = '<div class="span7" data-name="project-name"><input class="project-name" type="text" value="'+projectName+'" /><ul class="select2-results list-of-projects hide"></ul></div>';
					procentText = '<div class="span3"><input class="slider" type="range" value="'+procent+'" /></div><div class="span1" data-name="procent"><input type="text" class="slider-text procent-value" value="'+procent+'" /></div>';
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
		var projectText = '<div class="span7"><input class="project-name" type="text" value="" /><ul class="select2-results list-of-projects hide"></ul></div>',
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

		$element.parent().parent().find('.project-name').val($element.text());
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

		for(key in this.listOfProjects) {
			if(this.listOfProjects[key].indexOf(toSearch)!=-1) {
				results.push(this.listOfProjects[key]);
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

	moveOverProjectItem: function(e) {
		console.info(e)
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
    	var self = this;

    	$('div[data-name="employee-name"]').each(function(i, that) {
    		var employeeName = $(that).find('.ellipsis_inline').text(),
    			$parent = $(that).parent().parent();

    		self.updatedData[employeeName] = [];
    		$parent.find('.project-row').each(function(j, el) {
    			var projectName = $(el).find('.project-name').val(),
    				procent = $(el).find('.procent-value').val();

    			self.updatedData[employeeName].push({
    				'projectName': projectName,
    				'procent': procent
    			});
    		});
    	});
    },
})
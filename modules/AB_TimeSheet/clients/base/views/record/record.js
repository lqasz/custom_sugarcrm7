({
  	extendsFrom: "RecordView",
  	dataFetched: {},
  	updatedData: {},
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

			// get all data from db
            app.api.call('POST', 'index.php?entryPoint=getData&getAllTimeSheetData=1&time_sheet_id='+self.model.get('id'), null,{
                success: _.bind(function(data) {
                	console.info(data);
                	self.dataFetched = data.time_sheet;
                    self.listOfProjects = data.projects;
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
			string = '';
		
		_.each(self.dataFetched.data, function(el, userID) {
			string += '<div class="span12 first">';
			string += '<div class="span12 first" data-name="employee-name" data-id="'+userID+'"><div class="ellipsis_inline span7">'+self.dataFetched.users[userID]+'</div>';
			
			if(self.view == "edit") { string += '<div class="span1 first"><a data-id="'+userID+'" class="add-project"><i class="fa-plus fa"></i></a></div>'; }

			string += '</div><div class="span12 first">';
			string += '<ul class="project-list">';

			_.each(el, function(projectData, projectID) {
				var projectText = "",
					procentText = "";

				if(self.view == "edit") {
					projectText = '<div class="span7" data-name="project-name"><input class="project-name" type="text" value="'+self.listOfProjects[projectID]+'" /><ul class="select2-results list-of-projects hide"></ul></div>';
					procentText = '<div class="span3"><input class="slider" type="range" value="'+projectData.value+'" /></div><div class="span1" data-name="procent"><input type="text" class="slider-text procent-value" value="'+projectData.value+'" /></div>';
				} else {
					projectText = '<div class="span7 first">'+self.listOfProjects[projectID]+'</div>';
					procentText = '<div class="span3 first">'+projectData.value+'%</div>';
				}

				string += '<li class="span12 first"><div data-id="'+projectID+'" data-userid="'+userID+'" class="span12 project-row">'+projectText+procentText+'</div></li>';
			});

			string += '</ul>';
			string += '</div>';
			string += '</div>';
		});

		return string;
	},

	addProjectRow: function(e) {
		var projectText = '<div class="span7"><input class="project-name" type="text" value="" /><ul class="select2-results list-of-projects hide"></ul></div>',
			procentText = '<div class="span3"><input disabled class="slider" type="range" value="0" /></div><div class="span1"><input disabled type="text" class="slider-text" value="0"/></div>';
		
		var projectData = $(e.currentTarget).data(),
			string = '<li class="span12 first" data-userid="'+projectData.id+'"><div class="span12 project-row">'+projectText+procentText+'</div></li>';

		$(e.currentTarget).parent().parent().parent().find('.project-list').append(string);
	},

	setSliderTextValue: function(e) {
		var $element = $(e.currentTarget);
			$element.parent().parent().find('.slider-text').val($element.val()),
			projectData = ($element.parent().parent().parent()).data(),
			object = {};

		object['deleted'] = 0;
		object['value'] = $element.val();

		if(!_.isEmpty(this.dataFetched.data[projectData.userid])) {
			if(!_.isEmpty(this.dataFetched.data[projectData.userid][projectData.id])) {
				object['updated'] = 1;
			}
			
			this.dataFetched.data[projectData.userid][projectData.id] = object;
		} else {
			this.dataFetched.data[projectData.userid] = {};
			this.dataFetched.data[projectData.userid][projectData.id] = object;
		}
	},

	setProjectItem: function(e) {
		e.preventDefault();
		e.stopPropagation();

		var $element = $(e.currentTarget);

		$element.parent().parent().parent().find('.slider').removeAttr('disabled');
		$element.parent().parent().parent().find('.slider-text').removeAttr('disabled');
		$element.parent().parent().find('.project-name').val($element.text());
		$element.parent().addClass('hide');

		var projectID = Object.keys(this.listOfProjects).find(key => this.listOfProjects[key] === $element.text());
		$element.parent().parent().parent().parent().attr('data-id', projectID);
	},

	closeProjectList: function(e) {
		var $list = $(e.currentTarget).find('.select2-results');

		$list.html("");
		$list.addClass('hide');
	},

	setSliderValue: function(e) {
		var $element = $(e.currentTarget);
			$element.parent().parent().find('.slider').val($element.val()),
			projectData = ($element.parent().parent().parent()).data(),
			object = {};

		object[projectData.id] = {};
		object[projectData.id]['deleted'] = 0;
		object[projectData.id]['value'] = $element.val();
		this.dataFetched.data[projectData.userid] = object;
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

    	console.info("dataFetched: ", self.dataFetched);
    	// $('div[data-name="employee-name"]').each(function(i, that) {
    	// 	var employeeName = $(that).find('.ellipsis_inline').text(),
    	// 		$parent = $(that).parent().parent();

    	// 	var iter = 0;
    	// 	self.updatedData[employeeName] = {};
    	// 	$parent.find('.project-row').each(function(j, el) {
    	// 		var projectName = $(el).find('.project-name').val(),
    	// 			procent = $(el).find('.procent-value').val(),
    	// 			object = {};

    	// 		object[projectName] = procent;
    	// 		self.updatedData[employeeName][iter] = object;

    	// 		iter++;
    	// 	});
    	// });

    	// $.ajax({
     //        url: 'index.php?entryPoint=getData&updateTimeSheet=1&time_sheet_id='+ self.model.get('id')+'&noCache='+ (new Date().getTime()),
     //        type: 'POST',
     //        data: {
     //        	updated: self.updatedData,
     //        },
     //        success: function(data) {
     //            self.view = "detail";
     //            self._super('saveClicked');
     //        },
     //    }); // ajax
    },
})
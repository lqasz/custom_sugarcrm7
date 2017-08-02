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
        'click .remove-project': 'removeProjectRow',
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
		
		_.each(self.dataFetched.data, function(timeSheet, userID) {
			string += '<div class="span12 first user-records">';
			string += '<div class="span12 first" data-name="employee-name" data-userid="'+userID+'"><div class="ellipsis_inline span7">'+self.dataFetched.users[userID]+'</div>';
			
			if(self.view == "edit") { string += '<div class="span1 first"><a data-userid="'+userID+'" class="add-project"><i class="fa-plus fa"></i></a></div>'; }

			string += '</div><div class="span12 first">';
			string += '<ul class="project-list">';

			_.each(timeSheet, function(projectData, timeSheetID) {
				if(_.isEmpty(projectData.deleted) && projectData.deleted == 0) {	
					var projectText = "",
						procentText = "";

					if(self.view == "edit") {
						projectText = '<div class="span4" data-name="project-name"><input class="project-name" type="text" value="'+self.listOfProjects[projectData.id]+'" /><ul class="select2-results list-of-projects hide"></ul></div>';
						procentText = '<div class="span3"><input class="slider" type="range" value="'+projectData.value+'" /></div><div class="span1" data-name="procent"><input type="text" class="slider-text procent-value" value="'+projectData.value+'" /></div>';
						procentText += '<div class="span1"><a data-id="'+timeSheetID+'" class="remove-project"><i class="fa-remove fa red-color"></i></a></div>';
					} else {
						projectText = '<div class="span4" data-projectid="'+projectData.id+'">'+self.listOfProjects[projectData.id]+'</div>';
						procentText = '<div class="span3"><input class="slider" type="range" value="'+projectData.value+'" disabled /></div><div class="span1">'+projectData.value+'%</div>';
					}

					string += '<li class="span12 first timesheet-row"><div data-id="'+timeSheetID+'" data-userid="'+userID+'" class="span12 project-row">'+projectText+procentText+'</div></li>';
				}
			});

			string += '</ul>';
			string += '</div>';
			string += '</div>';
		});

		return string;
	},

	addProjectRow: function(e) {
		var timeSheetID = app.utils.generateUUID(),
			projectData = $(e.currentTarget).data(),
			projectText = '<div class="span4"><input class="project-name" type="text" value="" /><ul class="select2-results list-of-projects hide"></ul></div>',
			procentText = '<div class="span3"><input disabled class="slider" type="range" value="0" /></div><div class="span1"><input disabled type="text" class="slider-text" value="0"/></div>',
			deleteIcon = '<div class="span1"><a data-id="'+timeSheetID+'" class="remove-project"><i class="fa-remove fa red-color"></i></a></div>',
			string = '<li class="span12 first timesheet-row"><div data-id="'+timeSheetID+'" data-userid="'+projectData.userid+'" class="span12 project-row">'+projectText+procentText+deleteIcon+'</div></li>';

		$(e.currentTarget).parents('.user-records').find('.project-list').append(string);

		this.dataFetched.data[projectData.userid][timeSheetID] = {
			'id': '',
			'deleted': 0,
			'value': 0,
			'new': 1
		};
	},

	removeProjectRow: function(e) {
		var $projectRow = $(e.currentTarget).parents('.project-row'),
			projectData = $projectRow.data();

		if(this.dataFetched.data[projectData.userid][projectData.id]['new'] == undefined) {
			this.dataFetched.data[projectData.userid][projectData.id]['deleted'] = 1;
		} else {
			delete this.dataFetched.data[projectData.userid][projectData.id];
		}

		$projectRow.parent().remove();
	},

	setSliderTextValue: function(e) {
		var $element = $(e.currentTarget),
			$parentElement = $element.parents('.project-row'),
			projectData = $parentElement.data();

		$parentElement.find('.slider-text').val($element.val());

		if(this.dataFetched.data[projectData.userid][projectData.id]['new'] == undefined) {
			this.dataFetched.data[projectData.userid][projectData.id]['updated'] = 1;
		}

		this.dataFetched.data[projectData.userid][projectData.id]['value'] = $element.val();
	},

	setSliderValue: function(e) {
		var $element = $(e.currentTarget),
			$parentElement = $element.parents('.project-row'),
			projectData = $parentElement.data();
		
		$parentElement.find('.slider').val($element.val());

		if(this.dataFetched.data[projectData.userid][projectData.id]['new'] == undefined) {
			this.dataFetched.data[projectData.userid][projectData.id]['updated'] = 1;
		}

		this.dataFetched.data[projectData.userid][projectData.id]['value'] = $element.val();
	},

	setProjectItem: function(e) {
		e.preventDefault();
		e.stopPropagation();

		var $element = $(e.currentTarget),
			$parentElement = $element.parents('.project-row'),
			projectData = $parentElement.data();

		$parentElement.find('.slider').removeAttr('disabled');
		$parentElement.find('.slider-text').removeAttr('disabled');
		$parentElement.find('.project-name').val($element.text());
		$element.parent().addClass('hide');

		var projectID = Object.keys(this.listOfProjects).find(key => this.listOfProjects[key] === $element.text());
		$element.parent().parent().attr('data-projectid', projectID);

		if(this.dataFetched.data[projectData.userid][projectData.id]['new'] == undefined) {
			this.dataFetched.data[projectData.userid][projectData.id]['updated'] = 1;
		}

		this.dataFetched.data[projectData.userid][projectData.id]['id'] = projectID;
	},

	closeProjectList: function(e) {
		var $list = $(e.currentTarget).find('.select2-results');

		$list.html("");
		$list.addClass('hide');
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
        location.replace('http://'+window.location.hostname+'/#AB_TimeSheet/'+this.model.get("id"));
    },

    saveClicked: function() {
    	var self = this,
    		error = {
    			'users': "",
    			'validation': true,
    			'project': false,
    		};

    	_.each(this.dataFetched.data, function(timeSheet, userID) {
    		var sum = 0;
    		_.each(timeSheet, function(projectData, timeSheetID) {
    			if(projectData['deleted'] == 0) {
    				sum += parseInt(projectData['value']);
    			}

    			if(sum > 100) {
	    			if(error['users'] != "") {
	    				error['users'] += " and ";
	    			}

	    			error['validation'] = false;
	    			error['users'] += self.dataFetched.users[userID];
	   	 		}

    			if(_.isEmpty(projectData['id'])) {
    				error['validation'] = false;
    				error['project'] = true;
    				error['users'] = '';
    			}
    		});
    	});

    	if(!error['validation'] && error['project'] == true) {
    		app.alert.show('message-id', {
                level: 'confirmation',
                messages: 'Please choose a project',
                autoClose: false,
            });

            return;
    	}

    	if(!error['validation'] && error['users'] != "") {
    		app.alert.show('message-id', {
                level: 'confirmation',
                messages: 'Sum couldn`t be greater then 100% for '+ error['users'],
                autoClose: false,
            });

            return;
    	}

    	console.info('updated: '+ self.dataFetched.data);

    	$.ajax({
            url: 'index.php?entryPoint=getData&updateTimeSheet=1&time_sheet_id='+ self.model.get('id')+'&noCache='+ (new Date().getTime()),
            type: 'POST',
            data: {
            	updated: self.dataFetched.data,
            	users: self.dataFetched.users,
            },
            success: function(data) {
                self.view = "detail";
                self._super('saveClicked');
            },
        }); // ajax
    },
})
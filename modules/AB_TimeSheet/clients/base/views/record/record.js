({
  	extendsFrom: "RecordView",
  	dataFetched: {},
  	updatedData: {},
  	listOfProjects: [],
  	sum: {},

  	view: undefined,
  	events: _.extend({}, this.events, {
        'change .slider': 'setSliderTextValue',
        'blur .slider-text': 'setSliderValue',
        'click a[name="cancel_button"]': 'cancelClicked',
        'click .add-project': 'addProjectRow',
        'click .remove-project': 'removeProjectRow',
        'keyup .project-name': 'searchProjectByName',
        'focus .project-name': 'searchProjectByName',
    }),

	initialize: function(options) {
		this._super('initialize', [options]);

		this.view = options.context.get("action");
		var self = this;
		self.collection.on('data:sync:complete', function() {

			// get all data from db
            app.api.call('POST', 'index.php?entryPoint=getData&getAllTimeSheetData=1&time_sheet_id='+self.model.get('id')+'&user_id='+app.user.id, null,{
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
			var self = this;
			$('.record-cell[data-name="subordinates_c"]').html(this.rebuildSubordinatiesField());
			$("body").append('<style>.ui-autocomplete li {list-style: none;background:white;max-width:'+ $('.project-name').outerWidth() +'px;border-left: 1px solid #ddd; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;}.ui-autocomplete li a {color: black;padding-left:10px;display:block;max-width:'+ $('.project-name').outerWidth() +'px;}.ui-autocomplete li a.ui-state-hover{background:#ccc;}</style>');
		}
	},

	rebuildSubordinatiesField: function() {
		var self = this,
			string = '';
		
		_.each(self.dataFetched.data, function(timeSheet, userID) {
			self.sum[userID] = {
				'slider': 0,
				'text': 0,
			};

			string += '<div class="span12 first user-records">';
			string += '<div class="span12 first" data-name="employee-name" data-userid="'+userID+'"><div class="ellipsis_inline span10">'+self.dataFetched.users[userID]+'</div>';
			
			if(self.view == "edit") { string += '<div class="span1 first"><a data-userid="'+userID+'" class="add-project"><i class="fa-plus fa"></i></a></div>'; }

			string += '</div><div class="span12 first">';
			string += '<ul class="project-list">';

			_.each(timeSheet, function(projectData, timeSheetID) {
				if(_.isEmpty(projectData.deleted) && projectData.deleted == 0) {	
					var projectText = "",
						procentText = "";

					if(self.view == "edit") {
						projectText = '<div class="span4 time-sheet-project-data" data-name="project-name"><input class="project-name" type="text" value="'+self.listOfProjects[projectData.id]+'" /><ul class="select2-results list-of-projects hide"></ul></div>';
						procentText = '<div class="span3 time-sheet-range"><input class="slider" type="range" value="'+projectData.value+'" /></div><div class="span1 time-sheet-project-data" data-name="procent"><input type="text" class="slider-text procent-value" value="'+projectData.value+'" /></div>';
						procentText += '<div class="span1 time-sheet-project-data"><a data-id="'+timeSheetID+'" class="remove-project"><i class="fa-remove fa red-color"></i></a></div>';
					} else {
						projectText = '<div class="span4 time-sheet-project-data" data-projectid="'+projectData.id+'">'+self.listOfProjects[projectData.id]+'</div>';
						procentText = '<div class="span3 time-sheet-range"><input class="slider" type="range" value="'+projectData.value+'" disabled /></div><div class="span1 time-sheet-project-data">'+projectData.value+'%</div>';
					}

					self.sum[userID].text += parseInt(projectData.value);

					string += '<li class="span12 first timesheet-row"><div data-id="'+timeSheetID+'" data-userid="'+userID+'" class="span12 project-row">'+projectText+procentText+'</div></li>';
				}
			});

			self.sum[userID].slider = (self.sum[userID].text > 100) ? 100 : self.sum[userID].text;

			string += 	'<li class="span12 first timesheet-row sum-row">'+
							'<div class="span12 project-row">'+
								'<div class="span4 time-sheet-project-data">Sum</div>'+
								'<div class="span3 time-sheet-range"><input class="slider sum-slider" type="range" value="'+ self.sum[userID].slider +'" disabled /></div>'+
								'<div class="span1 time-sheet-project-data sum-text">'+ self.sum[userID].text +'%</div>'+
							'</div>'+
						'</li>';

			string += '</ul>';
			string += '</div>';
			string += '</div>';
		});

		return string;
	},

	addProjectRow: function(e) {
		var timeSheetID = app.utils.generateUUID(),
			projectData = $(e.currentTarget).data(),
			projectText = '<div class="span4 time-sheet-project-data"><input class="project-name" type="text" value="" /><ul class="select2-results list-of-projects hide"></ul></div>',
			procentText = '<div class="span3 time-sheet-range"><input disabled class="slider" type="range" value="0" /></div><div class="span1 time-sheet-project-data"><input disabled type="text" class="slider-text" value="0"/></div>',
			deleteIcon = '<div class="span1 time-sheet-project-data"><a data-id="'+timeSheetID+'" class="remove-project"><i class="fa-remove fa red-color"></i></a></div>',
			string = '<li class="span12 first timesheet-row"><div data-id="'+timeSheetID+'" data-userid="'+projectData.userid+'" class="span12 project-row">'+projectText+procentText+deleteIcon+'</div></li>';

		$(e.currentTarget).parents('.user-records').find('.project-list').prepend(string);

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

		this.setSum(projectData.userid, $projectRow.parents('.project-list').find('.sum-row'));
		$projectRow.parent().remove();
	},

	setSliderTextValue: function(e) {
		var $element = $(e.currentTarget),
			$parentElement = $element.parents('.project-row'),
			value = $element.val(),
			projectData = $parentElement.data();

		$parentElement.find('.slider-text').val($element.val());

		if(this.dataFetched.data[projectData.userid][projectData.id]['new'] == undefined) {
			this.dataFetched.data[projectData.userid][projectData.id]['updated'] = 1;
		}

		this.dataFetched.data[projectData.userid][projectData.id]['value'] = value;
		this.setSum(projectData.userid, $parentElement.parents('.project-list').find('.sum-row'));
	},

	setSliderValue: function(e) {
		var $element = $(e.currentTarget),
			$parentElement = $element.parents('.project-row'),
			value = $element.val(),
			projectData = $parentElement.data();
		
		$parentElement.find('.slider').val($element.val());

		if(this.dataFetched.data[projectData.userid][projectData.id]['new'] == undefined) {
			this.dataFetched.data[projectData.userid][projectData.id]['updated'] = 1;
		}

		this.dataFetched.data[projectData.userid][projectData.id]['value'] = value;
		this.setSum(projectData.userid, $parentElement.parents('.project-list').find('.sum-row'));
	},

	searchProjectByName: function(e) {
		var self = this,
			$element = $(e.currentTarget),
			$parentElement = $element.parents('.project-row'),
			projectData = $parentElement.data(),
			array = $.map(self.listOfProjects, function(value, index) {
			    return [value];
			});

		$element.autocomplete({
            minLength: 2,
            source: array,
            select: function(event, ui) {
		    	$parentElement.find('.slider').removeAttr('disabled');
				$parentElement.find('.slider-text').removeAttr('disabled');

				var projectID = Object.keys(self.listOfProjects).find(key => self.listOfProjects[key] === ui.item.value);
				$element.parent().parent().attr('data-projectid', projectID);

				if(self.dataFetched.data[projectData.userid][projectData.id]['new'] == undefined) {
					self.dataFetched.data[projectData.userid][projectData.id]['updated'] = 1;
				}

				self.dataFetched.data[projectData.userid][projectData.id]['id'] = projectID;
		    }
        });
	},

	setSum: function(userID, $element) {
		var self = this,
			sum = 0;

		this.sum[userID] = {};
		_.each(this.dataFetched.data[userID], function(projectData, timeSheetID) {
			if(projectData.deleted != "1") {
				sum += parseInt(projectData.value);
			}
		});

		this.sum[userID].text = sum;
		this.sum[userID].slider = (this.sum[userID].text > 100) ? 100 : this.sum[userID].text;
		
		$element.find('.sum-slider').val(this.sum[userID].slider);
		$element.find('.sum-text').text(this.sum[userID].text +"%");
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
                'validation': true,
                'message': "",
            };

        _.each(this.dataFetched.data, function(timeSheet, userID) {
            var sum = 0;

            if(_.isEmpty(timeSheet)) {
                error = {
                    'validation': false,
                    'message': 'Proszę o rozpisanie czasu pracy dla '+ self.dataFetched.users[userID],
                };
            }

            var projects = [];
            _.each(timeSheet, function(projectData, timeSheetID) {
                if(projectData['deleted'] == 0) {
                    sum += parseInt(projectData['value']);

                    if(_.isEmpty(projectData['id'])) {
                        error = {
                            'validation': false,
                            'message': 'Proszę o wybór projektu',
                        };
                    }

                    if(projects.indexOf(projectData['id']) != -1) {
                        error = {
                            'validation': false,
                            'message': 'Wybrałeś ten sam projekt więcej niż jeden raz dla pracownika '+ self.dataFetched.users[userID],
                        };
                    } else {
                        projects.push(projectData['id']);
                    }
                }
            });

            if(sum > 100) {
                error = {
                    'validation': false,
                    'message': 'Suma nie może być większa niż 100 dla pracownika '+ self.dataFetched.users[userID],
                };
            }
        });

        if(!error['validation']) {
            app.alert.show('message-id', {
                level: 'confirmation',
                messages: error['message'],
                autoClose: false,
            });

            return;
        }

    	$.ajax({
            url: 'index.php?entryPoint=getData&updateTimeSheet=1&time_sheet_id='+ self.model.get('id')+'&noCache='+ (new Date().getTime()),
            type: 'POST',
            data: {
            	updated: self.dataFetched.data,
            	users: self.dataFetched.users,
            	name: self.model.get('name'),
            	date_from: self.model.get('date_start_c'),
            	date_to: self.model.get('date_end_c'),
            	getRelated: false,
            },
            success: function(data) {
            	// self.model.set('date_end_c', data.date_end);
            	// self.model.set('name', data.name);
                self.view = "detail";
                self._super('saveClicked');
            },
        }); // ajax
    },
})
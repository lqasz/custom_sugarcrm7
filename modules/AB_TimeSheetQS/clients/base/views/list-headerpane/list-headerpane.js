/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
({
    extendsFrom: 'HeaderpaneView',
    events: _.extend({}, this.events, {
    	'click a[name="qs_structure_button"]': 'showQSStructure',
	}),

    initialize: function(options) {
        this._super('initialize', [options]);
    },

    showQSStructure: function(e) {
        var that = this;

      	app.api.call('GET', 'index.php?entryPoint=getData&getQSStructure=1', null, {
	        success: _.bind( function (data) {
	        	
	        	var select = function(item) { 
	        		var select = "<select class='team-leader'>";

		        	_.each(data, function(user, id) {
		        		var selected = (item == id) ? "selected='selected'" : "";

		        		select += "<option value='"+id+"' "+selected+">"+ user.user_name +"</option>";
		        	});

		        	select += "</select>";

		        	return select;
	        	};

        		var DepartmentStructure = Backbone.View.extend({
        			events: {
		                'click .modal-backdrop': 'removePanel',
		                'click #saveButtonMonit': 'save',
		                'change .team-leader': 'selectUser',
		            },

					initialize: function() {
		                this.render();
		            },

		            render: function() {
		            	this.addPanel();
		            },

		            addPanel: function() {
	                    this.setElement($('body').append('<div id="DepartmentStructureMonit"><div id="DepartmentStructurePanel" class="modal"></div><div class="modal-backdrop"></div></div>') );
	                    var html = '<div class="modal-header"><h3><i class="fa fa-sitemap"></i> QS Department</h3></div>'+
	                                '<div class="modal-body">'+
	                                    '<div class="panel-main">'+
	                                        '<div class="row-fluid department-users">'+
	                                            this.addUsersForm() +
	                                        '</div>'+
	                                    '</div>'+
	                                '</div>'+
	                                '<div class="modal-footer">'+
	                                    '<button id="saveButtonMonit" class="btn btn-primary">Save</button>'+
	                                '</div>';

	                    $('#DepartmentStructurePanel').html(html);
		            },

		            addUsersForm: function() {
		                var self = this,
		                    string = '<div class="span12 first department-users" >';
		                
						string += '<div class="span12 first single-user">';
						string += '<div class="span6 first"><b>Employee:</b></div>';
						string += '<div class="span6 first"><b>Team Leader:</b></div>';
						string += '</div>';

						var iter = 1;
		                _.each(data, function(user, id) {
		                	if(user.e_type == "employee") {
			                    string += '<div data-user_id="'+id+'" class="span12 first single-user">';
			                    string += '<div class="span6 first">'+iter+'. '+user.user_name+'</div>';
			                    string += '<div class="span6 first">'+select(user.reports_to)+'</div>';
			                    string += '</div>';
		                	}
		                    
		                    iter++;
		                });

		                string += '</div>';

		                return string;
		            },

		            removePanel: function(e) {
		            	$('#DepartmentStructureMonit').remove();
		            },

		            selectUser: function(e) {
		            	var $element = $(e.currentTarget),
		            		$parentElement = $element.parents('.single-user'),
		            		user_id = ($parentElement.data()).user_id;

		            	data[user_id].reports_to = $element.val();
		            },

		            save: function(e) {
		            	$.ajax({
				            url: 'index.php?entryPoint=getData&updateQSStructure=1',
				            type: 'POST',
				            data: {
				            	structure: data
				            },
				            success: function(msg) {
				            	$('#DepartmentStructureMonit').remove();
				            },
				        }); // ajax
		            },
        		});

        		var departmentStructure = new DepartmentStructure();
	      	})
	    });
    },
})
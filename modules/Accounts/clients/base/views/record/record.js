({
	extendsFrom: "RecordView",
	pclOpinions: undefined,

    id: 'CompanyRecord',
    events: _.extend({}, this.events, {
    	'click #more-opening-days': 'setTableSize',
    	'click a[name="edit_button"]': 'addContent',
    	'change input[name="category_service_c"]': 'changeListContent',
    	'click a[name="edit_button"]': 'addLabels',
    	'click a[name=cancel_button]': 'cancelClicked',
    }),
    plusValue: undefined,
    inlineEditMode: false,
 
	initialize: function(options) {
		var self = this;

		this.plugins = _.union(this.plugins, ['LinkedModel']); //, 'HistoricalSummary']
		this._super('initialize', [options]);

	    $('<style>'+
	    'div[data-subpanel-link="accounts_ac_feeproposal_1"] a[name="select_button"] { display: none; }'+
	    'div[data-subpanel-link="accounts_aa_buildings_1"] a[name="select_button"] { display: none; }'+
	    'div[data-subpanel-link="accounts_project_1"] a[name="select_button"] { display: none; }'+
	    'li.row-fluid.sortable:nth-child(3) { display: none; }'+
	    '.new-width { width: 8.5% !important;}'+
	    '.simple-form { float: right; font-size: 11px; color: #747474;}'+
	    '#more-opening-days { width: 24px; }'+
	    '.index p {margin: 0!important; }'+
	    '.vis_action_hidden { display: none !important; }'+
	    '.hide { display: none !important; }'+
	    '.label-top { margin-top: 15px !important; }'+
	    '#add-number { float: left; }'+
	    'h1 { font-size: 16px; }'+
	    // '.dataTable>thead>tr>th>span { display: none; }'+ 
	    '</style>').appendTo('head');

		// this.before('render', this.addRow, this);
		this.context.on('button:refresh_all_subpanels:click', this.refresh_all_subpanels, this);

		self.collection.on('data:sync:complete', function() {
            app.api.call('GET', 'index.php?entryPoint=getData&getPCLComments=1&parent_id='+self.model.get('id'), null,{
                success: _.bind(function(data) {
                    self.pclOpinions = data;
                    self.model.trigger('addShowPCLComments');
                })
            });
        }, self);

        self.model.on('addShowPCLComments', function() {
            self.render();
        }, self);
	},

	render: function() {
		this._super('render');

		this.$el.find('div[data-name="website"]').find('span.normal.index').find('span').find('a').attr('target', '_blank');
	    app.error.errorName2Keys['phone_office'] = 'Phone format (+48 730 444 867)';
        this.model.addValidationTask('check_phone_number', _.bind(this._doValidatePhoneNumber, this));

        this.$el.find('.record-cell[data-name="follow"]').hide(0);
        this.$el.find('.index[data-fieldname="name"]').children().children().css('font-weight', 'bold');
        this.$el.find('.record-cell[data-name="name"]').children().css('margin-top', '5px');
        this.$el.find('#service-type').css('margin-top', '5px');
        this.$el.find('#service-type').css('font-weight', 'bold');
        this.$el.find('.record-cell[data-name="category_service_c"]').children().css('margin-top', '5px');
        this.$el.find('.record-cell[data-name="category_service_c"]').children().children().css('font-weight', 'bold');
        this.$el.find('.record-cell[data-name="name"]').addClass('name-width');
        this.$el.find('.record-cell[data-name="category_service_c"]').addClass('category-width');

        this.addLabels();
        this.addContent();

        var $user = $('a[href^="#bwc/index.php?module=Employees&"]');
		$user.css("color","#555");
		$user.hover(function() {
          $(this).css("color","#555");
          $(this).css("text-decoration", "none");
          $(this).css("cursor", "text");
        });

		$user.parent().parent().parent().find('td').last().hide(0);
		$user.removeAttr("href");

		if(this.pclOpinions !== undefined) {
            this.addPCLFields();
        }
	},

	_doValidatePhoneNumber: function(fields, errors, callback) {  
		var mobilePhone = this.model.get('phone_office'),
			numberSign = mobilePhone.substring(0,1),
			numberOfSpaces = mobilePhone.split(' ');

		 if ( ((numberSign=='-') || (numberSign=='+')) && numberOfSpaces.length >=3 ) {

		}else{
			errors['phone_office'] = errors['phone_office'] || {}; 
			errors['phone_office'].phone_office = true;
		}
    	callback(null, fields, errors);
	},
	
	/**
	* Refreshes all subpanels
	*/
	refresh_all_subpanels: function() {
		_.each(this.model._relatedCollections, function(collection){
			collection.trigger('reset');
			collection.fetch({relate: true});
			// self.render();
		});
	},

	_renderHtml: function() {
		// this.addServiceType(12);
		this.addLabels();
		this._super('_renderHtml');
		this.addLabels();
	},

	setTableSize: function(event) {
		var $button = $(event.currentTarget),
			$rowFolded = $('.fold'),
			$rowExpanded = $('.expand');

		if($button.hasClass('expand-text')) {
			$button.addClass('fold-text');
			$button.removeClass('expand-text');
			$button.attr('value', '-');

			$rowFolded.addClass('hide');
			$rowExpanded.removeClass('hide');
		} else {
			$button.addClass('expand-text');
			$button.removeClass('fold-text');
			$button.attr('value', '+');

			$rowExpanded.addClass('hide');
			$rowFolded.removeClass('hide');
		}
	},

	addContent: function() {
		this.$el.find('div.record-cell[data-name="lcc_c"]').after('<div class="span2 record-cell dropdown-link"><a class="btn" target="_blank" href="http://rms.reesco.pl/#bwc/index.php?module=ModuleBuilder&action=index&type=dropdowns">Drop down list</a></div>');
		// this.$el.find('div.record-cell[data-name="fitters_c"]').after('<table class="simple-form">'+
		// 	'<tr><td class="span6"></td><td><input type="submit" id="more-opening-days" class="expand-text" value="+"></td><td>Dzień</td><td>Noc</td></tr>'+
		// 	'<tr class="fold"><td class="span6"></td><td class="span2 new-width" >Dzien roboczy:</td><td class="span1"><input name="work_day" type="text" placeholder="brak"></td><td class="span1"><input name="work_night" type="text" placeholder="brak"></td></tr>'+
		// 	'<tr class="expand hide"><td class="span6"></td><td class="span2 new-width" >Poniedziałek:</td><td class="span1"><input name="monday" type="text" placeholder="brak"></td><td class="span1"><input name="monday_night" type="text" placeholder="brak"></td></tr>'+
		// 	'<tr class="expand hide"><td class="span6"></td><td class="span2 new-width" >Wtorek:</td><td class="span1"><input name="tuesday" type="text" placeholder="brak"></td><td class="span1"><input name="tuesday_night" type="text" placeholder="brak"></td></tr>'+
		// 	'<tr class="expand hide"><td class="span6"></td><td class="span2 new-width" >Środa:</td><td class="span1"><input name="wednesday" type="text" placeholder="brak"></td><td class="span1"><input name="wednesday_night" type="text" placeholder="brak"></td></tr>'+
		// 	'<tr class="expand hide"><td class="span6"></td><td class="span2 new-width" >Czwartek:</td><td class="span1"><input name="thursday" type="text" placeholder="brak"></td><td class="span1"><input name="thursday_night" type="text" placeholder="brak"></td></tr>'+
		// 	'<tr class="expand hide"><td class="span6"></td><td class="span2 new-width" >Piątek:</td><td class="span1"><input name="friday" type="text" placeholder="brak"></td><td class="span1"><input name="friday_night" type="text" placeholder="brak"></td></tr>'+
		// 	'<tr><td class="span6"></td><td class="span2 new-width" >Sobota:</td><td class="span1"><input name="saturday" type="text" placeholder="brak"></td><td class="span1"><input name="saturday_night" type="text" placeholder="brak"></td></tr>'+
		// 	'<tr><td class="span6"></td><td class="span2 new-width" >Niedziela:</td><td class="span1"><input type="text" name="sunday" placeholder="brak"></td><td class="span1"><input name="sunday_night" type="text" placeholder="brak"></td></tr>'+
		// 	'</table>');
	},

	// removeContent: function() {
	// 	this.$el.find('.simple-form').remove();
	// 	this.$el.find('#add-number');
	// },

	addLabels: function() {
		this.$el.find('.fieldset-field[data-name="website"]').before('<div class="record-label label-top">Website</div>');
		this.$el.find('.fieldset-field[data-name="phone_fax"]').before('<div class="record-label">Fax</div>');
		this.$el.find('.fieldset-field[data-name="proxy"]').before('<div class="record-label label-top">Person to signing contracts</div>');
		this.$el.find('.fieldset-field[data-name="query"]').before('<div class="record-label label-top">Query to</div>');
		this.$el.find('.fieldset-field[data-name="reesco_curator_c"]').before('<div class="record-label label-top">Reesco Curator</div>');
		this.$el.find('.fieldset-field[data-name="krs_c"]').before('<div class="record-label label-top">KRS</div>');
		this.$el.find('.fieldset-field[data-name="nip_c"]').before('<div class="record-label label-top">NIP</div>');
		this.$el.find('.fieldset-field[data-name="regon_c"]').before('<div class="record-label label-top">REGON</div>');
		this.$el.find('.fieldset-field[data-name="phone_office"]').before('<div class="record-label label-top">Office phone</div>');
		this.$el.find('.fieldset-field[data-name="email"]').before('<div class="record-label label-top">Email</div>');

		var $rowElement = this.$el.find('#tabContent .LBL_RECORDVIEW_PANEL1:eq(0)');
		
		$rowElement.find('.record-cell:eq(3)').addClass('span6');
		$rowElement.find('.record-cell:eq(3)').removeClass('span4');
		$rowElement.find('.record-cell:eq(4)').addClass('span6');
		$rowElement.find('.record-cell:eq(4)').removeClass('span4');

		$rowElement.find('.record-cell:eq(2)').addClass('span4');
		$rowElement.find('.record-cell:eq(2)').removeClass('span2');

		this.$el.find('.fax-label .detail').replaceWith('<div class="record-label">Fax</div>');

		var $minValue = this.$el.find('div[data-name="min_value_c"] .currency-field'),
			$maxValue = this.$el.find('div[data-name="max_value_c"] .currency-field'),
			$averageOrders = this.$el.find('div[data-name="average_orders_c"] .currency-field'),
			$averageValue = this.$el.find('div[data-name="average_value_c"] .currency-field');

		$minValue.text($minValue.text().replace(",", " ").replace(".", ",") + " PLN");
		$maxValue.text($maxValue.text().replace(",", " ").replace(".", ",") + " PLN");
		$averageOrders.text($averageOrders.text().replace(",", " ").replace(".", ",") + " PLN");
		$averageValue.text($averageValue.text().replace(",", " ").replace(".", ",") + " PLN");
	},

	changeListContent: function(event) {
		var self = this,
			element = $(event.currentTarget).context.value;

		self.model.fields.company_type_c.options = self.manageElements(element);

		var renderField = this.getField('company_type_c');  
		renderField.render();
	},

	manageElements: function(value) {
		var companyList = app.lang.getAppListStrings('client_list');

		if(value == "customer") {
			Object.keys(companyList).forEach(function(key) {
				if(companyList[key].match(/Dystrybutor/) || companyList[key].match(/Producent/) || companyList[key].match(/Usługodawca/)) {
					delete companyList[key];
				}
			});
		} else {
			Object.keys(companyList).forEach(function(key) {
				if(!(companyList[key].match(/Dystrybutor/) || companyList[key].match(/Producent/) || companyList[key].match(/Usługodawca/))) {
					delete companyList[key];
				}
			});
		}

		return companyList;
	},

	// hideLegacyValues: function(event) {
	// 	// hide legacy items from territory classification dropdown
	// 	var companyList = app.lang.getAppListStrings('client_list'),
	// 		categoryService = (event != undefined) ? $(event.currentTarget).value : this.model.get('category_service_c');

	// 	console.info(categoryService);
	// 	if(categoryService == "contractor") {
	// 		Object.keys(companyList).forEach(function(key) {
	// 			if(companyList[key].match(/Usługodawca/) || companyList[key].match(/Producent/) || companyList[key].match(/Dystrybutor/)) {
	// 				delete companyList[key];
	// 			}
	// 		});
	// 	} else {
	// 		Object.keys(companyList).forEach(function(key) {
	// 			if(!(companyList[key].match(/Usługodawca/) || companyList[key].match(/Producent/) || companyList[key].match(/Dystrybutor/))) {
	// 				delete companyList[key];
	// 			}
	// 		});
	// 	}

	// 	this.model.fields['company_type_c'].options = companyList;
	// },

	// addNumber: function(event) {
	// 	// if()
	// 	for(var i = 1; i <= 2; i++) {
	// 		var $element = this.$el.find('.fieldset-field[data-name="phone_office'+i+'_c"]').children();

	// 		if($element.hasClass('hide')) {
	// 			$element.removeClass('hide');
	// 			break;
	// 		} else {
	// 			this.plusValue = false;
	// 		}
	// 	}

	// 	console.info(this.plusValue);
	// },

	saveClicked: function() {
		// var daysOfWeek = {};

		// daysOfWeek.day = {};
		// daysOfWeek.night = {};

		// daysOfWeek.day.work_day = $('input[name="work_day"]').val();
		// daysOfWeek.night.work_night = $('input[name="work_night"]').val();
		// daysOfWeek.day[1] = $('input[name="monday"]').val();
		// daysOfWeek.night[1] = $('input[name="monday_night"]').val();
		// daysOfWeek.day[2] = $('input[name="tuesday"]').val();
		// daysOfWeek.night[2] = $('input[name="tuesday_night"]').val();
		// daysOfWeek.day[3] = $('input[name="wednesday"]').val();
		// daysOfWeek.night[3] = $('input[name="wednesday_night"]').val();
		// daysOfWeek.day[4] = $('input[name="thursday"]').val();
		// daysOfWeek.night[4] = $('input[name="thursday_night"]').val();
		// daysOfWeek.day[5] = $('input[name="friday"]').val();
		// daysOfWeek.night[5] = $('input[name="friday_night"]').val();
		// daysOfWeek.day[6] = $('input[name="saturday"]').val();
		// daysOfWeek.night[6] = $('input[name="saturday_night"]').val();
		// daysOfWeek.day[7] = $('input[name="sunday"]').val();
		// daysOfWeek.night[7] = $('input[name="sunday_night"]').val();
	    
		// daysOfWeek = this.getValuesFromForm(daysOfWeek);

	 //    $.ajax({
  //           beforeSend: function (request) {
  //               request.setRequestHeader("OAuth-Token", SUGAR.App.api.getOAuthToken());
  //           },
  //           url: "rest/v10/Accounts/CustomApi",
  //           data: {
  //           	daysOfWeek,
  //           	"companyId": this.model.get('id'),
  //           },
  //           dataType: "json",
  //           type: "POST",
  //           success: function(data) {
  //               contractsole.info(data);
  //               // window.location.reload();
  //           }
	 //    });
		console.info(this);
		this._super('saveClicked');
	},

	cancelClicked: function() {
		this._super('cancelClicked');
		this.render();
	},

	// getValuesFromForm: function(data) {
	// 	var result = {},
	// 		stop = false;

	// 	_.each(data, function(week, time) {
	// 		result[time] = {};	
	// 		if(!_.isEmpty(week)) {
	// 			_.each(week, function(value, key) {
	// 				if((key == "work_day" || key == "work_night") && (!_.isEmpty(value)) ) {
	// 					for(var i = 1; i <= 5; i++) {
	// 						result[time][i] = value;
	// 					}
	// 					stop = true;
	// 				} else if(!_.isEmpty(value) && (key > 5 || stop == false) ) {
	// 					result[time][key] = value;
	// 				}
	// 			});
	// 		}
	// 	});

	// 	return result;
	// },

	addPCLFields: function() {
        var string = '<div class="row-fluid fee-naglowek">'+
                        '<strong class="span4">Osoba:</strong>'+
                        '<strong class="span8">Opinia:</strong>'+
                    '</div>';
        _.each(this.pclOpinions, function(value, key) {
            string += '<div class="span12 row-fluid first pcl-opinion">'+
                '<span class="span4">'+
                    key+
                '</span>'+
                '<span class="span8">'+
                    value+
                '</span>'+
            '</div>';
        });

        this.$el.find('.record-cell[data-name="comments"]').html(string);
    },
	
	_dispose: function() {
		this._super('_dispose');
	},
})
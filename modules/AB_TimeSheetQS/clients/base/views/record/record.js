({
	extendsFrom: "RecordView",
  id: "timeSheetForQS",

  data: {},
  sum: {},
  view: undefined,
	events: _.extend({}, this.events, {
    'click .add-row': 'addRow',
    'click .fa-remove': 'removeRow',
    'click a[name="cancel_button"]':'cancelClicked',
    'click input[name="accepted"]':'checkBoxClicked',
    'click input[name="rejected"]':'checkBoxClicked',
    'change .slider': 'setTimeSheetValue',
    'blur .slider-text': 'setTimeSheetValue',
    'keyup input[name="select-type"]': 'searchType',
    'focus input[name="select-type"]': 'searchType',
  }),

	initialize: function(options) {
    this._super('initialize', [options]);

    var self = this;
    self.view = options.context.get("action");
    self.collection.on('data:sync:complete', function() {
      // get all data from db
      app.api.call('POST', 'index.php?entryPoint=getData&getTimeSheetQSData=1&time_sheet_id='+self.model.get('id')+'&user_id='+app.user.id, null,{
        success: _.bind(function(data) {
          self.data = data;
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

    if(!_.isEmpty(this.data)) {
      this.sum['wyceny'] = 0;
      this.sum['projekty'] = 0;
      $('.record-cell[data-name="subordinates_c"]').html(this.rebuildSubordinatiesField());
      $("body").append('<style>.ui-autocomplete li {list-style: none;background:white;max-width: 483px;border-left: 1px solid #ddd; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;}.ui-autocomplete li a {color: black;padding-left:10px;display:block;max-width: 483px;}.ui-autocomplete li a.ui-state-hover{background:#ccc;}</style>');
    }
  },

  rebuildSubordinatiesField: function() {
    var html = '<div class="span12 record-cell">';

      html += '<div class="span12 first container">';
        html += this.addHeaderSection("Wyceny");
        html += this.addContentSection("Wyceny");
      html += '</div>';

      html += '<div class="span12 first container">';
        html += this.addHeaderSection("Projekty");
        html += this.addContentSection("Projekty");
      html += '</div>';
      
      if(!_.isEmpty(this.data.subordinates)) {
        html += '<div id="TeamData" class="span12 first container">';
          html += this.showTeamTimeSheets();
        html += '</div>';
      }

    html += '</div>';

    return html;
  },

  addHeaderSection: function(name) {
    var buttonHTML = '',
        lowerName = name.toLowerCase();

    if(this.view == "edit") {
      buttonHTML =  '<div class="span1 button-element">'+
                      '<button data-type="'+lowerName+'" class="btn add-row">+</button>'+
                    '</div>';
    }

    var html =  '<div class="span12 first header-row">'+
                  '<div class="span11 first type-element ellipsis_inline">'+
                    name+
                  '</div>'+
                  buttonHTML+
                '</div>';

    return html;
  },

  addContentSection: function(name) {
    var self = this,
        lowerName = name.toLowerCase(),
        html =  '<div class="span12 first content" data-type="'+lowerName+'">';

    _.each(this.data.fetched[lowerName], function(data, recordID) {
      self.sum[lowerName] += parseInt(data.value);
      html += self.returnHTMLRow(recordID, lowerName, self.data.type[lowerName][data.parent_id], data.value, false);
    });
    html += self.returnHTMLRow('qs-team-sum', lowerName, 'sum', this.sum[lowerName], true);

    html += '</div>';

    return html;
  },

  addRow: function(e) {
    var recordID = app.utils.generateUUID(),
        type =  ($(e.currentTarget).data()).type,
        html =  this.returnHTMLRow(recordID, type, "", 0, false);

    $('.content[data-type="'+type+'"]').prepend(html);
  },

  returnHTMLRow: function(recordID, type, parentName, value, sum) {
    var parentNameHTML =  '<span>'+parentName+'</span>',
        textValueHTML = '<span class="'+type+'-sum">'+value+'%</span>',
        removeRowHRML = '',
        disabled = 'disabled';

    if(this.view == "edit") {
      if(parentName != "" && !sum) {
        disabled = '';
      }

      if(!sum) {
        removeRowHRML = '<i class="fa-remove fa red-color"></i>';
        parentNameHTML = '<input type="text" name="select-type" value="'+parentName+'"/>';
        textValueHTML = '<input type="text" value="'+value+'" name="set-range" class="slider-text" '+disabled+'/>';
      }
    }

    var html =  '<div class="span12 timesheet-row" data-id="'+ recordID +'" data-type="'+ type +'">'+
                  '<div class="span5">'+
                    parentNameHTML+
                  '</div>'+
                  '<div class="span4 range-input">'+
                    '<input type="range" value="'+value+'" name="select-range" class="slider" '+disabled+'/>'+
                  '</div>'+
                  '<div class="span1">'+
                    textValueHTML+
                  '</div>'+
                  '<div class="span1 remove-row">'+
                    removeRowHRML+
                  '</div>'+
                '</div>';

    return html;
  },

  removeRow: function(e) {
    var $parentElement = $(e.currentTarget).parents('.timesheet-row'),
        data = $parentElement.data();

    if(!_.isEmpty(this.data.fetched[data.type][data.id])) {
      if(this.data.fetched[data.type][data.id]['is_new'] == 0) {
        this.data.fetched[data.type][data.id]['deleted'] = 1;
      } else {
        delete this.data.fetched[data.type][data.id];
      }
    }
    
    $parentElement.remove();
  },

  setTimeSheetValue: function(e) {
    var self = this,
        $element = $(e.currentTarget),
        $parentElement = $element.parents('.timesheet-row'),
        data = $parentElement.data();

    if($element.hasClass('slider')) {
      $parentElement.find('.slider-text').val($element.val());
    } else {
      $parentElement.find('.slider').val($element.val());
    }

    this.data.fetched[data.type][data.id]['value'] = $element.val();
    this.data.fetched[data.type][data.id]['updated'] = 1;

    this.sum[data.type] = 0;
    _.each(this.data.fetched[data.type], function(sheet, key) {
      self.sum[data.type] += parseInt(sheet.value);
    });

    $('.content[data-type="'+data.type+'"]').find('.timesheet-row[data-id="qs-team-sum"]').find('.slider').val(this.sum[data.type]);
    $('.timesheet-row[data-id="qs-team-sum"]').find('.'+data.type+'-sum').text(this.sum[data.type]+'%');
  },

  searchType: function(e) {
    var self = this,
        $element = $(e.currentTarget),
        $parentElement = $element.parents('.timesheet-row'),
        timeSheetData = $parentElement.data(),
        array = $.map(self.data.type[timeSheetData.type], function(value, index) {
          return [value];
        });

    $('#content').append('<div class="formatowanie"></div>');

    $element.autocomplete({
      minLength: 2,
      source: array,
      appendTo: ".formatowanie",
      select: function(event, ui) {
        $parentElement.find('.slider').removeAttr('disabled');
        $parentElement.find('.slider-text').removeAttr('disabled');

        var typeID = Object.keys(self.data.type[timeSheetData.type]).find(key => self.data.type[timeSheetData.type][key] === ui.item.value);
        $parentElement.attr('data-typeid', typeID);

        if(!_.isEmpty(self.data.fetched[timeSheetData.type][timeSheetData.id])) {
          self.data.fetched[timeSheetData.type][timeSheetData.id]['value'] = $('.slider').val();
          self.data.fetched[timeSheetData.type][timeSheetData.id]['parent_id'] = typeID;
          self.data.fetched[timeSheetData.type][timeSheetData.id]['updated'] = 1;
        } else {
          if(_.isEmpty(self.data.fetched[timeSheetData.type])) {
            self.data.fetched[timeSheetData.type] = {};
          } 
          
          self.data.fetched[timeSheetData.type][timeSheetData.id] = {
            "parent_id": typeID,
            'value': 0,
            'updated': 0,
            'is_new': 1,
            'deleted': 0,
          };
        }
      }
    });
  },

  showTeamTimeSheets: function() {
    var self = this,
        disabled = 'disabled';
        string = '<div class="span12 first header-team-row">'+
                    '<div class="span11 first type-element ellipsis_inline">'+
                      'Podwladni'+
                    '</div>'+
                  '</div>';

    if(this.view == "edit") {
      disabled = '';
    }

    string += '<div class="span12 first">';

    _.each(self.data.subordinates, function(data, userID) {
      var checkedAccepted = (self.data.subordinates[userID].accepted == 1) ? 'checked' : '',
          checkedRejected = (self.data.subordinates[userID].rejected == 1) ? 'checked' : '';

      string += '<div class="span12 first subordinate-data">'+
                  '<div class="span8 first subordinate-name">'+
                    data.user_name+
                  '</div>'+
                  '<div class="span2">'+
                    'Accept '+
                    '<input type="checkbox" name="accepted" data-user_id="'+userID+'" '+disabled+' '+checkedAccepted+'>'+
                  '</div>'+
                  '<div class="span2">'+
                    'Reject '+
                    '<input type="checkbox" name="rejected" data-user_id="'+userID+'" '+disabled+' '+checkedRejected+'>'+
                  '</div>'+
                '</div>';

      string += '<div class="span12">'+
                  data.date_entered+
                '</div>';

      string += '<div class="span12 subordinate-container">'+
                  '<div class="span12 first subordinate-row">'+
                    '<div class="span3"><b>Parent Type:</b></div>'+
                    '<div class="span4 first"><b>Parent Name:</b></div>'+
                    '<div class="span3"><b>Value:</b></div>'+
                  '</div>';

      _.each(data.time_sheet_data, function(timeSheetData, data_type) {
        string += '<div class="span12 first subordinate-row">';

        _.each(timeSheetData, function(v, k) {
          string += '<div class="span3">'+data_type.toLowerCase()+'</div>'+
                    '<div class="span4 first">'+self.data.type[data_type][v.parent_id]+'</div>'+
                    '<div class="span3">'+v.value+'%</div>';
        });
        
        string += '</div>';
      });

      string += '</div>';
    });

    string += '</div>';

    return string;
  },

  checkBoxClicked: function(e) {
    var $element = $(e.currentTarget),
        name = $element.attr('name'),
        userID = ($element.data()).user_id,
        elements = {
          'rejected': 'accepted',
          'accepted': 'rejected',
        };

    this.data.subordinates[userID][name] = !(this.data.subordinates[userID][name]);
  },

  saveClicked: function() {
    var self = this,
        myData = self.data.fetched,
        subordinatesData = self.data.subordinates;

    if(self.formValidator(myData, subordinatesData)) {
      $.ajax({
        url: 'index.php?entryPoint=getData&updateTimeSheetQS=1&time_sheet_id='+self.model.get('id')+'&user_id='+app.user.id,
        type: 'POST',
        data: {
          myData: myData,
          subordinatesData: subordinatesData,
        },
        success: function(msg) {
          self._super('saveClicked');
          self.view = "detail";
        },
      }); // ajax
    }
  },

  editClicked: function() {
    this.view = "edit";
    this.render();
    this._super('editClicked');
  },

  cancelClicked: function() {
    this.view = "detail";
    this._super('cancelClicked');
    this.render();
  },

  formValidator: function(myData, subordinatesData) {
    var self = this,
        error = "",
        validation = true;

    _.each(this.sum, function(value, type) {
      if(value > 100) {
        error += "sum bigger then 100%";
        validation = false;
      }
    });

    var selectedType = {
      'wyceny': [],
      'projekty': []
    };
    _.each(myData, function(type_data, parent_type) {
       _.each(type_data, function(data, id) {
        if(selectedType[parent_type].indexOf(data['parent_id']) != -1) {
          error += self.data.type[data['parent_id']] +" the same";
          validation = false;
        } else {
          selectedType[parent_type].push(data['parent_id']);
        }

        if(data['value'] == 0) {
          error += self.data.type[parent_type][data['parent_id']] +" rowne 0";
          validation = false;
        }
      });
    });

    if(!validation) {
      app.alert.show('message-id', {
          level: 'confirmation',
          messages: error,
          autoClose: false,
      });
    }

    return validation;
  },
})
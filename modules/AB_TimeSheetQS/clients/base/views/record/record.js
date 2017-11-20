({
	extendsFrom: "RecordView",
  id: "timeSheetForQS",

  data: {},
	events: _.extend({}, this.events, {
    'click .add-row': 'addRow',
    'click .fa-remove': 'removeRow',
    'change .slider': 'setTimeSheetValue',
    'blur .slider-text': 'setTimeSheetValue',
    'keyup input[name="select-type"]': 'searchType',
    'focus input[name="select-type"]': 'searchType',
  }),

	initialize: function(options) {
    this._super('initialize', [options]);

    var self = this;
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
    
    html += '</div>';

    return html;
  },

  addHeaderSection: function(name) {
    var lowerName = name.toLowerCase(),
        html =  '<div class="span12 first header-row">'+
                  '<div class="span11 first type-element ellipsis_inline">'+
                    name+
                  '</div>'+
                  '<div class="span1 button-element">'+
                    '<button data-type="'+lowerName+'" class="btn add-row">+</button>'+
                  '</div>'+
                '</div>';

    return html;
  },

  addContentSection: function(name) {
    var self = this,
        lowerName = name.toLowerCase(),
        html =  '<div class="span12 first content" data-type="'+lowerName+'">';
    
    _.each(this.data.fetched[lowerName], function(v, k) {
      
    });

    html += '</div>';

    return html;
  },

  addRow: function(e) {
    var recordID = app.utils.generateUUID(),
        type =  ($(e.currentTarget).data()).type,
        html =  this.returnHTMLRow(recordID, type);

    $('.content[data-type="'+type+'"]').append(html);
  },

  returnHTMLRow: function(recordID, type, value) {
    var html =  '<div class="span12 timesheet-row" data-typeid="" data-id="'+ recordID +'" data-type="'+ type +'">'+
                  '<div class="span5">'+
                    '<input type="text" name="select-type"/>'+
                  '</div>'+
                  '<div class="span4 range-input">'+
                    '<input type="range" value="'+value+'" name="select-range" class="slider" disabled/>'+
                  '</div>'+
                  '<div class="span1">'+
                    '<input type="text" value="'+value+'" name="set-range" class="slider-text" disabled/>'+
                  '</div>'+
                  '<div class="span1 remove-row">'+
                    '<i class="fa-remove fa red-color"></i>'+
                  '</div>'+
                '</div>';

    return html;
  },

  removeRow: function(e) {
    var $parentElement = $(e.currentTarget).parents('.timesheet-row'),
        data = $parentElement.data();

    if(!_.isEmpty(this.data.fetched[data.type][data.id])) {
      this.data.fetched[data.type][data.id]['deleted'] = 1;
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
    var self = this;

    _.each(self.data.subordinates, function(user, id) {

    });
  },
})
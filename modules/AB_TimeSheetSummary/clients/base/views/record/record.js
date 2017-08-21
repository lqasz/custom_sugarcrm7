({
	extendsFrom: "RecordView",

	events: _.extend({}, this.events, {
    'click .single': 'displayDepartmentTimeSheet',
    'click a[name=cancel_button]': 'cancelClicked',
    'click input[name="reject"]': 'rejectedClicked',
  }),

  dataFetched: undefined,
  view: undefined,

	initialize: function(options) {
    this._super('initialize', [options]);

    var self = this;
    self.view = options.context.get("action");
    self.collection.on('data:sync:complete', function() {
      var query = "";

      if(self.model.get("responsible_dep_c") == "fa") {
        query = "forFA";
      } else if(self.model.get("responsible_dep_c") == "sv") {
        query = "forSV";
      }

      // get all data from db
      app.api.call('POST', 'index.php?entryPoint=getData&getAllTimeSheets=1&'+query, null,{
        success: _.bind(function(data) {
          self.dataFetched = data;
          self.model.trigger('rebuildFields'); // trigger event in model
        })
      });
    }, self);

    self.model.on('rebuildFields', function() {
      self.render();
    });
  },

  editClicked: function() {
    this.view = "edit";
    $('input[name="reject"]').removeAttr("disabled");
    this._super('editClicked');
  },

  render: function() {
    this._super('render');
    $('.record-cell[data-name="responsible_dep_c"]').hide(0);

    if(!_.isEmpty(this.dataFetched)) {
      $('.record-cell[data-name="description"]').html(this.rebuildFields());

      if(this.view == "detail") {
        $('input[name="reject"]').attr("disabled", true);
      }
    }
  },

  rebuildFields: function() {
    var html = '<div class="span12 row-fluid panel_body">';
      
    html += '<div class="span4 first">';
    html += '<div class="span12 record-label">Departments</div>';
    html += '<table id="timeSheetsTable" class="table table-striped dataTable">'
    _.each(this.dataFetched, function(timeSheet, department) {
      html += '<tr class="single" data-dep_name="'+department+'"><td><span class="list">'+department+'</span></td></tr>';
    });

    html += '</table>';
    html += '</div>';
    html += '<div id="timeSheetRecord" class="span8 record-cell"></div>';

    return html;
  },

  displayDepartmentTimeSheet: function(e) {
    var self = this,
      html = '', 
      department = ($(e.currentTarget).data()).dep_name;

    _.each(this.dataFetched[department], function(userData, userName) {
      html += '<div class="span12 first">';
      html += '<div class="span12 record-label"><b>'+userName+'</b></div>';
      html += '<div class="span12 first">';

      _.each(userData, function(data, iter) {
        html += '<div class="span6 first">'+
                  '<div class="record-label">'+
                    'Project Name'+                  
                  '</div>'+
                  '<span class="normal">'+
                    '<span class="detail">'+
                      data['project']+
                    '</span>'+
                  '</span>'+
                '</div>';

        html += '<div class="span3">'+
                  '<div class="record-label">'+
                    'Value'+                  
                  '</div>'+
                  '<span class="normal">'+
                    '<span class="detail">'+
                      data['value']+
                    '%</span>'+
                  '</span>'+
                '</div>';

        var disabled = (self.view == "edit") ? "" : "disabled";

        html += '<div class="span2">'+
                  '<div class="record-label">'+
                    'Reject'+                  
                  '</div>'+
                  '<span class="normal">'+
                    '<span class="detail">'+
                      '<input data-dep_name="'+department+'" data-user_name="'+userName+'" data-id="'+data['id']+'" type="checkbox" name="reject" '+ disabled +'/>'+
                    '</span>'+
                  '</span>'+
                '</div>';
      });

      html += '</div>';
      html += '</div>';
    });

    $('#timeSheetRecord').html(html);
  },

  cancelClicked: function() {
      this.view = "detail";
      location.replace('http://'+window.location.hostname+'/#AB_TimeSheetSummary/'+this.model.get("id"));
  },

  rejectedClicked: function(e) {
    var data = $(e.currentTarget).data();
    this.dataFetched[data.dep_name][data.user_name].rejected = 1;

    console.info("data: ", this.dataFetched[data.dep_name][data.user_name]);
  },
})
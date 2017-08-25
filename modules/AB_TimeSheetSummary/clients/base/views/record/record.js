({
	extendsFrom: "RecordView",

	events: _.extend({}, this.events, {
    'click .single': 'displayDepartmentTimeSheet',
    'click a[name=cancel_button]': 'cancelClicked',
    'click input[name="reject"]': 'rejectedClicked',
    'click input[name="accept"]': 'acceptedClicked',
  }),

  dataFetched: undefined,
  view: undefined,

	initialize: function(options) {
    this._super('initialize', [options]);

    var self = this;
    self.view = options.context.get("action");
    self.collection.on('data:sync:complete', function() {
      // get all data from db
      app.api.call('POST', 'index.php?entryPoint=getData&getAllTimeSheets=1', null,{
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
    $('input[name="accept"]').removeAttr("disabled");
    this._super('editClicked');
  },

  render: function() {
    this._super('render');

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
      department = ($(e.currentTarget).data()).dep_name,
      disabled = (this.view == "edit") ? "" : "disabled";

    _.each(this.dataFetched[department], function(userData, userName) {
      var accept = (userData.accepted == 1) ? "checked" : "",
          reject = (userData.rejected == 1) ? "checked" : "";

      html += '<div class="span12 first time-sheet-report">'+
                '<div class="span3 record-label time-sheet-user-name">'+userName+'</div>'+
                '<div class="span9 time-sheet-summary" style="height: 28px;">'+
                  '<div class="span2 time-sheet-user-name">'+
                    '<div class="span6 record-label" style="text-align: right;">Accept</div>'+
                    '<div class="span6">'+
                      '<span class="normal">'+
                        '<span class="detail">'+
                          '<input data-dep_name="'+department+'" data-user_name="'+userName+'" type="checkbox" name="accept" '+disabled+' '+accept+'/>'+
                        '</span>'+
                      '</span>'+
                    '</div>'+
                  '</div>'+
                  '<div class="span2 time-sheet-user-name">'+
                    '<div class="span6 record-label" style="text-align: right;">Reject</div>'+
                    '<div class="span6">'+
                      '<span class="normal">'+
                        '<span class="detail">'+
                          '<input data-dep_name="'+department+'" data-user_name="'+userName+'" type="checkbox" name="reject" '+disabled+' '+reject+'/>'+
                        '</span>'+
                      '</span>'+
                    '</div>'+
                  '</div>'+
                  '<div class="span8 time-sheet-user-name hide rejected-text">'+
                    '<div class="span12">'+
                      '<span class="normal">'+
                        '<span class="detail">'+
                          '<input data-dep_name="'+department+'" data-user_name="'+userName+'" type="text" name="reject-text" '+disabled+' value="'+userData.rejectedText+'"/>'+
                        '</span>'+
                      '</span>'+
                    '</div>'+
                  '</div>'+
                '</div>';

      html += '<div class="span12 first time-sheet-header time-sheet-record">';
      html += '<div class="span9 first record-label">'+
                  'Project Name'+
              '</div>';

      html += '<div class="span3 record-label ">'+
                'Value'+
              '</div>';
      html += '</div>';

      _.each(userData.data, function(data, iter) {
        html += '<div class="span12 first time-sheet-row time-sheet-record">';

        html += '<div class="span9 first">'+
                  '<span class="normal">'+
                    '<span class="detail">'+
                      data['project']+
                    '</span>'+
                  '</span>'+
                '</div>';

        html += '<div class="span3">'+
                  '<span class="normal">'+
                    '<span class="detail">'+
                      data['value']+
                    '%</span>'+
                  '</span>'+
                '</div>';

        html += '</div>';
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

    console.info($(e.currentTarget).val());
    $(e.currentTarget).parents('.time-sheet-summary').find('.rejected-text').removeClass('hide');

    this.dataFetched[data.dep_name][data.user_name].accepted = 0;
    this.dataFetched[data.dep_name][data.user_name].rejected = 1;

    console.info("department: ", this.dataFetched[data.dep_name]);
  },

  acceptedClicked: function(e) {
    var data = $(e.currentTarget).data();

    this.dataFetched[data.dep_name][data.user_name].accepted = 1;
    this.dataFetched[data.dep_name][data.user_name].rejected = 0;

    console.info("department: ", this.dataFetched[data.dep_name]);
  },

  saveClicked: function() {
    var self = this;

    $.ajax({
      url: 'index.php?entryPoint=getData&updateTimeSheetSummary=1&noCache='+ (new Date().getTime()),
      type: 'POST',
      data: {
        updated: self.dataFetched,
      },
      success: function(data) {
          self.view = "detail";
          self._super('saveClicked');
      },
    }); // ajax
  },
})
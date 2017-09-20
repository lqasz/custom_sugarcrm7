({
  extendsFrom: "RecordView",

  events: _.extend({}, this.events, {
    'click .single': 'displayDepartmentTimeSheet',
    'click a[name=cancel_button]': 'cancelClicked',
    'click .show-details': 'showDetailsClicked',
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
      app.api.call('POST', 'index.php?entryPoint=getData&getAllTimeSheets=1&summary_id='+ self.model.get('id'), null,{
        success: _.bind(function(data) {
          console.info("data: ", data);
          self.dataFetched = data;
          self.model.trigger('rebuildFields'); // trigger event in model
        })
      });
    }, self);

    self.model.on('rebuildFields', function() {
      self.render();
      $('.single').first().click();
    });
  },

  editClicked: function() {
    this.view = "edit";
    $('input[name="reject"]').removeAttr("disabled");
    $('input[name="accept"]').removeAttr("disabled");
    $('input[name="reject-text"]').removeAttr("disabled");
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
      html = '', iter = 0,
      department = ($(e.currentTarget).data()).dep_name;

    _.each(this.dataFetched[department]['summary_values'], function(userData, userName) {
      var accept = (self.dataFetched[department].summary.accepted == 1) ? "checked" : "",
          disabled = (self.view == "detail") ? 'disabled="disabled"' : '';
      
      if(iter == 0) {
        html +=  '<div class="span12 time-sheet-summary" style="height: 28px;">'+
                    '<div class="span6 time-sheet-user-name">'+
                      '<div class="span12 record-label" style="text-align: left;">'+department+'</div>'+
                    '</div>'+
                    '<div class="span3 time-sheet-user-name">'+
                      '<div class="span6 record-label" style="text-align: right;">Accept</div>'+
                      '<div class="span6">'+
                        '<span class="normal">'+
                          '<span class="detail">'+
                            '<input data-dep_name="'+department+'" data-user_name="'+userName+'" type="checkbox" name="accept" '+disabled+' '+accept+'/>'+
                          '</span>'+
                        '</span>'+
                      '</div>'+
                    '</div>'+
                    '<div class="span3 time-sheet-user-name">'+
                      '<a class="show-details" data-dep_name="'+department+'">'+
                        '<div class="span6 record-label" style="text-align: right;">Detail</div>'+
                        '<div class="span6">'+
                            '<span class="detail">'+
                              '<i class="fa fa-list"></i>'+
                            '</span>'+
                        '</div>'+
                      '</a>'+
                    '</div>'+
                  '</div>';
      }

      var className = (iter == 0) ? "" : "time-sheet-report";

      html += '<div class="span12 first '+ className +'">'+
                '<div class="span12 summary-label time-sheet-header">'+
                  '<div class="span12 normal time-sheet-user-name">'+userName+'</div>'+
                '</div>'+
                '<div class="span12 first time-sheet-header time-sheet-record">'+
                '<div class="span9 first record-label">'+
                    'Project Name'+
                '</div>'+
                '<div class="span3 record-label ">'+
                  'Value'+
                '</div>'+
              '</div>';

      _.each(userData, function(value, project) {
        html += '<div class="span12 first time-sheet-row time-sheet-record">';

        html += '<div class="span9 first">'+
                  '<span class="normal">'+
                    '<span class="detail">'+
                      project+
                    '</span>'+
                  '</span>'+
                '</div>';

        html += '<div class="span3">'+
                  '<span class="normal">'+
                    '<span class="detail">'+
                      (parseInt(value) / 2)+
                    '%</span>'+
                  '</span>'+
                '</div>';

        html += '</div>';
      });

      html += '</div>';
      html += '</div>';

      iter++;
    });

    $('#timeSheetRecord').html(html);
  },

  showDetailsClicked: function(e) {
    
    var that = this,
        department = ($(e.currentTarget).data()).dep_name;
        TimeSheetPanel = Backbone.View.extend({
      
      events: {
        'click #saveButtonMonit':'saveClicked',
        'click #cancelButtonMonit':'cancelClicked',
      },

      initialize: function() {
        this.render();
      },

      render: function() {
        this.addPanel();
      },

      cancelClicked: function() {
        $('#timeSheetMonit').remove();
      },

      saveClicked: function() {
        $('#timeSheetMonit').remove();
      },

      addPanel: function() {
        if($('#limitInvoice').length == 0) {
          this.setElement($('body').append('<div id="timeSheetMonit"><div id="timeSheetPanel" class="modal"></div><div class="modal-backdrop"></div></div>') );
          var html = '<div class="modal-header"><h3><i class="fa fa-file-text-o"></i> '+department+'</h3></div>'+
                      '<div class="modal-body">'+
                          '<div class="panel-main">'+
                              '<div class="row-fluid department-users">'
                                  +(this.addTimeSheetData())+
                              '</div>'+
                          '</div>'+
                      '</div>'+
                      '<div class="modal-footer">'+
                          '<button id="cancelButtonMonit" class="btn btn-primary">Cancel</button>'+
                          '<button id="saveButtonMonit" class="btn btn-primary">Save</button>'+
                      '</div>';

          $('#timeSheetPanel').html(html);
        }
      },

      addTimeSheetData: function() {
        var html = '<div class="span12">';

        _.each(that.dataFetched[department].sheets_values, function(sheetData, period) {
          var sheetName = that.dataFetched[department].sheets[period].name;
          
          html += '<div class="span12">'+ sheetName +'</div>';

          html += '<div class="span12">'
          _.each(sheetData, function(userData, userName) {
            html += '<div class="span12">'+ userName +'</div>';
            
            html += '<div class="span12">'
            _.each(userData, function(value, project) {
              html += '<div class="span5">'+project+'</div>';
              html += '<div class="span5">'+value+'</div>';
            });

            html += '</div>';
          });

          html += '</div>';
        });

        html += '</div>';

        return html;
      },
    });

    var timeSheetPanel = new TimeSheetPanel();
  },

  cancelClicked: function() {
    this.view = "detail";
    location.replace('http://'+window.location.hostname+'/#AB_TimeSheetSummary/'+this.model.get("id"));
  },

  acceptedClicked: function(e) {
    var data = $(e.currentTarget).data();

    if(this.dataFetched[data.dep_name].summary.accepted == 0) {
      this.dataFetched[data.dep_name].summary.accepted = 1;
    } else {
      this.dataFetched[data.dep_name].summary.accepted = 0;
    }
  },

  saveClicked: function() {
    var self = this;

    _.each(this.dataFetched, function(users, projectTeam) {
      _.each(users, function(userData, userName) {
        if(userData.accepted == 1 && userData.rejected == 1) {
          app.alert.show('message-id', {
              level: 'confirmation',
              messages: 'You had accepted and rejected '+ userName +' Time Sheet',
              autoClose: false,
          });

          return;
        }
      });
    });

    $.ajax({
      url: 'index.php?entryPoint=getData&updateTimeSheetSummary=1&noCache='+ (new Date().getTime()),
      type: 'POST',
      data: {
        updated: self.dataFetched,
        summaryID: self.model.get('id'),
      },
      success: function(data) {
          self.view = "detail";
          self._super('saveClicked');
      },
    }); // ajax
  },
})
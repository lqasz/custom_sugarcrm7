({
  extendsFrom: 'RecordlistView',
  id: 'moduleTasks',

  /**
   * {@inheritDoc}
   * Constructor
   */
  initialize: function (options) {
    this._super('initialize', [options]);
    $('<style>'+ 
      'th.sorting_desc span:after { display: none; }'+
      'th.sorting_asc span:after { display: none; }'+
      'th-droppable-placeholder:nth-child(1) { display: none; }'+
      '.block-footer {display:none !important; }' +
      '</style>').appendTo('head');

    // bind "render next review date color" functionality with data syncronization
    this.listenTo(this.collection, 'data:sync:complete', this.renderNextReviewDateColor);
    // after click
    this.context.on('list:movetask:fire', this.moveTask, this); 
    this.context.on('list:closetask:fire', this.closeTask, this);
  }, // initialize

  /**
   * Function sets tasks status to the completed
   */
  closeTask: function(model) {
    var self = this,
        name = Handlebars.Utils.escapeExpression(app.utils.getRecordName(model)).trim(), // task name
        context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name; // module + record name

    // show sugar alert
    app.alert.show('complete_task_confirmation:' + model.get('id'), {
      level: 'confirmation', // confirm that message
      // message with module name and record name
      messages: app.utils.formatString(app.lang.get('LBL_ACTIVE_TASKS_DASHLET_CONFIRM_CLOSE'), [context]),
      onConfirm: function() {
        // set status to completed
        model.save({status: 'Completed'}, {
          showAlerts: true,
          success: function(e) { 
            // remove model
            self.collection.remove(model);
            self.collection.trigger('reset');
            self.context.resetLoadFlag();
            self.context.set('skipFetch', false);
             
            self.context.loadData({
              success: function() { 
                // data sync complete then "render next review date color"
                self.collection.trigger('data:sync:complete');
              } // success
            }); // loadData

            self.collection.trigger('data:sync:complete');
          } // success
        }); // save
      } // onConfirm
    }); // alert.show
  }, // closeTask

  /**
   * Function change tasks due date
   */
  moveTask: function(model){
    var self = this,
        name = Handlebars.Utils.escapeExpression(app.utils.getRecordName(model)).trim(), // task name
        context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name, // module + record name
        // get the due date after click, if due date is less then current date then take current date as due date
        duedate = (new Date(model.get('date_due')) < new Date()) ? new Date() : new Date(model.get('date_due')),
        oldDueDate = duedate.toString().split(" ");

    // to baypass weekend
    if(oldDueDate[0] == "Fri") { duedate.setDate(duedate.getDate() + 3); }
    else if(oldDueDate[0] == "Sat") { duedate.setDate(duedate.getDate() + 2); }
    else { duedate.setDate(duedate.getDate() + 1); }

    // show sugar alert
    app.alert.show('move_task_confirmation:' + model.get('id'), {
      level: 'confirmation', // confirm that message
      // message with module name and record name
      messages: app.utils.formatString(app.lang.get('LBL_ACTIVE_TASKS_DASHLET_CONFIRM_MOVE'), [context]),
      onConfirm: function() {
        var day = duedate.getDate(),
            month = duedate.getMonth() + 1,
            year = duedate.getFullYear();

          // ajax call to getData, query return array if new due date is a holiday
          app.api.call('GET', 'index.php?entryPoint=getData&isHoliday=1&day='+ day +'&month='+ month +'&year='+ year, null,{
            success: _.bind(function(data) {

              // if true then due date was a holiday but it has been moved 1 day
              if( !(data[0] == day) && (data[1] == month) && (data[2] == year) ) {
                duedate = new Date(data[2] +"-"+ data[1] +"-"+ data[0]);
                oldDueDate = duedate.toString().split(" ");

                // to baypass weekend
                if(oldDueDate[0] == "Sat") { duedate.setDate(duedate.getDate() + 2); }
                else if(oldDueDate[0] == "Sun") { duedate.setDate(duedate.getDate() + 1); }
              } // if
              // save task
              model.save({
                date_due: duedate,
                every_day_c: false,
                every_week_c: false,
                every_month_c: false
                },{
                  showAlerts: true,
                  success: function(e){ 
                    self.context.resetLoadFlag();
                    self.context.set('skipFetch', false);
                    if (_.isFunction(self.loadData)) {
                      self.loadData();
                    } else {
                      self.context.loadData();
                    } // if/else
                    self.collection.trigger('data:sync:complete');
                  } // success
              }); // save
            }, this) // success
          }) // call
        } // onConfirm
    }); // show
  }, // moveTask

  renderColors : function() {
    var self = this,
        userId = App.user.id;

    // changing colors, removing hrefs
    $('a[href^="#Opportunities/"]').css("color", "#6d17e5").css("text-align", "center");
    $('a[href^="#AA_RTeam/"]').removeAttr("href");
    $('a[href^="#AA_Persons/"]').removeAttr("href");
    $('a[href^="#AA_Departments/"]').removeAttr("href");

    setTimeout(function() {
      // loop over each row
      $("tr[name^='Tasks']").each(function () {
        $(this).find('div[data-original-title="High"]').css("color", "red").css("text-align", "center");
        $(this).find('div[data-original-title="Medium"]').css("color", "#176de5").css("text-align", "center");
        $(this).find('div[data-original-title="Low"]').css("color", "#555").css("text-align", "center");
        $(this).find('td:eq(5)').find('div').find('a').removeAttr("href");
      }); // each
    }, 1000); // setTimeout
  }, // renderColors

  renderNextReviewDateColor: function() {
    this.renderColors();
    $('.listTaskHeaders').remove();

    // remove classes which were responsible by sorting
    $("#moduleTasks th[data-fieldname='date_due']").removeClass();

    var importantDates = [],
        iter = 1,
        now = new Date(),
        tomorrow = new Date(),
        sevenDays = new Date();

    // set date variables
    now.setDate(now.getDate() );
    tomorrow.setDate(tomorrow.getDate() +1);
    sevenDays.setDate(sevenDays.getDate() + 7);
    now.setHours(0, 0, 0, 0);   
    tomorrow.setHours(0, 0, 0, 0);
    sevenDays.setHours(0, 0, 0, 0);    

    importantDates[0] = now;
    importantDates[1] = tomorrow;
    importantDates[2] = sevenDays;
    
    // set header to the list view
    $('#moduleTasks .dataTable tbody').prepend('<tr class="listTaskHeaders"><td colspan="100%">Overdue tasks</td></tr>');
    
    /**
     * ----------------------------------
     * Overdue tasks:
     *  some overdued task
     *  some overdued task 2
     * ----------------------------------
     * Today:
     *  some today task
     *  some today task 2
     * ----------------------------------
     * Tomorrow:
     * ----------------------------------
     * Next tasks:
     * some next task
     */

    _.each(this.rowFields, function(field) {

      // get Last Revenue Date field instance
      var obj = _.findWhere(field, {name: 'date_due'});

      // fix an error when Next Review Date hidden
      if (!_.isUndefined(obj)) {

        // get Last Revenue date
        var lastReviewDate = new Date(obj.model.get('date_due'));
        lastReviewDate.setHours(0, 0, 0, 0);

          // group by date
          if (_.isDate(lastReviewDate)) {
            switch(iter) {
              case 1:
                // if due_date is greater then tomorrow then add every header
                if(lastReviewDate.getTime() > importantDates[1].getTime()) {
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Today</td></tr>');
                  iter++; 
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Tomorrow</td></tr>');
                  iter++; 
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Next tasks</td></tr>');
                  iter++; 
                // if due_date equals to tomorrow
                } else if(lastReviewDate.getTime() == importantDates[1].getTime()) {
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Today</td></tr>');
                  iter++; 
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Tomorrow</td></tr>');
                  iter++; 
                // if due_date equals to today
                } else if(lastReviewDate.getTime() == importantDates[0].getTime()) {
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Today</td></tr>');
                  iter++;  
                } // if/elseif
                break;
              case 2:
                // if due_date is greater then tomorrow
                if(lastReviewDate.getTime() > importantDates[1].getTime()) {
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Tomorrow</td></tr>');
                  iter++; 
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Next tasks</td></tr>');
                  iter++;
                // if due_date equals to tomorrow
                } else if(lastReviewDate.getTime() == importantDates[1].getTime()) {
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Tomorrow</td></tr>');
                  iter++; 
                } // if/elseif
                break;
              case 3:
                // if due_date is greater then tomorrow
                if(lastReviewDate.getTime() > importantDates[1].getTime()) {
                  obj.$el.parent().parent().before('<tr class="listTaskHeaders"><td colspan="100%">Next tasks</td></tr>');
                  iter++;
                } // if
                break;
            } // switch

            // overdue tasks are red
            if(iter == 1) {
              obj.$el.parent().parent().addClass('Red');
            } // if
          } //if
        } // if
      obj.render();
    }); // each
    
    // after each function, put empty headers
    switch(iter) {
      case 0:
        $('#moduleTasks .block-footer').hide();
        $('#moduleTasks .dataTable tbody').append('<tr class="listTaskHeaders"><td colspan="100%">Today</td></tr>');
        $('#moduleTasks .dataTable tbody').append('<tr class="listTaskHeaders"><td colspan="100%">Tomorrow</td></tr>');
        $('#moduleTasks .dataTable tbody').append('<tr class="listTaskHeaders"><td colspan="100%">Next tasks</td></tr>');
        break;
      case 1:
        $('#moduleTasks .dataTable tbody').append('<tr class="listTaskHeaders"><td colspan="100%">Today</td></tr>');
        $('#moduleTasks .dataTable tbody').append('<tr class="listTaskHeaders"><td colspan="100%">Tomorrow</td></tr>');
        $('#moduleTasks .dataTable tbody').append('<tr class="listTaskHeaders"><td colspan="100%">Next tasks</td></tr>');
        break;
      case 2:
        $('#moduleTasks .dataTable tbody').append('<tr class="listTaskHeaders"><td colspan="100%">Tomorrow</td></tr>');
        $('#moduleTasks .dataTable tbody').append('<tr class="listTaskHeaders"><td colspan="100%">Next tasks</td></tr>');
        break;
    } // switch
  }, // renderNextReviewDateColor
})
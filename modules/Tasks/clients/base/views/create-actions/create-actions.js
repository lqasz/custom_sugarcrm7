({
    extendsFrom: 'CreateActionsView',
    id: 'TasksCreate',

    // set events
    events: _.extend({}, this.events, {
            'change [name=parent_type]':'getParentType',
            'change [name=parent_name]':'getParentName',
            'click #addQS':'getQS',
            'click #plus_day':'plusDay',
            'click #every_day':'everyDay',
            'click #every_week':'everyWeek',
            'click #every_month':'everyMonth',
    }), // events

    /**
     * {@inheritDoc}
     * Constructor
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins, ['LinkedModel']);
        this._super('initialize', [options]);

        $('<style>'+
            '#TasksCreate .row-fluid:nth-child(6), #TasksCreate .row-fluid:nth-child(7) { display:none; } ' +
          '</style>').appendTo('head');

        // set parent type list for specified users
        this.setParentTypeList();
        
        //add validation tasks
        this.model.addValidationTask('check_date_due', _.bind(this._doValidateCheckType, this));
    },// initialize

    /**
     * Function gets QS related to the project
     */
    getQS: function() {
        var self = this,
            projectID = this.model.get('parent_id'),
            requestProject = null,
            projectBean = SUGAR.App.data.createBean('Project', {id: projectID});

        // fetch qs from collection
        requestProject = projectBean.fetch();
        requestProject.xhr.done(function(data) {
            if(data.user_id1_c) {
                self.model.set('assigned_user_id', data.user_id1_c); // set qs id to the models assigned_user_id property
                self.model.set('assigned_user_name', data.qs_c); // set qs id to the models assigned_user_name property
            } else {
                console.warn("Brak QSa");
            } // if/else
        }); // ajax
    }, // getQS

    /**
     * Function gets PM related to the project
     */
    getPM: function() {
        var self = this,
            projectID = this.model.get('parent_id'),
            requestProject = null,
            projectBean = SUGAR.App.data.createBean('Project', {id: projectID});

        // fetch pm from collection
        requestProject = projectBean.fetch();
        requestProject.xhr.done(function(data) {
            if(data.user_id1_c) {
                self.model.set('assigned_user_id', data.user_id_c); // set pm id to the models assigned_user_id property
                self.model.set('assigned_user_name', data.pm_c); // set pm id to the models assigned_user_name property
            } else {
                console.warn("Brak PMa");
            } // if/else
        }); // ajax
    }, // getPM

    /**
     * Function gets user related to the persons module
     */
    getPerson: function() {
        var self = this,
            personID = this.model.get('parent_id'),
            requestPerson = null,
            personBean = SUGAR.App.data.createBean('AA_Persons', {id: personID});

        requestPerson = personBean.fetch();
        requestPerson.xhr.done(function(data) {
            self.model.set('assigned_user_id', personID); // set user id to the models assigned_user_id property
        }); // ajax
    }, // getPerson

    /**
     * Function sets responsible user to task related to the feeproposal
     */
    setResponsible: function() {
        var self = this,
            qsTeam = false,
            usersTeams = SUGAR.App.user.attributes.my_teams,
            usersID = SUGAR.App.user.id;

        // loop through user teams
        for(var i = 0; i < usersTeams.length; i++) {
            // user related to the qs department except board users
            if(usersTeams[i].id == "96dc75a8-bfdc-4232-88df-57492304ef7a" && !(usersID == "144c39bf-ccc3-65ec-2023-5407f7975b91" || usersID == "e07026a9-691a-67e7-32a6-5407f619ae5b")) {
                qsTeam = true;
            } // if
        } // for

        if(qsTeam === true) {
            // 
            self.model.set('assigned_user_id', usersID);
            self.model.set('assigned_user_name', SUGAR.App.user.attributes.full_name);
        } else {
            // 
            self.model.set('assigned_user_id', '801c0c78-edc1-e54f-08c2-5407f786ce48');
            self.model.set('assigned_user_name', 'QS MANAGER');
        } // if/else
    }, // setResponsible

    /*
    * Function render the view, if it is project task, then do not dispaly `parent_name` field
    */
    render: function() {
        var self = this;
        this._super('render');
        var projectID = this.model.get('parent_id') || '0';

        $('#TasksCreate').addClass('FromProject');
        $('.record-cell[data-name=new_one_c]').hide(0);

        if(projectID != '0') {
            $('div[data-name="parent_name"]').addClass('hide');
        } // if

        var $parent_name = this.$el.find('div.record-cell[data-name="parent_name"]');
        if($parent_name.hasClass('hide')) {
            self.$el.find('.record').find('.row-fluid.panel_body:eq(2)').find('.span6.record-cell:eq(1)').append('<input style="margin-left: -0.4%; margin-top: 1.7%;" class="button btn" type="button" id="addQS" name="addQS" value="Add QS">');
        } // if

        // add new buttons
        $(document).find('.record').find('.row-fluid.panel_body:eq(1)').find('.span6.record-cell:eq(0)').find('span').append('<input style=" margin-left: -6px; margin-bottom: 4px;" class="btn" type="button" id="plus_day" name="plus_day" value="+1 day">');
        $(document).find('.record').find('.row-fluid.panel_body:eq(1)').find('.span6.record-cell:eq(0)').find('span').append('<input style=" margin-left: 6px; margin-bottom: 4px;" class="btn every" type="button" id="every_day" name="every_day" value="every day">');
        $(document).find('.record').find('.row-fluid.panel_body:eq(1)').find('.span6.record-cell:eq(0)').find('span').append('<input style=" margin-left: 6px; margin-bottom: 4px;" class="btn every" type="button" id="every_week" name="every_week" value="every week">');
        $(document).find('.record').find('.row-fluid.panel_body:eq(1)').find('.span6.record-cell:eq(0)').find('span').append('<input style=" margin-left: 6px; margin-bottom: 4px;" class="btn every" type="button" id="every_month" name="every_month" value="every month">');
    }, // render

    /*
    *  Function do specified action after change the parent name with is in fact parent id
    */
    getParentName: function() {
        var self = this,
            userID = SUGAR.App.user.id,
            moduleName = (self.model.get('parent_type')) ? self.model.get('parent_type') : $('select[name="parent_type"]').filter(":selected").val(); // get parent type from list
        
        this.removeButtonQS();
        if(moduleName == 'AA_Persons') {
            self.getPerson();
        } // if

        if(moduleName == 'AC_FeeProposal') {
            self.setResponsible();
        } // if

        if(moduleName == 'Project') {
            // if qs button does not exists
            if(this.$el.find("#addQS").length == 0) { self.$el.find('.record').find('.row-fluid.panel_body:eq(2)').find('.span6.record-cell:eq(1)').append('<input style="margin-left: -0.4%; margin-top: 1.7%;" class="button btn" type="button" id="addQS" name="addQS" value="Add QS">'); } // if

            if(this.$el.find("#addQS").hasClass('hide')) { this.$el.find("#addQS").removeClass('hide'); } // if
            if(userID == "144c39bf-ccc3-65ec-2023-5407f7975b91" || userID == "e07026a9-691a-67e7-32a6-5407f619ae5b") { self.getPM(); } // if
        } // if
    }, // getParentName

    /*
     *  Function do specified action after change the parent type
     */
    getParentType: function() {
        var self = this,
            moduleName = (self.model.get('parent_type')) ? self.model.get('parent_type') : $('select[name="parent_type"]').filter(":selected").val(); // get parent type from list

        this.removeButtonQS();
        if(moduleName == 'AA_RTeam') {
            $('.record-cell[data-name="assigned_user_name"]').hide(0); // hide assigned user name field
            $('.flex-relate-record').hide(0); // hide parent id dropdown list
        } else if(moduleName == 'AA_Persons') {
            $('.record-cell[data-name="assigned_user_name"]').hide(0); // hide assigned user name field
        } else {
            if(moduleName == 'AA_Departments') {
                self.model.set('assigned_user_id', SUGAR.App.user.id);
                self.model.set('assigned_user_name', SUGAR.App.user.attributes.full_name);
            } // if

            $('.record-cell[data-name="assigned_user_name"]').show(0);
            $('.flex-relate-record').show(0);
        } // if/else
    }, // getParentType

    /*
     *  Function validates dates of task object
     */
    _doValidateCheckType: function(fields, errors, callback) {
        var self = this,
            today = new Date(),
            parent_name = self.model.get('parent_name'),
            dueDate = new Date(self.model.get('date_due')),
            startDate = new Date(self.model.get('date_start')),
            everyDay = self.model.get('every_day_c'),
            everyWeek = self.model.get('every_week_c'),
            everyMonth = self.model.get('every_month_c'),
            moduleName = (self.model.get('parent_type')) ? self.model.get('parent_type') : $('select[name="parent_type"]').filter(":selected").val();

        today.setDate(today.getDate() );
        today.setHours(0, 0, 0, 0);
        dueDate.setHours(0, 0, 0, 0);

        // validate requirements
        if (dueDate < today) {
            errors['date_due'] = errors['date_due'] || {};
            errors['date_due'].required = true; // then error
        } // if

        if((parent_name === undefined || parent_name === false || parent_name === "") && (moduleName != 'AA_RTeam')) {
            errors['parent_name'] = errors['parent_name'] || {};
            errors['parent_name'].required = true; // then error
        } // if

        if(everyDay == true || everyWeek == true || everyMonth == true) {
            // date errors
           if(startDate == "Invalid Date" || dueDate == "Invalid Date") {
                errors['date_due'] = errors['date_due'] || {};
                errors['date_start'] = errors['date_start'] || {};
                
                errors['date_due'].required = true;
                errors['date_start'].required = true;
           } // if
        } // if

        callback(null, fields, errors);
    }, // _doValidateCheckType

    /*
     *  Function hides qs button
     */
    removeButtonQS: function() {
        var buttonQS = $('#addQS').length;
        if(buttonQS !== 0) { $('#addQS').addClass('hide'); } // if
    }, // removeButtonQS

    /*
     *  Function sets specified dropdown list to the logged user
     */
    setParentTypeList: function() {
        var i,
            self = this,
            dev = false,
            board = false,
            myTeams = App.user.attributes.my_teams;

        for(i = 0; i < myTeams.length; i++) {
            if(myTeams[i].id == "1dc90971-b5cd-7168-2808-574923fed94e") { dev = true; } // if
            if(myTeams[i].id == "462fff89-1c17-981f-353b-57492384e7a2") { board = true; } // if
        } // for

        if(board === true) {
            self.model.fields.parent_type.options = "parent_type_display";
            self.model.fields.parent_name.options = "parent_type_display";
        } else if(dev === true) {
            self.model.fields.parent_type.options = "dev_parent_type";
            self.model.fields.parent_name.options = "dev_parent_type";
        } else {
            self.model.fields.parent_type.options = "new_parent_type";
            self.model.fields.parent_name.options = "new_parent_type";
        } // if/else
    }, // setParentTypeList

    /*
     *  Function adds 1 day to due date after click on the button
     */
    plusDay: function(event) {
        var self = this,
            dueDate = new Date(self.model.get('date_due')),
            oldDueDate = dueDate.toString().split(" ");

        if(oldDueDate[0] == "Fri") { dueDate.setDate(dueDate.getDate() + 3); } // add 3 days, baypass weekend
        else if(oldDueDate[0] == "Sat") { dueDate.setDate(dueDate.getDate() + 2); } // add 2 days, baypass weekend
        else { dueDate.setDate(dueDate.getDate() + 1); } // if/else

        var day = dueDate.getDate(),
            month = dueDate.getMonth() + 1,
            year = dueDate.getFullYear();

        // ajax GET to php script that check if our changed due date is not a holiday
        app.api.call('GET', 'index.php?entryPoint=getData&isHoliday=1&day='+ day +'&month='+ month +'&year='+ year, null,{
            success: _.bind(function(data) {
                // if due date match to the holiday, script returns new date
                if( !(data[0] == day) && (data[1] == month) && (data[2] == year) ) {
                    dueDate = new Date(data[2] +"-"+ data[1] +"-"+ data[0]);
                    oldDueDate = dueDate.toString().split(" ");

                    // check again if it is a weekend
                    if(oldDueDate[0] == "Sat") { dueDate.setDate(dueDate.getDate() + 2); }
                    else if(oldDueDate[0] == "Sun") { dueDate.setDate(dueDate.getDate() + 1); } // if/elseif
                } // if

                self.model.set('date_due', dueDate); // set new duedate
                self.render(); // render the view
            }, this) // success
        }) // ajax
    }, // plusDay

    /*
     *  Functions handled clicked actions to the buttons views
     */
    everyDay: function(event) {
        var self = this;

        if($(event.currentTarget).hasClass('clicked')) {
            self.model.set("every_day_c", false);
            $(event.currentTarget).removeClass('clicked'); 
        } else {
            self.model.set("every_day_c", true);
            $(event.currentTarget).addClass('clicked');
            $("#every_week").removeClass('clicked');
            $("#every_month").removeClass('clicked');
        } // if/else
    }, // everyDay

    everyWeek: function(event) {
        var self = this;

        if($(event.currentTarget).hasClass('clicked')) {
            self.model.set("every_week_c", false);
            $(event.currentTarget).removeClass('clicked');
        } else {
            self.model.set("every_week_c", true);
            $(event.currentTarget).addClass('clicked');
            $("#every_day").removeClass('clicked');
            $("#every_month").removeClass('clicked');
        } // if/else
    }, // everyWeek

    everyMonth: function(event) {
        var self = this;

        if($(event.currentTarget).hasClass('clicked')) {
            self.model.set("every_month_c", false);
            $(event.currentTarget).removeClass('clicked'); 
        } else {
            self.model.set("every_month_c", true);
            $(event.currentTarget).addClass('clicked');
            $("#every_day").removeClass('clicked');
            $("#every_week").removeClass('clicked');
        } // if/else
    }, // everyMonth
})
/*
 * Your installation or use of this SugaronsolRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * Notifications will pull information from the server based on a given delay.
 *
 * Supported properties:
 *
 * - {Number} delay How often (minutes) should the pulling mechanism run.
 * - {Number} limit Limit imposed to the number of records pulled.
 *
 * Example:
 * <pre><code>
 * // ...
 *     array(
 *         'delay' => 5,
 *         'limit' => 4,
 *     ),
 * //...
 * </code></pre>
 *
 * @class View.Views.Base.NotificationsView
 * @alias SUGAR.App.view.views.BaseNotificationsView
 * @extends View.View
 */
({
    id: 'menuNotifications',
    plugins: ['Dropdown', 'RelativeTime', 'EllipsisInline', 'LinkedModel'],

    /**
     * Notifications bean collection.
     *
     * @property {Data.BeanCollection}
     */
    collection: 0,
    oldOne: {},
    invoiceNotify: {},
    holidayNotify: {},

    /**
     * Collections for additional modules.
     */
    _alertsCollections: {},

    /**
     * @property {Number} Interval ID for checking reminders.
     */
    _remindersIntervalId: null,

    /**
     * @property {Number} Timestamp of last time when we checked reminders.
     */
    _remindersIntervalStamp: 0,

    /**
     * Interval ID defined when the pulling mechanism is running.
     *
     * @property {Number}
     * @protected
     */
    _intervalId: null,

    /**
     * Default options used when none are supplied through metadata.
     *
     * Supported options:
     * - delay: How often (minutes) should the pulling mechanism run.
     * - limit: Limit imposed to the number of records pulled.
     *
     * @property {Object}
     * @protected
     */
    _defaultOptions: {
        delay: 2,
        limit: 20
    },

    events: {
        'click [data-action=is-read-handler]': 'isReadHandler'
    },

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        options.module = 'Notifications';

        this._super('initialize', [options]);
        app.events.on('app:sync:complete', this._bootstrap, this);
        app.events.on('app:logout', this.stopPulling, this);

    },

    /**
     * Bootstrap feature requirements.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     * @protected
     */
    _bootstrap: function() {
        this._initOptions();
        this._initCollection();
        this._initReminders();
        this.startPulling();
        this.collection.on('change:is_read', this.render, this);

        return this;
    },

    /**
     * Initialize options, default options are used when none are supplied
     * through metadata.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     * @protected
     */
    _initOptions: function() {
        var options = _.extend(this._defaultOptions, this.meta || {});

        this.delay = options.delay * 60 * 250; //1000
        this.limit = options.limit;

        return this;
    },

    addClassOpen: function(){
        this.$('[data-name=notifications-list-button]').addClass('open');

    },
    removeClassOpen: function(){
        this.$('[data-name=notifications-list-button]').removeClass('open');
    },

    /**
     * Initialize feature collection.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     * @protected
     */
    _initCollection: function() {
        this.collection = app.data.createBeanCollection(this.module);
        this.collection.options = {
            params: {
                order_by: 'date_entered:desc'
            },
            limit: 50,
            myItems: true,
            fields: [
                'date_entered',
                'id',
                'is_read',
                'name',
                'severity',
                'parent_id',
                'description',
            ],
            apiOptions: {
                skipMetadataHash: true
            }
        };

        this.collection.filterDef = [{
            is_read: {$equals: false}
        }];

        // console.info('notifycations.js this collection %o', this.collection);
        return this;
    },

    /**
     * Initialize reminders for Calls and Meetings.
     *
     * Setup the reminderMaxTime that is based on maximum reminder time option
     * added to the pulls delay to get a big interval to grab for possible
     * reminders.
     * Setup collections for each module that we support with reminders.
     *
     * FIXME this will be removed when we integrate reminders with
     * Notifications on server side. This is why we have modules hardcoded.
     * We also don't check for meta as optional because it is required.
     * We will keep all this code private because we don't want to support it
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     * @private
     */
    _initReminders: function() {

        var timeOptions = _.keys(app.lang.getAppListStrings('reminder_time_options')),
            max = _.max(timeOptions, function(key) {
            return parseInt(key, 10);
        });

        this.reminderMaxTime = parseInt(max, 10) + this.delay / 1000;
        this.reminderDelay = 30 * 1000;

        _.each(['Calls', 'Meetings'], function(module) {
            this._alertsCollections[module] = app.data.createBeanCollection(module);
            this._alertsCollections[module].options = {
                limit: this.meta.remindersLimit,
                fields: ['date_start', 'id', 'name', 'reminder_time', 'location', 'parent_name']
            };
        }, this);

        return this;
    },

    /**
     * Start pulling mechanism, executes an immediate pull request and defines
     * an interval which is responsible for executing pull requests on time
     * based interval.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     */
    startPulling: function() {
        if (!_.isNull(this._intervalId)) {
            return this;
        }
        this._remindersIntervalStamp = new Date().getTime();

        this.pull();
        this._pullReminders();
        this._intervalId = window.setTimeout(_.bind(this._pullAction, this), this.delay);
        this._remindersIntervalId = window.setTimeout(_.bind(this.checkReminders, this), this.reminderDelay);
        return this;
    },

    /**
     * Pulling functionality.
     *
     * @protected
     */
    _pullAction: function() {
        if (!app.api.isAuthenticated()) {
            this.stopPulling();
            return;
        }

        this._intervalId = window.setTimeout(_.bind(this._pullAction, this), this.delay);
        this.pull();
        this._pullReminders();
    },

    /**
     * Stop pulling mechanism.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     */
    stopPulling: function() {
        if (!_.isNull(this._intervalId)) {
            window.clearTimeout(this._intervalId);
            this._intervalId = null;
        }
        if (!_.isNull(this._remindersIntervalId)) {
            window.clearTimeout(this._remindersIntervalId);
            this._remindersIntervalId = null;
        }
        return this;
    },

    /**
     * Pull and render notifications, if view isn't disposed or dropdown isn't
     * open.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     */
    pull: function() {
        if (this.disposed || this.isOpen()) {
            return this;
        }

        var self = this;
        var lastReviewDate = null;
        var now = new Date();
        now.setDate(now.getDate() );
        now.setHours(0, 0, 0, 0);
        var isQuestion;

        // console.info("W pull: "+ this.collection.length);
        self.collection.fetch({
            success: function() {

                if(self.getCookie('validInvoice')===''){ //App.user.id==1&&
                    self.invoiceNotify = _.filter(self.collection.models, function(model){
                        if( model.get('severity')==='invoice'){
                            return true; }else{return false; } 
                    });

                    if(self.invoiceNotify.length>4){
                        self.stopPulling();
                        self.showInvoiceNotification();
                        self.render();
                    }else{
                        self.setCookie("validInvoice", 1);
                    }
                    return this;
                }


                if( self.getCookie('validHolidays')==='' ){ //App.user.id==1 &&
                    self.holidayNotify = _.filter(self.collection.models, function(model){
                        if( model.get('severity')==='holidays'){
                            return true; }else{return false; } 
                    });
                    console.log('holidays');

                    if(self.holidayNotify.length>0){
                        self.stopPulling();
                        self.showHolidayNotification();
                        self.render();
                    }else{
                        self.setCookie("validHolidays", 1);
                    }
                    return this;
                }

                // getQuestions
                self.oldOne = _.filter(self.collection.models, function(model){
                    if(model.get('severity')==='education'){
                        lastReviewDate = new Date(model.get('date_entered'));
                        lastReviewDate.setHours(0, 0, 0, 0);

                        if(lastReviewDate.getTime() < now.getTime()) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                });

                self.oldTimeSheet = _.filter(self.collection.models, function(model){
                    if(model.get('severity')==='time sheet'){
                        lastReviewDate = new Date(model.get('date_entered'));
                        lastReviewDate.setHours(0, 0, 0, 0);

                        if(lastReviewDate.getTime() < now.getTime()) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                });

                if(self.oldTimeSheet.length > 0) {
                    self.stopPulling();
                    self.showTimeSheetView(self.oldTimeSheet[0]);
                    self.render();
                }

                if(self.oldOne.length>0) {
                    self.stopPulling();
                    
                    var ii = 0,
                        question_id = self.oldOne[ii].get('parent_id'),
                        qform = _.clone(self),
                        link = 'aa_questions_aa_answers_1',
                        qmodel = app.data.createBean('AA_Questions');

                    qmodel.set("id", question_id);
                    qform.collection = new Backbone.Collection();
                    qform.context = App.context.getContext("AA_Answers");
                    
                    model2 = qform.createLinkModel(qmodel, link);
                    quickCreateView = qform.layout.getComponent('quick-create');

                    if(!quickCreateView) {
                        var context = qform.context.getChildContext({
                            module: 'AA_Answers',
                            forceNew: true,
                            create: true,
                            model: model2,
                            link: link,
                            collection: qform.collection,
                        });
                        context.prepare();
                        context.set('parentModule', 'AA_Questions');
                       
                        // /** Create a new view object */
                        quickCreateView = app.view.createView({
                            context: context,
                            name: 'quick-create',
                            layout: qform.layout,
                            module: context.module,
                        });

                        self.layout._components.push(quickCreateView);
                        self.layout.$el.append(quickCreateView.$el);
                    }

                    self.layout.trigger("app:view:quick-create");
                }

                self.render();

                if(self.disposed || self.isOpen()) { //|| self.oldOne==undefined
                   return this;
                }
                
            },
            error: function() {
                console.group("Notification");
                console.log('Błąd, kod: 0ccbvn10');
                console.trace();
                console.groupEnd();
            }
        });

        var $refreshButton = $("#my-notifications").parent().parent().find("a[data-dashletaction='refreshClicked']");
        if($refreshButton.length>0){
            $refreshButton.click();
        }
        
        // this.layout._components[index2].render();
        this.render();
        return this;
    },

    /**
     * Pull next reminders from now to the next remindersMaxTime.
     *
     * This will give us all the reminders that should be triggered during the
     * next maximum reminders time (with pull delay).
     */
    _pullReminders: function() {

        if (this.disposed || !_.isFinite(this.reminderMaxTime)) {
            return this;
        }

        var date = new Date(),
            startDate = date.toISOString(),
            endDate;

        date.setTime(date.getTime() + this.reminderMaxTime * 1000);
        endDate = date.toISOString();

        _.each(['Calls', 'Meetings'], function(module) {
            // mateusz ruszkowski zdefiniowanie modułu kolkcji do notyfikacji, niezbędne po dodaniu quicktasków

            // console.log('kolekcja do notyfikacji %o',this._alertsCollections[module]);
            this._alertsCollections[module].module = module;
            this._alertsCollections[module].filterDef = _.extend({},
                this.meta.remindersFilterDef || {},
                {
                    'date_start': {'$dateBetween': [startDate, endDate]},
                    'users.id': {'$equals': app.user.get('id')}
                }
            );
            this._alertsCollections[module].fetch({
                silent: true,
                merge: true,
                //Notifications should never trigger a metadata refresh
                apiOptions: {skipMetadataHash: true}
            });
        }, this);

        return this;
    },

    /**
     * Check if there is a reminder we should show in the near future.
     *
     * If the reminder exists we immediately show it.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     */
    checkReminders: function() {
        if (!app.api.isAuthenticated()) {
            this.stopPulling();
            return this;
        }
        // console.log('check reminder');
        var date = (new Date()).getTime(),
            diff = this.reminderDelay - (date - this._remindersIntervalStamp) % this.reminderDelay;
        this._remindersIntervalId = window.setTimeout(_.bind(this.checkReminders, this), diff);
        _.each(this._alertsCollections, function(collection) {
            _.chain(collection.models)
                .filter(function(model) {
                    var needDate = (new Date(model.get('date_start'))).getTime() - parseInt(model.get('reminder_time'), 10) * 1000;
                    return needDate > this._remindersIntervalStamp && needDate - this._remindersIntervalStamp <= diff;
                }, this)
                .each(this._showReminderAlert, this);
        }, this);
        this._remindersIntervalStamp = date + diff;
        // console.log('after check reminder ');
        // console.log(this);
        return this;
    },

    /**
     * Show reminder alert based on given model.
     *
     * @param {Backbone.Model} model Model that is triggering a reminder.
     *
     * @private
     */
    _showReminderAlert: function(model) {
        console.log('show reminder' + model.module + '  ' + model.id);
        var url = app.router.buildRoute(model.module, model.id),
            dateFormat = app.user.getPreference('datepref') + ' ' + app.user.getPreference('timepref'),
            dateValue = app.date.format(new Date(model.get('date_start')), dateFormat),
            template = app.template.getView('notifications.notifications-alert'),
            message = template({
                title: app.lang.get('LBL_REMINDER_TITLE', model.module),
                module: model.module,
                model: model,
                location: model.get('location'),
                description: model.get('description'),
                dateStart: dateValue,
                parentName: model.get('parent_name')
            });
        _.defer(function() {

            if (confirm(message)) {
                app.router.navigate(url, {trigger: true});
            }
        });
    },
    
    showInvoiceNotification:function(){
        $qwer = this;

        var notifyAlert = Backbone.View.extend({
            events:{'click [data-action=removeme]':'removeme'},
            messages: new Array(),
            firstName: '',
            initialize: function(){
                var username = App.user.attributes.user_name.split(".");
                this.firstName = username[0].charAt(0).toUpperCase() + username[0].slice(1);
                this.messages[1] = 'Na%20Twoim%20koncie%20RMS%20s%C4%85%20ponad%204%20faktury%20do%20zaakceptowania.%3Cbr%20%2F%3E%3Cbr%20%2F%3EProsz%C4%99%20zaakceptuj%20je%2C%20u%C5%82atwi%20to%20%C5%BCycie%20wielu%20innym%20osobom%20%3A)%20Mi%C5%82ego%20dnia%20%3A)%3A)';
                this.messages[2] = 'Przypominam%20bardzo%20delikatnie%20i%20bez%20%C5%BCadnej%20presji%2C%20%C5%BCe%20mam%20dla%20Ciebie%20kilka%20faktur%20do%20zaakceptowania.%3Cbr%2F%3E%3Cbr%2F%3EJa%2C%20Tw%C3%B3j%20RMS%2C%20kt%C3%B3ry%20%C5%BCycz%C4%99%20Tobie%20mi%C5%82ego%20dnia%20%3A)';
                this.messages[3] = 'Mam%20nadziej%C4%99%2C%20%C5%BCe%20masz%20dobry%20dzie%C5%84%20%3A).%20%3Cbr%2F%3E%3Cbr%2F%3EJe%C5%BCeli%20chcesz%2C%20%C5%BCeby%20by%C5%82%20jeszcze%20lepszy%2C%20zaakceptuj%20faktury%20w%20RMS%20%3A).%20%3Cbr%20%2F%3EMi%C5%82ego%20dnia%20%3A)';
                this.messages[4] = 'Mam%20nadziej%C4%99%2C%20%C5%BCe%20czujesz%20si%C4%99%20%C5%9Bwietnie%2C%20tak%20jak%20ja%20%3A).%20%3Cbr%2F%3E%3Cbr%2F%3EAle%20je%C5%BCeli%20chcesz%20poprawi%C4%87%20mi%20jeszcze%20humor%2C%20zaakceptuj%20faktury%2C%20kt%C3%B3re%20przetrzymuje%20dla%20ciebie%20w%20Notification%20%3A).%20%3Cbr%20%2F%3E%C5%BBycz%C4%99%20Ci%20%C5%9Bwietnego%20dnia%20%3A)';
                this.messages[5] = 'W%20Notification%20masz%20obecnie%20ponad%204%20faktury%20do%20zaakceptowania.%3Cbr%2F%3E%3Cbr%2F%3EZaakceptuj%20je%20i%20podbij%20%C5%9Bwiat%20!!!%20%3A)%3Cbr%20%2F%3E%20%3Cbr%20%2F%3E%C5%BBycz%C4%99%20Tobie%20wspania%C5%82ego%20dnia%20!!!%20%3A)';
                this.render();
            },
            render: function(){
                if(jQuery('#limitInvoice').length == 0){
                    var whichOne = Math.floor((Math.random() * 5) + 1);
                    this.setElement(jQuery('body').append('<div id="limitInvoice" data-action="removeme"><div class="limitInvoiceMessage" data-action="removeme"></div></div>') );
                    jQuery(".limitInvoiceMessage").html(this.firstName+'<br /><br />'+ decodeURIComponent(this.messages[whichOne]));
                }
            },
            removeme: function(){
                console.log('remove me function');
                $qwer.setCookie("validInvoice", 1);
                $qwer.startPulling();
                jQuery('#limitInvoice').remove();
            }
          });

        $fff = new notifyAlert();
    },

    showHolidayNotification:function(){
        $qwer = this;

        var notifyAlert = Backbone.View.extend({
            events:{'click [data-action=removeme]':'removeme'},
            firstName: '',
            initialize: function(){
                var username = App.user.attributes.user_name.split(".");
                this.firstName = username[0].charAt(0).toUpperCase() + username[0].slice(1);
                this.render();
            },
            render: function(){
                if(jQuery('#limitInvoice').length == 0){
                    this.setElement(jQuery('body').append('<div id="limitInvoice" data-action="removeme"><div class="limitInvoiceMessage" data-action="removeme"></div></div>') );
                    jQuery('.limitInvoiceMessage').html(this.firstName+'<br /><br />'+decodeURIComponent('Bardzo%20delikatnie%20przypominam%2C%20%C5%BCe%20masz%20do%20zaakceptowania%20wnioski%20urlopowe.'));
                }
            },
            removeme: function(){
                // console.log('remove me function');
                $qwer.setCookie("validHolidays", 1);
                $qwer.startPulling();
                jQuery('#limitInvoice').remove();
            }
          });

        $fff = new notifyAlert();
    },

    showTimeSheetView: function(model) {
        var that = this,
        TimeSheetPanel = Backbone.View.extend({
            dataFetched: {},
            listOfProjects: [],
            events:{'click #saveButton':'saveClicked'},

            initialize: function() {
                var self = this;

                that.collection.on('data:sync:complete', function() {
                    // get all data from db
                    app.api.call('POST', 'index.php?entryPoint=getData&getAllTimeSheetData=1&time_sheet_id='+model.get('parent_id'), null,{
                        success: _.bind(function(data) {
                            self.dataFetched = data.time_sheet;
                            self.listOfProjects = data.projects;
                            self.trigger('rebuildFields'); // trigger event in model
                        })
                    });
                }, that);

                self.on('rebuildFields', function() {
                    self.render();
                });
            },

            render: function() {
                this.addPanel();
            },

            addPanel: function() {
                if($('#limitInvoice').length == 0){
                    this.setElement($('body').append('<div id="timeSheetMonit"><div id="timeSheetPanel" class="modal"></div><div class="modal-backdrop"></div></div>') );
                    var html = '<div class="modal-header"><h3><i class="fa fa-beer"></i>  Time Sheet</h3></div>'+
                                '<div class="modal-body">'+
                                    '<div class="panel-main">'+
                                        '<div class="row-fluid">'+model.get('description')+'</div>'+
                                        '<div class="row-fluid department-users">'
                                            +(this.addUsersForm())+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="modal-footer">'+
                                    '<button id="saveButton" class="btn btn-primary">save</button>'+
                                '</div>';

                    $('#timeSheetPanel').html(html);
                }
            },

            addUsersForm: function() {
                var self = this,
                    string = '';
                
                _.each(self.dataFetched.data, function(timeSheet, userID) {
                    string += '<div class="span12 first user-records">';
                    string += '<div class="span7 first" data-name="employee-name" data-userid="'+userID+'">'+self.dataFetched.users[userID]+'</div>';
                    string += '<div class="span1 first"><a data-userid="'+userID+'" class="add-project"><i class="fa-plus fa"></i></a></div>';
                    string += '</div>';
                });

                return string;
            },

            saveClicked: function() {
                $('#timeSheetMonit').remove();
            },
        });

        var timeSheetPanel = new TimeSheetPanel();
    },

    /**
     * Check if dropdown is open.
     *
     * @return {Boolean} `True` if dropdown is open, `false` otherwise.
     */
    isOpen: function() {
        return this.$('[data-name=notifications-list-button]').hasClass('open');
    },

    /**
     * Event handler for notifications.
     *
     * Whenever the user clicks a notification, its `is_read` property is
     * defined as read.
     *
     * We're doing this instead of a plain save in order to
     * prevent the case where an error could occur before the notification get
     * rendered, thus making it as read when the user didn't actually see it.
     *
     * @param {Event} event Click event.
     */
    isReadHandler: function(event) {
        var element = $(event.currentTarget),
            id = element.data('id'),
            notification = this.collection.get(id),
            isRead = notification.get('is_read');
            severity = notification.get('severity');
            console.debug("W isReadHandler: "+ this.collection.length);
        if (!isRead) {
            notification.set({is_read: true});
        }


        /** dodanie wyświetlenia formatki **/
        if (severity=='education') {
            event.preventDefault();
            event.stopPropagation();
            
            // do testów
            notification.set({is_read: false});
            var data = element.data(),
            question_id = data.notification_id,
            link_type = data.type,
            notification_id = data.id;

            if (Modernizr.touch) {
                app.$contentEl.addClass('content-overflow-visible');
            }

            /**
             * Check whether the view already exists in the layout.
             * If not we will create a new view and will add to the components list of the record layout
             * 
             */
            var self = this;
            var module = "AA_Answers";
            var model = null;

            self.collection = new Backbone.Collection();
            self.context = App.context.getContext(module);

            var parentModel = 'AA_Questions';
            var link = 'aa_questions_aa_answers_1';
            module = 'AA_Questions';
            model = app.data.createBean('AA_Questions');
            model.set("id", question_id);
            model2 = self.createLinkModel(model, link);
            quickCreateView = self.layout.getComponent('quick-create');

            if (!quickCreateView) {
                var context = self.context.getChildContext({
                    module: 'AA_Answers',
                    forceNew: true,
                    create: true,
                    model: model2,
                    link: link,

                });
                context.prepare();
               context.set('parentModule', parentModel);

               // /** Create a new view object */
               quickCreateView = app.view.createView({
                   context:context,
                   name: 'quick-create',
                   layout: self.layout,
                   module: context.module,
               });

                this.layout._components.push(quickCreateView);
                this.layout.$el.append(quickCreateView.$el);
            }

            this.layout.trigger("app:view:quick-create");
        } //is education

    },

    /**
     * {@inheritDoc}
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus === 'offline') {
            return;
        }

        this._super('_renderHtml');
    },

    /**
     * {@inheritDoc}
     *
     * Stops pulling for new notifications and disposes all reminders.
     */
    _dispose: function() {

        this.stopPulling();
        this._alertsCollections = {};

        this._super('_dispose');
    },
    getCookie: function(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                console.log('odczytano cookie');
                return c.substring(name.length, c.length);
            }
        }
        console.log('nie odczytano cookie');
        return "";
    },
    setCookie: function(cname, cvalue) {
        var d = new Date();
        console.log('dodano cookie' + cname + ' ' + cvalue);
        // d.setDate(d.getDate() +1);
        d.setHours(23, 59, 59, 0);
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    },
})

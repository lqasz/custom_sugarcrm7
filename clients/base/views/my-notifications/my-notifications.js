({
    plugins: ['Dashlet', 'LinkedModel'],
    _defaultOptions: {
        auto_refresh: 10000,
    },
    autoRefresh: 10000, // prawidłowe, zmiana z 3000 na 10000 ze względu na obciążenie serwera
    stopRefreshing: false,
    id: 'my-notifications',
    events: {
        'click [data-action=show-dailyquestion]': 'showDailyquestion',
        'click [data-action=show-invoice]': 'showInvoice',
        'click [data-action=confirm-invoice]': 'confirmInvoice',
        'click [data-action=confirm-notification]': 'confirmNotification',
        'click [data-action=confirm-information]': 'confirmInformation',
        'click [data-action=not-my-invoice]': 'doNotMyInvoice',
        // add action to view and accept invoices
    },
    collection: null,
    notifications: [],
    notifications2: [],
    notifications3: [],
    countner: 0,
    countner2: 0,
    number: null,
    documents: {},
            
    /**
     * Constructor
     */
    initialize: function(options) {
        var self = this;
        this._super('initialize', [options]);

        $(document).on("click", "div#review_background", function(event){
            $(this).remove();
            self.number = null;
            self.stopRefreshing = false;
        });

        // to move to another document
        $(document).on("click", "i.notes-arrows", function(event) {
            event.preventDefault();
            event.stopPropagation();
            var documentNumber = self.number,
                documentsMap = self.documents;

            console.info("Event");
            console.info(documentsMap);

            // get number of current document
            documentNumber = (self.number == null) ? _.indexOf(documentsMap.others, documentsMap.selected) : self.number;
            
            // move right or left to another document
            if($(event.currentTarget).hasClass('right-arrow')) {
                documentNumber = (documentNumber == documentsMap.others.length - 1) ? 0 : ++documentNumber;
            } else {
                documentNumber = (documentNumber == 0) ? documentsMap.others.length - 1 : --documentNumber;
            }

            self.number = documentNumber;
            console.info(documentNumber);

            // show selected document
            var $iFrame = $('#review_pdf iframe'),
                $noteElement = self.$el.find('a[data-noteid="'+ documentsMap.others[documentNumber] +'"]');

            // add class to attachment
            if(!$noteElement.hasClass('read')) {
                $noteElement.removeClass("unread");
                $noteElement.addClass("read");
            }

            self.removeDisable(documentsMap.invoiceID); // eneable confirm button
            $iFrame.attr('src', 'https://'+ window.location.hostname +'/rest/v10/Notes/'+documentsMap.others[documentNumber]+'/file/filename?force_download=0&platform=base&noteShare=1');
        });
    },

    /**
     * Function inits dashlet, if is set config from metadata then panels have dashlet configuration
     * else settings were loaded from default options, sets auto refresh timer if > 0 to *= 500
     */
    initDashlet: function(view) {
        if (this.meta.config) {
            this.meta.panels = this.dashletConfig.dashlet_config_panels;
        } else {
            this._scheduleReload(this.autoRefresh);
        }

        var settings = _.extend({}, this._defaultOptions, this.settings.attributes);
        this.settings.set(settings);
    },

    confirmInvoice: function(event) {
        var element = this.$(event.currentTarget),
            self = this,
            data = element.data(),
            parent_id = data.parentid,
            id = data.id;

        // remove notification
        element.parent().parent().parent().remove();
        // api which deletes notification
        app.api.call('GET', "index.php?entryPoint=getData&getNotification_id=1&deleteNotifi&parent_id="+ parent_id+"&id="+id+"&random"+Date.now(), null,{
            success: _.bind(function(data) {
                var index2 = _.indexOf(self.layout._components, _.findWhere(self.layout._components, { name: 'notifications' }));
                if(index2 != -1){
                    self.layout._components[index2].startPulling();
                }

                var invoiceBean = app.data.createBean('AC_Invoices',{id: parent_id}),
                    requestInvoice = invoiceBean.fetch();

                // add needed accepts
                requestInvoice.xhr.done(function(data) {
                    if(data.accept3_c === true) {
                        invoiceBean.set(_.extend({
                            'accept4_c': true
                        }, null));
                    } else if(data.accept2_c === true) {
                        invoiceBean.set(_.extend({
                            'accept3_c': true
                        }, null));
                    } else if(data.accept1_c === true) {
                        invoiceBean.set(_.extend({
                            'accept2_c': true
                        }, null));
                    } else {
                        invoiceBean.set(_.extend({
                            'accept1_c': true
                        }, null));
                    }

                    // save invoice
                    invoiceBean.save(null, {
                        success: function() {},
                    });
                });
            }, this)
        });
    },

    doNotMyInvoice: function(event) {
        var element = this.$(event.currentTarget),
            self = this,
            data = element.data(),
            parent_id = data.parentid;

        // remove notification
        element.parent().parent().parent().remove();
        // api which set not my invoice property in invoice bean and delete related notification
        app.api.call('GET', 'index.php?entryPoint=getData&notMyInvoice=1&invoice_id='+ parent_id +'&user_id='+ app.user.id, null,{
            success: _.bind(function(data) {
                var index2 = _.indexOf(self.layout._components, _.findWhere(self.layout._components, { name: 'notifications' }));
                
                if(index2 != -1) {
                    self.layout._components[index2].startPulling();
                }
            }, this)
        });
    },

    confirmNotification: function(event) {
        var element = this.$(event.currentTarget),
            self = this,
            data = element.data(),
            model = app.data.createBean('Notifications',{id: data.id});

        model.fetch();
        model.set('is_read', 1);
        model.save();

        var index2 = _.indexOf(this.layout._components, _.findWhere(this.layout._components, { name: 'notifications' }));
        if(index2 != -1){
            this.layout._components[index2].startPulling();
        }
        self._scheduleReload(self.autoRefresh);
        
        // remove notification
        element.parent().parent().parent().remove();
    },

    confirmInformation: function(event) {
        var element = this.$(event.currentTarget),
            self = this,
            data = element.data(),
            parent_id = (data.parentid) ? data.parentid : '';
            id = data.id;

        // remove notification
        if(data.remove === true) { element.parent().parent().parent().remove(); }
        app.api.call('get', "index.php?entryPoint=getData&getNotification_id=1&deleteNotifi&parent_id="+ parent_id+"&id="+id+"&random"+Date.now(), null,{
            success: _.bind(function(data) {
                var index2 = _.indexOf(self.layout._components, _.findWhere(self.layout._components, { name: 'notifications' }));
                if(index2 != -1){
                    self.layout._components[index2].startPulling();
                }
                self._scheduleReload(self.autoRefresh);
            }, self)
        });
    },

    /**
     * 1. Load properties
     * 2. Fetch data from current notification and set property `is_read` to true then save the model
     * 3. Create Answers module variable and get context from it
     * 4. Create Question module variable and set its id
     * 5. Fetch data from created link model Question and table `aa_questions_aa_answers_1` 
     * 6. Create view that contains question
     * 7. if view that contains question does not exist then create related context from module Answers and link between Question and table `aa_questions_aa_answers_1`
     * 8. Prepare context and set parent module to Questions
     * 9. Load the context to the view
     * 10. Load function loadData if is succes then load _scheduleReload function
     */
    showDailyquestion:function(event) {
        var element = this.$(event.currentTarget),
            data = element.data(),
            question_id = data.id,
            link_type = data.type,
            notification_id = data.notification_id;
        this._scheduleReloadStop(); // stop reloading

        $('.notification-list button').text($('.notification-list button').text()-1);
        // remove notification
        element.parent().parent().parent().remove();

        if (Modernizr.touch) {
            app.$contentEl.addClass('content-overflow-visible');
        }

        /**
         * Check whether the view already exists in the layout.
         * If not we will create a new view and will add to the components list of the record layout
         */
        var self = this;
        var module = "AA_Answers";
        model = null;
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

            // /** Create a new view */
            quickCreateView = app.view.createView({
                context:context,
                name: 'quick-create',
                layout: self.layout,
                module: context.module,
            });

            this.layout._components.push(quickCreateView);
            this.layout.$el.append(quickCreateView.$el);
            this._scheduleReloadStop();
        }
        this.layout.trigger("app:view:quick-create");
    },

    /**
     * Function creates action delay on `this` object
     */
    _scheduleReload: function(delay) {
        var self = this;

        if(self.timerId) {
            clearTimeout(self.timerId);
        }

        self.timerId = setTimeout(_.bind(function() {
            // Load function loadData if is success recursively load _scheduleReload function
            self.loadData();
        },self),delay);
    },

    _scheduleReloadStop: function() {
        var self = this;
        clearTimeout(self.timerId);
    },

    /**
     * Function load user id
     *      if user id is empty then return
     *      init `this` object collection
     *      fetch the data from collection if is success then `this` notification becomes model of initialized collection
     *      then render the view
     */
    loadData: function(options) {
        var user_id = app.user.id;
        var self = this;

        //check if user_id is empty
        if (_.isEmpty(user_id)) {
            return;
        }

        // api to fetch all notification related to the user
        app.api.call('post', 'index.php?entryPoint=getNotifications&getAll=1&userID='+user_id+"&random"+Date.now(), null, {
            success: _.bind(function (data) {
                if (data.length > 0 && self.stopRefreshing == false) {
                    if (self.disposed) {  // bez tego generuje błędy kiedy element został wygenerowany w innym view a to jego kopia                       
                        return;                         
                    }    
                    self._scheduleReload(self.autoRefresh);
                    self.notifications = data;
                    self.render();
                }
            }, this),
            error: _.bind(function (o) {
                console.log("Error retrieving Notifications for dashlet" + o);
                console.trace();
            }, this),
        });
    },

    showInvoice: function(event) {
        var self = this;
        event.preventDefault();
        event.stopPropagation();
        self._scheduleReloadStop();
        self.documents = {};
        self.stopRefreshing = true;

        var counter = 0,
            element = self.$(event.currentTarget),
            data = element.data(),
            invoice_id = data.invoiceid,
            note_id = data.noteid,
            relation_name = data.name;
        
        element.addClass('read');
        element.removeClass('unread');
        self.removeDisable(invoice_id);

        self.documents.others = [];
        self.documents.selected = note_id;
        self.documents.invoiceID = invoice_id;

        self.$el.find('a[data-invoiceid="'+ invoice_id +'"]').each(function(i) {
            counter++;
            self.documents.others[i] = $(this).data().noteid;
        });

        console.info(self.documents);

        if(counter > 1) {
            $('.dashboard').append('<div id="review_background"><div id="review_pdf"><i class="fa fa-chevron-left fa-4x notes-arrows left-arrow"></i><iframe id="review_iframe" title="PDF in an i-Frame" frameborder="1" scrolling="auto" height="100%" width="100%" ></iframe><i class="fa fa-chevron-right fa-4x notes-arrows right-arrow"></i></div></div>');
        } else {
            $('.dashboard').append('<div id="review_background"><div id="review_pdf"><iframe id="review_iframe" title="PDF in an i-Frame" frameborder="1" scrolling="auto" height="100%" width="100%" ></iframe></div></div>');
        }

        var $iFrame = $('#review_pdf iframe');
        $iFrame.load(function() {
            $('#review_pdf ').css("background","transparent");
        });

        $('#review_pdf ').css("background","grey");
        $iFrame.attr('src', 'https://'+ window.location.hostname +'/rest/v10/Notes/'+note_id+'/file/filename?force_download=0&platform=base&noteShare=1');
    },

    enableConfirm: function(invoice_id) {
        var counter = 0;
        this.$el.find('a[data-invoiceid="'+ invoice_id +'"]').each(function(i) {
            if($(this).hasClass('read')) {
                counter++;
            }
        });

        this._scheduleReloadStop();
        return this.$el.find('a[data-invoiceid="'+ invoice_id +'"]').length == counter;
    },

    removeDisable: function(invoice_id) {
        if(this.enableConfirm(invoice_id)) {
            this.$el.find('button[data-parentid="'+ invoice_id +'"]').each(function(iii){
                $(this).removeAttr('disabled');
            });
        }
    },
})
({
    extendsFrom: 'TasksDashletBaseView',
    id: 'tasksToday',

    /**
     * {@inheritDoc}
     *
     * @property {Object} _defaultSettings
     * @property {Number} _defaultSettings.limit Maximum number of records to
     *   load per request, defaults to '10'.
     * @property {String} _defaultSettings.visibility Records visibility
     *   regarding current user, supported values are 'user' and 'group',
     *   defaults to 'user'.
     */
    _defaultSettings: {
        limit: 50,
        visibility: 'user'
    },

    /**
     * {@inheritDoc}
     * Constructor
     */
    initialize: function(options) {
        options.meta = options.meta || {};
        options.meta.template = 'tasks-dashlet-base';

        this.plugins = _.union(this.plugins, [
            'LinkedModel'
        ]);

        this.tbodyTag = 'ul[data-action="pagination-body"]';

        this._super('initialize', [options]);
    },

    /**
     * {@inheritDoc}
     * @return events to process the record.
     */
    _initEvents: function() {
        this._super('_initEvents');
        this.on('tasks-actions:close-task:fire', this.closeTask, this);
        this.on('tasks-actions:move-task:fire', this.moveTask, this);
        return this;
    },

    /**
     * Completes the selected task.
     * Shows a confirmation alert and sets the task as `Completed` on confirm.
     *
     * @param {Data.Bean} model The task to be marked as completed.
     */
    closeTask: function(model){
        var self = this;
        var name = Handlebars.Utils.escapeExpression(app.utils.getRecordName(model)).trim();
        var context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name;
        app.alert.show('complete_task_confirmation:' + model.get('id'), {
            level: 'confirmation',
            messages: app.utils.formatString(app.lang.get('LBL_ACTIVE_TASKS_DASHLET_CONFIRM_CLOSE'), [context]),
            onConfirm: function() {
                model.save({status: 'Completed'}, {
                    showAlerts: true,
                    success: self._getRemoveModelCompleteCallback()
                });
            }
        });
    },

    /**
     * Move the selected task.
     * Shows a confirmation alert and sets +1 day to task, refresh next dashlet
     *
     * @param {Data.Bean} model The task to be marked as completed.
     */
    moveTask: function(model){
        var self = this;
        var name = Handlebars.Utils.escapeExpression(app.utils.getRecordName(model)).trim();
        var context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name;
        var duedate = new Date(model.get('date_due'));
            oldDueDate =  duedate.toString().split(" ");

        if(oldDueDate[0] == "Fri") { duedate.setDate(duedate.getDate() + 3); }
        else if(oldDueDate[0] == "Sat") { duedate.setDate(duedate.getDate() + 2); }
        else { duedate.setDate(duedate.getDate() + 1); }

        var day = duedate.getDate(),
            month = duedate.getMonth() + 1,
            year = duedate.getFullYear();

        app.api.call('GET', 'index.php?entryPoint=getData&isHoliday=1&day='+ day +'&month='+ month +'&year='+ year, null,{
            success: _.bind(function(data) {

                if( !(data[0] == day) && (data[1] == month) && (data[2] == year) ) {
                    duedate = new Date(data[2] +"-"+ data[1] +"-"+ data[0]);
                    oldDueDate = duedate.toString().split(" ");

                    if(oldDueDate[0] == "Sat") { duedate.setDate(duedate.getDate() + 2); }
                    else if(oldDueDate[0] == "Sun") { duedate.setDate(duedate.getDate() + 1); }
                }
                model.save({
                    date_due: duedate,
                    every_day_c: false,
                    every_week_c: false,
                    every_month_c: false
                }, {
                    showAlerts: false,
                    success: function(e){

                        // Refreshing selected dashlet
                        var $refreshButton = $("#tasks-today").parent().parent().find("a[data-dashletaction='refreshClicked']");
                        $refreshButton.click();
                        var $refreshButton = $("#tasks-tomorrow").parent().parent().find("a[data-dashletaction='refreshClicked']");
                        $refreshButton.click();
                        console.log('refresh today');
                        // self._getRemoveModelCompleteCallback();
                    },
                    error: function() {
                        console.log('-----------------------------------------------');
                    }
                });
            }, this)
        })
    },

    /**
     * {@inheritDoc}
     *
     * FIXME: This should be removed when metadata supports date operators to
     * allow one to define relative dates for date filters.
     */
    _initTabs: function() {
        this._super("_initTabs");
    },

    /**
     * New model related properties are injected into each model.
     * Update the picture url's property for model's assigned user.
     * Update the opportunitie's color.
     *
     * @param {Bean} model Appended new model.
     */
    bindCollectionAdd: function(model) {
        var pictureUrl = app.api.buildFileURL({
            module: 'Users',
            id: model.get('assigned_user_id'),
            field: 'picture'
        });
        // mruszkowski zmiana, błąd przy dodawaniu dashletów na stronę główną
        if(!model){
            debugger;
            return;
        }
        var username = model.get('created_by_name');
        var user_name = username.substring(0,1)+". ";
        var lastname = username.split(" ");
        var user_last = lastname[1];

        var userId = App.user.id;
        var parent_type = model.get('parent_type');
        var parent_id = model.get('parent_id');

        // jeżeli mamy brak id niech parent_name równy będzie parent_type
        if(parent_id===''){
            console.log('brak id przy rekordzie '+model.get('parent_id'));
            model.set('islink', 0);

            if(parent_type == 'AA_Persons') {
                model.set('parent_name', 'Person');
            } else if(parent_type == 'AA_RTeam') {
                model.set('parent_name', 'Reesco');
            } else if(parent_type == 'AA_Buildings') {
                model.set('parent_name', 'Building');
            } else if(parent_type == 'Opportunities') {
                model.set('color', '#6d17e5');
            } else {
                model.set('parent_name', parent_type);
            }

        }else{

            if(parent_type == 'AA_Persons') {
                model.set('parent_name', 'Person');
                model.set('islink', 0);
            } else if(parent_type == 'AA_RTeam') {
                model.set('parent_name', 'Reesco');
                model.set('islink', 0);
            } else if(parent_type == 'Opportunities') {
                model.set('color', '#6d17e5');
                model.set('islink', 1);
            } else {
                if((userId != "144c39bf-ccc3-65ec-2023-5407f7975b91" && userId != "ada95982-6143-43d9-e3ae-540f494996bf" && userId != "e07026a9-691a-67e7-32a6-5407f619ae5b" && userId != "137a88d7-df78-8f89-9c4c-540f4ad585e4" && userId != "4f8dce84-18ed-b30b-9658-5807486740a0") && (parent_type == 'AA_Departments')) {
                    model.set('islink', 0);
                } else {
                    model.set('islink', 1);
                }
            }

        }

        model.set('picture_url', pictureUrl);
        model.set('user_name', user_name);
        model.set('user_last', user_last);
        model.set('picture_url', pictureUrl);
        this._super('bindCollectionAdd', [model]);
    },

    /**
     * Render the view
     */
    _renderHtml: function() {
        if (this.meta.config) {
            this._super('_renderHtml');
            return;
        }

        var tab = this.tabs[this.settings.get('activeTab')];

        if (tab.overdue_badge) {
            this.overdueBadge = tab.overdue_badge;
        }

        this._super('_renderHtml');
    }
})

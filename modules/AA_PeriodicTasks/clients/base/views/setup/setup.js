({
    id: 'PeriodicTasks',

    // some usefull events
    events: _.extend({}, this.events, {
        'click .plus': 'addClicked',
        'click .minus': 'minusClicked',
        'click .btn[name="edit_button"]': 'editClicked',
        'click .btn[name="save_button"]': 'saveClicked',
        'click .btn[name="cancel_button"]': 'cancelClicked',
        'blur input[name="task_name"]': 'setTaskName',
        'change .time-period': 'setTimePeriod',
        'click .task-dep': 'setRelatedDepartments',
    }),
    
    edit: false, // for rendering template
    periodicTasks: {}, // main model

    initialize: function(options) {
        this._super('initialize', [options]);
        this.fetchModel();
    },

    render: function() {
        this._super('render');
    },

    /*
     * Fetch all data from db to the model
     */
    fetchModel: function() {
        var self = this;
        app.api.call('GET', 'index.php?entryPoint=periodicTasks&getTasks=1', null,{
            success: _.bind(function(data) {
                self.periodicTasks = data;
                self.render();
            })
        }); // call
    },

    /*
     * Delete `bad` records from model
     */
    cleanModel: function() {
        var self = this;

        _.each(this.periodicTasks, function(positionData, position) {
            _.each(positionData.tasks, function(task, taskID) {
                self.periodicTasks[position].tasks[taskID].update = 0;

                if(task.name == "" || task.deleted == 1) {
                    delete self.periodicTasks[position].tasks[taskID];
                }
            }); // each
        }); // each
    },

    /*
     * Check for the data correctness
     * @return validation message
     */
    validateModel: function() {
        var self = this,
            callBack = {
                "error": "",
                "validation": true
            };

        _.each(this.periodicTasks, function(positionData, position) {
            _.each(positionData.tasks, function(task, taskID) {
                if(_.isEmpty(task.departments)) {
                    callBack.validation = false;
                    callBack.error = "departments";
                } else {
                    callBack.validation = false;
                    callBack.error = "departments";

                    _.each(task.departments, function(value, key) {
                        if(value != undefined) {
                            callBack.validation = true;
                            callBack.error = "";
                        }
                    }); // each
                }

                if(task.name.trim() == "") {
                    callBack.validation = false;
                    callBack.error = "name";
                }

                if(task.dayOfWeek.trim() == "" || task.dayOfMonth.trim() == "" || task.month.trim() == "") {
                    callBack.validation = false;
                    callBack.error = "periods";
                } else {
                    if(task.dayOfWeek != "*" && task.dayOfMonth != "*") {
                        callBack.validation = false;
                        callBack.error = "wrong_periods";
                    }
                }
            }); // each
        }); // each

        return callBack;
    },

    /*
     * Add new row to the view and new record to the model
     */
    addClicked: function(event) {
        var self = this,
            unique = App.utils.generateUUID(),
            newRowData = $(event.currentTarget).parent().parent().parent().data(),
            string = '<div class="span12 periodic-task first" data-id="'+unique+'">'+
                        '<div class="span4">'+
                            '<input type="text" class="span12" name="task_name"/>'+
                        '</div>'+
                        '<div class="span4">'+
                            self.returnDayOfWeekField()+
                        '</div>'+
                        '<div class="span4">'+
                            self.returnDepartmentsField()+
                        '</div>'+
                    '</div>';
        
        $(event.currentTarget).parent().parent().next('.periodic-tasks-content').append(string);

        // create new record
        if(_.isEmpty(self.periodicTasks[newRowData.name].tasks)) {
            self.periodicTasks[newRowData.name].tasks = {};
        }

        // set parameters to default
        self.periodicTasks[newRowData.name].tasks[unique] = {
            'name': '',
            'dayOfWeek': '*',
            'dayOfMonth': '*',
            'month': '*',
            'departments': [],
            'update': 0,
            'deleted': 0,
            'new': 1,
        };
    },

    /*
     * Remove row from the view and set model`s delete flag to true
     */
    minusClicked: function(event) {
        var self = this,
            $task = $(event.currentTarget).parent().parent(),
            position = ($task.parent().parent().data()).name;

        if(self.periodicTasks[position].tasks[($task.data()).id].new == 1) {
            delete self.periodicTasks[position].tasks[($task.data()).id];
        } else {
            self.periodicTasks[position].tasks[($task.data()).id].deleted = 1;   
        }
        
        $task.remove();
    },

    /*
     * Set record`s task name
     */
    setTaskName: function(event) {
        var self = this,
            $task = $(event.currentTarget).parent().parent(),
            position = ($task.parent().parent().data()).name;

        self.periodicTasks[position].tasks[($task.data()).id].new = 0;
        self.periodicTasks[position].tasks[($task.data()).id].update = 1;
        self.periodicTasks[position].tasks[($task.data()).id].name = $(event.currentTarget).val();
    },

    /*
     * Set record`s time period
     */
    setTimePeriod: function(event) {
        var self = this,
            $element = $(event.currentTarget),
            $task = $element.parent().parent(),
            position = ($task.parent().parent().data()).name,
            value = $element.val();

        if($element.hasClass('day-of-week')) {
            self.periodicTasks[position].tasks[($task.data()).id].dayOfWeek = value;
        } else if($element.hasClass('day-of-month')) {
            self.periodicTasks[position].tasks[($task.data()).id].dayOfMonth = value;
        } else {
            self.periodicTasks[position].tasks[($task.data()).id].month = value;
        }

        self.periodicTasks[position].tasks[($task.data()).id].departments;
        self.periodicTasks[position].tasks[($task.data()).id].new = 0;
        self.periodicTasks[position].tasks[($task.data()).id].update = 1;
    },

    /*
     * Set record`s related departments where task can appear
     */
    setRelatedDepartments: function(event) {
        var self = this,
            $element = $(event.currentTarget),
            $task = $element.parent().parent().parent(),
            position = ($task.parent().parent().data()).name,
            value = $element.val(),
            departments = self.periodicTasks[position].tasks[($task.data()).id].departments;

        if(!_.isEmpty(departments)) {
            var index = departments.indexOf(value);
            if(index == -1) {
                self.periodicTasks[position].tasks[($task.data()).id].departments.push(value); // add to model action
            } else {
                delete self.periodicTasks[position].tasks[($task.data()).id].departments[index]; // delete from model action
            }
        } else {
            self.periodicTasks[position].tasks[($task.data()).id].departments.push(value); // add to model action
        }

        self.periodicTasks[position].tasks[($task.data()).id].new = 0;
        self.periodicTasks[position].tasks[($task.data()).id].update = 1;
    },

    /*
     * Functon run validation and save model to db
     */
    saveClicked: function(event) {
        var self = this;

        $(event.currentTarget).attr("disabled", "disabled");
        $('a[name="cancel_button"]').attr("disabled", "disabled");

        $('#alerts').append('<div id="loadingPane">'+
                                '<div id="loadingIcon">'+
                                    '<i class="fa fa-spinner fa-spin fa-2x pull-left"></i> Generating'+
                                '</div>'+
                            '</div>');

        // run validation
        if(self.validateModel().validation) {
            $.ajax({
                url: 'index.php?entryPoint=periodicTasks&update=1',
                type: 'POST',
                data: {
                    JSONperiodicTasks: self.periodicTasks, // all data
                },
                success: function(data) {
                    self.edit = false;
                    self.cleanModel();
                    $('#loadingPane').remove();
                    self.render();
                },
            });
        } else {
            var message = "";

            // get message
            switch(self.validateModel().error) {
                case 'name':
                    message = "Pole `Nazwa zadania` nie może być puste";
                break;
                case 'departments':
                    message = "Musisz wybrać conajmniej jeden departament";
                break;
                case 'periods':
                    message = "Wszystkie pola związane z cyklem zadań muszą być wypełnine";
                break;
                case 'wrong_periods':
                    message = "Pole `Dzień tygonia` i pole `Dzień miesiąca` nie mogą mieć jednocześnie zdefiniowanej reguły";
                break;
            }

            // show alert box
            app.alert.show('message-id', {
                level: 'confirmation',
                messages: message,
                autoClose: false,
            });
        }
    },

    /*
     * Set view option and render the view
     */
    editClicked: function() {
        this.edit = true;
        this.render();
    },

    /*
     * Set view option, clean model and render the view
     */
    cancelClicked: function() {
        this.edit = false;
        this.cleanModel();
        this.render();
    },

    _renderHtml: function() {
        this._super('_renderHtml');
    },

    /*
     * @return departments view in html
     */
    returnDepartmentsField() {
        return '<div class="span1 task-center"><input class="task-dep" value="pt" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="qs" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="it" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="fa" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="dev" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="ct" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="board" type="checkbox"/></div>'+
                '<div class="span3 btn btn-invisible minus"><i class="fa fa-times"></i></div>';
    },

    /*
     * @return period view in html
     */
    returnDayOfWeekField() {
        return '<input type="text" class="span4 time-period day-of-week" value="*"/>'+
                '<input type="text" class="span4 time-period day-of-month" value="*"/>'+
                '<input type="text" class="span4 time-period month" value="*"/>';
    },
})
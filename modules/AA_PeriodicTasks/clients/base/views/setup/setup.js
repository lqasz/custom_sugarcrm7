({
    id: 'PeriodicTasks',
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
    
    edit: false,
    periodicTasks: {},

    initialize: function(options) {
        this._super('initialize', [options]);
        this.fetchModel();
    },

    render: function() {
        this._super('render');
    },

    fetchModel: function() {
        var self = this;
        app.api.call('GET', 'index.php?entryPoint=periodicTasks&getTasks=1', null,{
            success: _.bind(function(data) {
                self.periodicTasks = data;
                self.render();
            })
        });
    },

    cleanModel: function() {
        var self = this;

        _.each(this.periodicTasks, function(positionData, position) {
            _.each(positionData.tasks, function(task, taskID) {
                self.periodicTasks[position].tasks[taskID].update = 0;

                if(task.name == "" || task.deleted == 1) {
                    delete self.periodicTasks[position].tasks[taskID];
                }
            });
        });
    },

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

        if(_.isEmpty(self.periodicTasks[newRowData.name].tasks)) {
            self.periodicTasks[newRowData.name].tasks = {};
        }

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

    setTaskName: function(event) {
        var self = this,
            $task = $(event.currentTarget).parent().parent(),
            position = ($task.parent().parent().data()).name;

        self.periodicTasks[position].tasks[($task.data()).id].new = 0;
        self.periodicTasks[position].tasks[($task.data()).id].update = 1;
        self.periodicTasks[position].tasks[($task.data()).id].name = $(event.currentTarget).val();
    },

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

    setRelatedDepartments: function(event) {
        var self = this,
            $element = $(event.currentTarget),
            $task = $element.parent().parent().parent(),
            position = ($task.parent().parent().data()).name,
            value = $element.val(),
            departments = self.periodicTasks[position].tasks[($task.data()).id].departments;

        if(!_.isEmpty(departments)) {
            if(departments.indexOf(value) == -1) {
                self.periodicTasks[position].tasks[($task.data()).id].departments.push(value);
            } else {
                delete self.periodicTasks[position].tasks[($task.data()).id].departments[value];
            }
        } else {
            self.periodicTasks[position].tasks[($task.data()).id].departments.push(value);
        }

        self.periodicTasks[position].tasks[($task.data()).id].new = 0;
        self.periodicTasks[position].tasks[($task.data()).id].update = 1;
    },

    saveClicked: function() {
        var self = this;

        $.ajax({
            url: 'index.php?entryPoint=periodicTasks&update=1',
            type: 'POST',
            data: {
                JSONperiodicTasks: self.periodicTasks,
            },
            success: function(data) {
                self.edit = false;
                self.cleanModel();
                self.render();
            },
        });
    },

    editClicked: function() {
        this.edit = true;
        this.render();
    },

    cancelClicked: function() {
        this.edit = false;
        this.cleanModel();
        this.render();
    },

    _renderHtml: function() {
        this._super('_renderHtml');
    },

    returnDepartmentsField() {
        return '<div class="span1 task-center"><input class="task-dep" value="pt" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="qs" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="it" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="fa" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="dev" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="ct" type="checkbox"/></div>'+
                '<div class="span1 task-center"><input class="task-dep" value="b" type="checkbox"/></div>'+
                '<div class="span3 btn btn-invisible minus"><i class="fa fa-times"></i></div>';
    },

    returnDayOfWeekField() {
        return '<input type="text" class="span4 time-period day-of-week" value="*"/>'+
                '<input type="text" class="span4 time-period day-of-month" value="*"/>'+
                '<input type="text" class="span4 time-period month" value="*"/>';
    },
})
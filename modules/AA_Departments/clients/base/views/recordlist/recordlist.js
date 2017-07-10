({
    extendsFrom: 'RecordlistView',
    departments: undefined,

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args:[options]});

        var self = this;
        // get all departments to the model
        self.collection.on('data:sync:complete', function() {
            app.api.call('GET', 'index.php?entryPoint=getData&rebuildDepList=1', null,{
                success: _.bind(function(data) {
                    self.departments = data;
                    self.render();
                })
            });
        });
    },

    render: function() {
    	this._super('render');
    	$(".table tr th:eq(0)").hide(0);
        $(".table tr th:eq(2)").hide(0);

        var self = this;
        if(!_.isEmpty(self.departments)) {
            $("tr[name^='AA_Departments']").each(function () {
                var departmentID = $(this).attr('name').split('AA_Departments_')[1]; // get department id

                $(this).css("border", "none");
                $(this).html(self.generateFields(departmentID));
            });
        }
    },

    /**
     * Function create new row view for each department
     * @param departmentID
     */
    generateFields: function(departmentID) {
        var departmentName = (this.collection.where({id: departmentID}))[0].get("name"),
            string = '<td style="text-align: left; padding: 0; background: #fff;">'+
                        '<div style="padding: 4px 0% 0px 1%; background-color: #f6f6f6; border: 1px solid #ddd;" class="ellipsis_inline span12" data-placement="bottom" data-original-title="'+departmentName+'">'+
                            '<a href="#AA_Departments/'+departmentID+'">'+departmentName+'</a>'+
                        '</div>'+
                        '<div class="span12 first" style="padding: 1% 0 0 0;">'+
                            '<ul>';

        _.each(this.departments[departmentID], function(user, key) {
            string += "<li class='span12 first'><div class='span3'>"+user.name+"</div><div class='span6'>"+user.position+"</div></li>";
        });

        string += "</ul></div></td>";
        return string;
    }
})
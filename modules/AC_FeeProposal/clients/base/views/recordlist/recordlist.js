({

    extendsFrom: 'RecordlistView',
    initialize: function (options) {

        // this._super("initialize", [options]);

        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args:[options]});
        var self = this;
        //add listener for custom button
        this.context.on('list:makeproject:fire', this.addtoproject, this);

        // tylko JJ może tworzyć projekty w RMS
        if(App.user.id !== "144c39bf-ccc3-65ec-2023-5407f7975b91"  && App.user.id !== "e07026a9-691a-67e7-32a6-5407f619ae5b"){
            $('<style>'+
              '.single .btn-group .btn.dropdown-toggle { display: none !important; }'+
             // '.dataTable>thead>tr>th>span { display: none; }'+
              '</style>').appendTo('head');
        }

        this.collection.on('data:sync:complete', function() {
            this.render_colors();
        }, this);
    },

    render_colors : function() {
        setTimeout(
            function() {
                $("tr[name^='AC_FeeProposal']").each(function () { //loop over each row
                    if ($(this).find('div[data-original-title="Won"]').length > 0) { //check value of TD
                        $(this).find($('td')).each(function () {
                            $(this).css("background-color", "#70b933");
                        });
                    } else if($(this).find('div[data-original-title="Prospecting"]').length > 0) {
                        $(this).find($('td')).each(function () {
                            $(this).css("background-color", "#26C8FE");
                        });
                    }
                    // else if ($(this).find('div[data-original-title="Green"]').length > 0) {
                    //     $(this).find($('td')).each(function () {
                    //         $(this).css("background-color", "#C3F8B5");
                    //     });
                    // } else if ($(this).find('div[data-original-title="Orange"]').length > 0) {
                    //     $(this).find($('td')).each(function () {
                    //         $(this).css("background-color", "#FFCF8F");
                    //     });
                    // } else if ($(this).find('div[data-original-title="Yellow"]').length > 0) {
                    //     $(this).find($('td')).each(function () {
                    //         $(this).css("background-color", "#FAFE8E");
                    //     });
                    // }
                });
            }, 
        1000);
    },
    _initEvents: function() {
        this._super('_initEvents');
        this.on('list:addtoproject:fire', this.addtoproject, this);
        return this;
    },
    addtoproject : function(model) {
        console.log('ddd');
        console.log('ddd'+model );
        var self = this;

        var name = Handlebars.Utils.escapeExpression(app.utils.getRecordName(model)).trim();
        // var context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name;
        var context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name;
        var jestID = model.toString().split("/");

        var idCSV = '';
        var selector = 'AC_FeeProposal';

        window.location.href = '#bwc/index.php?module=Project&action=EditView&return_module=Project&return_action=DetailView&feeid='+jestID[1]+"&feename=" + name;

        // console.log('ddd'+jestID[1] + ' name ' + name);

        // $.ajax({
        //     url: 'index.php?module=CSTM_ANIMALS&action=ADD_TO_CIRCUS',
        //     type: 'POST',
        //     data: {uid: idCSV},
        //     success: function(errorResponse) {
        //         if(errorResponse != '') {
        //             app.alert.show('bad-add-to-circus', {
        //                 level: 'error',
        //                 messages: errorResponse,
        //                 autoClose: false
        //             });
        //         }
        //     }
        // });
    },

    render: function() {
        var self = this;
        this._super('render');

        var filter = _.filter(app.user.attributes.roles, function(role) { 
            if(role == "Assistant Dev" ||
                role == "Assistant F&A" ||
                role == "Assistant P&C" ||
                role == "Junior Manager F&A" ||
                role == "Junior Manager P&C" ||
                role == "Junior Manager QS") {
                return role;
            }
        });

        if(filter.length > 0) {
            $(document).find('a[name="create_button"]').addClass('hide');
        }
    },
})

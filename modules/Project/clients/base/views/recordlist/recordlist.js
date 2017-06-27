({

    extendsFrom: 'RecordlistView',
    id: 'moduleProject',
    numberP: 0,
    initialize: function (options) {

        // this._super("initialize", [options]);

        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args:[options]});
        var self = this;

        this.collection.on('data:sync:complete', function() {
            this.numberP = this.collection.length;
            this.render_colors();
        }, this);
    },

    render_colors : function() {
        var self=this;
        setTimeout(
            function() {
                var ii = 0;
                $("tr[name^='Project']").each(function () { //loop over each row
                    if ($(this).find('td[data-type="int"]')) { //check value of TD
                        $(this).find('td[data-type="int"] div').css("text-align", "center").text(self.numberP-ii);
                    }
                    ii++;
                });
            }, 1000);
    },

    render: function() {
        var self = this;
        this._super('render');
        $(document).find('a[name="create_button"]').addClass('hide');
    },
    // _initEvents: function() {
    //     this._super('_initEvents');
    //     return this;
    // },
    // addtoproject : function(model) {
    //     console.log('ddd');
    //     console.log('ddd'+model );
    //     var self = this;

    //     var name = Handlebars.Utils.escapeExpression(app.utils.getRecordName(model)).trim();
    //     // var context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name;
    //     var context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name;
    //     var jestID = model.toString().split("/");

    //     var idCSV = '';
    //     var selector = 'AC_FeeProposal';

    //     // console.log('ddd'+jestID[1] + ' name ' + name);
    //     window.location.href = '#bwc/index.php?module=Project&action=EditView&return_module=Project&return_action=DetailView&feeid='+jestID[1]+"&feename=" + name;

    //     // $.ajax({
    //     //     url: 'index.php?module=CSTM_ANIMALS&action=ADD_TO_CIRCUS',
    //     //     type: 'POST',
    //     //     data: {uid: idCSV},
    //     //     success: function(errorResponse) {
    //     //         if(errorResponse != '') {
    //     //             app.alert.show('bad-add-to-circus', {
    //     //                 level: 'error',
    //     //                 messages: errorResponse,
    //     //                 autoClose: false
    //     //             });
    //     //         }
    //     //     }
    //     // });
    // },
})
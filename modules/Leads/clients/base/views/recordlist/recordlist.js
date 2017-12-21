({
    extendsFrom: 'RecordlistView',

    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args:[options]});
        var self = this;

        this.collection.on('data:sync:complete', function() {
            self.addCustomFields();
        }, this);
    },

    render: function() {
        this._super('render');

        var $element = $('.search-filter .filter .btn-group'),
            $children = $element.children('.btn[data-view="activitystream"]');
            
        $element.show();
        $children.removeAttr('disabled');
        $children.children('.btn[data-view="activitystream"]').removeClass('disabled');
        $children.children('.btn[data-view="activitystream"]').attr('data-original-title', 'Reesco Chat');
    },

    addCustomFields : function() {
        var iter = 1;

        setTimeout(
            function() {
                $("tr[name^='Leads']").each(function () {
                    if($(this).find('td[data-type="url"]').length > 0) {
                        $(this).find('td[data-type="url"]').find('.ellipsis_inline').html('<i class="fa fa-linkedin-square fa-2x"></i>');
                        $(this).find('td[data-type="url"]').find('.ellipsis_inline').css("text-align", "center");
                        $(this).find('td[data-type="url"]').find('.ellipsis_inline').attr('target', "_blank");
                    }

                    $(this).find('td').eq(0).find('.fieldset-field').eq(1).html("<span>"+ iter +"</span>");
                    iter++;
                });
            },
        1000);
    },
})
({
    extendsFrom: 'RecordlistView',

    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args:[options]});
        var self = this;

        this.collection.on('data:sync:complete', function() {
            self.addLinkedInIcon();
        }, this);
    },

    addLinkedInIcon : function() {
        setTimeout(
            function() {
                $("tr[name^='Leads']").each(function () { //loop over each row
                    if($(this).find('td[data-type="url"]').length > 0) {
                        $(this).find('td[data-type="url"]').find('.ellipsis_inline').html('<i class="fa fa-linkedin-square fa-2x"></i>');
                        $(this).find('td[data-type="url"]').find('.ellipsis_inline').css("text-align", "center");
                        $(this).find('td[data-type="url"]').find('.ellipsis_inline').attr('target', "_blank");
                    }
                });
            },
        1000);
    },
})
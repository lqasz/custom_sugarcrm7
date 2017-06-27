({
    extendsFrom: 'CreateActionsView',
    id: 'AccountCreate',

    initialize: function(options) {
    	this._super('initialize', [options]);

        $('<style>'+
            '.vis_action_hidden { display: none !important; }'+
        '</style>').appendTo('head');
    },

    render: function() {
    	this._super('render');

        var $rowElement = this.$el.find('#tabContent .LBL_RECORDVIEW_PANEL1:eq(0)');
        
        $rowElement.find('.record-cell:eq(3)').addClass('span6');
        $rowElement.find('.record-cell:eq(3)').removeClass('span4');
        $rowElement.find('.record-cell:eq(4)').addClass('span6');
        $rowElement.find('.record-cell:eq(4)').removeClass('span4');

        $rowElement.find('.record-cell:eq(2)').addClass('span4');
        $rowElement.find('.record-cell:eq(2)').removeClass('span2');

    	this.$el.find('.record-cell[data-name="name"]').addClass('name-width');
        this.$el.find('.record-cell[data-name="category_service_c"]').addClass('category-width');
    },
})
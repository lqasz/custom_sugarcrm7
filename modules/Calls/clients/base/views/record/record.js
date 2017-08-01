({
    extendsFrom: "RecordView",
    id: 'CallView',

    initialize: function(options) {
        this._super('initialize', [options]);
    },

    saveClicked: function() {
        this._super('saveClicked');
    },
})
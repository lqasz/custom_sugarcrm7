({
	extendsFrom: "RecordView",

	events: _.extend({}, this.events, {

  }),

	initialize: function(options) {
    this._super('initialize', [options]);

    var self = this;
    self.collection.on('data:sync:complete', function() {
      // get all data from db
      app.api.call('POST', 'index.php?entryPoint=getData&getAllTimeSheets=1', null,{
        success: _.bind(function(data) {
          self.dataFetched = data.time_sheet;
          self.model.trigger('rebuildFields'); // trigger event in model
        })
      });
    }, self);

    self.model.on('rebuildFields', function() {
      self.render();
    });
  },

  render: function() {
    this._super('render');
    this.rebuildFields();
  },

  rebuildFields: function() {
    var string = '';
    
    string += '<div class="span12">'+
                '<>'
              '</div>';
  },
})
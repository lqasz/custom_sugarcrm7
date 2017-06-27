({

    extendsFrom: 'RecordlistView',
    initialize: function (options) {

        this._super("initialize", [options]);
        // app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args:[options]});
        var self = this;
        //add listener for custom button
        // this.context.on('list:makeproject:fire', this.addtoproject, this);

        // tylko JJ może tworzyć projekty w RMS
        // if(App.user.id !== "144c39bf-ccc3-65ec-2023-5407f7975b91"){
            $('<style>'+
              'a { padding-left: 5px !important;}'+

              '</style>').appendTo('head');
        // }
    },

    // _initEvents: function() {
    //     this._super('_initEvents');
    //     this.on('list:addtoproject:fire', this.addtoproject, this);
    //     return this;
    // },

})



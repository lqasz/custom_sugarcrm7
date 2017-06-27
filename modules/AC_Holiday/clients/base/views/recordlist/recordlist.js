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
              '.flex-list-view.left-actions .dataTable th:first-child, .flex-list-view.left-actions .dataTable td:first-child { display: none !important; }'+
             // '.dataTable>thead>tr>th>span { display: none; }'+
             ' .xxsmall{ width:70px !important; }' +
              '</style>').appendTo('head');
        // }
    },

    // _initEvents: function() {
    //     this._super('_initEvents');
    //     this.on('list:addtoproject:fire', this.addtoproject, this);
    //     return this;
    // },

})



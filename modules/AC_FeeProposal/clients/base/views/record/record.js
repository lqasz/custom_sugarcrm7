({
    extendsFrom: "RecordView",
    alert: undefined,
    id: 'FeeEdit',
    inlineEditMode: false,
    // cancelClicked: function() {
    //     var changedAttributes = this.model.changedAttributes(this.model.getSyncedAttributes());
    //     this.model.set(changedAttributes)
    //     this._super('cancelClicked');
    // },
    countner: 0,
    initialize: function(options) {
        this.plugins = _.union(this.plugins, ['LinkedModel']);
        this._super('initialize', [options]);
    console.log('jak ustawic ospobe odpowiedzialną');
    console.log(this.model);


        $('<style>'+ 
            '.subpanels-layout .subpanel-header .btn-toolbar {margin-top: 7px; display: none; } ' + 
          '</style>').appendTo('head');

        // powinno być to napisane przy użyciu, ale nie ma czasu ;)
        // app.events.on("create:model:changed",this.createModelChanged,this);
        var self = this;
        $(document).on("change", 'input[name="ac_feeproposal_aa_buildings_1_name"]', function(e){
          self.getBuildingFloors();
        });


console.log('mamy model');
console.log(this.model.attributes);
     if (!_.isEmpty(this.model.link)) {
console.log('mamy link do opportunity');
console.log(this.model.link.bean);
this.model.attributes.assigned_user_id=this.model.link.bean.attributes.assigned_user_id;
this.model.attributes.assigned_user_name=this.model.link.bean.attributes.assigned_user_name;
this.model.attributes.base_rate=this.model.link.bean.attributes.base_rate;
this.model.attributes.best_case=this.model.link.bean.attributes.best_case;
this.model.attributes.commit_stage=this.model.link.bean.attributes.commit_stage;
this.model.attributes.currency_id=this.model.link.bean.attributes.currency_id;
this.model.attributes.currency_symbol=this.model.link.bean.attributes.currency_symbol;
this.model.attributes.next_step=this.model.link.bean.attributes.next_step;
this.model.attributes.propability=this.model.link.bean.attributes.propability;
this.model.attributes.sales_stage=this.model.link.bean.attributes.sales_stage;
this.model.attributes.custom_c=this.model.link.bean.attributes.custom_c;
this.model.attributes.service_c=this.model.link.bean.attributes.service_c;
this.model.attributes.date_closed=this.model.link.bean.attributes.date_closed;

this.model.attributes.responsible_c=this.model.link.bean.attributes.responsible_c;//CAM
this.model.attributes.delegated_c=this.model.link.bean.attributes.delegated_c; // Responsible 
this.model.attributes.supervisor_c=this.model.link.bean.attributes.supervisor_c; // Supervisor

// user_id1_c : "e07026a9-691a-67e7-32a6-5407f619ae5b"
// user_id2_c : "144c39bf-ccc3-65ec-2023-5407f7975b91"
// user_id_c : "6865ad50-a9cf-a92b-37f1-5407f690c6d5"
this.model.attributes.user_id_c=this.model.link.bean.attributes.user_id_c;//CAM
this.model.attributes.user_id1_c=this.model.link.bean.attributes.user_id1_c; // Responsible 
this.model.attributes.user_id2_c=this.model.link.bean.attributes.user_id2_c; // Supervisor
console.log('mamy link do opportunity');
console.log(this.model.link.bean);
console.log('mamy model');
console.log(this.model.attributes);
// user_id1_c : "e07026a9-691a-67e7-32a6-5407f619ae5b" artur supervisor
// user_id2_c : "144c39bf-ccc3-65ec-2023-5407f7975b91" jakub delegated
// user_id_c : "6865ad50-a9cf-a92b-37f1-5407f690c6d5" mateusz responsible

     }
        // zachowane jako dobry pattern, uzywamy multienum więc to jest zbędne
        // this.events=_.extend({},this.events,options.events,{
        //                         'change [name=floors_c]':'showFloors',
        //                     });

        // zachowane jako dobry pattern tutaj zbędne
        self.collection.on('data:sync:complete', function() {
             self.getBuildingFloors();
        }, this);

    },
    // getCustomSaveOptions: function(options) {
    //     if (app.metadata.getModule('Opportunities', 'config').opps_view_by === 'RevenueLineItems') {
    //         this.createdModel = this.model;
    //         this.listContext = this.context.parent || this.context;
    //         this.originalSuccess = options.success;
    //         var success = _.bind(function(model) {
    //             this.originalSuccess(model);
    //             var addedRLIs = model.get('revenuelineitems') || false;
    //             addedRLIs = (addedRLIs && addedRLIs.create && addedRLIs.create.length);
    //             if (!addedRLIs && options.lastSaveAction != 'saveAndCreate') {
    //                 this.showRLIWarningMessage(this.listContext.get('module'));
    //             }
    //         }, this);
    //         return {
    //             success: success
    //         };
    //     }
    // },
    showRLIWarningMessage: function() {
        app.routing.before('route', this.dismissAlert, undefined, this);
        var message = app.lang.get('TPL_RLI_CREATE', 'Opportunities') + '  <a href="javascript:void(0);" id="createRLI">' +
            app.lang.get('TPL_RLI_CREATE_LINK_TEXT', 'Opportunities') + '</a>';
        this.alert = app.alert.show('opp-rli-create', {
            level: 'warning',
            autoClose: false,
            title: app.lang.get('LBL_ALERT_TITLE_WARNING') + ':',
            messages: message,
            onLinkClick: _.bind(function() {
                app.alert.dismiss('create-success');
                this.openRLICreate();
            }, this),
            onClose: _.bind(function() {
                app.routing.offBefore('route', this.dismissAlert, this);
            }, this)
        });
    },
    dismissAlert: function(data) {
        if (data && !(data.args && data.args[0] === 'Opportunities' && data.route === 'list')) {
            app.alert.dismiss('opp-rli-create');
            app.routing.offBefore('route', this.dismissAlert, this);
        }
    },
    openRLICreate: function() {
        this.dismissAlert(true);
        var model = this.createLinkModel(this.createdModel || this.model, 'revenuelineitems');
        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                module: model.module,
                model: model
            }
        }, _.bind(function(model) {
          $('input[name="name"]').val("dsadasdasd");
            if (!model) {

                return;
            }
            var ctx = this.listContext || this.context;
            ctx.reloadData({
                recursive: false
            });
            ctx.trigger('subpanel:reload', {
                links: ['opportunities', 'revenuelineitems']
            });
        }, self));
    },
    getBuildingFloors: function(){
        var self = this;
        var get_floors_options = self.model.attributes.floors_c;

        console.log('nasz fee');
        console.log(this);
            // pobranie informacji o budynku
            var buildingID = self.model.attributes.ac_feeproposal_aa_buildings_1aa_buildings_idb;
            console.log('id budynku '+buildingID);
            if(!buildingID){ return; }
            var building = SUGAR.App.data.createBean('AA_Buildings', {id: buildingID});
            requestBuilding = building.fetch();

            requestBuilding.xhr.done(function(data) {
                var floorsList = {};
                var ii = 0;// zmiana 23 maja 2016
                var mainII = 0;// zmiana 23 maja 2016
                var standard_upper_floors = 25;

                console.log('data z budynku');
                console.log(data);
                data.underground_floors = (data.underground_floors) ? data.underground_floors : 0;
                data.upper_floors = (data.upper_floors) ? data.upper_floors : 0;

                if( data.upper_floors > 0 ){ // było >= , zmiana 23 maja 2016
                    // dodanie do fee informacji na temat pięter
                    self.model.set('underground_floors_c', data.underground_floors);
                    self.model.set('floors_above_ground', data.upper_floors);

                    for( ii=0; ii <= data.upper_floors; ii++){
                            floorsList[mainII] = ii;
                            mainII++;
                    }

                    floorsList[''] = '';
                }else{
                    // zmiana 23 maja 2016
                    self.model.set('underground_floors_c', 0);
                    // nasz standard 25
                    self.model.set('floors_above_ground', standard_upper_floors);

                    for( ii=0; ii <= standard_upper_floors; ii++){
                            floorsList[mainII] = ii;
                            mainII++;
                    }
                    console.log('jak nasze fiętra');
                    console.log(self.model.attributes);
                    // self.model.set('underground_floors_c', 0);
                    // self.model.set('floors_above_ground', 0);
                    floorsList[''] = '';
                    // console.log("Brak informacji w budynku na temat pięter");
                }

                console.log('przypisanie listy pięter dropdownom');
                self.model.fields.floors_c.options = floorsList;

                self.model.attributes.floors_c = get_floors_options;
                self.render();
            });

    },
    _render: function() {
      // $('input[name="pattern_c"]').attr("disabled", true);  
      // $('input[name="pattern_c"]').attr("disabled", "disable");  
      this._super('_render', []);
      $('span[data-name="name"]').attr("disabled", true);
      $(document).find('.main-pane').addClass('hideFilterView');
      console.log('create model change');
    },
    render: function(){
        this._super('render');

        $('.subpanels-layout .subpanel-header .btn-toolbar').hide();
    },
    _dispose: function() {
        if (this.alert) {
            this.alert.getCloseSelector().off('click');
        }
        this._super('_dispose', []);
    }
})
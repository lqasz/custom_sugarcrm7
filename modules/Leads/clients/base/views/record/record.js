({
    extendsFrom: 'RecordView',
    id: "LeadRecord",
    events: _.extend({}, this.events, {
        'click .history-button': 'showHistoryClicked'
    }),

    initialize: function(options) {
        app.view.invokeParent(this, {
            type: 'view', 
            name: 'record', 
            method: 'initialize', 
            args:[options]
        });

        this.context.on('button:convert_fee:click', this.convertDelegator, this);
        this.context.on('button:convert_opportunitie:click', this.convertDelegator, this);
        this.model.addValidationTask('check_status', _.bind(this._doValidateCheckStatus, this));
    },

    _doValidateCheckStatus: function(fields, errors, callback) {
        //validate type requirements
        if((this.model.get('status') == 'Postponed' || this.model.get('status') == 'In Process') && 
        	(this.model.get('prospective_date_c') == '1970-01-01' || this.model.get('prospective_date_c') == '')) {
            errors['prospective_date_c'] = errors['prospective_date_c'] || {};
            errors['prospective_date_c'].required = true;
        }

        callback(null, fields, errors);
    },
    showHistoryClicked: function(e) {
        app.drawer.open({
            layout: 'activitystream',
        });
    },
    convertDelegator: function(model, DOM) {
        if(DOM.name == "convert_fee") {
            this.convertContactClicked("AC_FeeProposal");
        } else {
            this.convertContactClicked("Opportunities");
        }
    },
    convertContactClicked: function(module) {
        var self = this,
            toLowerModule = module.toLowerCase(),
            prefill = app.data.createBean(module);

        prefill.set("leads_"+ toLowerModule +"_1_name", self.model.get("full_name"));
        prefill.set("leads_"+ toLowerModule +"_1leads_ida", self.model.get("id"));
        
        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                model: prefill,
                module: module
            }
        });
    }
})
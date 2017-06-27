({
    extendsFrom: "RecordView",
    id: 'CallView',

    initialize: function(options) {
        this._super('initialize', [options]);
    },

    saveClicked: function() {
        var self = this;

        if(this.model.get('target_status_c') == "Convert to Lead") {
            if(!((this.model.get('date_of_meeting_c') == '1970-01-01' || this.model.get('date_of_meeting_c') == ''))) {
                target = SUGAR.App.data.createBean('Prospects', {id: self.model.get('prospects_calls_1prospects_ida')});
                targetBean = target.fetch({     
                    success: function(data) {
                        self.convertToLeadClicked(data);
                        self._super('saveClicked');
                    },
                    error: function() { console.info('Błąd, tworzenia Leada');}
                });
            } else {
                app.alert.show('message-id', {
                    level: 'confirmation',
                    messages: 'Field `Date of First Meeting` is required!',
                    autoClose: false,
                });
                return;
            }
        } else {
            this._super('saveClicked');
        }
    },

    convertToLeadClicked: function(object) {
        var self = this,
            prefill = app.data.createBean('Leads');
        prefill.copy(object);

        prefill.set("website", object.get("website_c"));
        prefill.set("accounts_leads_1_name", object.get("accounts_prospects_1_name"));
        prefill.set("accounts_leads_1accounts_ida", object.get("accounts_prospects_1accounts_ida"));
        prefill.set("data_of_first_meeting_c", self.model.get('date_of_meeting_c'));
        prefill.set("from_target_c", true);

        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                model: prefill,
                module: 'Leads',
                prospect_id: object.get('id')
            }
        });
    }
})
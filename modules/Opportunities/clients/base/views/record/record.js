({
	extendsFrom: "RecordView",
  id: 'OppEdit',
    
	initialize: function(options){
		this.plugins = _.union(this.plugins, ['LinkedModel']);

		this._super('initialize', [options]);
		// this.before('render', this.addRow, this);

		this.context.on('button:convert_fee:click', this.convertOpportunitieClicked, this);
	},
  cancelClicked: function() {
    var changedAttributes = this.model.changedAttributes(this.model.getSyncedAttributes());
    this.model.set(changedAttributes);
    this._super('cancelClicked');
  },
  convertOpportunitieClicked: function() {
    var self = this,
        prefill = app.data.createBean("AC_FeeProposal");

    prefill.copy(this.model);
    prefill.set("cam", self.model.get("responsible_c"));
    prefill.set("responsible", self.model.get("delegated_c"));
    prefill.set("supervisor", self.model.get("supervisor_c"));
    prefill.set("sales_stage", "In Proccess");
    prefill.set("accounts_ac_feeproposal_1_name", self.model.get("account_name"));
    prefill.set("accounts_ac_feeproposal_1accounts_ida", self.model.get("account_id"));
    prefill.set("ac_feeproposal_aa_buildings_1_name", self.model.get("aa_buildings_opportunities_1_name"));
    prefill.set("ac_feeproposal_aa_buildings_1aa_buildings_idb", self.model.get("aa_buildings_opportunities_1aa_buildings_ida"));
    prefill.set("leads_ac_feeproposal_1_name", self.model.get("leads_opportunities_1_name"));
    prefill.set("leads_ac_feeproposal_1leads_ida", self.model.get("leads_opportunities_1leads_ida"));
    prefill.set("opportunities_ac_feeproposal_1_name", self.model.get("name"));
    prefill.set("opportunities_ac_feeproposal_1opportunities_ida", self.model.get("id"));
    
    app.drawer.open({
      layout: 'create-actions',
      context: {
        create: true,
        model: prefill,
        module: "AC_FeeProposal"
      }
    });
  },
})

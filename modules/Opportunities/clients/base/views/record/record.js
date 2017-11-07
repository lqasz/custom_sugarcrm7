({
	extendsFrom: "RecordView",
  id: 'OppEdit',
    
	initialize: function(options){
		this.plugins = _.union(this.plugins, ['LinkedModel']);
    var self = this;

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
    prefill.set("responsible", self.model.get("delegated_c"));
    prefill.set("sales_stage", "In Proccess");
    prefill.set("accounts_ac_feeproposal_1_name", self.model.get("account_name"));
    prefill.set("accounts_ac_feeproposal_1accounts_ida", self.model.get("account_id"));
    prefill.set("ac_feeproposal_aa_buildings_1_name", self.model.get("opportunities_aa_buildings_1_name"));
    prefill.set("ac_feeproposal_aa_buildings_1aa_buildings_idb", self.model.get("opportunities_aa_buildings_1aa_buildings_idb"));
    prefill.set("leads_ac_feeproposal_1_name", self.model.get("leads_opportunities_1_name"));
    prefill.set("leads_ac_feeproposal_1leads_ida", self.model.get("leads_opportunities_1leads_ida"));
    prefill.set("opportunities_ac_feeproposal_1_name", self.model.get("name"));
    prefill.set("opportunities_ac_feeproposal_1opportunities_ida", self.model.get("id"));
    prefill.set("account_id_c", self.model.get("opportunities_accounts_1accounts_idb"));
    prefill.set("tenant_c", self.model.get("opportunities_accounts_1_name"));
    prefill.set("probability", "");
    prefill.set("description", "");

    app.drawer.open({
      layout: 'create-actions',
      context: {
        create: true,
        model: prefill,
        module: "AC_FeeProposal"
      }
    });
  },
  saveClicked: function() {
    if(this.model.get('framework_c')) {
      this.model.set('opportunity_type', 'framework');
    } else {
      this.model.set('opportunity_type', 'single');
    }

    this._super('saveClicked');
  },
  render: function() {
    this._super("render");

    if(this.model.get('framework_c') == 0) {
      $('.rowaction[name="convert_fee"]').parent().parent().hide(0);
    }
  },
})

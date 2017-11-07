({
    extendsFrom: "RecordView",
    id: 'ContactEdit',

    initialize: function(options) {
        this._super('initialize', [options]);
        $('<style>.btn-group + .btn-group {margin-left: 0 !important; }' +
            '.btn-group > .btn:first-child:not(:last-of-type) {margin-left: 0; margin-right: 0 !important;' +
            '.btn-group[data-emailproperty="opt_out"] { display: none !important; } ' +
            '</style>').appendTo('.main-pane'); // #appendTo(head)

        $(document).find('.main-pane').addClass('hideFilterView');

        app.error.errorName2Keys['phone_format'] = 'Phone format (+48 730 444 867 xxx)';
        this.model.addValidationTask('check_phone_number', _.bind(this._doValidatePhoneNumber, this));
	},
    delegateButtonEvents: function() {
        this.context.on('button:convert_lead:click', this.convertDelegator, this);
        this.context.on('button:convert_target:click', this.convertDelegator, this);
        this._super("delegateButtonEvents");
    },
    render: function(){
        this._super('render');
        $(document).find('.main-pane').addClass('hideFilterView');
    },
	_doValidatePhoneNumber: function(fields, errors, callback) {  
		var mobilePhone = this.model.get('phone_mobile'),
			numberSign = mobilePhone.substring(0,1),
			numberOfSpaces = mobilePhone.split(' ');

		 if ( ((numberSign=='-') || (numberSign=='+')) && numberOfSpaces.length >=3 ) {

		}else{
			errors['phone_mobile'] = errors['phone_mobile'] || {}; 
			errors['phone_mobile'].phone_format = true;
		}
    	callback(null, fields, errors);
	},
    convertDelegator: function(model, DOM) {
        if(DOM.name == "convert_target") {
            this.convertContactClicked("Prospects", "prospect_id_c");
        } else {
            this.convertContactClicked("Leads", "lead_id_c");
        }
    },
    convertContactClicked: function(module, related) {
        if(_.isEmpty(this.model.get(related))) {
            var self = this,
                toLowerModule = module.toLowerCase(),
                prefill = app.data.createBean(module);

            prefill.copy(this.model);
            
            console.info("prefill: ", prefill);

            prefill.set("contact_id_c", this.model.get('id'));
            prefill.set("accounts_"+ toLowerModule +"_1_name", self.model.get("account_name"));
            prefill.set("accounts_"+ toLowerModule +"_1accounts_ida", self.model.get("account_id"));

            app.drawer.open({
                layout: 'create-actions',
                context: {
                    create: true,
                    model: prefill,
                    module: module
                }
            });
        } else {
            app.alert.show('message-id', {
                level: 'confirmation',
                messages: this.model.get('full_name') +" already exists",
                autoClose: false,
            });
        }
    }
})
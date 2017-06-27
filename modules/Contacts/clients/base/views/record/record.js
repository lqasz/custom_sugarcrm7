({
    extendsFrom: "RecordView",
    id: 'ContactEdit',

    /**
     * {@inheritDoc}
     * Constructor
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        $('<style>.btn-group + .btn-group {margin-left: 0 !important; }' +
            '.btn-group > .btn:first-child:not(:last-of-type) {margin-left: 0; margin-right: 0 !important;' +
            '.btn-group[data-emailproperty="opt_out"] { display: none !important; } ' +
            '</style>').appendTo('.main-pane'); // add custom style to the head

        app.error.errorName2Keys['phone_format'] = 'Phone format (+48 730 444 867 xxx)';
        this.model.addValidationTask('check_phone_number', _.bind(this._doValidatePhoneNumber, this)); // phone format validation
	}, // initialize

    render: function(){
        this._super('render');
        $(document).find('.main-pane').addClass('hideFilterView');
    }, // render

	/**
     * Function checks phone number format, if find errors then callback with them
     */
    _doValidatePhoneNumber: function(fields, errors, callback) {  
        var mobilePhone = this.model.get('phone_mobile'),
            numberSign = mobilePhone.substring(0,1),
            numberOfSpaces = mobilePhone.split(' ');

        if ( !(((numberSign=='-') || (numberSign=='+')) && numberOfSpaces.length >=3)) {
            errors['phone_mobile'] = errors['phone_mobile'] || {}; 
            errors['phone_mobile'].phone_format = true;
        } // if
        callback(null, fields, errors);
    } // _doValidatePhoneNumber
})
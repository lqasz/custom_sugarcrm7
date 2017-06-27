/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 * źródła /jssource/src_files/clients/base/views/create/create.js
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
({
    extendsFrom: 'CreateActionsView',
    id: 'HolidaysCreate',
    userID: undefined,
    user_agreement: undefined,
    events: _.extend({}, this.events, {
            'change [name=assigned_user_name]':'getAssignedUser',
    }),

    /*
    * Constructor inicjalize functions, display 
    */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.userID = this.model.get('assigned_user_id');

        //add validation tasks
        this.model.addValidationTask('check_dates ', _.bind(this._doValidateCheckDates, this));
    },
    /*
    * Function render the view, if it is project task, then do not dispaly `parent_name` field
    */
    render: function() {
        this._super('render');

        if(this.userID != 'ada95982-6143-43d9-e3ae-540f494996bf') {
            $('.record-cell[data-name="sick_leave"]').hide(0);
            $('.record-cell[data-name="assigned_user_name"]').hide(0);
            $('.record-cell[data-name="withdrawal_of_leave_c"]').css('margin-left', '0');
        }

        $('.record-cell[data-name="name"]').hide(0);
        $('.record-cell[data-name="days_hd"]').hide(0);
        $('.record-cell[data-name="team_name"]').hide(0);

        $('.record').find('.row-fluid:eq(2)').remove();
        $('.record').find('.row-fluid:eq(2)').remove();
        $('.record').find('.row-fluid:eq(2)').remove();
    },
    save: function() {
        var days,
            self = this,
            date_start = this.model.get('v_from'),
            date_end = this.model.get('v_to');

        var fieldName,
            agreementUser = null,
            agreement = SUGAR.App.data.createBean('Users', {id: this.userID});

        
        agreementUser = agreement.fetch();
        agreementUser.xhr.done(function(data) {

            if(data.aggrement_c) {
                if(data.aggrement_c == "umowa_o_prace" || data.aggrement_c == "umowa_o_prace_na_czas_nieokreslony") self.user_agreement = 1;
                else self.user_agreement = 0;
            }

            if(self.model.get('withdrawal_of_leave_c') === true) {
                fieldName = "Withdrawal request "+ date_start +" - "+ date_end;
            } else if(self.model.get('sick_leave') === true) {
                fieldName = "Sick leave from "+ date_start +" - "+ date_end;
            } else {
                if(self.user_agreement == 1)    fieldName = "Holiday request "+ date_start +" - "+ date_end;
                else    fieldName = "Absence request "+ date_start +" - "+ date_end;
            }
            self.model.set('name', fieldName);
            self.model.set('days_hd', 0);

            app.api.call('GET', "index.php?entryPoint=getData&checkHoliday=1&dateStart="+date_start+"&dateEnd="+date_end+"&userID="+self.userID, null,{
                success: _.bind(function(data) {
                    
                    console.info(data);

                    if(data[0] == 1) {
                        if(self.model.get('sick_leave') === true || self.model.get('withdrawal_of_leave_c') === true) {
                            self.model.set('days_hd', data[1]);
                            self._super('save'); // spowoduje wykonanie reszty kodu
                        } else{
                            app.alert.show('message-id', {
                                level: 'confirmation',
                                messages: 'Employee hasn\'t got '+ data[1] +' free days to use',
                                autoClose: false,
                            });
                            return;
                        }
                    } else {
                        self.model.set('days_hd', data[1]);
                        self._super('save'); // spowoduje wykonanie reszty kodu
                    }
                }, self)
            });

        });
    },
    getAssignedUser: function() {
        this.userID = this.model.get('assigned_user_id');
    },
    _doValidateCheckDates: function(fields, errors, callback) {
        var self = this,
            dateFrom = new Date(self.model.get('v_from')),
            dateTo = new Date(self.model.get('v_to'));
        dateFrom.setHours(0, 0, 0, 0);
        dateTo.setHours(0, 0, 0, 0);

        //validate requirements
        if (dateTo < dateFrom) {
            errors['v_from'] = errors['v_from'] || {};
            errors['v_from'].required = true;
            errors['v_to'] = errors['v_to'] || {};
            errors['v_to'].required = true;
        }

        callback(null, fields, errors);
    }
})
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
({
    extendsFrom: 'CreateActionsView',
    id: 'InvoiceCreate',
    relatedScans: null, // relacyjne notatki z skanami faktur
    countBrutto: true,
    events: _.extend({}, this.events, {
        'click .deleteAttachment': 'deleteAttachment',
        'click .addDocument': 'addDocument',
        'click .record-cell[data-name="owner_unknown_c"]': 'clickedOwner',
        'change .record-cell': 'putBrutto',
    }),
    initialize: function(options) {

        this.plugins = _.union(this.plugins, ['LinkedModel','Dashlet', 'DragdropInvoices','Taggable']);
        this._super('initialize', [options]);
        this.on('attachments:add attachments:remove attachments:end', this.toggleSubmitButton, this);
        this.on('attachments:start', this.disableSubmitButton, this);

        //add invoice validation
        this.model.addValidationTask('check_attachment', _.bind(this._doValidateAttachment, this));
    },
    _renderHtml:function(){
        this._super('_renderHtml');
        if(this.$el.length>0){
            // create drop pdf field
            // przycisk usuniemy ale jeszcze trochę  :)
            this.$el.find('[data-fieldname="scan_c"]').parent().parent().hide().after('<div class="row-fluid panel_body panel_body"><div class="span6 record-cell edit" data-type="relate" data-name="upload_scan"> <div class="inputwrapper"> <div contenteditable="false" class="sayit attachable taggable" style="height: 75px; text-align: center;"> <p style="margin-top: 2%;"><i class="fa fa-upload fa-3x"></i><br /> <span id="beforeUpload" style="margin-left: 20px;">Drag and drop document here !</span> <span id="afterUpload" style="margin-left: 20px;" class="hide">Thank you !</span> </p> </div> <div class="inputactions"> <button class="pull-right btn btn-primary addDocument btn-upload" track="click:addDocument" style="display:none;">Submit</button> </div> </div></div><div class="span6 record-cell"> <div class="inputwrapper"><ul id="invoiceList"></ul></div></div></div>');

        }

        return;
    },
    save: function() {
        var self = this,
            noteID = this.$('input[name="customid_c"]').val();
        
        // add generated ID
        this.model.set('customid_c',noteID);
        // Invoice default name
        this.model.set('name', 'New Invoice');

        app.api.call('GET', "index.php?entryPoint=getData&checkInvoice=1&invoice_no="+ self.model.attributes.invoice_no_c +"&supplier="+ self.model.attributes.account_id_c +"&net="+ self.model.attributes.nett_c, null,{
            success: _.bind(function(data) {
                if(data == 0) {
                    this._super('save');
                } else {
                    app.alert.show('bad-add-to-circus', {
                        level: 'warning',
                        messages: "Faktura o numerze "+ self.model.attributes.invoice_no_c +" istnieje już w bazie RMS",
                        autoClose: false
                    });
                    return;
                }
            }, self)
        });
    },
    addDocument: function() {
        var self = this,
            attachments = this.$('.activitystream-pending-attachment'),
            $submitButton = this.$('button.addDocument');

        var recivedData = null;
        var payload = {
            activity_type: "post",
            data: {}
        };

        if (!$submitButton.hasClass('disabled') && attachments.length !== 0 && _.size(attachments) !== 0) {
            payload.data = this.getPost();

            if (payload.data.value && (payload.data.value.length > 0)) {
                $submitButton.addClass('disabled');
                this.trigger("attachments:process");
            }
        }
    },

    deleteAttachment:function(event) {
        var noteID = event.currentTarget.parentElement.id,
            relationship = 1;

        app.api.call('GET', "index.php?entryPoint=getData&deleteAttachment=1&attachmentID="+ noteID +"&relationship="+ relationship, null,{
            success: _.bind(function(data) {
                if(data) {
                    $("#"+ noteID).parent().remove();
                    console.info("Usunięto załącznik do faktury");
                }
            }, self)
        });
    },

    loadRelatedScans: function(){
        var self = this;
        var invoiceID = this.$('input[name="customid_c"]').val();
        var $submitButton = this.$('button.addDocument');

        if( invoiceID!==''){
            this.relatedScans = app.data.createBeanCollection('Notes');
            this.collection.options = {
                params: {
                    order_by: 'date_entered:desc'
                },
                limit: 10,
                fields: [
                    'id',
                    'name',
                    'parent_id',
                ],
                apiOptions: {
                    skipMetadataHash: true
                }
            };
            this.relatedScans.filterDef = [{
                        parent_id: {$equals: invoiceID},
                        invoice_scan_c: {$equals: 1}
                    }];
            this.relatedScans.fetch({
                showAlerts:false,
                success: function(collection){
                    // this.$('#invoiceList').html('');
                    for (var i = 0; i < collection.models.length; i++) {
                        var item = collection.models[i];
                        this.$('#invoiceList').append('<li><div class="activitystream-pending-attachment2" id="'+ item.attributes.id +'"><a class="deleteAttachment close" tabindex="-1"><i class="fa fa-times"></i></a><a href="http://'+ window.location.hostname +'/rest/v10/Notes/'+ item.attributes.id +'/file/filename?force_download=0&platform=base&noteShare=1"><img style="display:block; margin-top: 0;" src="/themes/default/images/pdf.jpeg"></a><p>'+ item.attributes.name +'</p></div></li>');
                        ///file/filename?force_download=0&platform=base&noteShare=1
                    }
                    self.enableSubmitButton();$submitButton.removeClass('disabled'); 
                },
                error: function() {
                    console.log('-----------------------------------------------');
                    console.log('Błąd');
                    console.log('-----------------------------------------------');
                }
            });

             // if($submitButton.hasClass('disabled')) { $submitButton.removeClass('disabled'); }
        }
    },
    bindDataChange: function(){
        this._super('bindDataChange');
    },
    /*
    * Function render the view, if it is project task, then do not dispaly `parent_name` field
    */
    render: function() {
        this._super('render');
        $('.record-cell[data-name="ac_invoices_ac_invoices_1_name"]').addClass('vis_action_hidden');
        $('.record-cell[data-name="assigned_user_name"]').addClass('vis_action_hidden');
        $('.record-cell[data-name="team_name"]').addClass('vis_action_hidden');
        $('.alert-danger').hide(0);

        $('#InvoiceCreate').find('.row-fluid:eq(18)').hide(0);
        $('#InvoiceCreate').find('.row-fluid:eq(19)').hide(0);
        $('#InvoiceCreate').find('.row-fluid:eq(20)').hide(0);
    },

    _renderFields: function() {
        this._super('_renderFields');

        var field = this.getField("gross_c");
        if(field && this.model.get("gross_c") ) {
            field.setMode('edit');
        }

        
    },

    /*
    * Function returns formatted message
    */
    getPost: function() {
        var post = this.unformatTags(this.$('div.sayit'));
        post.value = post.value.replace(this.nbspRegExp, ' ');
        return post;
    },
    disableSubmitButton: function() {
        this.$('.addPost').addClass('disabled');
    },
    enableSubmitButton: function() {
        this.$('.addPost').removeClass('disabled');
    },
    /*
    * if message is empty then disable submit button
    * else enable submit button
    */
    toggleSubmitButton: function() {
        var post = this.getPost(),
            attachments = this.getAttachments();
            $submitButton = this.$('button.addDocument');

        if ( (post.value.length === 0) || (_.size(attachments) === 0) || $submitButton.hasClass('disabled') ){
            this.disableSubmitButton();
            // this.loadRelatedScans();
        } else {
            this.addDocument();
            var log = _.bind(this.loadRelatedScans, this);
            _.delay(log, 3000, this);
            this.disableSubmitButton();
        }

    },

    _handleContentPaste: function(e) {
        _.defer(_.bind(this._handleContentChange, this), e);
    },

    _doValidateAttachment: function(fields, errors, callback) {
        if($('.activitystream-pending-attachment2').length === 0) {
            app.alert.show('bad-add-to-circus', {
                level: 'warning',
                messages: "Dodaj proszę skan do faktury.",
                autoClose: false
            });

            errors['upload_scan'] = errors['upload_scan'] || {};
            errors['upload_scan'].required = true;
        }

        callback(null, fields, errors);
    },

    clickedOwner: function(event) {
        console.info(event.currentTarget);
        console.info($(event.currentTarget));
    },

    putBrutto: function(event) {
        var element = $(event.currentTarget).data().name;

        if(element == "gross_c") {
            this.countBrutto = false;
        }

        if(this.countBrutto == true) {
            var vat = this.model.get('vat_c'),
                netto = this.model.get('nett_c'),
                brutto = netto * vat;

            this.model.set("gross_c", brutto);
        }
    },
//ac_invoices_ac_invoices_1_name ukryc vis_action_hidden











    // _handleContentChange: function(e) {
    //     console.log('_handleContentChange');
    //     // to handle the clicked enter and shift enter
    //     if(e.keyCode==13 && e.shiftKey==false){
    //         this.addDocument();
    //         // shift && enter give us return on the text
    //     }else{
    //         var el = e.currentTarget;
    //         if (el.textContent) {
    //             el.setAttribute('data-hide-placeholder', 'true');
    //         } else {
    //             el.removeAttribute('data-hide-placeholder');
    //         }
    //         this.toggleSubmitButton();
    //     }
    // },
/*
if(!this.model.attributes.project5_c){
            $('.record-cell[data-name="project5_c"]').addClass('vis_action_hidden');} */
/*
        //     console.log(nodeModel);
        //  console.log('dodałem plik');
        //     var setParent = _.bind(function(model) {
        //     var parentNameField = this.getField('parent_name');
        //     if (model.module && parentNameField.isAvailableParentType(model.module)) {
        //         model.value = model.get('name');
        //         parentNameField.setValue(model);
        //     }
        // }, this);

        // if (!_.isEmpty(relatedModel.get('id')) && !_.isEmpty(relatedModel.get('name'))) {
        //     setParent(relatedModel);
        // } else if (!_.isEmpty(relatedModel.get('id'))) {
        //     relatedModel.fetch({
        //         showAlerts: false,
        //         success: function(relatedModel) {
        //             setParent(relatedModel);
        //         },
        //         fields: ['name']
        //     });
        // }

    viewMultiproject: function(e){
        var i = 0;

        if(e.target.checked === true){
            console.log('ok');
            for(i=2;i<=7;i++){
                console.log('[data-name="project'+i+'_c"]');
                this.$el.find('[data-name="project'+i+'_c"]').parent().show();
            }
        }else{
            for(i=2;i<=7;i++){
                console.log('[data-name="project'+i+'_c"]');
                this.$el.find('[data-name="project'+i+'_c"]').parent().hide();
            }
        }
    },
    viewPartLastInvoice:function(e){

        if(e.target.checked === true){
            this.$el.find('[data-fieldname="last_invoice_c"]').parent().show();
        }else{
            this.$el.find('[data-fieldname="last_invoice_c"]').parent().hide();
        }
    },



*/


})
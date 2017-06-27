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
	extendsFrom:'RecordView',

    countBrutto: true,
    events: _.extend({}, this.events, {
        'click [data-action=show-document]': 'showDocument',
        'click .deleteAttachment': 'deleteAttachment',
        'click #not_my_invoice':'doNotMyInvoice',
        'click .addDocument' : 'addDocument',
        'click a[name=cancel_button]': 'cancelClicked',
        'click .record-cell[data-name="na_all_c"]': 'naClicked',
        'blur .record-cell[data-name="project1_c"]': 'changeProject',
        'change .record-cell': 'putBrutto',
    }),
    invoiceRole: 'NONE',
    /* -------------------------------------------- */
    noEditFieldsForQS: ['accept2_c', 'accept3_c', 'accept4_c', 'gross_c'],
    noEditFieldsForPM: ['accept3_c','accept4_c','package_no_c','proform_paid_c','fcplist3_c', 'gross_c'],
    noEditFieldsForSV: ['accept4_c','package_no_c','proform_paid_c','fcplist3_c', 'gross_c'],
    noEditFieldsForBoardI: ['accept4_c','package_no_c','proform_paid_c','fcplist3_c', 'warranty_na_c', 'work_completed_na_c', 's_safety_na_c', 'agreement_na_c', 'na_all_c'],
    noEditFieldsForALL: ['invoice_no_c', 'supplier_c', 'without_project_c', 'unproject_pm_c','without_project_comment_c', 'part_invoice_c','board_invoice_c',
                        'last_invoice_c', 'board_member_c', 'multiproject_c', 'project_id_c', 'nett1_c', 'project_c','project1_c',
                        'project2_c', 'nett2_c', 'project3_c', 'nett3_c', 'project4_c', 'nett4_c', 'project5_c', 'nett5_c',
                        'project6_c', 'nett6_c', 'project7_c', 'nett7_c','assigned_user_name','team_name',
                        'date_of_issue_c','scan_c','ac_invoices_ac_invoices_1_name', 'description', 'vat_c','name'
                        ],
    noEditFieldsForNONE: ['invoice_no_c', 'supplier_c', 'without_project_c',
                        'unproject_pm_c','without_project_comment_c', 'part_invoice_c','board_invoice_c',
                        'last_invoice_c', 'board_member_c', 'multiproject_c', 'project_id_c', 'nett1_c',
                        'project2_c', 'nett2_c', 'project3_c', 'nett3_c', 'project4_c', 'nett4_c', 'project5_c', 'nett5_c',
                        'project6_c', 'nett6_c', 'project7_c', 'nett7_c','assigned_user_name','team_name',
                        'date_of_issue_c','scan_c','ac_invoices_ac_invoices_1_name', 'description', 'vat_c','name'
                        ],
    /* -------------------------------------------- */
    requiredForQS: ['package_no_c','proform_paid_c','fcplist3_c'], // jezeli to nie jest pm to pm dodać do nonedit fields
    requiredForPM: [],
    requiredForSW: [],
    requiredForSV: [],
    requiredForAGA: [],
    requiredFields: [],
    requiredForCurrUser: [],
    /* -------------------------------------------- */
    hiddenFieldsForAGA: [],
    hiddenFieldsForQS: [],
    hiddenFieldsForPM: ['warranty_na_c', 'work_completed_na_c', 's_safety_na_c', 'agreement_na_c', 'na_all_c'],
    hiddenFieldsForSV: [],
    hiddenFieldsForSW: [],
    /* -------------------------------------------- */
    conditionalQSFields:['board_invoice_c','without_project_c','multiproject_c','part_invoice_c', 'owner_unknown_c'],

	initialize:function(options){
        this.plugins = _.union(this.plugins, ['LinkedModel','Dashlet', 'DragdropInvoices','Taggable']);
		this._super('initialize',[options]);

        var self = this;

        this.toggleSubmitButton = _.debounce(this.toggleSubmitButton, 200);
        this.on('attachments:add attachments:remove attachments:end', this.toggleSubmitButton, this);
        this.on('attachments:start', this.disableSubmitButton, this);
        this.context.on('button:show_description:click', this.showDescription, this);

        // określenie osoby przeglądającej fakturę
        if(App.user.id=='9122d6b9-46e5-9013-99f7-540f4beb464e' || App.user.id=='3437dc50-5512-c6ef-38f3-57fcfa6c6394' || App.user.id=='85ac3697-84bc-9400-07f9-5770d2e0c12e' || App.user.id=='d8c6bac7-eb79-5cc3-6580-5770ce45b719'){self.invoiceRole='AG';} // Agnieszka/Milena/Olga/Tomasz
        if(App.user.id=='137a88d7-df78-8f89-9c4c-540f4ad585e4'){self.invoiceRole='SW';} // Sylwia

         _.each(self.meta.panels, function(panel) {
            _.each(panel.fields, function(field) {
                if($.inArray(field.name, self.conditionalQSFields)>-1){
                    if(self.model.get(field.name) === false){
                        field.readonly=true;
                        field.css+=' vis_action_hidden ';
                        field.cell_css_class+=' vis_action_hidden ';
                    }
                }
            });
        });
	},
    getNewMeta: function(){
        var self = this,
            regularUser = true,
            noEditFieldsMix = [],
            stageRequiredFields = [];

        if( self.invoiceRole == 'QS' ){
            noEditFieldsMix = _.union(self.noEditFieldsForALL, self.noEditFieldsForQS);
            noEditFieldsMix = _.without(noEditFieldsMix, 'project_id_c', 'project_c', 'project1_c', 'project2_c', 'project3_c', 'project4_c', 'project5_c', 'project6_c', 'project7_c');
            stageRequiredFields = _.union(self.requiredFields, self.requiredForQS);

            self.model.addValidationTask(['fcplist3_c'], _.bind(self._doValidateQS, self));

        }
        if( self.invoiceRole == 'PM' ){
            regularUser = false;
            noEditFieldsMix = _.union(self.noEditFieldsForALL, self.noEditFieldsForPM);
            stageRequiredFields = _.union(self.requiredFields, self.requiredForPM);
        }
        if( self.invoiceRole == 'SV' ){
            regularUser = false;
            noEditFieldsMix = _.union(self.noEditFieldsForALL, self.noEditFieldsForSV);
            stageRequiredFields = _.union(self.requiredFields, self.requiredForSV);
        }
        if( self.invoiceRole == 'SW' ){
            regularUser = false;
            noEditFieldsMix = self.noEditFieldsForALL;
            stageRequiredFields = _.union(self.requiredFields, self.requiredForSW);
        }
        if( regularUser == true){
            if( self.invoiceRole != 'AG' ) {
                noEditFieldsMix = self.noEditFieldsForNONE; //_.union(self.noEditFieldsForALL);
                stageRequiredFields = self.requiredFields;
                $('<style>'+'#scan_upload { display: none; }'+'</style>').appendTo('head');
            }
        }

        // do dodawania do pól własności tylko do odczytu
        _.each(noEditFieldsMix, function(num, key){
            self.noEditFields.push(num);
        });

        _.each(self.meta.panels, function(panel) {
            _.each(panel.fields, function(field) {
                if($.inArray(field.name, self.conditionalQSFields) > -1 && self.invoiceRole != 'MD') {
                    if(!self.model.attributes[field.name]){ $('.record-cell[data-name="'+field.name+'"]').addClass('vis_action_hidden'); }
                }

                if($.inArray(field.name, self.hiddenFieldsForPM) > -1 && self.invoiceRole == 'PM') {
                    if(!self.model.attributes[field.name]){
                        $('.record-cell[data-name="'+field.name+'"]').addClass('vis_action_hidden');
                    }
                }
            });
        });

        self.setEditableFields();
    },
    _doValidateQS: function(fields, errors, callback) {
        var self=this,
            fcplist = self.model.get('fcplist3_c'),
            package_no = self.model.get('package_no_c');

        //validate type requirements
        if (_.isEmpty(fcplist))
        {
            errors['fcplist3_c'] = errors['fcplist3_c'] || 'Uzupełnij FCP';
            errors['fcplist3_c'].required = true;
        }

        if (!package_no)
        {
            errors['package_no_c'] = errors['package_no_c'] || {};
            errors['package_no_c'].required = true;
        }
        callback(null, fields, errors);
    },
    whoIsLoggedIn: function() {
        var self = this,
            projectId = null;

         if(typeof(self.model.attributes.project_id_c) !== "undefined" && self.model.attributes.project_id_c!== null){
                projectId = self.model.attributes.project_id_c;
            }

        if(projectId !== null){
            var project = SUGAR.App.data.createBean('Project', {id: projectId} );
            request = project.fetch();
            request.xhr.done(function(cProject) {
                if(app.user.id == cProject.user_id1_c){
                    self.invoiceRole = 'QS';
                }else if(app.user.id == cProject.user_id_c){
                    self.invoiceRole = 'PM';
                }else if(app.user.id == cProject.user_id2_c){
                    self.invoiceRole = 'SV';
                }
                self.getNewMeta();
            });
        }
    },
    _render: function() {
        var self = this;
        self._super('_render');
        self.whoIsLoggedIn();
        self.getNewMeta(); // startowe
    },
    render: function() {
        var self = this;
        this._super('render');

        $(document).find('.main-pane').addClass('hideFilterView');
        $(document).find('.main-pane').addClass('detailInvoiceView');

        if(self.model.attributes.name == "F-NW") {
            $(document).find('.record').find('.row-fluid.panel_body:eq(1)').append('<input class="btn" type="button" id="not_my_invoice" name="not_my_invoice" value="It`s not my invoice">');

            app.api.call('GET', 'index.php?entryPoint=getData&notMyInvoice=1&invoice_id='+ self.model.attributes.id +'&user_id='+ app.user.id +'&search=1', null,{
                success: _.bind(function(data) {
                    if(data[0] != "brak notifikacji" && data[0] != 0) {
                        self.$el.find('#not_my_invoice').addClass('clicked');
                    }
                }, this)
            })
        }

        this.ifBoardInvoice();
        this.loadRelatedScans();
    },
    cancelClicked: function() {
        this._super('cancelClicked');
    },
    addDocument: function() {
        var self = this,
            attachments = this.$('div[data-name="upload_scan"] .activitystream-pending-attachment'),
            $submitButton = this.$('button.addDocument'),
            recivedData = null,
            payload = {
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
    bindDataChange: function(){
        this._super('bindDataChange');
    },
    deleteAttachment:function(event) {
        var noteID = event.currentTarget.parentElement.id,
            relationship = 1;

        app.api.call('GET', "index.php?entryPoint=getData&deleteAttachment=1&attachmentID="+ noteID +"&relationship="+ relationship, null,{
            success: _.bind(function(data) {
                if(data) {
                    $("#"+ noteID).parent().remove();
                }
            }, self)
        });
    },
    _renderHtml:function(){
        this._super('_renderHtml');
        if(this.$el.length>0){
            // create drop pdf field
            this.$el.find('[data-fieldname="scan_c"]').parent().parent().hide().after('<div class="row-fluid panel_body panel_body"><div id="scan_upload" class="span6 record-cell edit" data-type="relate" data-name="upload_scan"> <div class="inputwrapper"> <div contenteditable="false" class="sayit attachable taggable" style="height: 75px; text-align: center;"> <p style="margin-top: 2%;"><i class="fa fa-upload fa-3x"></i><br /> <span id="beforeUpload" style="margin-left: 20px;">Drag and drop document here !</span> <span id="afterUpload" style="margin-left: 20px;" class="hide">Thank you !</span> </p> </div> <div class="inputactions"> <button class="pull-right btn btn-primary addDocument btn-upload" track="click:addDocument" style="display:none;">Submit</button> </div> </div></div><div class="span6 record-cell"> <div class="inputwrapper"><ul id="invoiceList"></ul></div></div></div>');

        }

        return;
    },
    loadRelatedScans: function(){
        var self = this;
        var invoiceID = this.$('input[name="customid_c"]').val() || self.model.attributes.id;
        var $submitButton = this.$('button.addDocument');

        if( invoiceID !== undefined){
            var multiprojectPart = (self.model.attributes.multiproject_part_c) ? 1 : 0;

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

            app.api.call('GET', 'index.php?entryPoint=getData&invoce_relation=1&invoice_id='+ invoiceID +'&multi_part='+multiprojectPart+"&random"+Date.now(), null,{
                success: _.bind(function(data) {
                    this.$('#invoiceList').html('');

                    if(!_.isEmpty(data.id)) {
                        for (var i = 0; i < data.id.length; i++) {

                            if(App.user.id == '9122d6b9-46e5-9013-99f7-540f4beb464e' || App.user.id=='3437dc50-5512-c6ef-38f3-57fcfa6c6394' || App.user.id=='85ac3697-84bc-9400-07f9-5770d2e0c12e' || App.user.id=='d8c6bac7-eb79-5cc3-6580-5770ce45b719') {
                                this.$('#invoiceList').append('<li><div id="'+ data.id[i] +'" class="activitystream-pending-attachment2"><a class="deleteAttachment close" tabindex="-1"><i class="fa fa-times"></i></a><a data-note_id="'+ data.id[i] +'" data-action=show-document><img style="display:block; margin-top: 0;" src="/themes/default/images/pdf.jpeg"></a><p>'+ data.name[i] +'</p></div></li>');
                            } else {
                                this.$('#invoiceList').append('<li><div class="activitystream-pending-attachment2" id="'+ data.id[i] +'" ><a data-note_id="'+ data.id[i] +'" data-action=show-document><img style="display:block; margin-top: 0;" src="/themes/default/images/pdf.jpeg"></a><p>'+ data.name[i] +'</p></div></li>');
                            }

                            self.enableSubmitButton();
                        }
                    }
                })
            })

            if($submitButton.hasClass('disabled')) { $submitButton.removeClass('disabled'); }
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
    /*
    * if message is empty then disable submit button
    * else enable submit button
    */
    disableSubmitButton: function() {
        this.$('.addPost').addClass('disabled');
    },
    enableSubmitButton: function() {
        this.$('.addPost').removeClass('disabled');
    },
    toggleSubmitButton: function() {
        var post = this.getPost(),
            attachments = this.getAttachments();
            $submitButton = this.$('button.addDocument');

        if ( (post.value.length === 0) || (_.size(attachments) === 0) || $submitButton.hasClass('disabled') ){
            this.disableSubmitButton();
            this.loadRelatedScans();
        } else {
            this.addDocument();
            this.disableSubmitButton();
        }
        this.loadRelatedScans();
    },
    _handleContentPaste: function(event) {
        _.defer(_.bind(this._handleContentChange, this), event);
    },
    doNotMyInvoice: function(event) {
        if($(event.currentTarget).hasClass('clicked')) { $(event.currentTarget).removeClass('clicked'); }
        else { $(event.currentTarget).addClass('clicked'); }

        if(this.$el.find('#not_my_invoice').hasClass('clicked')) {
            var self = this;

            app.api.call('GET', 'index.php?entryPoint=getData&notMyInvoice=1&invoice_id='+ self.model.attributes.id +'&user_id='+ app.user.id, null,{
                success: _.bind(function(data) {
                    window.top.close();
                }, this)
            });
        }
    },
    naClicked: function() {
        var self = this;

        if(self.model.attributes.na_all_c === false) {
            self.model.set('warranty_na_c',true);
            self.model.set('work_completed_na_c',true);
            self.model.set('s_safety_na_c',true);
            self.model.set('agreement_na_c',true);
            self.model.set('na_all_c',true);
        } else {
            self.model.set('warranty_na_c',false);
            self.model.set('work_completed_na_c',false);
            self.model.set('s_safety_na_c',false);
            self.model.set('agreement_na_c',false);
            self.model.set('na_all_c',false);
        }
    },
    _doValidateAttachment: function(fields, errors, callback) {
        if($('.activitystream-pending-attachment').length === 0) {
            // TODO faktury: akienko z standardu
            alert('Dodaj proszę skan do faktury');
            errors['upload_scan'] = errors['upload_scan'] || {};
            errors['upload_scan'].required = true;
        }

        callback(null, fields, errors);
    },
    showDescription: function() {
        var self = this;

        window.open('https://'+ window.location.hostname +'/index.php?entryPoint=invoiceDescription&fv='+ self.model.attributes.id +'&multi_part='+ self.model.attributes.multiproject_part_c+'&multi='+ self.model.attributes.multiproject_c);
    },
    showDocument: function(event){
        event.preventDefault();
        event.stopPropagation();

        var element = this.$(event.currentTarget),
            data = element.data(),
            note_id = data.note_id;

        $('#content').append('<div id="review_background"><div id="review_pdf"><iframe id="review_iframe" title="PDF in an i-Frame" frameborder="1" scrolling="auto" height="100%" width="100%" ></iframe></div></div>');

        var $iFrame = $('#review_pdf iframe');
        $iFrame.load(function(){
            $('#review_pdf ').css("background","transparent");
        });
        $('#review_pdf ').css("background","grey");

        $iFrame.attr('src', 'https://'+ window.location.hostname +'/rest/v10/Notes/'+note_id+'/file/filename?force_download=0&platform=base&noteShare=1');
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
    ifBoardInvoice: function() {
        var self = this,
            boardCheckbox = self.model.get("board_invoice_c");

        if(boardCheckbox) {
            $('.LBL_RECORDVIEW_PANEL1').hide(0);
            $('.record-cell[data-name="package_no_c"]').hide(0);
            $('.record-cell[data-name="nett1_c"]').hide(0);
            $('.record-cell[data-name="fcplist3_c"]').hide(0);
            $('.record-cell[data-name="project1_c"]').hide(0);

            $('.record-cell[data-name="package_no_c"]').next().css("margin-left", 0);
            $('.record-cell[data-name="nett1_c"]').next().css("margin-left", 0);
        }
    },
    changeProject: function() {
        this.model.set("accept1_c", false);
        this.model.set("accept2_c", false);
        this.model.set("accept3_c", false);
        this.model.set("accept4_c", false);
    },
})

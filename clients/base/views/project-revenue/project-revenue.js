({
    plugins: ['Dashlet'],
    id: 'project-revenue',

    edit: false,
    delta: {},
    annexModelJSON: {},

    /**
     * {@inheritDoc}
     * Constructor
     */
    initialize: function(options) {
        var self = this;
        // call the parent's (View's) initialize function
        // passing options as an array
        self._super('initialize', [options]);
        self.fetchAllData();

        this.events = _.extend({}, this.events, {
            'click #saveRevenue': 'saveProjectData',
            'click #cancelRevenue': 'cancelProjectData',
            'click #plusAnnex': 'addAnnexRow',
            'click .minus-annex': 'removeAnnexRow',
        }); // events
    },// initialize

    removeAnnexRow: function(event) {
        var element = this.$(event.currentTarget).parent();
        element.remove();
    },

    cancelProjectData: function() {
        var self = this;

        self.edit = false;
        self.render();
    },

    addAnnexRow: function() {
        var html = '<div class="agreement-annexes">'+
                          '<span class="span3">'+
                              '<input type="text" data-id="value" class="revenue-input annex-value" placeholder="Value" value=""/>'+
                          '</span>'+
                          '<span class="span7">'+
                              '<input type="text" data-id="description" class="revenue-input annex-description" placeholder="Description" value=""/>'+
                        '</span>'+
                        '<div class="btn btn-invisible minus-annex span1"><i class="fa fa-minus"></i></div>'+
                    '</div>';

        $('#agreementAnnexes').append(html);
    }, 

    saveProjectData: function() {
        var self = this,
            $agreementInput = $('#agreement-revenue'),
            $projectCosts = $('#project-costs'),
            $otherCosts = $('#other-costs'),
            $tvoAmount = $('#tvo-amount'),
            $cvoAmount = $('#cvo-amount');

        self.annexModelJSON = {};

        $(".agreement-annexes").each(function(index, el) {
            if(($(this).find('.annex-value[data-id="value"]').val() != "") && ($(this).find('.annex-description[data-id="description"]').val() != "")) {
                self.annexModelJSON[index] = {
                    'annexValue': Number($(this).find('.annex-value[data-id="value"]').val().toString().replace(/,/g, '')).toFixed(2),
                    'annexDescription': $(this).find('.annex-description[data-id="description"]').val()
                }
            }
        });

        /**
         * if .agreement-revenue not equals "" or approved-amount not equals "" then
         *  Get values from inputs
         *  get user id and time when quicktask was created
         *
         *  ajax:
         *   send user id and time into php script
         *    success then
         *      refresh dashlet with new values
         * end if
         */
        if($agreementInput.val() !== '' || $projectCosts.val() !== '') {
            var userID = App.user.id,
                inputAgreementValue = $agreementInput.val().toString().replace(/,/g, ''),
                inputProjectCosts = $projectCosts.val().toString().replace(/,/g, ''),
                inputOtherCosts = $otherCosts.val().toString().replace(/,/g, ''),
                inputTvoAmount = $tvoAmount.val().toString().replace(/,/g, ''),
                inputCvoAmount = $cvoAmount.val().toString().replace(/,/g, ''),
                nocache = new Date().getTime();

            $.ajax({
                url: 'index.php?entryPoint=getData&project_revenue=1&project_id='+ self.model.get('id') +"&update=1&cache="+ nocache,
                type: 'POST',
                cache: false,
                dataType: 'json',
                data: {
                    userID: userID,
                    textAgreementValue: inputAgreementValue,
                    textProjectCosts: inputProjectCosts,
                    textOtherCosts: inputOtherCosts,
                    textTvoAmount: inputTvoAmount,
                    textCvoAmount: inputCvoAmount,
                    JSONAnnexesValues: JSON.stringify(self.annexModelJSON),
                },
                success: function(data) {
                    self.agreementValue = inputAgreementValue;
                    self.projectCosts = inputProjectCosts;
                    self.otherCosts = inputOtherCosts;
                    self.tvoValue = inputTvoAmount;
                    self.cvoValue = inputCvoAmount;
                    self.profitMargin = Number(data.profit_margin).toFixed(2);
                    self.procentProfitMargin = Number(data.profit_margin_procent);

                    var sum = 0;
                    _.each(self.annexModelJSON, function(value, key) {
                        sum = (Number(sum) + Number(value.annexValue));
                        self.annexModelJSON[key].annexValue = app.utils.formatNumberLocale(value.annexValue);
                    });

                    self.annexesSum = Number(sum).toFixed(2);
                    self.totalSummary = (Number(sum) + Number(inputAgreementValue) + Number(inputTvoAmount) + Number(inputCvoAmount)).toFixed(2);
                    self.totalMargin = (Number(self.totalSummary) - Number(data.all_costs)).toFixed(2);
                    self.totalMarginProcent = (Number(self.totalSummary) == 0) ? 0.00 : (100 * (Number(self.totalSummary) - Number(data.all_costs)) / Number(self.totalSummary)).toFixed(2);
                    self.marginDeviation = (Number(self.totalMarginProcent) - Number(self.procentProfitMargin)).toFixed(2);

                    self.delta.profitMargin = (self.profitMargin <= 0) ? false : true;
                    self.delta.totalMargin = (self.totalMargin <= 0) ? false : true;
                    self.delta.marginDeviation = (self.marginDeviation <= 0) ? false : true;

                    self.agreementValue = app.utils.formatNumberLocale(self.agreementValue);
                    self.projectCosts = app.utils.formatNumberLocale(self.projectCosts);
                    self.tvoValue = app.utils.formatNumberLocale(self.tvoValue);
                    self.cvoValue = app.utils.formatNumberLocale(self.cvoValue);
                    self.profitMargin = app.utils.formatNumberLocale(self.profitMargin);
                    self.annexesSum = app.utils.formatNumberLocale(self.annexesSum);
                    self.totalSummary = app.utils.formatNumberLocale(self.totalSummary);
                    self.totalMargin = app.utils.formatNumberLocale(self.totalMargin);
                    self.otherCosts = app.utils.formatNumberLocale(self.otherCosts);
                    self.totalCosts = app.utils.formatNumberLocale(data.all_costs);

                    self.edit = false;
                    self.render();
                }, // success
            }); // ajax
        } // if
    }, // addProjectData

    editProjectData: function(e) {
        var self = this;

        self.edit = true;
        self.agreementValue = self.agreementValue.toString().replace(/,/g, '');
        self.projectCosts = self.projectCosts.toString().replace(/,/g, '');
        self.otherCosts = self.otherCosts.toString().replace(/,/g, '');
        self.tvoValue = self.tvoValue.toString().replace(/,/g, '');
        self.cvoValue = self.cvoValue.toString().replace(/,/g, '');

        _.each(self.annexModelJSON, function(value, key) {
            self.annexModelJSON[key].annexValue = value.annexValue.toString().replace(/,/g, '');
        });

        self.render();
    }, // editProjectData

    render: function() {
        this._super("render");
        $("#agreement-revenue").focus(); // #agreement-revenue has been focused all the time
        var self = this;

        if(!(app.user.id == self.model.get('user_id1_c') || 
            app.user.id == self.model.get('user_id_c') || 
            app.user.id == "144c39bf-ccc3-65ec-2023-5407f7975b91" ||
            app.user.id == "e07026a9-691a-67e7-32a6-5407f619ae5b" ||
            app.user.id == "e22f8f47-6388-e3b0-2802-55fbf7383322" ||
            app.user.id == "801c0c78-edc1-e54f-08c2-5407f786ce48" ||
            (self.model.get('id') == "3826bc82-e4e2-9fcc-9deb-5847e7caa33a"
            && app.user.id == "d42f87ee-eaab-66cd-3c4b-540f485835dd") ||
            app.user.id == "1")
        ) {
            self.$el.parent().parent().parent().parent().hide(0);
        } else {
            if(self.model.get("archival_c") == true) {
               $(".dropdown-menu").find('a[name="project-revenue-edit"]').hide();
            }
        }
    }, // render

    fetchAllData: function() {
        var self = this,
          nocache = new Date().getTime();

        $.ajax({
            url: 'index.php?entryPoint=getData&project_revenue=1&project_id='+ self.model.get('id') +"&update=0&cache="+ nocache,
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(data) {
                self.agreementValue = data.agreement_revenue;
                self.projectCosts = data.project_costs;
                self.otherCosts = data.other_costs;
                self.annexModelJSON = (!_.isEmpty(data.annex_data)) ? JSON.parse(data.annex_data.replace(/&amp;quot;/g,'"')) : {};
                self.tvoValue = data.tvo;
                self.cvoValue = data.cvo;
                self.profitMargin = Number(data.profit_margin).toFixed(2);
                self.procentProfitMargin = Number(data.profit_margin_procent).toFixed(2);

                var sum = 0;
                // console.info("Annexes: ", self.annexModelJSON);
                _.each(self.annexModelJSON, function(value, key) {
                    sum = (Number(sum) + Number(value.annexValue));
                    self.annexModelJSON[key].annexValue = app.utils.formatNumberLocale(value.annexValue);
                });

                self.annexesSum = Number(sum).toFixed(2);
                self.totalSummary = (Number(sum) + Number(data.agreement_revenue) + Number(data.tvo) + Number(data.cvo)).toFixed(2);
                self.totalMargin = (Number(self.totalSummary) - Number(data.all_costs)).toFixed(2);
                self.totalMarginProcent = (Number(self.totalSummary) == 0) ? 0.00 : (100 * (Number(self.totalSummary) - Number(data.all_costs)) / Number(self.totalSummary)).toFixed(2);
                self.marginDeviation = (Number(self.totalMarginProcent) - Number(self.procentProfitMargin)).toFixed(2);

                self.delta.profitMargin = (self.profitMargin <= 0) ? false : true;
                self.delta.totalMargin = (self.totalMargin <= 0) ? false : true;
                self.delta.marginDeviation = (self.marginDeviation <= 0) ? false : true;

                self.agreementValue = app.utils.formatNumberLocale(self.agreementValue);
                self.projectCosts = app.utils.formatNumberLocale(self.projectCosts);
                self.tvoValue = app.utils.formatNumberLocale(self.tvoValue);
                self.cvoValue = app.utils.formatNumberLocale(self.cvoValue);
                self.profitMargin = app.utils.formatNumberLocale(self.profitMargin);
                self.annexesSum = app.utils.formatNumberLocale(self.annexesSum);
                self.totalSummary = app.utils.formatNumberLocale(self.totalSummary);
                self.totalMargin = app.utils.formatNumberLocale(self.totalMargin);
                self.otherCosts = app.utils.formatNumberLocale(self.otherCosts);
                self.totalCosts = app.utils.formatNumberLocale(data.all_costs);

                self.edit = false;
                self.render();
            }, // success
        }); // ajax
    },
})

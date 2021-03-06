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
    createdModel: undefined,
    listContext: undefined,
    id: 'OppCreate',
    originalSuccess: undefined,
    alert: undefined,
    initialize: function(options) {
        this.plugins = _.union(this.plugins, ['LinkedModel']);
        this._super('initialize', [options]);
    },
    getCustomSaveOptions: function(options) {
        if (app.metadata.getModule('Opportunities', 'config').opps_view_by === 'RevenueLineItems') {
            this.createdModel = this.model;
            this.listContext = this.context.parent || this.context;
            this.originalSuccess = options.success;
            var success = _.bind(function(model) {
                this.originalSuccess(model);
                var addedRLIs = model.get('revenuelineitems') || false;
                addedRLIs = (addedRLIs && addedRLIs.create && addedRLIs.create.length);
                if (!addedRLIs && options.lastSaveAction != 'saveAndCreate') {
                    this.showRLIWarningMessage(this.listContext.get('module'));
                }
            }, this);
            return {
                success: success
            };
        }
    },
    showRLIWarningMessage: function() {
        app.routing.before('route', this.dismissAlert, undefined, this);
        var message = app.lang.get('TPL_RLI_CREATE', 'Opportunities') + '  <a href="javascript:void(0);" id="createRLI">' +
            app.lang.get('TPL_RLI_CREATE_LINK_TEXT', 'Opportunities') + '</a>';
        this.alert = app.alert.show('opp-rli-create', {
            level: 'warning',
            autoClose: false,
            title: app.lang.get('LBL_ALERT_TITLE_WARNING') + ':',
            messages: message,
            onLinkClick: _.bind(function() {
                app.alert.dismiss('create-success');
                this.openRLICreate();
            }, this),
            onClose: _.bind(function() {
                app.routing.offBefore('route', this.dismissAlert, this);
            }, this)
        });
    },
    dismissAlert: function(data) {
        if (data && !(data.args && data.args[0] === 'Opportunities' && data.route === 'list')) {
            app.alert.dismiss('opp-rli-create');
            app.routing.offBefore('route', this.dismissAlert, this);
        }
    },
    openRLICreate: function() {
        this.dismissAlert(true);
        var model = this.createLinkModel(this.createdModel || this.model, 'revenuelineitems');
        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                module: model.module,
                model: model
            }
        }, _.bind(function(model) {
        	$('input[name="name"]').val("dsadasdasd");
            if (!model) {

                return;
            }
            var ctx = this.listContext || this.context;
            ctx.reloadData({
                recursive: false
            });
            ctx.trigger('subpanel:reload', {
                links: ['opportunities', 'revenuelineitems']
            });
        }, this));
    },
    _render: function() {
    	this._super('_render', []);
        $('span[data-name="name"]').css("display", "none");
    	$('.record-cell[data-name="canceled_c"]').css("visibility", "hidden");
    },
    _dispose: function() {
        if (this.alert) {
            this.alert.getCloseSelector().off('click');
        }
        this._super('_dispose', []);
    },
    save: function() {
        if(this.model.get('framework_c')) {
            this.model.set('opportunity_type', 'framework');
        } else {
            this.model.set('opportunity_type', 'single');
        }

        this._super('save');
    },
})
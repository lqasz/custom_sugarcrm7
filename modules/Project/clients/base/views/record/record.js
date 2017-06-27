({
    extendsFrom: 'RecordView',
    initialize: function(options) {
        this.plugins = _.union(this.plugins, ['LinkedModel']);
        // this._super('initialize', [options]);

        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', args:[options]});
        this.context.on('button:project_gantt:click', this.project_gantt, this);
        this.context.on('button:project_invoices:click', this.getProjectInvoices, this);
        
        this.events = _.extend({}, this.events, {
            'click a[name="project_gantt"]' : 'fproject_gantt',
            'click a[name="project_pcl"]' : 'showProjectPCL',
        });
    },
    fproject_gantt: function() {

        var win = window.open('http://' + window.location.hostname + '/project_gantt/index.php?project_id=' + this.model.id + '#en', '_blank');
        win.focus();
    },
    getProjectInvoices: function() {
        window.open('http://'+ window.location.hostname +'/projectallinvoices_csv.php?projectID='+ this.model.get('id') +'&projectName='+ this.model.get('name'));
    },
    _render: function() {  
      this._super('_render', []);
      
        var filter = _.filter(app.user.attributes.roles, function(role) { 
            if(role == "Partner" ||
                role == "Manager F&A" ||
                role == "Manager P&C" ||
                role == "Manager QS") {
                return role;
            }
        });

        if(filter.length == 0) {
            $(document).find('a[name="project_pcl"]').addClass('hide');
        }
    },
    render: function() {
        this._super('render');

        $('.record-cell[data-name="cases_project_1_name"]').hide(0);
        if(App.user.get('id') !== '1') {
            $(".subpanels-layout .subpanel-header .btn-toolbar").hide();
            $("div[data-subpanel-link='project_aa_routers_1']").hide();
        }
    },

    showProjectPCL: function() {
        var self = this;
        window.open('http://'+window.location.hostname+'/#Cases/'+self.model.get("cases_project_1cases_ida"));
    }
})

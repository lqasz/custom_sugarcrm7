({
    extendsFrom: "RecordView",
    id: 'PCLEdit',
    pclView: undefined, // main view action
    pclModel: {}, // main model
    events: _.extend({}, this.events, {
        'blur .description': 'addDescription',
        'blur .pcl-comment-text': 'addComment',
        'change .pcl-na': 'changeNA',
        'change .responsible-person': 'changePerson',
        'change .verification': 'changeVerification',
        'click a[name=cancel_button]': 'cancelClicked',
        'click a[name="pcl_dropdown"]' : 'showDropDowns',
    }),

    initialize: function(options) {
        this._super('initialize', [options]);
        var self = this;
            pclSections = app.lang.getAppListStrings('pcl_sections_list'); // get all ections fro DropDown list

        self.pclView = options.context.get("action"); // current view action
        // on synchronize complete event in collection 
        self.collection.on('data:sync:complete', function() {
            // get all data from db
            app.api.call('GET', 'index.php?entryPoint=getData&PCLData=1&pcl_id='+ self.model.get('id'), null,{
                success: _.bind(function(data) {
                    self.pclModel = data;

                    /**
                     * if empty data set then create scheme records for all responsible users
                     * else save `clean` data to the model
                     */
                    if(data.empty == true) {
                        self.pclModel.section = {};

                        _.each(pclSections, function(value, key) {
                            // split by responsibles
                            var dropDown = value.split("|"),
                                subject = dropDown[1],
                                responsiblesSet = dropDown[0].split(",");

                            self.pclModel.section[key] = {
                                'subject': subject,
                                'responsibles': {},
                            };

                            for(var i = 0; i < responsiblesSet.length; i++) {
                                var responsibleKey = responsiblesSet[i].toLowerCase().trim();

                                // add scheme to the empty main model
                                self.pclModel.section[key].responsibles[i] = {
                                    "id": self.pclModel.users[responsibleKey].id,
                                    "name": self.pclModel.users[responsibleKey].name,
                                    "responsible": responsibleKey,
                                    "verification": "PUSTY",
                                    "description": "",
                                    "iter": i
                                };
                            }
                        }); // each
                    } else {
                        self.pclModel.section = JSON.parse(data.section.replace(/&quot;/g,'"'));
                    }
                    
                    self.model.trigger('addToSelect'); // trigger event in model
                })
            });
        }, self);

        self.model.on('addToSelect', function() {
            self.render();
        }, self);
    },

    /**
     * Change responsible person
     */
    changePerson: function(e) {
        var self = this,
            data = $(e.currentTarget).data(),
            newValue = $(e.currentTarget).val();

        _.each(this.pclModel.users, function(value, key) {
            if(value.id == newValue) {
                self.pclModel.section[data.name].responsibles[data.iter] = {
                    'id': newValue,
                    'name': value.name,
                    'responsible': key,
                };
            }
        }); // each
    },

    /**
     * Change verification stage
     */
    changeVerification: function(e) {
        var self = this,
            oldPCLClass = '',
            PCLClass = '',
            data = $(e.currentTarget).data(),
            newValue = $(e.currentTarget).val(),
            toRemove = self.pclModel.section[data.name].responsibles[data.iter].verification;
        
        if(toRemove == 'NIE') {
            oldPCLClass = 'pcl-red';
        } else if(toRemove == 'TAK' ) {
            oldPCLClass = 'pcl-green';
        } else if(toRemove == 'NA' ) {
            oldPCLClass = 'pcl-gray';
        }

        if(newValue == 'NIE') {
            PCLClass = 'pcl-red';
        } else if(newValue == 'TAK' ) {
            PCLClass = 'pcl-green';
        } else if(newValue == 'NA' ) {
            PCLClass = 'pcl-gray';
        }

        $(e.currentTarget).removeClass(oldPCLClass);
        $(e.currentTarget).addClass(PCLClass);

        self.pclModel.section[data.name].responsibles[data.iter].verification = newValue;
    },

    /**
     * Change n/a stage in pcl comments
     */
    changeNA: function(e) {
        var self = this,
            data = $(e.currentTarget).data(),
            newValue = $(e.currentTarget).val();

        self.pclModel.answers[data.type].answer[data.id]["update"] = 1;
        self.pclModel.answers[data.type].answer[data.id].na = newValue;
    },

    /**
     * Add a description to the model
     */
    addDescription: function(e) {
        var self = this,
            PCLClass = '',
            data = $(e.currentTarget).data(),
            newValue = $(e.currentTarget).val();

        if(self.pclModel.section[data.name].responsibles[data.iter].verification == 'NIE') {
            PCLClass = 'pcl-red';
        } else if(self.pclModel.section[data.name].responsibles[data.iter].verification == 'TAK' ) {
            PCLClass = 'pcl-green';
        } else if(self.pclModel.section[data.name].responsibles[data.iter].verification == 'NA' ) {
            PCLClass = 'pcl-gray';
        }

        $(e.currentTarget).addClass(PCLClass);
        self.pclModel.section[data.name].responsibles[data.iter].description = newValue;
    },

    /**
     * Add comment to pcl comments
     */
    addComment: function(e) {
        var self = this,
            data = $(e.currentTarget).data(),
            newValue = $(e.currentTarget).val();

        self.pclModel.answers[data.type].answer[data.id]["update"] = 1;
        self.pclModel.answers[data.type].answer[data.id].text = newValue;
    },

    /**
     * Before render function simply hide all dropdowns
     */
    _renderHtml:function(){
        this._super('_renderHtml');

        if(app.user.id != "144c39bf-ccc3-65ec-2023-5407f7975b91") {
            this.$el.find('.btn[name="pcl_dropdown"]').hide();
        }
        return;
    },

    /**
     * Add a description to the model
     */
    render: function() {
        this._super('render');

        $('.label-Cases').parent().removeAttr('href');
        $(".search-filter").addClass("hide");
        this.rebuildFields();

        var self = this;
        // goes for each row, find label row and replace it with new label
        this.$el.find(".span11").each(function(index, el) {
            var label = $(el).children().text();
            $(el).replaceWith(self.returnLabelField(label)); 
        });

        this.$el.find(".pcl-row").attr("disabled", true); // add disabled attribiute
        if(this.pclView == "edit") {
            this.$el.find(".not-disabled").removeAttr("disabled"); // and remove disabled if row has this class
        }
    },

    editClicked: function() {
        this.pclView = "edit";
        this.$el.find(".not-disabled").removeAttr("disabled");
        this._super('editClicked');
    },

    /**
     * Add new html fields to the view
     */
    rebuildFields: function() {
        var self = this;

        if(!_.isEmpty(this.pclModel)) {
            var iter = 1;

            _.each(this.pclModel.section, function(value, key) {
                var html = '', container = '',
                    section = key.split("."),
                    countResponsibles = _.size(value.responsibles),
                    $recordPanel = self.$el.find(".record-panel:eq("+(section[0] - 1)+")").children('.record-panel-content');
                
                html += '<div class="first panel_body span12" data-name="'+key+'">';
                if(section[0] != 5) {
                    html += '<div class="span11 record-cell"><b>'+key+' '+value.subject+'</b></div>';
                    
                    // add sections fields, represented in models structure
                    for(var i = 0; i < countResponsibles; i++) {
                        var className = "next",
                            n = 12 / countResponsibles;

                        if(i == 0) { className = "first"; }

                        container += '<div class="span'+n+' '+className+'">';
                        container += self.returnResponsibleField(value.responsibles[i], key, i);
                        container += self.returnVerificationField(key, i)+self.returnDescriptionField(key, i);
                        container += '</div>';
                    }
                } else {
                    html += '<div class="span5 record-cell pcl-row-height"><b>'+section[2]+' '+value.subject+'</b></div>'+
                            '<div class="span3 first">'+self.returnResponsibleField(value.responsibles[0], key, 0)+self.returnVerificationField(key, 0)+'</div>'+
                            '<div class="span4">'+self.returnDescriptionField(key, 0)+'</div>';

                    $recordPanel = $recordPanel.find(".row-fluid:eq("+(section[1] - 1)+")");
                }
                html += container;
                html += '</div>';
                $recordPanel.append(html);

                iter++;
            }); // each

            $recordPanel = self.$el.find(".record-panel:eq(5)").children('.record-panel-content');
            _.each(this.pclModel.answers, function(answer, type) {
                if(answer != "brak") {
                    $recordPanel.append(self.returnCommentField(answer, type));
                }
            });
        }
    },

    /**
     * Return html fields which represents comments in model
     */
    returnCommentField: function(answer, type) {
        var self = this,
            html = '<div class="panel_body">',
            optionsObject = {
                "": "",
                "NA": "N/A",
            };
        
        html += '<div class="record-label span12 first"><b>'+answer.name+'</b></div>';
        _.each(self.pclModel.users, function(value, key) {
            var disabled = '',
                options = '',
                comment = answer.answer[value.id];

            if(value.id == app.user.id || app.user.id == "e22f8f47-6388-e3b0-2802-55fbf7383322") {
                disabled = "not-disabled";
            }

            _.each(optionsObject, function(text, textKey) {
                var selected = '';
                
                if(comment.na == textKey) {
                    selected = 'selected="selected"';
                }

                options += '<option '+selected+' value="'+textKey+'">'+text+'</option>';
            });

            html += '<div class="record-cell .pcl-row-height span12 first pcl-comment">';
            html += '<div class="record-label span3">'+value.name+'</div>';
            html += '<div class="record-cell span8"><input value="'+comment.text+'" type="text" data-id="'+value.id+'" data-type="'+type+'" class="pcl-comment-text pcl-row inherit-width '+disabled+'"/></div>';
            html += '<div class="record-cell span1"><select class="pcl-na pcl-row '+disabled+'" data-id="'+value.id+'" data-type="'+type+'">'+options+'</select></div>';
            html += '</div>';

            self.pclModel.answers[type].answer[value.id]["update"] = 0;
            self.pclModel.answers[type].answer[value.id]["user_name"] = value.name;
        });

        html += '</div>';

        return html;
    },

    /**
     * Return html responsible field represented in model
     */
    returnResponsibleField: function(responsible, dataName, iter) {
        var self = this,
            options = '';

        _.each(this.pclModel.users, function(value, key) {
            var selected = '';
            if(responsible.responsible == key) {
                selected = 'selected="selected"';
            }

            options += '<option '+selected+' data-name="'+key+'" value="'+value.id+'">'+value.name+'</option>';
        });

        var disabled = (app.user.id == "144c39bf-ccc3-65ec-2023-5407f7975b91" || app.user.id == "e22f8f47-6388-e3b0-2802-55fbf7383322") ? "not-disabled" : "",
            string = '<div class="span9">'+
                        '<div class="record-label label-text"></div>'+
                        '<span class="normal index">'+
                            '<span>'+
                                '<div>'+
                                    '<select data-iter="'+iter+'" data-name="'+dataName+'" class="pcl-row responsible-person '+disabled+'">'+
                                        options+
                                    '</select>'+
                                '</div>'+
                            '</span>'+
                        '</span>'+
                    '</div>';

        return string;
    },

    /**
     * Return html verification field represented in model
     */
    returnVerificationField: function(dataName, iter) {
        var options = '',
            PCLClass = "pcl-black",
            responsible = this.pclModel.section[dataName].responsibles[iter],
            disabled = (responsible.id == app.user.id) ? "not-disabled" : "",
            verification = responsible.verification, // set default verification
            optionsObject = {
               "PUSTY": "",
               "TAK": "TAK",
               "NIE": "NIE",
               "NA": "N/A", 
            };

        _.each(optionsObject, function(value, key) {
            var selected = '';
            
            if(verification == key) {
                selected = 'selected="selected"';

                if(key == 'NIE') {
                    PCLClass = 'pcl-red';
                } else if(key == 'TAK' ) {
                    PCLClass = 'pcl-green';
                } else if(key == 'NA' ) {
                    PCLClass = 'pcl-gray';
                }
            }

            options += '<option '+selected+' value="'+key+'">'+value+'</option>';
        });

        var string = '<div class="span3">'+
                        '<div class="record-label label-text"></div>'+
                        '<span class="normal index">'+
                            '<span>'+
                                '<div>'+
                                    '<select data-iter="'+iter+'" data-name="'+dataName+'" class="pcl-row verification '+PCLClass+' '+disabled+'">'+
                                        options+
                                    '</select>'+
                                '</div>'+
                            '</span>'+
                        '</span>'+
                    '</div>';

        return string;
    },

    /**
     * Return simple description field
     */
    returnDescriptionField: function(dataName, iter) {
        var PCLClass = '',
            responsible = this.pclModel.section[dataName].responsibles[iter],
            disabled = (responsible.id == app.user.id) ? "not-disabled" : "",
            description = (_.isEmpty(responsible.description)) ? "" : responsible.description;
        
        if(responsible.verification == 'NIE') {
            PCLClass = 'pcl-red';
        } else if(responsible.verification == 'TAK' ) {
            PCLClass = 'pcl-green';
        } else if(responsible.verification == 'NA' ) {
            PCLClass = 'pcl-gray';
        }

        var string = '<div class="span12 first">'+
                        '<div class="record-label label-text"></div>'+
                        '<span class="normal index">'+
                            '<span>'+
                                '<div class="input-description">'+
                                    '<textarea data-iter="'+iter+'" data-name="'+dataName+'" class="pcl-row inherit-width description span12 '+disabled+' '+PCLClass+'">'+description+'</textarea>'+
                                '</div>'+
                            '</span>'+
                        '</span>'+
                    '</div>';

        return string;
    },

    returnLabelField: function(label) {
        return '<div class="span12 record-cell record-label"><b class="label-bold">'+ label +'</b></div>';
    },

    /**
     * Send specific data to backend sever
     */
    saveClicked: function() {
        var self = this;
        this.$el.find('.btn[name="save_button"]').attr("disabled", true);

        $.ajax({
            url: 'index.php?entryPoint=getData&updatePCLData=1&pcl_id='+ self.model.get('id')+'noCache='+ (new Date().getTime()),
            type: 'POST',
            data: {
                count: self.pclModel.count,
                userID: app.user.id,
                projectName: self.model.get('name'),
                JSONpclModel: self.pclModel.section,
                JSONpclComments: self.pclModel.answers,
            },
            success: function(data) {
                self.pclView = "detail";
                self._super('saveClicked');
            },
        }); // ajax
    },

    cancelClicked: function() {
        this.pclView = "detail";
        location.replace('http://'+window.location.hostname+'/#Cases/'+this.model.get("id"));
    },

    showDropDowns: function() {
        window.open('http://'+window.location.hostname+'//#bwc/index.php?module=ModuleBuilder&action=index&type=dropdowns');
    },
})
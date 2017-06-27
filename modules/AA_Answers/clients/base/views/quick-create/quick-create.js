  /**
   * @class View.Views.Base.QuickCreateView
   * @alias SUGAR.App.view.views.BaseQuickCreateView
   * @extends View.Views.Base.BaseeditmodalView
   */
  ({
      extendsFrom: 'BaseeditmodalView',
      fallbackFieldTemplate: 'edit',
      question: undefined,
      licznik: 0,
      timerId: 0,
      // events:function(){
      //       return _.extend({},ParentView.prototype.events,{
      //           'click [type=radio]': 'chooseAnswer',
      //       });

      // },

     /**
     * {@inheritDoc}
     * Constructor
     */
      initialize: function(options) {
        var self = this;
          //this.plugins = _.union(this.plugins, ['LinkedModel', 'HistoricalSummary']);
          app.view.View.prototype.initialize.call(this, options);

          this.events = _.extend({}, this.events, {
              'click [type=radio]': 'chooseAnswer',
          });

          if (this.layout) {
              this.layout.on('app:view:quick-create', function() {
                  var createModel = this.model,
                  nowePytanie = null,
                  request = null;
                  // console.log('nowe rzeczy');
                  // console.log(createModel);
                  // nowePytanie = createModel.getRelatedCollection('aa_questions');
                  // request = createModel.fetch({relate: true}); //{relate: true});

                  // Creates bean containing AA_Questions model with id
                  var nn = SUGAR.App.data.createBean('AA_Questions', {id: createModel.link.bean.id} );

                  // Take the data from AA_Questions
                  request = nn.fetch(); //{relate: true});
                  // Loading the data
                  request.xhr.done(function() {
                      // console.log('calos wynikow po pobraniu'); 
                      // console.log(createModel);
                      // console.log('----------------a');
                      // console.log(nowePytanie.get('a1') );
                      // console.log('----------------b');
                      // console.log(createModel.link.bean.get('a1') );
                      // console.log('----------------');
                      // console.log(nn.get('a1'));
                      // console.log('----------------');

                      // Create collection and get data from bean
                      var pytanie = {};
                      pytanie.a1 = nn.get('a1');
                      pytanie.a2 = nn.get('a2');
                      pytanie.a3 = nn.get('a3');
                      pytanie.a4 = nn.get('a4');
                      pytanie.tresc = nn.get('description');
                      pytanie.type_of_question = nn.get('type_of_question');
                      pytanie.right_answer = nn.get('right_answer_c');
                      self.question = pytanie;

                      // console.log(self.question + '   ' + this.question);
                      self.render();

                      // self.render();
                      self.$('.modal').modal({
                          backdrop: 'static'
                      }, this);

                      self.$('.modal').modal('show');
                      $('.datepicker').css('z-index', '20000');
                      $('.navbar-fixed-top').hide(0);
                      $('#content').css('top','0');
                      app.$contentEl.attr('aria-hidden', true);
                      $('.modal-backdrop').insertAfter($('.modal'));

                      // Timer
                      if (self.timerId) {
                          clearInterval(self.timerId);
                          self.licznik = 0;
                      }
                      self.timerId = setInterval(_.bind(function() {
                          console.log(self.licznik);
                          self.licznik++;
                          self.$el.find('.timer').html(self.licznik);
                                       
                      }, self), 1000);

                      self.disableButtons(true);

                      // console.log('self timer id inicjacja' );
                      /**If any validation error occurs, system will throw error and we need to enable the buttons back*/
                      self.context.get('model').on('error:validation', function() {
                          console.log('-----------------------------------------------');
                          console.log('Błąd, kod błędu: 0cma1101110');
                          console.log('-----------------------------------------------');
                          self.disableButtons(false);
                      }, self);
                  });
              }, this);

          }
          this.bindDataChange();
      },

      /**
      * That function allows user to choose an answer and synchronize blocking radio buttons 
      */
      chooseAnswer: function() {
        // var element = $(event.currentTarget),
          var ifChecked = 0;
          var self = this;

          // console.log('wybrana opdowiedz');
          ifChecked = this.$el.find(".odp:checked").val();
          // console.log('wybrana opdowiedz');
          if(ifChecked ){
            self.disableButtons(false);
            // console.log('wybrana '+ifChecked);
          }else{
            self.disableButtons(true);
            // console.log('niewybrana '+ifChecked);
          }

      },

      /**Overriding the base saveButton method*/
      saveButton: function(event) {
        event.stopPropagation();
          var self = this;

          self.$el.find('.radio').css('color', 'red');

          for(var i = 1; i <= 3; i++) {
            if(self.$el.find('#a'+ i).val() == self.question.right_answer) {
              self.$el.find('#a'+ i).parent().css('color', 'green');
            }
          }

          var createModel = this.context.get('model'),
            czas = this.licznik;
          this.$el.find('.timer').html('0');
          var value = this.$el.find(".odp:checked").val();
          // console.log(czas);
          // console.log(value);
  
          clearInterval(this.timerId);
          this.licznik = 0;

          if(self.question.right_answer == value) {
              createModel.set('is_good', 1);
          }

          // Setting needed informations
          createModel.set('name', 'odpowiedz');
          createModel.set('answer', value);
          createModel.set('answer_time', czas);

          this.$('[name=save_button]').attr('data-loading-text', app.lang.get('LBL_LOADING'));
          this.$('[name=save_button]').button('loading');

          /** Disable the buttons during save.*/
          this.disableButtons(true);
          this.processModel(createModel);

          // alert(self.question.right_answer);

          console.info("createModel");
          console.info(createModel);
          /** Saves the related note bean*/
          createModel.save(null, {
              relate: true,
              fieldsToValidate: this.getFields(this.module),
              success: _.bind(function() {
                  app.api.call('GET', 'index.php?entryPoint=getData&mark_question=1&question_id='+ createModel.attributes.aa_questions_aa_answers_1aa_questions_ida+'&user_id='+app.user.id, null,{
                      success: _.bind(function(data) {
                        if(data == true) {
                          self.sleep(300);
                          this.saveComplete();
                        }
                      }, this)
                  })
              }, this),
              error: _.bind(function() {
                  this.disableButtons(false);
                  console.info('disableButtons');
              }, this)
          });
      },

      // Before render
      _render: function() {
          this._super('_render');
            // console.log('_render');


      },

      // Before dispose
      _dispose: function() {
          this._super('_dispose');
      },

      /**Overriding the base cancelButton method*/
      cancelButton: function() {
          this._super('cancelButton');
          app.$contentEl.removeAttr('aria-hidden');
          this._disposeView();
      },

      /**Overriding the base saveComplete method*/
      saveComplete: function() {
        if (this.timerId) {
          // console.log('self timer save complete' + this.licznik );                    
            clearInterval(this.timerId);
            // console.log('self timer save complete' + this.licznik );         
        }

        this.$('.modal').modal('hide').find('form').get(0).reset();
        this.disableButtons(false);
        // this._super('saveComplete');
          app.$contentEl.removeAttr('aria-hidden');
          this._disposeView();
      },

      sleep: function(milliseconds) {
        var start = new Date().getTime();

        for (var i = 0; i < 1e7; i++) {
          var time = new Date().getTime() - start;
      
          if (time > milliseconds) {
            break;
          }
        } 
      },

      /**
      * Custom method to dispose the view
      * It is doing after initialize and before render
      */
      _disposeView: function() {
        if (this.timerId) {
            clearInterval(this.timerId);
            // console.log('self timer   _disposeView' );            
        }

        // zakomentowane odswieżanie notyfikacji po kliknięciu na pytanie mateusz ruszkowski 09.05
        var index2 = _.indexOf(this.layout._components, _.findWhere(this.layout._components, { name: 'notifications' }));
        // console.log('odswiezamy' + index2);
        // console.log(this.layout._components[index2]);
        if(index2 == -1){
          console.log('-----------------------------------------------');
          console.log('Ostrzeżenie, kod: 0cma1101111');
          console.log('-----------------------------------------------');
        }else{
          this.layout._components[index2].startPulling();
        }
        
        // this.layout._components[index2].render();
        // this.layout._components[index2].dispose();
        //var options2 = this.layout._components[index2].options;
        //this.layout._components[index2].initialize(options2); /// odświeżamy notyfikacje
        // console.log('jak nie ma to nie bedzie, koniec dispoze');
        // console.log('disposeView');

          /** Odkrycie menu */
          $('#content').css('top','47px');
          $('.navbar-fixed-top').show(0);
          
          /**Find the index of the view in the components list of the layout*/
          var index = _.indexOf(this.layout._components, _.findWhere(this.layout._components, { name: 'quick-create' }));
          if (index > -1) {
              // console.log('mamy disposeView');
              /** dispose the view so that the evnets, context elements etc created by it will be released*/
              this.layout._components[index].dispose();
              /**remove the view from the components list**/
              this.layout._components.splice(index, 1);
          }
          // app.events.on('app:sync:complete', this._bootstrap, this);
          // app.events.on('app:logout', this.stopPulling, this);
          //  this.layout.trigger("app:sync:complete");


      },

      // Start render the view
      render: function() {
          // console.log('dane:' + this.question);
          this._super('render');
      },
  })

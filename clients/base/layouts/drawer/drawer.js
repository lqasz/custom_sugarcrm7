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
    backdropHtml: '<div class="drawer-backdrop"></div>',
    plugins: ['Tooltip'],
    onCloseCallback: null,
    scrollTopPositions: [],
    pixelsFromFooter: 80,

    initialize: function(options) {
        var self = this;
        if (!this.$el.is('#drawers')) {
            app.logger.error('Drawer layout can only be included as an Additional Component.');
            return;
        }
        app.drawer = this;
        this.onCloseCallback = [];
        this.name = 'drawer';
        app.routing.before("route", this.reset, this, true);
        app.view.Layout.prototype.initialize.call(this, options);
        $(window).on('scroll.prevent', function() {
            self._preventScroll($(this));
        });
        // console.debug('drawer layout options ');
        // console.debug(options);
        app.$contentEl.on('scroll.prevent', function() {
            self._preventScroll($(this));
        });
    },
    open: function(layoutDef, onClose) {
        var layout;
        app.shortcuts.saveSession();
        if (!app.triggerBefore('app:view:change')) {
            return;
        }
        console.log('open drawer function ');
        console.log(layoutDef);

        if (_.isUndefined(onClose)) {
            this.onCloseCallback.push(function() {});
        } else {
            this.onCloseCallback.push(onClose);
        }
        this._initializeComponentsFromDefinition(layoutDef);
        layout = _.last(this._components);
        this._scrollToTop();
        this._animateOpenDrawer(function() {
            if (layout.context) {
                app.trigger("app:view:change", layout.options.name, layout.context.attributes);
            }
        });
        layout.loadData();
        layout.render();
    },
    close: function() {
        var self = this,
            args = Array.prototype.slice.call(arguments, 0);
        if (!Modernizr.csstransitions) {
            this.closeImmediately.apply(this, args);
            return;
        }
        if (this._components.length > 0) {
            if (!app.triggerBefore('app:view:change')) {
                return;
            }
            this._animateCloseDrawer(function() {
                self._afterCloseActions(args);
            });
        }
    },
    closeImmediately: function() {
        if (this._components.length > 0) {
            var args = Array.prototype.slice.call(arguments, 0),
                drawers = this._getDrawers(false),
                drawerHeight = this._determineDrawerHeight();
            if (!app.triggerBefore('app:view:change')) {
                return;
            }
            drawers.$bottom.css('top', '');
            if (drawers.$next) {
                drawers.$next.css('top', this._isMainAppContent(drawers.$next) ? drawerHeight : drawers.$next.offset().top - drawerHeight);
            }
            this._removeTabAndBackdrop(drawers.$bottom);
            this._cleanUpAfterClose(drawers);
            this._afterCloseActions(args);
        }
    },
    load: function(layoutDef) {
        var layout = this._components.pop(),
            top = layout.$el.css('top'),
            height = layout.$el.css('height'),
            drawers;
        layout.dispose();
        if (!app.triggerBefore('app:view:change')) {
            return;
        }

        console.log('load function drawer ');
        console.log(layoutDef);

        this._initializeComponentsFromDefinition(layoutDef);
        drawers = this._getDrawers(true);
        drawers.$next.addClass('drawer active').css({
            top: top,
            height: height
        });
        this._removeTabAndBackdrop(drawers.$top);
        this._createTabAndBackdrop(drawers.$next, drawers.$top);
        layout = _.last(this._components);
        layout.loadData();
        layout.render();
    },
    count: function() {
        return this._components.length;
    },
    isActive: function(el) {
        return ((this.count() === 0) || ($(el).parents('.drawer.active').length > 0));
    },
    getActiveDrawerLayout: function() {
        if (this.count() === 0) {
            return app.controller.layout;
        } else {
            return _.last(this._components);
        }
    },
    reset: function(triggerBefore) {
        triggerBefore = triggerBefore === false ? false : true;
        if (triggerBefore && !this.triggerBefore("reset", {
                drawer: this
            })) {
            return false;
        }
        var $main = app.$contentEl.children().first();
        _.each(this._components, function(component) {
            component.dispose();
        }, this);
        this._components = [];
        this.onCloseCallback = [];
        if ($main.hasClass('drawer')) {
            $main.removeClass('drawer inactive').removeAttr('aria-hidden').css('top', '');
            this._removeTabAndBackdrop($main);
        }
        $('body').removeClass('noscroll');
        app.$contentEl.removeClass('noscroll');
    },
    _initializeComponentsFromDefinition: function(layoutDef) {
        var parentContext;
        if (_.isUndefined(layoutDef.context)) {
            layoutDef.context = {};
        }
        if (_.isUndefined(layoutDef.context.forceNew)) {
            layoutDef.context.forceNew = true;
        }
        if (!(layoutDef.context instanceof app.Context) && layoutDef.context.parent instanceof app.Context) {
            parentContext = layoutDef.context.parent;
            delete layoutDef.context.parent;
        }
        this._addComponentsFromDef([layoutDef], parentContext);
    },
    _animateOpenDrawer: function(callback) {
        if (this._components.length === 0) {
            return;
        }
        var drawers = this._getDrawers(true),
            drawerHeight = this._determineDrawerHeight(),
            topDrawerCurrentTopPos = drawers.$top.offset().top,
            aboveWindowTopPos = topDrawerCurrentTopPos - drawerHeight,
            bottomDrawerTopPos = this._isMainAppContent(drawers.$top) ? drawerHeight : topDrawerCurrentTopPos + drawerHeight,
            belowWindowTopPos;
        if (drawers.$bottom) {
            belowWindowTopPos = drawers.$bottom.offset().top + drawerHeight
        }
        if (this._isMainAppContent(drawers.$top)) {
            drawers.$top.addClass('drawer');
            $('body').addClass('noscroll');
            app.$contentEl.addClass('noscroll');
        }
        this._createTabAndBackdrop(drawers.$next, drawers.$top);
        drawers.$next.addClass('drawer active');
        drawers.$next.css('height', drawerHeight);
        drawers.$next.css('top', aboveWindowTopPos);
        drawers.$top.addClass('inactive').removeClass('active').attr('aria-hidden', true);
        drawers.$top.on('scroll.prevent', _.bind(function() {
            this._preventScroll(drawers.$top);
        }, this));
        _.defer(_.bind(function() {
            this._setTransition(drawers);
            this._onTransitionEnd(drawers.$next, function() {
                this._removeTransition(drawers);
                if (_.isFunction(callback)) {
                    callback();
                }
                this.trigger('drawer:resize', drawerHeight);
            });
            drawers.$next.css('top', '');
            drawers.$top.css('top', bottomDrawerTopPos);
            if (drawers.$bottom) {
                drawers.$bottom.css('top', belowWindowTopPos);
            }
            if (this._components.length === 1) {
                $(window).on('resize.drawer', _.bind(this._resizeDrawer, this));
            }
        }, this));
    },
    _animateCloseDrawer: function(callback) {
        if (this._components.length === 0) {
            return;
        }
        var drawers = this._getDrawers(false),
            drawerHeight = this._determineDrawerHeight(),
            aboveWindowTopPos = drawers.$top.offset().top - drawerHeight,
            bottomDrawerTopPos;
        if (drawers.$next) {
            bottomDrawerTopPos = this._isMainAppContent(drawers.$next) ? drawerHeight : drawers.$next.offset().top - drawerHeight;
        }
        this._setTransition(drawers);
        this._onTransitionEnd(drawers.$bottom, function() {
            this._removeTransition(drawers);
            this._cleanUpAfterClose(drawers);
            if (_.isFunction(callback)) {
                callback();
            }
        });
        drawers.$top.css('top', aboveWindowTopPos);
        drawers.$bottom.css('top', '');
        if (drawers.$next) {
            drawers.$next.css('top', bottomDrawerTopPos);
        }
        this._removeTabAndBackdrop(drawers.$bottom);
    },
    _getDrawers: function(open) {
        var $main = app.$contentEl.children().first(),
            $nextDrawer, $topDrawer, $bottomDrawer, open = _.isUndefined(open) ? true : open,
            drawerCount = this._components.length;
        switch (drawerCount) {
            case 0:
                break;
            case 1:
                $nextDrawer = open ? this._components[drawerCount - 1].$el : undefined;
                $topDrawer = open ? $main : this._components[drawerCount - 1].$el;
                $bottomDrawer = open ? undefined : $main;
                break;
            case 2:
                $nextDrawer = open ? this._components[drawerCount - 1].$el : $main;
                $topDrawer = open ? this._components[drawerCount - 2].$el : this._components[drawerCount - 1].$el;
                $bottomDrawer = open ? $main : this._components[drawerCount - 2].$el;
                break;
            default:
                $nextDrawer = open ? this._components[drawerCount - 1].$el : this._components[drawerCount - 3].$el;
                $topDrawer = open ? this._components[drawerCount - 2].$el : this._components[drawerCount - 1].$el;
                $bottomDrawer = open ? this._components[drawerCount - 3].$el : this._components[drawerCount - 2].$el;
        }
        return {
            $next: $nextDrawer,
            $top: $topDrawer,
            $bottom: $bottomDrawer
        };
    },
    _isMainAppContent: function($layout) {
        return !$layout.parent().is(this.$el);
    },
    _determineDrawerHeight: function() {
        var windowHeight = $(window).height(),
            headerHeight = $('#header .navbar').outerHeight(),
            footerHeight = $('footer').outerHeight();
        return (windowHeight/1.2) - headerHeight - footerHeight - this.pixelsFromFooter;
    },
    _determineCollapsedHeight: function() {
        return $(window).height() / 2;
    },
    _createTabAndBackdrop: function($top, $bottom) {
        var $drawerTab;
        var $backdrop;

        this.expandTpl = app.template.getLayout(this.name + '.expand');
        this.expandTabHtml = this.expandTpl();
        $bottom.append(this.expandTabHtml).append(this.backdropHtml);
        $drawerTab = $bottom.find('.drawer-tab');
        this.addPluginTooltips($drawerTab);

        $backdrop = $bottom.find('.drawer-backdrop');


        $drawerTab.on('click', _.bind(function(event) {
            if ($('i', event.currentTarget).hasClass('fa-chevron-up')) {
                this._collapseDrawer($top, $bottom);
            } else {
                console.log('expoand drawe powyzej');
                this._expandDrawer($top, $bottom);
            }
            return false;
        }, this));

        var self = this;
        $backdrop.on('click', _.bind(function(event) {
            // mateusz ruszkowski trzeba dodac info ze jezeli nie jest to pytanie ani faktura
            if($('#drawers a[name="cancel_button"]').length == 0) {
                self.close();
            } else {
                $('#drawers a[name="cancel_button"]').click();
            }

            return false;
        }, this));

        app.accessibility.run($drawerTab, 'click');
    },
    _removeTabAndBackdrop: function($drawer) {
        var $drawerTab = $drawer.find('.drawer-tab').off('click').remove();
        this.removePluginTooltips($drawerTab);
        $drawer.find('.drawer-backdrop').remove();
    },
    _cleanUpAfterClose: function(drawers) {
        drawers.$top.removeClass('active');
        drawers.$bottom.removeClass('inactive').addClass('active').removeAttr('aria-hidden').off('scroll.prevent');
        if (this._isMainAppContent(drawers.$bottom)) {
            drawers.$bottom.removeClass('drawer active');
            $('body').removeClass('noscroll');
            app.$contentEl.removeClass('noscroll');
        } else {
            this._expandDrawer(drawers.$bottom, drawers.$next);
        }
        this._scrollBackToOriginal(drawers.$bottom);
        if (this._components.length === 1) {
            $(window).off('resize.drawer');
        }
    },
    _afterCloseActions: function(callbackArgs) {
        var layout;
        this._components.pop().dispose();
        layout = _.last(this._components);
        if (layout) {
            app.trigger("app:view:change", layout.options.name, layout.context.attributes);
        } else {
            app.trigger("app:view:change", app.controller.context.get("layout"), app.controller.context.attributes);
        }
        app.shortcuts.restoreSession();
        (this.onCloseCallback.pop()).apply(window, callbackArgs);
    },
    _expandDrawer: function($top, $bottom) {
        var expandHeight = this._determineDrawerHeight();
        $top.css('height', expandHeight);
        if (this._isMainAppContent($bottom)) {
            $bottom.css('top', expandHeight);
        } else {
            $bottom.css('top', expandHeight + $top.offset().top);
        }
        $bottom.find('.drawer-tab i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        this.trigger('drawer:resize', expandHeight);
    },
    _collapseDrawer: function($top, $bottom) {
        var collapseHeight = this._determineCollapsedHeight();
        $top.css('height', collapseHeight);
        if (this._isMainAppContent($bottom)) {
            $bottom.css('top', collapseHeight);
        } else {
            $bottom.css('top', collapseHeight + $top.offset().top);
        }
        $bottom.find('.drawer-tab i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        this.trigger('drawer:resize', collapseHeight);
    },
    _setTransition: function(drawers) {
        drawers.$top.addClass('transition');
        if (drawers.$next) {
            drawers.$next.addClass('transition');
        }
        if (drawers.$bottom) {
            drawers.$bottom.addClass('transition');
        }
    },
    _removeTransition: function(drawers) {
        drawers.$top.removeClass('transition');
        if (drawers.$next) {
            drawers.$next.removeClass('transition');
        }
        if (drawers.$bottom) {
            drawers.$bottom.removeClass('transition');
        }
    },
    _isInTransition: function(drawer) {
        return drawer.hasClass('transition');
    },
    _onTransitionEnd: function($drawer, callback) {
        var self = this,
            transitionEndEvents = 'webkitTransitionEnd oTransitionEnd otransitionend transitionend msTransitionEnd';
        $drawer.one(transitionEndEvents, function() {
            $drawer.off(transitionEndEvents);
            callback.call(self);
        });
        _.delay(function() {
            $drawer.trigger('transitionend');
        }, 400);
    },
    _scrollToTop: function() {
        var drawers = this._getDrawers(true),
            $mainpane = drawers.$top.find('.main-pane'),
            $sidepane = drawers.$top.find('.sidebar-content'),
            $content = app.$contentEl;
        this.scrollTopPositions.push({
            main: $mainpane.scrollTop(),
            side: $sidepane.scrollTop(),
            drawer: drawers.$top.scrollTop(),
            content: $content.scrollTop()
        });
        drawers.$top.scrollTop(0);
        $mainpane.scrollTop(0);
        $sidepane.scrollTop(0);
        $content.scrollTop(0);
    },
    _scrollBackToOriginal: function($drawer) {
        var scrollPositions = this.scrollTopPositions.pop();
        if ($drawer) {
            $drawer.scrollTop(scrollPositions.drawer);
        } else {
            $drawer = app.$contentEl;
        }
        $drawer.find('.main-pane').scrollTop(scrollPositions.main);
        $drawer.find('.sidebar-content').scrollTop(scrollPositions.side);
        app.$contentEl.scrollTop(scrollPositions.content);
    },
    getHeight: function() {
        if (_.isEmpty(this._components)) {
            return 0;
        }
        var $top = this._getDrawers(false).$top;
        return $top.height();
    },
    _preventScroll: function($scrollable) {
        if (!Modernizr.touch && (app.drawer.count() > 0)) {
            $scrollable.scrollTop(0);
        }
    },
    _dispose: function() {
        app.routing.offBefore("route", this.reset, this);
        this.reset();
        $(window).off('resize.drawer');
        $(window).off('scroll.prevent');
        app.$contentEl.on('scroll.prevent');
        this._super('_dispose');
    },
    _resizeDrawer: _.throttle(function() {
        var drawers = this._getDrawers(false);
        if (drawers.$top && !this._isInTransition(drawers.$top)) {
            this._expandDrawer(drawers.$top, drawers.$bottom);
        }
    }, 300)
})
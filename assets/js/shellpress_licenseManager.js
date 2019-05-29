(function( $ ){

    /**
     * The plugin namespace, ie for $('.selector').myPluginName(options)
     *
     * Also the id for storing the object state via $('.selector').data()
     */
    var PLUGIN_NS = 'shellc_licenseManager';

    /*###################################################################################
     * PLUGIN BRAINS
     *
     * INSTRUCTIONS:
     *
     * To init, call...
     * $('selector').myPluginName(options)
     *
     * Some time later...
     * $('selector').myPluginName('myActionMethod')
     *
     * DETAILS:
     * Once inited with $('...').myPluginName(options), you can call
     * $('...').myPluginName('myAction') where myAction is a method in this
     * class.
     *
     * The scope, ie "this", **is the object itself**.  The jQuery match is stored
     * in the property this.$T.  In general this value should be returned to allow
     * for jQuery chaining by the user.
     *
     * Methods which begin with underscore are private and not
     * publically accessible.
     *
     * CHECK IT OUT...
     * var mySelecta = 'DIV';
     * jQuery(mySelecta).myPluginName();
     * jQuery(mySelecta).myPluginName('publicMethod');
     * jQuery(mySelecta).myPluginName('_privateMethod');
     *
     *###################################################################################*/

    /**
     * @param target
     * @param options
     * @return {Plugin}
     * @constructor
     */
    var Plugin = function( target, options ) {

        //  ----------------------------------------
        //  Core elements
        //  ----------------------------------------

        this.$element   = $( target );

        //  ----------------------------------------
        //  Private properties
        //  ----------------------------------------

        //  ----------------------------------------
        //  Default options
        //  ----------------------------------------

        this.options = $.extend(
            true,               // deep extend
            {},
            options
        );

        //  ----------------------------------------
        //  Initialize plugin
        //  ----------------------------------------

        this._init( this );

        return this;
    };

    /**
     * Main Plugin initializer.
     *
     * @name Plugin#_init
     * @param {Plugin} plugin
     * @private
     *
     * @retun void
     */
    Plugin.prototype._init = function( plugin ) {

    };

    //  ================================================================================
    //  jQuery HOOK
    //  ================================================================================

    /**
     * Generic jQuery plugin instantiation method call logic
     *
     * Method options are stored via jQuery's data() method in the relevant element(s)
     * Notice, myActionMethod mustn't start with an underscore (_) as this is used to
     * indicate private methods on the PLUGIN class.
     */
    $.fn[ PLUGIN_NS ] = function( methodOrOptions ) {
        if (!$(this).length) {
            return $(this);
        }

        var instance = $(this).data(PLUGIN_NS);

        // CASE: action method (public method on PLUGIN class)
        if ( instance
            && methodOrOptions.length
            && methodOrOptions.indexOf('_') !== 0
            && instance[ methodOrOptions ]
            && typeof( instance[ methodOrOptions ] ) == 'function' ) {

            return instance[ methodOrOptions ]( Array.prototype.slice.call( arguments, 1 ) );

            // CASE: argument is options object or empty = initialise
        } else if ( typeof methodOrOptions === 'object' || ! methodOrOptions ) {

            instance = new Plugin( $(this), methodOrOptions );    // ok to overwrite if this is a re-init
            $(this).data( PLUGIN_NS, instance );
            return $(this);

            // CASE: method called before init
        } else if ( !instance ) {
            $.error( 'Plugin must be initialised before using method: ' + methodOrOptions );

            // CASE: private method
        } else if ( methodOrOptions.indexOf('_') === 0 ) {
            $.error( 'Method ' +  methodOrOptions + ' is private!' );

            // CASE: method does not exist
        } else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };

})( jQuery );
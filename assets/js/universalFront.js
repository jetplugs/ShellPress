(function( $ ){

    /**
     * The plugin namespace, ie for $('.selector').myPluginName(options)
     *
     * Also the id for storing the object state via $('.selector').data()
     */
    var PLUGIN_NS = 'spUniversalFront';

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

        this.$element       = $( target );
        this.$form          = $( '#' + this.$element.data( 'form-id' ) );
        this.$fieldset      = this.$element.find( '.sp-universalfront-fieldset' );
        this.$dynamicArea   = this.$element.find( '.sp-universalfront-dynamic-area' );

        //  ----------------------------------------
        //  Private properties
        //  ----------------------------------------

        this._isLocked          = true;
        this._$fakeSubmitButton = null;

        //  ----------------------------------------
        //  Default options
        //  ----------------------------------------

        this.options = $.extend(
            true,   // deep extend
            {
                'refreshOnActions':     [],     //  This component will be refreshed on these actions.
                'submitOnActions':      [],     //  This component will be refreshed on these actions.
                'requestDelay':         500     //  How long it takes to make REST request.
            },
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

        //  Unlock form.

        plugin.isLocked( false );

        //  Refresh me on actions.

        $.each( plugin.options.refreshOnActions, function( key, value ){

            $( document ).on( value, function( event ){
                plugin.triggerSubmit( 'refresh' );
            } );

        } );

        //  Submit me on actions.

        $.each( plugin.options.submitOnActions, function( key, value ){

            $( document ).on( value, function( event ){
                plugin.triggerSubmit( 'submit' );
            } );

        } );

        //  ----------------------------------------
        //  Passing fake submit button
        //  ----------------------------------------

        /**
         * Serializing form with javascript, doesn't pass
         * pressed buttons. We need to fake this behaviour, by
         * creating fake hidden input with name and value of
         * pressed button. We will remove this fake input
         * after REST request ends up.
         */
        plugin.$fieldset.on( 'click', '[type="submit"]', function(){

            var $button = $( this );

            $button.after( plugin._appendFakeSubmitInputToButton( $button ) );

        } );

        //  ----------------------------------------
        //  Submitter
        //  ----------------------------------------

        plugin.$form.on( 'submit', function( event, action = 'submit' ){

            event.preventDefault();

            //  Bail early or lock.
            if( plugin.isLocked() ){
                return;
            }

            //  ----------------------------------------
            //  Change action type input value
            //  ----------------------------------------

            plugin.$fieldset.find( '[name="sp-universalfront[action]"]' ).val( action );

            //  ----------------------------------------
            //  Prepare submitted form data
            //  ----------------------------------------

            var submittedFormData = plugin.$form.serializeArray();

            if( action === 'refresh' ){

                /**
                 * When refreshing, we only want to simulate "fresh request".
                 * Therefore we do not accept any values from inputs,
                 * which are not an shortcode array.
                 */
                submittedFormData = $.grep( submittedFormData, function( input ){

                    return input.name.indexOf( 'sp-universalfront[' ) === 0;

                } );

            }

            //  ----------------------------------------
            //  Lock fieldset
            //  ----------------------------------------

            plugin.isLocked( true );

            //  ----------------------------------------
            //  Make ajax request
            //  ----------------------------------------

            setTimeout( function(){

                $.post( plugin.$form.attr( 'action' ), submittedFormData )
                    .done( function( response ){

                        //  Replace HTML.
                        if( response.hasOwnProperty( 'replacementHtml' ) && response.replacementHtml.length > 0 ){

                            plugin.$dynamicArea.html( response.replacementHtml );

                        }

                        //  Trigger jQuery event.
                        if( response.hasOwnProperty( 'triggerFrontActions' )
                            && response.triggerFrontActions.length > 0
                            && Array.isArray( response.triggerFrontActions )
                        ){

                            $.each( response.triggerFrontActions, function( key, value ) {

                                $( document ).trigger( value );

                            } );

                        }

                        //  Redirect.
                        if( response.hasOwnProperty( 'redirectUrl' ) && response.redirectUrl.length > 0 ){

                            window.location = response.redirectUrl;

                        }

                        //  I am ready!
                        plugin.isLocked( false );

                    } )
                    .fail( function( response ){


                    } )
                    .always( function( response ){

                        //  We always want to remove fake submit input.
                        plugin._maybeRemoveFakeSubmitInput();

                        //  Re-bind all fields to form.
                        plugin.bindAllFieldsToForm();

                    } );

            }, plugin.options.requestDelay );

        } );

        //  ----------------------------------------
        //  Bind all fields, just to be sure.
        //  ----------------------------------------

        plugin.bindAllFieldsToForm();

        //  ----------------------------------------
        //  Mark form as initialized
        //  ----------------------------------------

        plugin.$fieldset.css( { "visibility" : "" } );
        plugin.$element.removeClass( 'is-not-initialized' );

    };

    /**
     * Multi-purpose method for setting and checking lock value.
     *
     * @name Plugin#isLocked
     * @param {boolean|null} value
     *
     * @return boolean|Plugin
     */
    Plugin.prototype.isLocked = function( value = null ){

        if( typeof value === "boolean" ){

            if( value ){

                this._isLocked = true;
                this.$element.addClass( 'is-locked' );
                this.$fieldset.prop( 'disabled', true );

            } else {

                this._isLocked = false;
                this.$element.removeClass( 'is-locked' );
                this.$fieldset.prop( 'disabled', false );

            }

            //  Return self for chaining.
            return this;

        } else {

            //  Return value;
            return this._isLocked;

        }

    };


    /**
     * Triggers form element submit action.
     *
     * @name Plugin#triggerSubmit
     * @param {string} action submit,refresh
     *
     * @return {Plugin}
     */
    Plugin.prototype.triggerSubmit = function( action = 'submit' ) {

        this.$form.trigger( 'submit', [ action ] );

        //  Return self for chaining.
        return this;

    };

    /**
     * Creates new jQuery element and adds it after given button instance.
     *
     * @name Plugin#_appendFakeSubmitInputToButton
     * @private
     * @param {$} $button
     * @return {$}
     */
    Plugin.prototype._appendFakeSubmitInputToButton = function( $button ) {

        this._$fakeSubmitButton = $( '<input>' ).attr( {
            'type':     'hidden',
            'name':     $button.attr( 'name' ),
            'value':    $button.val()
        } );

        $button.after( this._$fakeSubmitButton );

        return this._$fakeSubmitButton;

    };

    /**
     * Removes fake submit input.
     *
     * @name Plugin#_maybeRemoveFakeSubmitInput
     * @private
     * @return {$}
     */
    Plugin.prototype._maybeRemoveFakeSubmitInput = function() {

        if( this._$fakeSubmitButton !== null ){

            this._$fakeSubmitButton.remove();
            this._$fakeSubmitButton = null;

        }

    };

    /**
     * Rebinds all fields inside fieldset to form.
     *
     * @name Plugin#bindAllFieldsToForm
     *
     * @return {$}
     */
    Plugin.prototype.bindAllFieldsToForm = function() {

        this.$fieldset.find( 'input, textarea, button, select' ).attr( 'form', this.$form.attr( 'id' ) );

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

            return instance[ methodOrOptions ].apply( instance, Array.prototype.slice.call( arguments, 1 ) );

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
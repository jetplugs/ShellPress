/**
 * Created by jakubkuranda@gmail.com on 2017-07-25.
 */

jQuery( document ).ready( function( $ ){

    $.fn.ShellPressAjaxListTable = function( args ){

        var ajaxListTable = $( this );

        list = {
            isLocked:   false,
            init:       function(){

                // This will have its utility when dealing with the page number input

                var timer;
                var delay = 500;

                //  ----------------------------------------
                //  CLICK - Pagination links
                //  ----------------------------------------

                ajaxListTable.find( '.tablenav-pages a' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ){

                        list.isLocked = true;   //  Lock callbacks

                        // Simple way: use the URL to extract our needed variables
                        var query = this.search.substring( 1 );

                        //  Writing attributes
                        ajaxListTable.attr( 'data-paged',       list.__query( query, 'paged' ) || '1' );

                        list.update();

                    }

                } );

                //  ----------------------------------------
                //  CLICK - Sortable link
                //  ----------------------------------------

                ajaxListTable.find( '.manage-column.sortable a, .manage-column.sorted a' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;   //  Lock callbacks

                        // Simple way: use the URL to extract our needed variables
                        var query = this.search.substring( 1 );

                        //  Writing attributes
                        ajaxListTable.attr( 'data-order', list.__query( query, 'order' ) || 'asc' );
                        ajaxListTable.attr( 'data-orderby', list.__query( query, 'orderby' ) || 'id' );

                        list.update();

                    }

                } );

                //  ----------------------------------------
                //  KEYUP - Page number input
                //  ----------------------------------------

                ajaxListTable.find( 'input[name=paged]' ).on( 'keyup', function(e) {

                    if( e.keyCode === 13 ){

                        e.preventDefault();

                        if( ! list.isLocked ) {

                            list.isLocked = true;   //  Lock callbacks

                            //  Wait `delay` before sending request.
                            window.clearTimeout(timer);

                            timer = window.setTimeout(function () {

                                //  Writing attributes
                                ajaxListTable.attr('data-paged', parseInt( ajaxListTable.find('input[name="paged"]').val() ) || '1');

                                list.update();

                            }, delay);

                        }

                    }

                } );

                //  ----------------------------------------
                //  KEYUP - Search input
                //  ----------------------------------------

                ajaxListTable.find( 'input[name="search"]' ).on( 'keyup', function(e) {

                    if( e.keyCode === 13 ){

                        e.preventDefault();

                        if( ! list.isLocked ) {

                            list.isLocked = true;   //  Lock callbacks

                            //  Wait `delay` before sending request.
                            window.clearTimeout(timer);

                            timer = window.setTimeout(function () {

                                //  Writing attributes
                                ajaxListTable.attr('data-search', ajaxListTable.find('input[name="search"]').val() || '');
                                ajaxListTable.attr('data-paged', 1 );   //  Reset pagination

                                list.update();

                            }, delay);

                        }

                    }

                } );

                //  ----------------------------------------
                //  CLICK - Search
                //  ----------------------------------------

                ajaxListTable.find( '.search-box input[type="submit"]' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;   //  Lock callbacks

                        ajaxListTable.attr('data-search', ajaxListTable.find('input[name="search"]').val() || '' );
                        ajaxListTable.attr('data-paged', 1 );   //  Reset pagination

                        list.update();
                    }

                } );

                //  ----------------------------------------
                //  Dismissible notices
                //  ----------------------------------------

                ajaxListTable.find( '.notice.is-dismissible' ).each( function() {
                    var $el = $( this ),
                        $button = $( '<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>' ),
                        btnText = commonL10n.dismiss || '';

                    // Ensure plain text
                    $button.find( '.screen-reader-text' ).text( btnText );
                    $button.on( 'click.wp-dismiss-notice', function( event ) {
                        event.preventDefault();
                        $el.fadeTo( 100, 0, function() {
                            $el.slideUp( 100, function() {
                                $el.remove();
                            });
                        });
                    });

                    $el.append( $button );
                });

            },
            update:     function(){

                ajaxListTable.find( '.tablenav .clear' ).before( '<div class="spinner is-active"></div>' );

                $.ajax( {
                    type:   'POST',
                    url:    ajaxurl,
                    data:   {
                        nonce:      ajaxListTable.attr( 'data-nonce' ),
                        action:     ajaxListTable.attr( 'data-ajax-action' ),
                        paged:      ajaxListTable.attr( 'data-paged' ),
                        order:      ajaxListTable.attr( 'data-order' ),
                        orderby:    ajaxListTable.attr( 'data-orderby' ),
                        search:     ajaxListTable.attr( 'data-search' )
                    },
                    success: function( response ) {

                        response = $.parseJSON( response );

                        ajaxListTable.html( response );

                        list.init();

                    },
                    statusCode: {
                        403: function () {

                            ajaxListTable.html( '<div class="spinner is-active" style="float:none"></div>' );
                            console.log( "You need to refresh your session." );

                        }
                    },
                    fail:   function() {

                        console.log( "Got an error while calling ListTable AJAX." );

                    },
                    complete:   function() {

                        list.isLocked = false;  //  Unlock callbacks

                    }
                } );

            },
            __query:    function( query, variable ) {

                var vars = query.split("&");

                for ( var i = 0; i <vars.length; i++ ) {

                    var pair = vars[ i ].split("=");

                    if ( pair[0] === variable ){

                        return pair[1];

                    }

                }

                return false;

            }
        };

        list.update();

    };

} );
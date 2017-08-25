/**
 * Created by jakubkuranda@gmail.com on 2017-07-25.
 */

( function( $ ) {

    $.fn.ShellPressAjaxListTable = function( nonce, ajaxDisplayAction ){

        var ajaxListTable = $( this );

        list = {
            isLocked:           false,
            data:               {
                'nonce':                nonce,
                'ajaxDisplayAction':    ajaxDisplayAction
            },
            temp:               {},
            init:               function(){

                // This will have its utility when dealing with the page number input

                var timer;
                var delay = 500;

                //  ----------------------------------------
                //  Reset dataTemp
                //  ----------------------------------------

                list.dataTemp = [];

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
                        ajaxListTable.attr( 'data-paged',       list._query( query, 'paged' ) || '1' );

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
                        list.data.order     = list._query( query, 'order' ) || 'asc';
                        list.data.orderBy   = list._query( query, 'orderby' ) || 'id';

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
                                list.data.pagesd = parseInt( ajaxListTable.find('input[name="paged"]').val() ) || '1';

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
                                list.data.search    = ajaxListTable.find('input[name="search"]').val() || '';
                                list.data.paged     = 1;   //  Reset pagination

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

                        list.data.search    = ajaxListTable.find('input[name="search"]').val() || '';
                        list.data.paged     =  1;   //  Reset pagination

                        list.update();
                    }

                } );

                //  ----------------------------------------
                //  CLICK - View
                //  ----------------------------------------

                ajaxListTable.find( '.subsubsub a[data-value]' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;   //  Lock callbacks

                        list.data.view      = $( this ).attr( 'data-value' ) || '';
                        list.data.paged     = 1;   //  Reset pagination

                        list.update();
                    }

                } );

                //  ----------------------------------------
                //  CLICK - Apply bulk action
                //  ----------------------------------------

                ajaxListTable.find( '.bulkactions input[type="submit"]' ).on( 'click', function(e) {

                    e.preventDefault();

                    var inputSelect = $( this ).closest( '.bulkactions' ).find( 'select' );

                    if( ! list.isLocked && inputSelect.val() !== '-1' ) {

                        list.isLocked = true;   //  Lock callbacks

                        list.temp.currentBulkAction     = inputSelect.val();
                        list.temp.currentBulkItems      = ajaxListTable.find( '.check-column [name="item-id"]:checked' ).map( function(){ return $( this ).val(); } ).get();

                        list.update();
                    }

                } );

                //  ----------------------------------------
                //  CLICK - Row action
                //  ----------------------------------------

                ajaxListTable.find( '.row-actions [data-row-action]' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;   //  Lock callbacks

                        list.temp.currentRowAction     = $( this ).attr( 'data-row-action' );
                        list.temp.currentRowItem       = $( this ).attr( 'data-row-item' );

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

                //  ----------------------------------------
                //  Toggle row visibility
                //  ----------------------------------------

                ajaxListTable.find( 'button.toggle-row' ).on( 'click', function( e ){

                    $( this ).closest( 'tr' ).toggleClass( 'is-expanded' );

                } );

            },
            update:             function(){

                ajaxListTable.find( '.tablenav .clear' ).before( '<div class="spinner is-active"></div>' );

                $.ajax( {
                    type:   'POST',
                    url:    ajaxurl,
                    data:   {
                        nonce:              list.data.nonce                 || '',
                        action:             list.data.ajaxDisplayAction     || '',
                        paged:              list.data.paged                 || '',
                        order:              list.data.order                 || '',
                        orderBy:            list.data.orderBy               || '',
                        search:             list.data.search                || '',
                        view:               list.data.view                  || '',
                        currentBulkAction:  list.temp.currentBulkAction     || '',
                        currentBulkItems:   list.temp.currentBulkItems      || '',
                        currentRowAction:   list.temp.currentRowAction      || '',
                        currentRowItem:     list.temp.currentRowItem        || ''
                    },
                    success: function( response ) {

                        if( parseInt( response ) !== 0 ){

                            response = $.parseJSON( response );

                            ajaxListTable.html( response );

                            list.init();

                        } else {

                            ajaxListTable.html( '<i class="dashicons dashicons-update"></i><i class="dashicons dashicons-hidden"></i>' );
                            console.log( "General problem with access to ajax action?" );

                        }

                    },
                    statusCode: {
                        403: function () {

                            ajaxListTable.html( '<i class="dashicons dashicons-update"></i><i class="dashicons dashicons-hidden"></i>' );
                            console.log( "You need to refresh your session." );

                        }
                    },
                    fail:   function() {

                        ajaxListTable.html( '<i class="dashicons dashicons-update"></i><i class="dashicons dashicons-hidden"></i>' );
                        console.log( "Got an error while calling ListTable AJAX." );

                    },
                    complete:   function() {

                        list.clearTemp();       //  Clear temporary data

                        list.isLocked = false;  //  Unlock callbacks

                    }
                } );

            },
            _query:         function( query, variable ) {

                var vars = query.split("&");

                for ( var i = 0; i <vars.length; i++ ) {

                    var pair = vars[ i ].split("=");

                    if ( pair[0] === variable ){

                        return pair[1];

                    }

                }

                return false;

            },
            clearTemp:      function() {

                list.temp = {};

            }
        };

        list.update();

    };

}( jQuery ) );
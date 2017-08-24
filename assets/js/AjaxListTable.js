/**
 * Created by jakubkuranda@gmail.com on 2017-07-25.
 */

( function( $ ) {

    $.fn.ShellPressAjaxListTable = function( args ){

        var ajaxListTable = $( this );

        list = {
            isLocked:           false,
            dataTemp:           {
                bulkAction:         null,
                bulkItems:          null,
                rowAction:          null,
                rowItem:            null
            },
            nonce:              args.nonce,
            ajaxDisplayAction:  args.ajaxDisplayAction,
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
                        ajaxListTable.attr( 'data-order', list._query( query, 'order' ) || 'asc' );
                        ajaxListTable.attr( 'data-orderby', list._query( query, 'orderby' ) || 'id' );

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
                //  CLICK - Search
                //  ----------------------------------------

                ajaxListTable.find( '.subsubsub a[data-value]' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;   //  Lock callbacks

                        ajaxListTable.attr( 'data-view', $( this ).attr( 'data-value' ) || '' );
                        ajaxListTable.attr( 'data-paged', 1 );   //  Reset pagination

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

                        list.dataTemp.bulkAction    = inputSelect.val();
                        list.dataTemp.bulkItems     = ajaxListTable.find( '.check-column [name="item-id"]:checked' ).map( function(){ return $( this ).val(); } ).get();

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

                        list.dataTemp.rowAction     = $( this ).attr( 'data-row-action' );
                        list.dataTemp.rowItem       = $( this ).attr( 'data-row-item' );

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
                        nonce:      list.nonce,
                        action:     list.ajaxDisplayAction,
                        paged:      ajaxListTable.attr( 'data-paged' ),
                        order:      ajaxListTable.attr( 'data-order' ),
                        orderby:    ajaxListTable.attr( 'data-orderby' ),
                        search:     ajaxListTable.attr( 'data-search' ),
                        view:       ajaxListTable.attr( 'data-view' ),
                        bulkaction: list.dataTemp.bulkAction || '',
                        bulkitems:  list.dataTemp.bulkItems || '',
                        rowaction:  list.dataTemp.rowAction || '',
                        rowitem:    list.dataTemp.rowItem || ''
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
            _query:         function( query, variable ) {

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

}( jQuery ) );
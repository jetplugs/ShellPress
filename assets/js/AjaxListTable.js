/**
 * Created by jakubkuranda@gmail.com on 2017-07-25.
 */

jQuery( document ).ready( function( $ ){

    $.fn.ShellPressAjaxListTable = function( args ){

        var ajaxListTable = $( this );

        list = {
            init:   function(){

                // This will have its utility when dealing with the page number input

                var timer;
                var delay = 500;

                //  ----------------------------------------
                //  CLICK - Pagination links
                //  ----------------------------------------

                ajaxListTable.find( '.tablenav-pages a' ).on( 'click', function(e) {

                    // We don't want to actually follow these links
                    e.preventDefault();

                    // Simple way: use the URL to extract our needed variables
                    var query = this.search.substring( 1 );

                    console.log( query );

                    //  Writing attributes
                    ajaxListTable.attr( 'data-paged',       list.__query( query, 'paged' ) || '1' );

                    list.update();

                } );

                //  ----------------------------------------
                //  CLICK - Sortable link
                //  ----------------------------------------

                ajaxListTable.find( '.manage-column.sortable a, .manage-column.sorted a' ).on( 'click', function(e) {

                    // We don't want to actually follow these links
                    e.preventDefault();

                    // Simple way: use the URL to extract our needed variables
                    var query = this.search.substring( 1 );

                    console.log( query );

                    //  Writing attributes
                    ajaxListTable.attr( 'data-order',       list.__query( query, 'order' ) || 'asc' );
                    ajaxListTable.attr( 'data-orderby',     list.__query( query, 'orderby' ) || 'id' );

                    list.update();

                } );

                //  ----------------------------------------
                //  KEYUP - Page number and search input
                //  ----------------------------------------

                ajaxListTable.find( 'input[name=paged], input[name="search"]' ).on( 'keyup', function(e) {

                    if( e.keyCode === 13 ){

                        e.preventDefault();

                        //  Wait `delay` before sending request.
                        window.clearTimeout( timer );

                        timer = window.setTimeout( function() {

                            //  Writing attributes
                            ajaxListTable.attr('data-paged', parseInt( ajaxListTable.find('input[name="paged"]').val() ) || '1' );
                            ajaxListTable.attr('data-search', ajaxListTable.find('input[name="search"]').val() || '' );

                            list.update();

                        }, delay );

                    }

                } );

                //  ----------------------------------------
                //  CLICK - Search
                //  ----------------------------------------

                ajaxListTable.find( '.search-box input[type="submit"]' ).on( 'click', function(e) {

                    e.preventDefault();

                    var searchValue = ajaxListTable.find( 'input[name="search"]' ).val() || '';    //  Value of input search

                    ajaxListTable.attr( 'data-search', searchValue );

                    list.update();

                } );

            },
            update: function(){

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
                    fail:   function( ) {

                        console.log( "Got an error while calling ListTable AJAX." );

                    }
                } );

            },
            __query: function( query, variable ) {

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
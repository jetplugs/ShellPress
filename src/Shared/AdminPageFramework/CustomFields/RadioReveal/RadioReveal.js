( function ( $ ) {

    $.fn.ShellPress_RadioReveal = function( reveal ) {

        var fieldObj = $( this );

        $.each( reveal, function( inputValue, args ){

            var inputObj = fieldObj.find( 'input[type="radio"][value="' + inputValue + '"]' );

            //  TODO rest of switching mechanism

        } );

        return this;

    };

}( jQuery ) );
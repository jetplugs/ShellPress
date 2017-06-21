<?php
namespace shellpress\v1_0_0\src\Shared;


use shellpress\v1_0_0\lib\_Includes\WP_List_Table;
use shellpress\v1_0_0\ShellPress;

class WPListTable extends WP_List_Table {

    /**
     * @var ShellPress
     */
    public $app;

    /**
     * WPListTable constructor.
     *
     * @param ShellPress $app
     * @param array $args
     */
    function __construct( $app, $args = array() ) {

        parent::__construct( $args );

        $this->app = $app;

        //  ----------------------------------------
        //  Actions and filters
        //  ----------------------------------------

        add_filter( 'set_url_scheme',   array( $this, '_f_addSearchArgsToUrl') );

    }

    /**
     * This method gets visual output by using output buffering.
     *
     * @return string - HTML
     */
    function getDisplay() {

        ob_start();
        $this->display();
        return ob_get_clean();

    }

    /**
     * This method gets visual output by using output buffering.
     *
     * @param string $text
     * @param string $inputId
     *
     * @return string - HTML
     */
    function getSearchBox( $text, $inputId ) {

        ob_start();
        parent::search_box( $text, $inputId );
        return ob_get_clean();

    }

    //  ================================================================================
    //  FILTERS
    //  ================================================================================

    /**
     * Filters the resulting URL.
     * This filter is used by Wordpress's WP_List_Table
     * while generating pagination buttons.
     *
     * @param string $url - The complete URL including scheme and path.
     *
     * @filter set_url_scheme
     * @see WP_List_Table::pagination()
     */
    public function _f_addSearchArgsToUrl( $url ) {

        if( isset( $_POST['s'] ) ){

            $url = add_query_arg( 's', $_POST['s'], $url );      //  adds search argument
            $url = remove_query_arg( 'paged', $url );       //  removes current paged argument, because we handle NEW search

        }

        return $url;

    }

}
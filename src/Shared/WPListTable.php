<?php
namespace shellpress\v1_0_4\src\Shared;


use shellpress\v1_0_4\lib\_Includes\WP_List_Table;
use shellpress\v1_0_4\ShellPress;

class WPListTable extends WP_List_Table {

    /**
     * WPListTable constructor.
     *
     * @param ShellPress $app
     * @param array $args
     */
    public function __construct( $args = array() ) {

        parent::__construct( $args );

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
     * This method gets visual output of search box by using output buffering.
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

    /**
     * This method gets visual output of search box by using output buffering.
     * Additionally search box is wrapped by its own form.
     *
     * @param string $text
     * @param string $inputId
     * @param string $formActionUrl
     *
     * @return string - HTML
     */
    function getSearchBoxPacked( $text, $inputId, $formActionUrl = null ) {

        if( $formActionUrl === null ){

            //  prevent display errors when those arguments are already set
            $formActionUrl = remove_query_arg( array( 's', 'paged' ) );

        }

        $html = '';

        $html .= sprintf( '<form method="post" action="%1$s">', $formActionUrl );
        $html .= $this->getSearchBox( $text, $inputId );
        $html .= sprintf( '</form>' );

        return $html;

    }

    /**
     * Just returns $_REQUEST['order'] or string 'asc'.
     *
     * @return string
     */
    public function getOrder() {

        if( isset( $_REQUEST['order'] ) && empty( $_REQUEST['order'] ) ){

            return $_REQUEST['order'];

        } else {

            return 'asc';

        }

    }

    /**
     * Just returns $_REQUEST['orderby'] or string 'id'.
     *
     * @return string
     */
    public function getOrderBy() {

        if( isset( $_REQUEST['orderby'] ) && empty( $_REQUEST['orderby'] ) ){

            return $_REQUEST['orderby'];

        } else {

            return 'id';

        }

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

            $url = add_query_arg( 's', $_POST['s'], $url );         //  adds search argument
            $url = remove_query_arg( 'paged', $url );               //  removes current paged argument, because we handle NEW search

        }

        return $url;

    }

}
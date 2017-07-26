<?php
namespace shellpress\v1_0_4\src\Shared;

/**
 * @author DualJack
 * Date: 2017-07-25
 * Time: 19:46
 */

use shellpress\v1_0_4\lib\_Includes\WP_Ajax_List_Table;

/**
 * Class AjaxListTable.
 * You need to extend this class, redefine some methods and use filters to construct table content.
 *
 * @package shellpress\v1_0_4\src\Shared
 */
class AjaxListTable extends WP_Ajax_List_Table {

    /** @var string */
    private $slug;

    /** @var string */
    private $ajaxActionName;

    public $example_data = array(
        array(
            'ID'		=> 1,
            'title'		=> '300',
            'rating'	=> 'R',
            'director'	=> 'Zach Snyder'
        ),
        array(
            'ID'		=> 2,
            'title'		=> 'Eyes Wide Shut',
            'rating'	=> 'R',
            'director'	=> 'Stanley Kubrick'
        ),
        array(
            'ID'		=> 3,
            'title'		=> 'Moulin Rouge!',
            'rating'	=> 'PG-13',
            'director'	=> 'Baz Luhrman'
        ),
        array(
            'ID'		=> 4,
            'title'		=> 'Snow White',
            'rating'	=> 'G',
            'director'	=> 'Walt Disney'
        ),
        array(
            'ID'		=> 5,
            'title'		=> 'Super 8',
            'rating'	=> 'PG-13',
            'director'	=> 'JJ Abrams'
        ),
        array(
            'ID'		=> 6,
            'title'		=> 'The Fountain',
            'rating'	=> 'PG-13',
            'director'	=> 'Darren Aronofsky'
        ),
        array(
            'ID'		=> 7,
            'title'		=> 'Watchmen',
            'rating'	=> 'R',
            'director'	=> 'Zach Snyder'
        ),
        array(
            'ID'		=> 8,
            'title'		=> 'The Descendants',
            'rating'	=> 'R',
            'director'	=> 'Alexander Payne'
        ),
        array(
            'ID'		=> 9,
            'title'		=> 'Moon',
            'rating'	=> 'R',
            'director'	=> 'Duncan Jones'
        ),
        array(
            'ID'		=> 10,
            'title'		=> 'Elysium',
            'rating'	=> 'R',
            'director'	=> 'Neill Blomkamp'
        ),
        array(
            'ID'		=> 11,
            'title'		=> 'Source Code',
            'rating'	=> 'PG-13',
            'director'	=> 'Duncan Jones'
        ),
        array(
            'ID'		=> 12,
            'title'		=> 'Django Unchained',
            'rating'	=> 'R',
            'director'	=> 'Quentin Tarantino'
        )
    );


    /**
     * AjaxListTable constructor.
     *
     * @param string $tableSlug - Unique key
     * @param string $singular - Label for singular item
     * @param string $plural - Label for plural items
     */
    public function __construct( $tableSlug, $singular = 'item', $plural = 'items' ) {

        //Set parent defaults
        parent::__construct(
            array(
                'singular'	=> $singular,
                'plural'	=> $plural,
            )
        );

        //  ----------------------------------------
        //  Properties
        //  ----------------------------------------

        $this->slug             = sanitize_key( $tableSlug );
        $this->ajaxActionName   = 'table_' . $this->getSlug();

        //  ----------------------------------------
        //  Actions
        //  ----------------------------------------

        add_action( 'wp_ajax_' . $this->getAjaxActionName(),        array( $this, '_a_ajaxResponse') );

    }

    //  ================================================================================
    //  LIST TABLE SPECIFIC METHODS
    //  ================================================================================

    /**
     *
     */
    public function prepare_items() {

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 4;

        $columns    = $this->get_columns();
        $hidden     = $this->get_hidden_columns();
        $sortable   = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $data = $this->example_data;


        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder( $a, $b ) {
            //If no sort, default to title
            $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'title';
            //If no order, default to asc
            $order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';
            //Determine sort order
            $result = strcmp( $a[ $orderby ], $b[ $orderby ] );
            //Send final sort direction to usort
            return ( 'asc' === $order ) ? $result : -$result;
        }
        usort( $data, 'usort_reorder' );


        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);



        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(
            array(
                'total_items'	    => $total_items,
                'per_page'	        => $per_page,
                'total_pages'	    => ceil( $total_items / $per_page ),
                'orderby'	        => ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'title',
                'order'		        => ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'asc'
            )
        );

    }

    public function get_bulk_actions() {

        return $actions = array(
            'delete'	=>  'Delete'
        );

    }

    public function get_columns() {

        return $columns = array(
            'cb'		=> '<input type="checkbox" />',
            'title'		=> 'Title',
            'rating'	=> 'Rating',
            'director'	=> 'Director'
        );

    }

    public function get_sortable_columns() {

        return $sortable_columns = array(
            'title'	 	=> array( 'title', false ),	//true means it's already sorted
            'rating'	=> array( 'rating', false ),
            'director'	=> array( 'director', false )
        );

    }

    public function get_hidden_columns() {

        return array();

    }

    public function column_default( $item, $column_name ) {

        return __( 'No cell declaration', 'mailboo' );

    }

    public function column_cb( $item ){

        return sprintf( '<input type="checkbox">' );

    }

    //  ================================================================================
    //  ADVANCED GETTERS
    //  ================================================================================

    /**
     * Call this method to get main table wrapper.
     */
    public function getDisplayRoot() {

        $attributes = array(
            sprintf( 'data-nonce="%1$s"',           wp_create_nonce( $this->getAjaxActionName() ) ),
            sprintf( 'data-ajax-action="%1$s"',     $this->getAjaxActionName() ),
            sprintf( 'data-paged="%1$s"',           1 ),
            sprintf( 'data-order="%1$s"',           'asc' ),
            sprintf( 'data-orderby="%1$s"',         'id' )
        );

        $html = sprintf(
            '<div id="%1$s" class="%2$s" %3$s>',
            /** %1$s */ $this->getSlug(),
            /** %2$s */ '',
            /** %3$s */ implode( ' ', $attributes )
        );
        $html .= sprintf( '<div class="spinner is-active" style="float:none"></div>' );
        $html .= sprintf( '</div>' );

        $html .= PHP_EOL;

        $html .= $this->_getInitScript();

        return $html;

    }

    /**
     * Returns full string of script embed.
     * Script applies jQuery plugin on table wrapper.
     *
     * @return string
     */
    protected function _getInitScript() {

        ob_start();
        ?>

        <script type='text/javascript'>

            jQuery( document ).ready( function( $ ){

                <?php printf( '$( "#%1$s" ).ShellPressAjaxListTable();', $this->getSlug() );?>

            } );

        </script>

        <?php
        $script = ob_get_clean();
        $script .= PHP_EOL;

        return $script;

    }

    //  ================================================================================
    //  SIMPLE GETTERS
    //  ================================================================================

    /**
     * @return string
     */
    public function getSlug() {

        return $this->slug;

    }

    /**
     * @return string
     */
    public function getAjaxActionName() {

        return $this->ajaxActionName;

    }

    //  ================================================================================
    //  ACTIONS
    //  ================================================================================

    /**
     * Builds whole ajax response ( including HTML )
     *
     * @action `wp_ajax_`
     */
    public function _a_ajaxResponse() {

        check_ajax_referer( $this->getAjaxActionName(), 'nonce' );

        $this->prepare_items();

        ob_start();

        $this->display();

        $response = ob_get_clean();


        die( wp_json_encode( $response ) );

    }

}
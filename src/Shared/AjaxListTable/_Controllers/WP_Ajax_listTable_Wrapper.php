<?php
namespace shellpress\v1_0_4\src\Shared\AjaxListTable\_Controllers;

/**
 * @author DualJack
 * Date: 2017-07-25
 * Time: 19:46
 */

/**
 * Class AjaxListTable.
 * This class just extends the modified version of WP_List_Table.
 * It sets up some basic hooks for you.
 *
 * @package shellpress\v1_0_4\src\Shared
 */
class WP_Ajax_listTable_Wrapper extends WP_Ajax_List_Table {

    /** @var string */
    public $slug;

    /** @var string */
    public $ajaxActionName;

    /** @var int */
    public $totalItems = 0;

    /** @var int */
    public $itemsPerPage = 20;

    /** @var string */
    public $order = 'asc';

    /** @var string */
    public $orderBy = 'id';

    /** @var int */
    public $paged = 1;

    /** @var string */
    public $search = '';


    /**
     * AjaxListTable constructor.
     *
     * @param string $tableSlug - Unique key
     * @param string $singular - Label for singular item
     * @param string $plural - Label for plural items
     */
    public function __construct( $tableSlug, $singular, $plural ) {

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
        $this->ajaxActionName   = 'display_' . $this->slug;

    }

    //  ================================================================================
    //  LIST TABLE SPECIFIC METHODS
    //  ================================================================================

    public function prepare_items() {

        //  ----------------------------------------
        //  Column headers
        //  ----------------------------------------

        $columns    = $this->get_columns();
        $hidden     = $this->get_hidden_columns();
        $sortable   = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );

        //  ----------------------------------------
        //  Items
        //  ----------------------------------------

        $this->items = apply_filters( 'items_' . $this->slug, array() );

        //  ----------------------------------------
        //  Pagination arguments
        //  ----------------------------------------

        $this->set_pagination_args(
            array(
                'total_items'	    =>  $this->getTotalItems(),
                'per_page'	        =>  $this->getItemsPerPage(),
                'total_pages'	    =>  ceil( $this->getTotalItems() / $this->getItemsPerPage() ),
                'orderby'	        =>  $this->getOrderBy(),
                'order'		        =>  $this->getOrder()
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
            'lololol'	=> 'Test1',
            'director'	=> 'Test2'
        );

    }

    public function get_sortable_columns() {

        return $sortable_columns = array(
            'title'	 	=> array( 'title', false ),	//true means it's already sorted
            'lololol'	=> array( 'lololol', false ),
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
    //  SIMPLE GETTERS
    //  ================================================================================

    /**
     * Just returns $_REQUEST['order'] or default value.
     *
     * @return string
     */
    public function getOrder() {

        if( isset( $_REQUEST['order'] ) && empty( $_REQUEST['order'] ) ){

            return $_REQUEST['order'];

        } else {

            return $this->order;

        }

    }

    /**
     * Just returns $_REQUEST['orderby'] or default value.
     *
     * @return string
     */
    public function getOrderBy() {

        if( isset( $_REQUEST['orderby'] ) && empty( $_REQUEST['orderby'] ) ){

            return $_REQUEST['orderby'];

        } else {

            return $this->orderBy;

        }

    }

    /**
     * Just returns $_REQUEST['paged'] or default value.
     *
     * @return int
     */
    public function getPaged() {

        if( isset( $_REQUEST['paged'] ) && empty( $_REQUEST['paged'] ) ){

            return (int) $_REQUEST['paged'];

        } else {

            return $this->paged;

        }

    }

    /**
     * Just returns $_REQUEST['totalitems'] or default value.
     *
     * @return int
     */
    public function getTotalItems() {

        if( isset( $_REQUEST['totalitems'] ) && empty( $_REQUEST['totalitems'] ) ){

            return (int) $_REQUEST['totalitems'];

        } else {

            return $this->totalItems;

        }

    }

    /**
     * Just returns $_REQUEST['itemsperpage'] or default value.
     *
     * @return int
     */
    public function getItemsPerPage() {

        if( isset( $_REQUEST['itemsperpage'] ) && empty( $_REQUEST['itemsperpage'] ) ){

            return (int) $_REQUEST['itemsperpage'];

        } else {

            return $this->itemsPerPage;

        }

    }

    /**
     * Just returns $_REQUEST['search'] or default value.
     *
     * @return int
     */
    public function getSearch() {

        if( isset( $_REQUEST['search'] ) && empty( $_REQUEST['search'] ) ){

            return (int) $_REQUEST['search'];

        } else {

            return $this->search;

        }

    }

}
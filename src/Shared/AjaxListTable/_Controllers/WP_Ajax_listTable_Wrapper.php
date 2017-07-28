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

    public $noItemsText = "No items found.";

    /**
     * Table columns headers array.
     *
     * Format:
     * array(
     *      '{slug}'    =>  array(
     *          'isHidden'          =>  false,                  (optional)
     *          'isSortable'        =>  true,                   (optional)
     *          'isAlreadySorted'   =>  false,                  (optional)
     *          'realColumnName'    =>  '{sql column name}'     (optional)
     *          'title'             =>  'Your column title'     (optional)
     *      )
     * )
     *
     * @var array
     */
    public $headers = array();


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
        //  Columns headers hook
        //  ----------------------------------------

        $headers = $this->headers;  //  Get default value ( empty array )

        //  Add bulk actions checkbox column

        if( ! empty( $this->get_bulk_actions() ) ){

            $headers['cb'] = array(
                'title'     =>  '<input type="checkbox">'
            );

        }

        //  Add id column

        $headers['id'] = array(
            'title'         =>  'ID'
        );

        /**
         * Apply filter on empty array.
         * Filter tag: `headers_{tableSlug}`
         *
         * @param array $headers
         */
        $headers = apply_filters(
            'headers_' . $this->slug,               //  Filter tag
            $headers                                //  $headers
        );

        //  Apply default properties

        foreach( $headers as $slug => $columnArgs ){

            $defaultColumnArgs = array(
                'isHidden'          =>  false,
                'isSortable'        =>  false,
                'isAlreadySorted'   =>  false,
                'realColumnName'    =>  $slug,
                'title'             =>  $slug
            );

            $headers[ $slug ] = array_merge( $defaultColumnArgs, (array) $columnArgs );

        }

        //  Set headers

        $this->headers = $headers;

        //  ----------------------------------------
        //  Applying columns headers
        //  ----------------------------------------

        $columns    = $this->get_columns();
        $hidden     = $this->get_hidden_columns();
        $sortable   = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );

        //  ----------------------------------------
        //  Items
        //  ----------------------------------------

        /**
         * Apply filter on empty array.
         * Filter tag: `items_{tableSlug}`
         *
         * @param array $items
         * @param int $paged
         * @param string $search
         * @param string $order
         * @param string $orderBy
         */
        $this->items = apply_filters(
            'items_' . $this->slug,     //  Filter tag
            array(),                    //  $items
            $this->getPaged(),          //  $paged
            $this->getSearch(),         //  $search
            $this->getOrder(),          //  $order
            $this->getOrderBy()         //  $orderBy
        );

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

        $columns = array();

        foreach( $this->headers as $slug => $columnArgs ){

            $columns[ $slug ] = $columnArgs['title'];

        }

        return $columns;

    }

    public function get_sortable_columns() {

        $sortableColumns = array();

        foreach( $this->headers as $slug => $columnArgs ){

            if( $columnArgs['isSortable'] === true ){

                $sortableColumns[ $slug ] = array( $columnArgs['realColumnName'], $columnArgs['isAlreadySorted'] );

            }

        }

        return $sortableColumns;

    }

    public function get_hidden_columns() {

        $hiddenColumns = array();

        foreach( $this->headers as $slug => $columnArgs ){

            if( $columnArgs['isHidden'] === true ){

                $hiddenColumns[] = $slug;

            }

        }

        return $hiddenColumns;

    }

    protected function get_views() {

        return array(
            'all'   =>  sprintf( '<a href="#">All( 20 )</a>' )
        );

    }

    public function no_items() {

        echo $this->noItemsText;

    }

    public function column_default( $item, $column_name ) {

        $html = sprintf( '<div style="color:silver"><b>Filter:</b><br/>cell_%1$s_%2$s</div>', $this->slug, $column_name );

        /**
         * Apply filter on string.
         * Filter tag: `cell_{tableSlug}_(columnName)`
         *
         * @param string $html
         * @param mixed $item
         */
        $html = apply_filters( 'cell_' . $this->slug . '_' . $column_name, $html, $item );

        return $html;

    }

    public function column_cb( $item ) {

        $html = '';

        /**
         * Apply filter on string.
         * Filter tag: `cell_{tableSlug}_cb`
         *
         * @param string $html
         * @param mixed $item
         */
        $html = apply_filters( 'cell_' . $this->slug . '_cb', $html, $item );

        return $html;

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

        if( isset( $_REQUEST['order'] ) && ! empty( $_REQUEST['order'] ) ){

            return esc_sql( $_REQUEST['order'] );

        } else {

            return esc_sql( $this->order );

        }

    }

    /**
     * Just returns $_REQUEST['orderby'] or default value.
     *
     * @return string
     */
    public function getOrderBy() {

        if( isset( $_REQUEST['orderby'] ) && ! empty( $_REQUEST['orderby'] ) ){

            return esc_sql( $_REQUEST['orderby'] );

        } else {

            return esc_sql( $this->orderBy );

        }

    }

    /**
     * Just returns $_REQUEST['paged'] or default value.
     *
     * @return int
     */
    public function getPaged() {

        if( isset( $_REQUEST['paged'] ) && ! empty( $_REQUEST['paged'] ) ){

            return (int) $_REQUEST['paged'];

        } else {

            return (int) $this->paged;

        }

    }

    /**
     * Just returns $_REQUEST['totalitems'] or default value.
     *
     * @return int
     */
    public function getTotalItems() {

        if( isset( $_REQUEST['totalitems'] ) && ! empty( $_REQUEST['totalitems'] ) ){

            return (int) $_REQUEST['totalitems'];

        } else {

            return (int) $this->totalItems;

        }

    }

    /**
     * Just returns $_REQUEST['itemsperpage'] or default value.
     *
     * @return int
     */
    public function getItemsPerPage() {

        if( isset( $_REQUEST['itemsperpage'] ) && ! empty( $_REQUEST['itemsperpage'] ) ){

            return (int) $_REQUEST['itemsperpage'];

        } else {

            return (int) $this->itemsPerPage;

        }

    }

    /**
     * Just returns $_REQUEST['search'] or default value.
     *
     * @return int
     */
    public function getSearch() {

        if( isset( $_REQUEST['search'] ) && ! empty( $_REQUEST['search'] ) ){

            return esc_sql( $_REQUEST['search'] );

        } else {

            return esc_sql( $this->search );

        }

    }

}
<?php
namespace shellpress\v1_0_5\src\Shared\AjaxListTable\_Controllers;

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
 * The code style is different in some parts of class, because it extends
 * the core class WP_List_Table - made by WordPress team.
 *
 * @package shellpress\v1_0_5\src\Shared
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

    /** @var string */
    public $view = 'default';

    /** @var string */
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

    /** @var array */
    public $currentBulkItems = array();

    /** @var string|null */
    public $currentBulkAction = null;

    /** @var string|null */
    public $currentRowAction = null;

    /** @var string|null */
    public $currentRowItem = null;


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

    /**
     * **** WP_List_Table specific
     */
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
         * @param int $itemsPerPage
         * @param int $paged
         * @param string $search
         * @param string $order
         * @param string $orderBy
         * @param string $view
         */
        $this->items = apply_filters(
            'items_' . $this->slug,     //  Filter tag
            array(),                    //  $items
            $this->getItemsPerPage(),          //  $itemsPerPage
            $this->getPaged(),          //  $paged
            $this->getSearch(),         //  $search
            $this->getOrder(),          //  $order
            $this->getOrderBy(),        //  $orderBy
            $this->getView()            //  $view
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

    /**
     * Should be called before $this->prepare_items()
     */
    public function process_bulk_action() {

        if( $this->getCurrentBulkAction() ){

            $itemsIds = (array) $this->getCurrentBulkItems();

            /**
             * Do bulk action.
             * Action tag: `bulk_{tableSlug}_(currentBulkActionSlug)`
             *
             * @param array $itemsIds
             */
            do_action( 'bulk_' . $this->slug . '_' . $this->getCurrentBulkAction(), $itemsIds );

        }

    }

    /**
     * **** WP_List_Table specific
     *
     * @return array
     */
    public function get_bulk_actions() {

        $bulkActions = array();

        /**
         * Apply filter on empty array.
         * Filter tag: `bulk_{tableSlug}`
         *
         * @param array $bulkActions
         */
        $bulkActions = apply_filters( 'bulk_' . $this->slug, $bulkActions );

        return $bulkActions;

    }

    /**
     * Should be called before $this->prepare_items()
     */
    public function process_row_action() {

        if( $this->getCurrentRowAction() ){

            $itemId = $this->getCurrentRowItem();

            /**
             * Do row action.
             * Action tag: `action_{tableSlug}_(currentRowActionSlug)`
             *
             * @param string $itemId
             */
            do_action( 'action_' . $this->slug . '_' . $this->getCurrentRowAction(), $this->getCurrentRowItem() );

        }

    }

    /**
     * **** WP_List_Table specific
     *
     * @return array
     */
    public function get_columns() {

        $columns = array();

        foreach( $this->headers as $slug => $columnArgs ){

            $columns[ $slug ] = $columnArgs['title'];

        }

        return $columns;

    }

    /**
     * **** WP_List_Table specific
     *
     * @return array
     */
    public function get_sortable_columns() {

        $sortableColumns = array();

        foreach( $this->headers as $slug => $columnArgs ){

            if( $columnArgs['isSortable'] === true ){

                $sortableColumns[ $slug ] = array( $columnArgs['realColumnName'], $columnArgs['isAlreadySorted'] );

            }

        }

        return $sortableColumns;

    }

    /**
     * @return array
     */
    public function get_hidden_columns() {

        $hiddenColumns = array();

        foreach( $this->headers as $slug => $columnArgs ){

            if( $columnArgs['isHidden'] === true ){

                $hiddenColumns[] = $slug;

            }

        }

        return $hiddenColumns;

    }

    /**
     * **** WP_List_Table specific
     *
     * @return array
     */
    protected function get_views() {

        $views = array(
            'default'   =>  __( "All" )
        );

        /**
         * Apply filter on array.
         * Filter tag: `views_{tableSlug}`
         *
         * @param array $views
         */
        $views = apply_filters( 'views_' . $this->slug, $views );

        return $views;

    }

    /**
     * **** WP_List_Table specific
     */
    public function views() {

        $views = $this->get_views();

        //  ----------------------------------------
        //  Prepare inside
        //  ----------------------------------------

        if ( ! empty( $views ) ){

            $viewsLinks = array();

            foreach ( $views as $slug => $view ) {

                $viewsLinks[ $slug ] = sprintf( '<li class="%1$s"><a class="%2$s" href="%3$s" data-value="%1$s">%4$s</a></li>',
                    $slug,
                    ( $this->getView() === $slug ) ? 'current' : '',
                    '',
                    $view
                );

            }

            //  ----------------------------------------
            //  Display whole list
            //  ----------------------------------------

            printf( '<ul class="subsubsub">%1$s</ul>',
                implode( " | ", $viewsLinks )
            );

        }

    }

    /**
     * **** WP_List_Table specific
     *
     * @return void
     */
    public function no_items() {

        echo $this->noItemsText;

    }

    /**
     * **** WP_List_Table specific
     *
     * @return string
     */
    public function column_default( $item, $column_name ) {

        $html = '';

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

    /**
     * **** WP_List_Table specific
     *
     * @return string
     */
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

    /**
     * Just returns $_REQUEST['view'] or default value.
     *
     * @return string
     */
    public function getView() {

        if( isset( $_REQUEST['view'] ) && ! empty( $_REQUEST['view'] ) ){

            return esc_sql( $_REQUEST['view'] );

        } else {

            return esc_sql( $this->view );

        }

    }

    /**
     * Just returns $_REQUEST['bulkaction'] or default value.
     *
     * @return string
     */
    public function getCurrentBulkAction() {

        if( isset( $_REQUEST['bulkaction'] ) && ! empty( $_REQUEST['bulkaction'] ) ){

            return esc_sql( $_REQUEST['bulkaction'] );

        } else {

            return esc_sql( $this->currentBulkAction );

        }

    }

    /**
     * Just returns $_REQUEST['bulkitems'] or default value.
     *
     * @return array
     */
    public function getCurrentBulkItems() {

        if( isset( $_REQUEST['bulkitems'] ) && ! empty( $_REQUEST['bulkitems'] ) ){

            return (array) esc_sql( $_REQUEST['bulkitems'] );

        } else {

            return (array) esc_sql( $this->currentBulkItems );

        }

    }

    /**
     * Just returns $_REQUEST['rowaction'] or default value.
     *
     * @return string
     */
    public function getCurrentRowAction() {

        if( isset( $_REQUEST['rowaction'] ) && ! empty( $_REQUEST['rowaction'] ) ){

            return esc_sql( $_REQUEST['rowaction'] );

        } else {

            return esc_sql( $this->currentRowAction );

        }

    }

    /**
     * Just returns $_REQUEST['rowitem'] or default value.
     *
     * @return string
     */
    public function getCurrentRowItem() {

        if( isset( $_REQUEST['rowitem'] ) && ! empty( $_REQUEST['rowitem'] ) ){

            return esc_sql( $_REQUEST['rowitem'] );

        } else {

            return esc_sql( $this->currentRowItem );

        }

    }

}
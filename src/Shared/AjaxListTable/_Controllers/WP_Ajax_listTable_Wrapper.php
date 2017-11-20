<?php
namespace shellpress\v1_0_9\src\Shared\AjaxListTable\_Controllers;

/**
 * @author DualJack
 * Date: 2017-07-25
 * Time: 19:46
 */
use tmc\mailboo\src\App;

/**
 * Class AjaxListTable.
 * This class just extends the modified version of WP_List_Table.
 * It sets up some basic hooks for you.
 *
 * The code style is different in some parts of class, because it extends
 * the core class WP_List_Table - made by WordPress team.
 *
 * @package shellpress\v1_0_9\src\Shared
 */
class WP_Ajax_listTable_Wrapper {

    /** @var string */
    protected $slug;

    /** @var array */
    protected $params;

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
    protected $headers = array();

    /** @var array */
    protected $columnHeaders;

    /** @var array */
    protected $items;

    /** @var array */
    protected $paginationArgs;

    /** @var object */
    protected $screen;



    /**
     * AjaxListTable constructor.
     *
     * @param string $tableSlug     Unique key
     * @param array $params         Table parameters
     */
    public function __construct( $tableSlug, & $params ) {

        $this->slug = sanitize_key( $tableSlug );
        $this->params = & $params;
        $this->screen = (object) array( 'id' => '_invalid', 'base' => '_are_belong_to_us' );    //  Fake object

    }

    //  ================================================================================
    //  LIST TABLE SPECIFIC METHODS
    //  ================================================================================

    /**
     * **** WP_List_Table specific
     */
    public function prepareItems() {

        //  ----------------------------------------
        //  Columns headers hook
        //  ----------------------------------------

        $headers = $this->headers;  //  Get default value ( empty array )

        //  Add bulk actions checkbox column

        if( $this->hasBarActions() ){

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

        $columns    = $this->getColumns();
        $hidden     = $this->getHiddenColumns();
        $sortable   = $this->getSortableColumns();

        $this->columnHeaders = array( $columns, $hidden, $sortable );

        //  ----------------------------------------
        //  Items
        //  ----------------------------------------

        /**
         * Apply filter on empty array.
         * Filter tag: `items_{tableSlug}`
         *
         * @param array     $items
         * @param int       $itemsPerPage
         * @param int       $paged
         * @param string    $search
         * @param string    $order
         * @param string    $orderBy
         * @param string    $view
         * @param array     $actions
         */
        $this->items = apply_filters( 'items_' . $this->slug, array(),
            $this->getItemsPerPage(),   //  $itemsPerPage
            $this->getPaged(),          //  $paged
            $this->getSearch(),         //  $search
            $this->getOrder(),          //  $order
            $this->getOrderBy(),        //  $orderBy
            $this->getView(),           //  $view
            $this->getCurrentActions()  //  $actions
        );

        //  ----------------------------------------
        //  Pagination arguments
        //  ----------------------------------------

        $this->setPaginationArgs(
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
     * An internal method that sets all the necessary pagination arguments
     *
     * @since 3.1.0
     * @access protected
     *
     * @param array|string $args Array or string of arguments with information about the pagination.
     */
    protected function setPaginationArgs( $args ) {
        $args = wp_parse_args( $args, array(
            'total_items' => 0,
            'total_pages' => 0,
            'per_page' => 0,
        ) );

        if ( !$args['total_pages'] && $args['per_page'] > 0 )
            $args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );

        // Redirect if page number is invalid and headers are not already sent.
        if ( ! headers_sent() && ! wp_doing_ajax() && $args['total_pages'] > 0 && $this->getPagenum() > $args['total_pages'] ) {
            wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
            exit;
        }

        $this->paginationArgs = $args;
    }

    /**
     * Access the pagination args.
     *
     * @since 3.1.0
     * @access public
     *
     * @param string $key Pagination argument to retrieve. Common values include 'total_items',
     *                    'total_pages', 'per_page', or 'infinite_scroll'.
     * @return int Number of items that correspond to the given pagination argument.
     */
    public function getPaginationArg($key ) {

        if ( 'page' === $key ) {
            return $this->getPagenum();
        } elseif ( isset( $this->paginationArgs[$key] ) ) {
            return $this->paginationArgs[$key];
        } else {
            return null;
        }

    }

    public function getPagenum() {
        $pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

        if ( isset( $this->paginationArgs['total_pages'] ) && $pagenum > $this->paginationArgs['total_pages'] )
            $pagenum = $this->paginationArgs['total_pages'];

        return max( 1, $pagenum );
    }

    /**
     * Should be called before $this->prepare_items()
     */
    public function processCurrentActions() {

        $currentActions     = $this->getCurrentActions();
        $selectedItems      = $this->getSelectedItems();

        /**
         * Do bulk actions.
         * Action tag: `actions_{tableSlug}`
         *
         * @param array $currentActions
         * @param array $selectedItems
         */
        do_action( 'actions_' . $this->slug, $currentActions, $selectedItems );

    }

    /**
     * **** WP_List_Table specific
     *
     * @return array
     */
    public function get_bar_actions() {

        $barActions = array();
        $currentView = $this->getView();

        /**
         * Apply filter on empty array.
         * Filter tag: `bar_actions_{tableSlug}`
         *
         * @param array     $barActions
         * @param string    $currentView
         */
        $barActions = apply_filters( 'bar_actions_' . $this->slug, $barActions, $currentView );

        return $barActions;

    }

    /**
     * Checks if there are defined bar actions.
     *
     * @return bool
     */
    public function hasBarActions() {

        $barActions = $this->get_bar_actions();

        if( empty( $barActions ) ){
            return false;
        } else {
            return true;
        }

    }

    /**
     * **** WP_List_Table specific
     *
     * @return array
     */
    public function getColumns() {

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
    public function getSortableColumns() {

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
    public function getHiddenColumns() {

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

        echo $this->params['noItemsText'];

    }

    /**
     * **** WP_List_Table specific
     *
     * @param mixed $item
     * @param string $column_name
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
     * @param mixed $item
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

    /**
     * Display the actions controls on navigation bar.
     *
     * @see getDisplayOfBarActionComponent()
     */
    protected function bar_actions() {

        $barActions = $this->get_bar_actions();

        foreach( $barActions as $groupSlug => $group ){

            printf( '<span class="group" data-bar-group="%1$s" style="margin-right:15px; display:inline-block;">', $groupSlug );

            foreach( $group as $actionId => $component ){

                echo $this->getDisplayOfBarActionComponent( $actionId, $component );

            }

            printf( '</span>' );

        }

    }

    /**
     * **** WP_List_Table specific
     *
     * @param bool $withId
     */
    protected function print_column_headers( $withId = true ) {

        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        $current_url = remove_query_arg( 'paged', $current_url );

        if ( isset( $_REQUEST['orderBy'] ) && ! empty( $_REQUEST['orderBy'] ) ) {
            $current_orderby = $_REQUEST['orderBy'];
        } else {
            $current_orderby = $this->params['orderBy'];
        }

        if ( isset( $_REQUEST['order'] ) && 'desc' === $_REQUEST['order'] ) {
            $current_order = 'desc';
        } else {
            $current_order = $this->params['order'];
        }

        if ( ! empty( $columns['cb'] ) ) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
                             . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name ) {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) ) {
                $class[] = 'hidden';
            }

            if ( 'cb' === $column_key )
                $class[] = 'check-column';
            elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
                $class[] = 'num';

            if ( $column_key === $primary ) {
                $class[] = 'column-primary';
            }

            if ( isset( $sortable[$column_key] ) ) {
                list( $orderby, $desc_first ) = $sortable[$column_key];

                if ( $current_orderby === $orderby ) {
                    $order = 'asc' === $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order = $desc_first ? 'desc' : 'asc';
                    $class[] = 'sortable';
                    $class[] = $desc_first ? 'asc' : 'desc';
                }

                $column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
            }

            $tag = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id = $withId ? "id='$column_key'" : '';

            if ( !empty( $class ) )
                $class = "class='" . join( ' ', $class ) . "'";

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    /**
     * Get a list of all, hidden and sortable columns, with filter applied
     *
     * @since 3.1.0
     * @access protected
     *
     * @return array
     */
    protected function get_column_info() {
        // $_column_headers is already set / cached
        if ( isset( $this->_column_headers ) && is_array( $this->_column_headers ) ) {
            // Back-compat for list tables that have been manually setting $_column_headers for horse reasons.
            // In 4.3, we added a fourth argument for primary column.
            $column_headers = array( array(), array(), array(), $this->get_primary_column_name() );
            foreach ( $this->_column_headers as $key => $value ) {
                $column_headers[ $key ] = $value;
            }

            return $column_headers;
        }

        $columns = get_column_headers( $this->screen );
        $hidden = get_hidden_columns( $this->screen );

        $sortable_columns = $this->getSortableColumns();

        $sortable = array();
        foreach ( $sortable_columns as $id => $data ) {
            if ( empty( $data ) )
                continue;

            $data = (array) $data;
            if ( !isset( $data[1] ) )
                $data[1] = false;

            $sortable[$id] = $data;
        }

        $primary = $this->get_primary_column_name();
        $this->_column_headers = array( $columns, $hidden, $sortable, $primary );

        return $this->_column_headers;
    }

    /**
     * Gets the name of the primary column.
     *
     * @since 4.3.0
     * @access protected
     *
     * @return string The name of the primary column.
     */
    protected function get_primary_column_name() {
        $columns = get_column_headers( $this->screen );
        $default = $this->get_default_primary_column_name();

        // If the primary column doesn't exist fall back to the
        // first non-checkbox column.
        if ( ! isset( $columns[ $default ] ) ) {
            $default = static::get_default_primary_column_name();
        }

        /**
         * Filters the name of the primary column for the current list table.
         *
         * @since 4.3.0
         *
         * @param string $default Column name default for the specific list table, e.g. 'name'.
         * @param string $context Screen ID for specific list table, e.g. 'plugins'.
         */
        $column  = apply_filters( 'list_table_primary_column', $default, $this->screen->id );

        if ( empty( $column ) || ! isset( $columns[ $column ] ) ) {
            $column = $default;
        }

        return $column;
    }

    /**
     * Gets the name of the default primary column.
     *
     * @since 4.3.0
     * @access protected
     *
     * @return string Name of the default primary column, in this case, an empty string.
     */
    protected function get_default_primary_column_name() {
        $columns = $this->getColumns();
        $column = '';

        if ( empty( $columns ) ) {
            return $column;
        }

        // We need a primary defined so responsive views show something,
        // so let's fall back to the first non-checkbox column.
        foreach ( $columns as $col => $column_name ) {
            if ( 'cb' === $col ) {
                continue;
            }

            $column = $col;
            break;
        }

        return $column;
    }

    /**
     * Get HTML of every type of bar actions component.
     *
     * @param string $actionId
     * @param array $component
     *
     * @return string
     */
    private function getDisplayOfBarActionComponent( $actionId, $component ) {

        $html = '';

        //  ----------------------------------------
        //  Defaults
        //  ----------------------------------------

        $componentDefault = array(
            'temp'          =>  true,
            'type'          =>  null,
            'attributes'    =>  array(),
            'select'        =>  array(),
            'title'         =>  ''
        );

        $component = wp_parse_args( $component, $componentDefault );

        //  ----------------------------------------
        //  Requirement of type definition
        //  ----------------------------------------

        if( empty( $component['type'] ) ) {   //  We don't know type of component. Abort.

            return $html;

        }

        //  ----------------------------------------
        //  Component attributes
        //  ----------------------------------------

        $attrArray                      = array();

        $attrArray['data-action-id']    = sprintf( 'data-action-id="%1$s"', $actionId );    //  Action id

	    if( $component['temp'] === true ){

		    $attrArray['data-action-temp'] = sprintf( 'data-action-temp="%1$s"', $actionId );

	    }

        foreach( $component['attributes'] as $attrName => $attrValue ){

            $attrArray[ $attrName ] = sprintf( '%1$s="%2$s"', $attrName, $attrValue );

        }

        //  ----------------------------------------
        //  Type: select
        //  ----------------------------------------

        if( $component['type'] === 'select' ){

            $html .= sprintf( '<select %1$s>', implode( ' ', $attrArray ) );

            //  This component is a group of components, so we call this method again

            foreach( (array) $component['select'] as $optionId => $selectOption ){

                //  Defaults

                $defaultSelectOption = array(
                    'title'         =>  'Title',
                    'data'          =>  '',
	                'attributes'    =>  array()
                );

                $selectOption = wp_parse_args( $selectOption, $defaultSelectOption );

                //  Attributes

                $optionAttrArray                        = array();

                $optionAttrArray['value']               = sprintf( 'value="%1$s"', $optionId );

                $optionAttrArray['data-action-data']    = sprintf( 'data-action-data="%1$s"', esc_attr( wp_json_encode( $selectOption['data'] ) ) );

                //  Remember clicked option

                $currentActions = $this->getCurrentActions();

                if( $component['temp'] === false ){

                    if( isset( $currentActions[ $actionId ][ $optionId ] ) ){

                        $optionAttrArray['selected'] = 'selected="selected"';

                    }

                }

                //  Custom attributes

	            foreach( $selectOption['attributes'] as $attrName => $attrValue ){

		            $optionAttrArray[ $attrName ] = sprintf( '%1$s="%2$s"', $attrName, $attrValue );

	            }

                //  Display options

                $html .= sprintf( '<option %1$s>%2$s</option>', implode( ' ', $optionAttrArray ), $selectOption['title'] );

            }

            $html .= sprintf( '</select>' );

        } else

        //  ----------------------------------------
        //  Type: submit
        //  ----------------------------------------

        if( $component['type'] === 'submit' ){

            $attrArray[]        =   sprintf( 'data-action-data="%1$s"', esc_attr( wp_json_encode( $component['data'] ) ) );

            $attrArray['value'] =   sprintf( 'value="%1$s"', $component['title'] );

            $attrArray['type']  =   sprintf( 'type="submit"' );



            $html .= sprintf( '<input %1$s>', implode( ' ', $attrArray ) );

        }

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

            return esc_sql( $this->params['order'] );

        }

    }

    /**
     * Just returns $_REQUEST['orderby'] or default value.
     *
     * @return string
     */
    public function getOrderBy() {

        if( isset( $_REQUEST['orderBy'] ) && ! empty( $_REQUEST['orderBy'] ) ){

            return esc_sql( $_REQUEST['orderBy'] );

        } else {

            return esc_sql( $this->params['orderBy'] );

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

            return (int) $this->params['paged'];

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

            return (int) $this->params['totalItems'];

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

            return (int) $this->params['itemsPerPage'];

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

            return esc_sql( $this->params['search'] );

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

            return esc_sql( $this->params['view'] );

        }

    }

    /**
     * Just returns $_REQUEST['currentActions'] or default value.
     *
     * @return array
     */
    public function getCurrentActions() {

        if( isset( $_REQUEST['currentActions'] ) && ! empty( $_REQUEST['currentActions'] ) ){

            return (array) esc_sql( $_REQUEST['currentActions'] );

        } else {

            return (array) esc_sql( $this->params['currentActions'] );

        }

    }

    /**
     * Just returns $_REQUEST['selectedItems'] or default value.
     *
     * @return array
     */
    public function getSelectedItems() {

        if( isset( $_REQUEST['selectedItems'] ) && ! empty( $_REQUEST['selectedItems'] ) ){

            return (array) esc_sql( $_REQUEST['selectedItems'] );

        } else {

            return (array) esc_sql( $this->params['selectedItems'] );

        }

    }

}
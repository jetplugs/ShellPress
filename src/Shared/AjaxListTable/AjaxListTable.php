<?php
namespace shellpress\v1_0_4\src\Shared\AjaxListTable;

/**
 * @author jakubkuranda@gmail.com
 * Date: 27.07.2017
 * Time: 12:18
 */

use shellpress\v1_0_4\src\Shared\AjaxListTable\_Controllers\WP_Ajax_listTable_Wrapper;

abstract class AjaxListTable {

    /** @var WP_Ajax_listTable_Wrapper */
    private $listTable;

    /** @var bool */
    private $isSearchboxVisible = true;

    /** @var bool */
    private $isEndOfSetUp = false;

    /**
     * Format:
     * array(
     *      array(
     *          'text'      =>  "Hello",
     *          'class'     =>  'notice-info'
     *      )
     * )
     *
     * @var array
     */
    private $notices = array();

    /**
     * AjaxListTable constructor.
     *
     * @param string $tableSlug - Unique key
     * @param string $singular - Label for singular item
     * @param string $plural - Label for plural items
     */
    public function __construct( $tableSlug, $singular = 'item', $plural = 'items' ){

        //  ----------------------------------------
        //  ListTable creation
        //  ----------------------------------------

        $this->listTable = new WP_Ajax_listTable_Wrapper( $tableSlug, $singular, $plural );

        //  ----------------------------------------
        //  Actions
        //  ----------------------------------------

        add_action( 'wp_ajax_' . $this->getAjaxActionName(),        array( $this, '_a_ajaxResponse') );

        //  ----------------------------------------
        //  Set up
        //  ----------------------------------------

        $this->setUp();

        $this->isEndOfSetUp = true; //  For now dynamic setters will set parameters in global $_REQUEST

    }

    /**
     * Extend this method.
     * It's called automatically on object creation.
     */
    protected abstract function setUp();

    /**
     * Extend this method.
     * It's called automatically in ajax response.
     */
    protected abstract function loadTable();

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
            sprintf( 'data-paged="%1$s"',           $this->listTable->getPaged() ),
            sprintf( 'data-order="%1$s"',           $this->listTable->getOrder() ),
            sprintf( 'data-orderby="%1$s"',         $this->listTable->getOrderBy() ),
            sprintf( 'data-search="%1$s"',          $this->listTable->getSearch() )
        );

        $html = sprintf(
            '<div id="%1$s" class="%2$s" %3$s>',
            /** %1$s */ $this->getSlug(),
            /** %2$s */ 'sp-a-list-table',
            /** %3$s */ implode( ' ', $attributes )
        );
        $html .= sprintf( '<div class="spinner is-active" style="float:none"></div>' );
        $html .= sprintf( '</div>' );

        $html .= PHP_EOL;

        $html .= $this->_getInitScript();

        return $html;

    }

    /**
     * @return string
     */
    public function getSlug() {

        return $this->listTable->slug;

    }

    /**
     * @return string
     */
    public function getAjaxActionName() {

        return $this->listTable->ajaxActionName;

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

    /**
     * Gets search box visibility.
     *
     * @param bool $isVisible - if set, this method works as setter
     *
     * @return bool
     */
    public function isSearchBoxVisible( $isVisible = null ) {

        if( $isVisible !== null ){

            $this->isSearchboxVisible = $isVisible;

        }

        return $this->isSearchboxVisible;

    }

    /**
     * Creates checkbox for bulk actions column.
     *
     * @param int $itemId
     *
     * @return string - Checkbox HTML
     */
    public function generateRowCheckbox( $itemId ) {

        return sprintf( '<input type="checkbox" name="item-id" value="%1$s">', $itemId );

    }

    /**
     * Returns HTML of all notices.
     *
     * @uses $this->notices
     *
     * @return string - HTML
     */
    public function getDisplayOfNotices() {

        $html = '';

        foreach( $this->notices as $notice ){

            $classes = array( 'notice', 'is-dismissible', $notice['class'] );

            $html .= sprintf( '<div class="%1$s"><p>%2$s</p></div>', implode( ' ', $classes ), $notice['text'] );

        }

        return $html;

    }

    //  ================================================================================
    //  SETTERS
    //  ================================================================================

    /**
     * @param int $totalItems
     */
    public function setTotalItems( $totalItems ) {

        $this->listTable->totalItems = $totalItems;

        if( $this->isEndOfSetUp ){

            $_REQUEST['totalitems'] = $totalItems;

        }

    }

    /**
     * @param string $order
     */
    public function setOrder( $order ) {

        $this->listTable->order = $order;

        if( $this->isEndOfSetUp ){

            $_REQUEST['order'] = $order;

        }

    }

    /**
     * @param string $orderBy
     */
    public function setOrderBy( $orderBy ) {

        $this->listTable->orderBy = $orderBy;

        if( $this->isEndOfSetUp ){

            $_REQUEST['orderby'] = $orderBy;

        }

    }

    /**
     * @param int $paged
     */
    public function setPaged( $paged ) {

        $this->listTable->paged = $paged;

        if( $this->isEndOfSetUp ){

            $_REQUEST['paged'] = $paged;

        }

    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage( $itemsPerPage ) {

        $this->listTable->itemsPerPage = $itemsPerPage;

        if( $this->isEndOfSetUp ){

            $_REQUEST['itemsperpage'] = $$itemsPerPage;

        }

    }

    /**
     * @param string $search
     */
    public function setSearch( $search ) {

        $this->listTable->search = $search;

        if( $this->isEndOfSetUp ){

            $_REQUEST['search'] = $search;

        }

    }

    /**
     * @param string $noItemsText - It's visible when there are no rows in table.
     */
    public function setNoItemsText( $noItemsText ) {

        $this->listTable->noItemsText = $noItemsText;

    }

    /**
     * Add notice above table.
     *
     * @param string $text
     * @param string $class - notice-error, notice-warning, notice-success, notice-info
     */
    public function addNotice( $text, $class = 'notice-info' ) {

        $newNotice = array(
            'text'      =>  $text,
            'class'     =>  $class
        );

        $this->notices[] = $newNotice;

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

        $this->loadTable();

        $this->listTable->prepare_bulk_actions();

        $this->listTable->prepare_items();

        ob_start();

        printf( '<pre>%1$s</pre>', print_r( $_REQUEST, true ) );

        echo $this->getDisplayOfNotices();

        $this->listTable->views();

        if( $this->isSearchboxVisible() ){

            $this->listTable->search_box( __( "Search" ), $this->getSlug() );

        }

        $this->listTable->display();

        $response = ob_get_clean();


        die( wp_json_encode( $response ) );

    }

}
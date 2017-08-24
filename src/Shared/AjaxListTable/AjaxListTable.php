<?php
namespace shellpress\v1_0_6\src\Shared\AjaxListTable;

/**
 * @author jakubkuranda@gmail.com
 * Date: 27.07.2017
 * Time: 12:18
 */

use shellpress\v1_0_6\src\Shared\AjaxListTable\_Controllers\WP_Ajax_listTable_Wrapper;

abstract class AjaxListTable {

    /** @var WP_Ajax_listTable_Wrapper */
    private $listTable;

    /** @var bool */
    private $isSearchboxVisible = true;

    /** @var bool */
    private $isListOfViewsVisible = true;

    /** @var bool */
    private $isEndOfSetUp = false;
    
    /** @var string */
    private $slug;

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
     * @param string $tableSlug         Unique key.
     */
    public function __construct( $tableSlug ){
        
        //  ----------------------------------------
        //  Properties
        //  ----------------------------------------
        
        $this->slug = $tableSlug;

        //  ----------------------------------------
        //  Actions
        //  ----------------------------------------

        add_action( 'wp_ajax_' . $this->getAjaxActionName(),        array( $this, '_a_ajaxResponse') );

    }

    /**
     * It's called automatically on object creation.
     * Defines all table settings.
     */
    protected abstract function setUp();

    //  ================================================================================
    //  ADVANCED GETTERS
    //  ================================================================================

    /**
     * Call this method to get main table wrapper.
     */
    public function getDisplayRoot() {

        $attributes = array();

        $html = sprintf( '<div id="%1$s" class="%2$s" %3$s>',
            $this->getSlug(),
            'sp-ajax-list-table',
            implode( ' ', $attributes )
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

        return $this->slug;

    }

    /**
     * @return string
     */
    public function getAjaxActionName() {

        return 'display_' . $this->getSlug();

    }

    /**
     * Returns full string of script embed.
     * Script applies jQuery plugin on table wrapper.
     *
     * @return string
     */
    protected function _getInitScript() {

        $listTableArgs = array(
            'nonce'                 =>  wp_create_nonce( $this->getAjaxActionName() ),
            'ajaxDisplayAction'     =>  $this->getAjaxActionName()
        );

        ob_start();
        ?>

        <script type='text/javascript'>

            jQuery( document ).ready( function( $ ){

                <?php printf( '$( "#%1$s" ).ShellPressAjaxListTable( JSON.parse( \'%2$s\' ) );', $this->getSlug(), wp_json_encode( $listTableArgs ) );?>

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
     * Gets list of views visibility.
     *
     * @param bool $isVisible - if set, this method works as setter
     *
     * @return bool
     */
    public function isListOfViewsVisible( $isVisible = null ) {

        if( $isVisible !== null ){

            $this->isListOfViewsVisible = $isVisible;

        }

        return $this->isListOfViewsVisible;

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
    //  GENERATORS
    //  ================================================================================

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
     * Creates row actions.
     *
     * $actions = array(
     *      {actionSlug}    =>  array(
     *          'title'         =>  "Title",        //  (optional)
     *          'url'           =>  '#',            //  (optional)
     *          'ajax'          =>  false           //  (optional)
     *      )
     * )
     *
     * @param array $actions
     * @param string $itemId - ID of your item
     * @param bool  $alwaysVisible
     *
     * @return string - Whole row actions HTML
     */
    public function generateRowActions( $actions, $itemId = null, $alwaysVisible = false ) {

        $rowActions = array();

        foreach( $actions as $actionSlug => $actionArgs ){

            //  ----------------------------------------
            //  Default arguments
            //  ----------------------------------------

            $defaultActionArgs = array(
                'title'     =>  $actionSlug,
                'url'       =>  '',
                'ajax'      =>  false
            );

            $actionArgs = array_merge( $defaultActionArgs, $actionArgs );

            //  ----------------------------------------
            //  Link tag attributes
            //  ----------------------------------------

            $tagAttributes = array();

            $tagAttributes[]        = sprintf( 'href="%1$s"', esc_attr( $actionArgs['url'] ) );

            if( $actionArgs['ajax'] === true ){

                $tagAttributes[]    = sprintf( 'data-row-action="%1$s"', $actionSlug );
                $tagAttributes[]    = sprintf( 'data-row-item="%1$s"', $itemId );

            }

            $tagAttributesString = implode( '', $tagAttributes );

            //  ----------------------------------------
            //  Preparing links
            //  ----------------------------------------

            $rowActions[ $actionSlug ] = sprintf( '<span class="%1$s"><a %2$s>%3$s</a></span>', $actionSlug, $tagAttributesString, $actionArgs['title'] );

        }

        //  ----------------------------------------
        //  Return HTML
        //  ----------------------------------------

        $html = '';

        $html .= sprintf( '<div class="%1$s">', $alwaysVisible ? 'row-actions visible' : 'row-actions' );

        $html .= implode( ' | ', $rowActions );     //  Glue links

        $html .= '</div>';

        $html .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

        return $html;

    }

    //  ================================================================================
    //  SETTERS
    //  ================================================================================

    /**
     * @param int $totalItems
     */
    public function setTotalItems( $totalItems ) {



    }

    /**
     * @param string $order
     */
    public function setOrder( $order ) {



    }

    /**
     * @param string $orderBy
     */
    public function setOrderBy( $orderBy ) {



    }

    /**
     * @param int $paged
     */
    public function setPaged( $paged ) {



    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage( $itemsPerPage ) {



    }

    /**
     * @param string $search
     */
    public function setSearch( $search ) {



    }

    /**
     * @param string $noItemsText - It's visible when there are no rows in table.
     */
    public function setNoItemsText( $noItemsText ) {



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

        $this->setUp();

        $listTable = new WP_Ajax_listTable_Wrapper( $this->getSlug() );

        $listTable->process_bulk_action();
        $listTable->process_row_action();

        $listTable->prepare_items();

        ob_start();

        echo $this->getDisplayOfNotices();

        if( $this->isListOfViewsVisible() ){

            $listTable->views();

        }

        if( $this->isSearchboxVisible() ){

            $listTable->search_box( __( "Search" ), $this->getSlug() );

        }

        $listTable->display();

        $response = ob_get_clean();


        die( wp_json_encode( $response ) );

    }

}
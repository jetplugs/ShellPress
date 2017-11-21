<?php
namespace shellpress\v1_1_0\src\Shared\AjaxListTable;

/**
 * @author jakubkuranda@gmail.com
 * Date: 27.07.2017
 * Time: 12:18
 */

use shellpress\v1_1_0\src\Shared\AjaxListTable\_Controllers\WP_Ajax_listTable_Wrapper;

abstract class AjaxListTable {

    /** @var bool */
    private $isSearchboxVisible = true;

    /** @var bool */
    private $isListOfViewsVisible = true;
    
    /** @var string */
    private $slug;

    /** @var array */
    private $ajaxParams = array();

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

        $this->ajaxParams = array(
            'totalItems'        =>  0,
            'order'             =>  'asc',
            'orderBy'           =>  'id',
            'paged'             =>  1,
            'itemsPerPage'      =>  20,
            'search'            =>  '',
            'view'              =>  'default',
            'noItemsText'       =>  'No items found.',
            'currentActions'    =>  array(),
            'selectedItems'     =>  array()
        );

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
     *
     * @param array $attributes     Div root attributes. Attr => Value
     * @param string $loader        HTML of loader.
     */
    public function getDisplayRoot( $attributes = array(), $loader = null ) {

        $defaultAttributes = array(
            'id'    =>  $this->getSlug(),
            'class' =>  'sp-ajax-list-table'
        );

        $attributes = wp_parse_args( $attributes, $defaultAttributes );

        $attrHTML = array();

        foreach( $attributes as $attr => $value ){

            $attrHTML[] = sprintf( '%1$s="%2$s"', $attr, $value );

        }

        $html = sprintf( '<div %1$s>', implode( ' ', $attrHTML ) );

        if( $loader === null ){

            $html .= sprintf( '<div class="spinner is-active" style="float:none"></div>' );

        } else {

            $html .= $loader;

        }

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

        ob_start();
        ?>

        <script type='text/javascript'>

            jQuery( document ).ready( function( $ ){

                <?php printf( '$( "#%1$s" ).ShellPressAjaxListTable( \'%2$s\', \'%3$s\' );', $this->getSlug(), wp_create_nonce( $this->getAjaxActionName() ), $this->getAjaxActionName() );?>

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
     * @return bool
     */
    public function getSearchBoxVisibility() {

        return (bool) $this->isSearchboxVisible;

    }

    /**
     * Sets visibility of search box.
     *
     * @param bool $isVisible
     */
    public function setSearchBoxVisibility( $isVisible ) {

        $this->isSearchboxVisible = $isVisible;

    }

    /**
     * Gets list of views visibility.
     *
     * @param bool $isVisible - if set, this method works as setter
     *
     * @return bool
     */
    public function getListOfViewsVisibility() {

        return $this->isListOfViewsVisible;

    }

    /**
     * Sets visibility of list of views.
     *
     * @param bool $isVisible
     */
    public function setListOfViewsVisibility( $isVisible ) {

        $this->isListOfViewsVisible = $isVisible;

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
     * @param mixed $data   - Data passed to checkbox
     *
     * @return string - Checkbox HTML
     */
    public function generateRowCheckbox( $data ) {

        $dataAsJson         = wp_json_encode( $data );
        $safeDataAsJson     = esc_html( $dataAsJson );

        return sprintf( '<input type="checkbox" data-row-checkbox="%1$s">', $safeDataAsJson );

    }


    /**
     * Creates row actions.
     *
     * $actions = array(
     *      {actionSlug}    =>  array(
     *          'title'         =>  "Title",        //  (optional)
     *          'url'           =>  '#',            //  (optional)
     *          'ajax'          =>  false,          //  (optional)
     *          'data'          =>  array( 'thg' )  //  (optional)
     *      )
     * )
     *
     * @param array $actions
     * @param bool  $alwaysVisible
     *
     * @return string - Whole row actions HTML
     */
    public function generateRowActions( $actions, $alwaysVisible = false ) {

        $rowActions = array();

        foreach( $actions as $actionSlug => $actionArgs ){

            //  ----------------------------------------
            //  Default arguments
            //  ----------------------------------------

            $defaultActionArgs = array(
                'title'     =>  $actionSlug,
                'url'       =>  '#',
                'ajax'      =>  false,
                'data'      =>  null,
                'temp'      =>  true
            );

            $actionArgs = wp_parse_args( $actionArgs, $defaultActionArgs );

            //  ----------------------------------------
            //  Link tag attributes
            //  ----------------------------------------

            $tagAttributes = array();

            //  Tag attributes

            $tagAttributes[]        = sprintf( 'href="%1$s"', esc_attr( $actionArgs['url'] ) );         //  Action url

            if( $actionArgs['ajax'] === true ){

                $tagAttributes[]    = sprintf( 'data-action-id="%1$s"', $actionSlug );                 //  Action slug

                if( ! empty( $actionArgs['data'] ) ){

                    $dataAsJson         = wp_json_encode( $actionArgs['data'] );
                    $safeDataAsJson     = esc_html( $dataAsJson );

                    $tagAttributes[]    = sprintf( 'data-action-data="%1$s"', $safeDataAsJson );    //  Action data ( as json )

                }

            }

            if( $actionArgs['temp'] === true ){                                                     //  Temp action id

                $tagAttributes[] = sprintf( 'data-action-temp="%1$s"', $actionSlug );

            }

            $tagAttributesString = implode( ' ', $tagAttributes );

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

        $this->ajaxParams['totalItems'] = $totalItems;

    }

    /**
     * @param string $order     asc / desc
     */
    public function setOrder( $order ) {

        $this->ajaxParams['order'] = $order;

    }

    /**
     * @param string $orderBy
     */
    public function setOrderBy( $orderBy ) {

        $this->ajaxParams['orderBy'] = $orderBy;

    }

    /**
     * @param int $paged
     */
    public function setPaged( $paged ) {

        $this->ajaxParams['paged'] = $paged;

    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage( $itemsPerPage ) {

        $this->ajaxParams['itemsPerPage'] = $itemsPerPage;

    }

    /**
     * @param string $search
     */
    public function setSearch( $search ) {

        $this->ajaxParams['search'] = $search;

    }

    /**
     * @param string $view
     */
    public function setView( $view ) {

        $this->ajaxParams['view'] = $view;

    }

    /**
     * @param string $noItemsText - It's visible when there are no rows in table.
     */
    public function setNoItemsText( $noItemsText ) {

        $this->ajaxParams['noItemsText'] = $noItemsText;

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

        $listTable = new WP_Ajax_listTable_Wrapper( $this->getSlug(), $this->ajaxParams );

        $listTable->processCurrentActions();

        $listTable->prepareItems();

        ob_start();

        echo $this->getDisplayOfNotices();

        if( $this->getListOfViewsVisibility() ){

            $listTable->views();

        }

        if( $this->getSearchBoxVisibility() ){

            $listTable->searchBox( __( "Search" ), $this->getSlug() );

        }

        $listTable->display();

        $response = ob_get_clean();


        die( wp_json_encode( $response ) );

    }

}
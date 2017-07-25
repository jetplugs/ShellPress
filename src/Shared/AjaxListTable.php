<?php
namespace shellpress\v1_0_4\src\Shared;

/**
 * @author DualJack
 * Date: 2017-07-25
 * Time: 19:46
 */

class AjaxListTable {

    /** @var string */
    private $slug;

    /** @var string */
    private $ajaxActionName;

    /**
     * AjaxListTable constructor.
     *
     * @param string $tableSlug
     */
    public function __construct( $tableSlug ) {

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
    //  ADVANCED GETTERS
    //  ================================================================================

    /**
     * Call this method to get main table wrapper.
     */
    public function getDisplayRoot() {

        $attributes = array(
            sprintf( 'data-nonce="%1$s"',           wp_create_nonce( $this->getAjaxActionName() ) ),
            sprintf( 'data-ajax-action="%1$s"',     $this->getAjaxActionName() )
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

        echo json_encode( "YAY" );

        wp_die();

    }

}
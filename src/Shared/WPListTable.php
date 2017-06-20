<?php
namespace shellpress\v1_0_0\src\Shared;


use shellpress\v1_0_0\lib\_Includes\WP_List_Table;
use shellpress\v1_0_0\ShellPress;

class WPListTable extends WP_List_Table {

    /**
     * @var ShellPress
     */
    public $app;

    /**
     * WPListTable constructor.
     *
     * @param ShellPress $app
     * @param array $args
     */
    function __construct( $app, $args = array() ) {

        parent::__construct( $args );

        $this->app = $app;

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

}
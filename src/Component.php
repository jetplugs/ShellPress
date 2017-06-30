<?php
namespace shellpress\v1_0_1\src;

use shellpress\v1_0_1\ShellPress;


/**
 * Extend this class for every component ( class )
 * used in project. It keeps reference to main
 * ShellPress ( app ) object;
 */
class Component {

    /**
     * @var ShellPress
     */
    public $app;


    /**
     * @param ShellPress $app
     */
    public function __construct( $app ) {

        $this->app = $app;

    }

}
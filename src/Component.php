<?php
namespace shellpress\v1_0_0\src;




/**
 * Extend this class for every component ( class )
 * used in project. It keeps reference to main
 * ShellPress ( app ) object;
 */
class Component {

    protected $app;




    function _construct( $app ) {

        $this->app = $app;

    }

}
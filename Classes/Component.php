<?php
namespace shellpress_1_0_0\Classes;

use shellpress_1_0_0\ShellPress;




/**
 * Extend this class for every component ( class )
 * used in project. It keeps reference to main
 * ShellPress ( app ) object;
 */
class Component {

    protected $app;




    function _construct( ShellPress $app ) {

        $this->app = $app;

    }

}
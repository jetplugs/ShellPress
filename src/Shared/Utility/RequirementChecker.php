<?php

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-08-21
 * Time: 22:49
 */

/**
 * Class RequirementChecker.
 *
 * @deprecated
 */
class ShellPress_RequirementChecker {

    protected $php_version;
    protected $wp_version;

    public function __construct() {

        //  ----------------------------------------
        //  Properties
        //  ----------------------------------------

        $this->php_version  = phpversion();
        $this->wp_version   = $GLOBALS['wp_version'];

    }

    //  TODO checking methods

}
<?php

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-08-21
 * Time: 22:49
 */

if( ! class_exists( 'ShellPress_RequirementChecker' ) ) :

class ShellPress_RequirementChecker {

    protected $versionPHP;
    protected $versionWP;

    /** @var array */
    protected $warnings = array();

    public function __construct() {

        //  ----------------------------------------
        //  Properties
        //  ----------------------------------------

        $this->versionPHP = phpversion();
        $this->versionWP  = $GLOBALS['wp_version'];

        //  ----------------------------------------
        //  Actions
        //  ----------------------------------------

        add_action( 'admin_notices',    array( $this, '_a_printAdminNotices' ) );

    }

    /**
     * @param string $requiredVersion   Required version, example: '5.3'.
     * @param string $errorText         Error which will popup as admin notice in dashboard.
     *
     * @return bool
     */
    public function checkPHPVersion( $requiredVersion, $errorText = null ) {

        $isSatisfied = (bool) version_compare( $this->versionPHP, $requiredVersion, ">=" );

        if( ! $isSatisfied && ! empty( $errorText ) ){

            $this->addWarning( $errorText );

        }

        return $isSatisfied;

    }

    /**
     * @param string $requiredVersion   Required version, example: '4.7'.
     * @param string $errorText         Error which will popup as admin notice in dashboard.
     *
     * @return bool
     */
    public function checkWPVersion( $requiredVersion, $errorText = null ) {

        $isSatisfied = (bool) version_compare( $this->versionWP, $requiredVersion, ">=" );

        if( ! $isSatisfied && ! empty( $errorText ) ){

            $this->addWarning( $errorText );

        }

        return $isSatisfied;

    }

    /**
     * @param string $functionName  Required function name, example: 'woocommerce'.
     * @param string $errorText     Error which will popup as admin notice in dashboard.
     *
     * @return bool
     */
    public function checkFunctionExistance( $functionName, $errorText = null ) {

        $isSatisfied = (bool) function_exists( $functionName );

        if( ! $isSatisfied && ! empty( $errorText ) ){

            $this->addWarning( $errorText );

        }

        return $isSatisfied;

    }

    /**
     * @param string $className     Required class name, example: 'Woocommerce'.
     * @param string $errorText     Error which will popup as admin notice in dashboard.
     *
     * @return bool
     */
    public function checkClassExistance( $className, $errorText = null ) {

        $isSatisfied = (bool) class_exists( $className );

        if( ! $isSatisfied && ! empty( $errorText ) ){

            $this->addWarning( $errorText );

        }

        return $isSatisfied;

    }

    /**
     * Adds warning to array of warnings.
     *
     * @param $text
     */
    protected function addWarning( $text ) {

        $this->warnings[] = $text;

    }

    //  ================================================================================
    //  Actions
    //  ================================================================================

    public function _a_printAdminNotices() {

        foreach( $this->warnings as $warning ){

            $icon = sprintf( '<i class="dashicons dashicons-warning"></i>' );

            printf( '<div class="error notice is-dismissible"><p>%1$s %2$s</p></div>', $icon, $warning );

        }

    }

}

endif;
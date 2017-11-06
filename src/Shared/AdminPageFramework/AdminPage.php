<?php
namespace shellpress\v1_0_8\src\Shared\AdminPageFramework;

use TMC_v3_8_15_AdminPageFramework;

/**
 * # Helper class for simply code separation.
 * In `setUp()` method you should add hook callbacks and other definitions.
 */
abstract class AdminPage {

    /** @var string */
    public $pageSlug;

    /** @var TMC_v3_8_15_AdminPageFramework */
    public $pageFactory;

    /** @var string */
    public $pageFactoryClassName;

    /**
     * AdminPage constructor.
     *
     * @param TMC_v3_8_15_AdminPageFramework $pageFactory
     * @param string $pageSlug
     */
    public function __construct( $pageFactory, $pageSlug ) {

        $this->pageFactory              = $pageFactory;
        $this->pageFactoryClassName     = $pageFactory->oProp->sClassName;
        $this->pageSlug                 = $pageSlug;

        if( method_exists( $this, 'setUp' ) ){

            //  Call it as soon as possible
            call_user_func( array( $this, 'setUp' ) );

        }

    }

    /**
     * Declaration of current element.
     */
    public abstract function setUp();

}
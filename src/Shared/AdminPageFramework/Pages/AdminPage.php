<?php
namespace shellpress\v1_0_7\src\Shared\AdminPageFramework\Pages;

use SP_v1_0_7_AdminPageFramework;

/**
 * # Helper class for simply code separation.
 * In `setUp()` method you should add hook callbacks and other definitions.
 */
abstract class AdminPage {

    /** @var string */
    public $pageSlug;

    /** @var SP_v1_0_7_AdminPageFramework */
    public $pageFactory;

    /** @var string */
    public $pageFactoryClassName;

    /**
     * AdminPage constructor.
     *
     * @param SP_v1_0_7_AdminPageFramework $pageFactory
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
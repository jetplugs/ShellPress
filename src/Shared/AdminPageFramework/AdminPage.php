<?php
namespace shellpress\v1_2_8\src\Shared\AdminPageFramework;

use TMC_v1_0_3_AdminPageFramework;

/**
 * # Helper class for simply code separation.
 * In `setUp()` method you should add hook callbacks and other definitions.
 */
abstract class AdminPage {

    /** @var string */
    public $pageSlug;

    /** @var TMC_v1_0_3_AdminPageFramework */
    public $pageFactory;

    /** @var string */
    public $pageFactoryClassName;

    /**
     * AdminPage constructor.
     *
     * @param TMC_v1_0_3_AdminPageFramework $pageFactory
     * @param string $pageSlug
     */
    public function __construct( $pageFactory, $pageSlug ) {

        $this->pageFactory              = $pageFactory;
        $this->pageFactoryClassName     = $pageFactory->oProp->sClassName;
        $this->pageSlug                 = $pageSlug;

        $this->registerActions();

    }

    /**
     * Called on construction of object.
     */
    protected function registerActions() {

        call_user_func( array( $this, 'setUp' ) );

        add_action( 'load_' . $this->pageSlug,      array( $this, 'load' ) );

    }

    /**
     * Declaration of current element.
     */
    public abstract function setUp();

    /**
     * Called while current component is loaded.
     */
    public abstract function load();

}
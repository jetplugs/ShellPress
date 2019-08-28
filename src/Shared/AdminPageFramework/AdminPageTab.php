<?php
namespace shellpress\v1_3_76\src\Shared\AdminPageFramework;

/**
 * # Helper class for simply code separation.
 * In `setUp()` method you should add hook callbacks and other definitions.
 */
abstract class AdminPageTab extends AdminPage {

    /** @var string */
    public $tabSlug;

    /**
     * AdminPage constructor.
     *
     * @param mixed $pageFactory AdminPageFramework instance
     * @param string $pageSlug
     * @param string $tabSlug
     */
    public function __construct( $pageFactory, $pageSlug, $tabSlug ){

        $this->tabSlug = $tabSlug;

        parent::__construct( $pageFactory, $pageSlug );

    }

    /**
     * Called on construction of object.
     */
    protected function registerActions() {

        call_user_func( array( $this, 'setUp' ) );

        add_action( 'load_' . $this->pageSlug . '_' . $this->tabSlug,      array( $this, 'load' ) );

    }

}
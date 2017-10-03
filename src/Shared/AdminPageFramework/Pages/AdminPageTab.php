<?php
namespace shellpress\v1_0_8\src\Shared\AdminPageFramework\Pages;

use SP_v1_0_8_AdminPageFramework;

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
     * @param SP_v1_0_8_AdminPageFramework $pageFactory
     * @param string $pageSlug
     * @param string $tabSlug
     */
    public function __construct( $pageFactory, $pageSlug, $tabSlug ){

        $this->tabSlug = $tabSlug;

        parent::__construct( $pageFactory, $pageSlug );

    }

}
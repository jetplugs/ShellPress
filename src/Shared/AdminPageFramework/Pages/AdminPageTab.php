<?php
namespace tmc\mailboo\src\Shared\AdminPageFramework\Pages;

use SP_v1_0_6_AdminPageFramework;

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
     * @param SP_v1_0_6_AdminPageFramework $pageFactory
     * @param string $pageSlug
     * @param string $tabSlug
     */
    public function __construct( $pageFactory, $pageSlug, $tabSlug ){

        $this->tabSlug = $tabSlug;

        parent::__construct( $pageFactory, $pageSlug );

    }

}
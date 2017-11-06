<?php
namespace shellpress\v1_0_9\src\Shared\AdminPageFramework;

use TMC_v3_8_15_AdminPageFramework;

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
     * @param TMC_v3_8_15_AdminPageFramework $pageFactory
     * @param string $pageSlug
     * @param string $tabSlug
     */
    public function __construct( $pageFactory, $pageSlug, $tabSlug ){

        $this->tabSlug = $tabSlug;

        parent::__construct( $pageFactory, $pageSlug );

    }

}
<?php
namespace shellpress\v1_3_4\src\Components\External\AdminPage\Objects;

/**
 * @author jakubkuranda@gmail.com
 * Date: 16.01.2019
 * Time: 11:05
 */

class AdminPage {

	/** @var bool */
	private $_wasRegistered = false;

	/** @var string */
	private $_slug = '';

	/** @var string */
	private $_parentSlug = '';

	/** @var string */
	private $_pageTitle = '';

	/** @var string */
	private $_menuTitle = '';

	/** @var int */
	private $_order = 10;

	/** @var string */
	private $_capability = '';

	/** @var string */
	private $_icon = '';

	/**
	 * AdminPage constructor.
	 *
	 * @param string $slug
	 */
	public function __construct( $slug ) {

		$this->setSlug( $slug );

	}

	/**
	 * @return string
	 */
	public function getSlug() {

		return $this->_slug;

	}

	/**
	 * @param string $slug
	 *
	 * @return void
	 */
	public function setSlug( $slug ) {

		$this->_slug = $slug;

	}

	/**
	 * @return string
	 */
	public function getParentSlug() {

		return $this->_parentSlug;

	}

	/**
	 * @param $parentSlug
	 *
	 * @return void
	 */
	public function setParentSlug( $parentSlug ) {

		$this->_parentSlug = $parentSlug;

	}

	/**
	 * @return string
	 */
	public function getPageTitle() {

		return $this->_pageTitle;

	}

	/**
	 * @param string $pageTitle
	 *
	 * @return void
	 */
	public function setPageTitle( $pageTitle ) {

		$this->_pageTitle = $pageTitle;

	}

	/**
	 * @return string
	 */
	public function getMenuTitle() {

		return $this->_menuTitle;

	}

	/**
	 * @param string $menuTitle
	 *
	 * @return void
	 */
	public function setMenuTitle( $menuTitle ) {

		$this->_menuTitle = $menuTitle;

	}

	/**
	 * @return string
	 */
	public function getOrder() {

		return $this->_order;

	}

	/**
	 * @param int $order
	 *
	 * @return void
	 */
	public function setOrder( $order ) {

		$this->_order = $order;

	}

	/**
	 * @return string
	 */
	public function getCapability() {

		return $this->_capability ?: 'manage_options';

	}

	/**
	 * @param string $capability
	 *
	 * @return void
	 */
	public function setCapability( $capability ) {

		$this->_capability = $capability;

	}

	/**
	 * @return string
	 */
	public function getIcon() {

		return $this->_icon;

	}

	/**
	 * @param string $icon
	 *
	 * @return void
	 */
	public function setIcon( $icon ) {

		$this->_icon = $icon;

	}

	/**
	 * This method should be called once.
	 * Developers must not use it.
	 * Should be called on admin_init hook.
	 *
	 * @return void
	 */
	public function register() {

		if( $this->_wasRegistered ) return;    //  Bail early. We do not need copies.

		if( $this->getParentSlug() ){

			add_submenu_page(
				$this->getParentSlug(),
				$this->getPageTitle() ?: $this->getMenuTitle(),
				$this->getMenuTitle() ?: $this->getPageTitle(),
				$this->getCapability(),
				$this->getSlug(),
				array( $this, '_a_adminPageDisplay' )
			);

		} else {

			add_menu_page(
				$this->getPageTitle() ?: $this->getMenuTitle(),
				$this->getMenuTitle() ?: $this->getPageTitle(),
				$this->getCapability(),
				$this->getSlug(),
				array( $this, '_a_adminPageDisplay' ),
				$this->getIcon(),
				$this->getOrder()
			);

		}

		$this->_wasRegistered = true;

	}

	//  ================================================================================
	//  ACTIONS
	//  ================================================================================

	/**
	 * This is admin page display callback.
	 *
	 * @internal
	 *
	 * @return void
	 */
	public function _a_adminPageDisplay() {

		echo 'test';

	}

}
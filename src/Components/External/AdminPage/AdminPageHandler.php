<?php
namespace shellpress\v1_3_4\src\Components\External\AdminPage;

/**
 * @author jakubkuranda@gmail.com
 * Date: 16.01.2019
 * Time: 10:55
 */

use shellpress\v1_3_4\src\Components\External\AdminPage\Objects\AdminPage;
use shellpress\v1_3_4\src\Shared\Components\IComponent;

class AdminPageHandler extends IComponent {

	/** @var bool */
	private $_wasInitialized = false;

	/** @var AdminPage[] */
	private $_adminPages = array();

	/**
	 * Called on creation of component.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		$this->init();  //  Init

	}

	/**
	 * Registers admin pages.
	 *
	 * @return void
	 */
	public function init() {

		if( $this->_wasInitialized ) return;    //  Bail early.

		//  ----------------------------------------
		//  Register AdminPages
		//  ----------------------------------------

		foreach( $this->getPages() as $adminPage ){
			$adminPage->register();
		}

		$this->_wasInitialized = true;  //  Mark component as initialized

	}

	/**
	 * @param string $slug
	 */
	public function addPage( $slug ) {

		$adminPage = new AdminPage( $slug );

		$this->_adminPages[] = &$adminPage;

		return $adminPage;

	}

	/**
	 * Returns all AdminPages registered by this instance of ShellPress.
	 *
	 * @return AdminPage[]
	 */
	public function getPages() {

		return (array) $this->_adminPages;

	}

}
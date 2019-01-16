<?php
namespace shellpress\v1_3_4\demo;

/**
 * Date: 15.01.2019
 * Time: 21:40
 */

use shellpress\v1_3_4\ShellPress;

class App extends ShellPress {

	/**
	 * Called automatically after core is ready.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		add_action( 'admin_init',       array( $this, '_a_addAdminPages' ) );

	}

	//  ================================================================================
	//  ACTIONS
	//  ================================================================================

	/**
	 * Called on admin_init.
	 */
	public function _a_addAdminPages() {

		$page = $this::s()->adminPage->addPage( $this::s()->getPrefix( '_settings' ) );
		$page->setPageTitle( 'ShellPress Demo' );

	}

}
<?php
namespace shellpress\v1_3_72\demo;

/**
 * Date: 15.01.2019
 * Time: 21:40
 */

use shellpress\v1_3_72\demo\src\Components\UniversalFrontExample;
use shellpress\v1_3_72\ShellPress;

class App extends ShellPress {

	/** @var UniversalFrontExample */
	public $universalFrontExample;

	/**
	 * Called automatically after core is ready.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		$this->universalFrontExample = new UniversalFrontExample( $this );

		//  ----------------------------------------
		//  Filters
		//  ----------------------------------------

		add_filter( 'plugin_action_links_' . $this::s()->getPluginBasename(),   array( $this, '_f_addPluginLink' ) );

	}

	//  ================================================================================
	//  FILTERS
	//  ================================================================================

	public function _f_addPluginLink( $links ) {

		$links[] = $this->universalFrontExample->getDisplay();

		return $links;

	}

}
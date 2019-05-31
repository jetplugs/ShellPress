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

		add_action( 'plugin_row_meta',   array( $this, '_f_addPluginLink' ), 10, 4 );

	}

	//  ================================================================================
	//  FILTERS
	//  ================================================================================

	public function _f_addPluginLink( $plugin_meta, $plugin_file, $plugin_data, $status ) {

		if( strpos( $this::s()->getMainPluginFile(), $plugin_file ) !== false ){

			$plugin_meta[] = $this->universalFrontExample->getDisplay();

		}

		return $plugin_meta;

	}

}